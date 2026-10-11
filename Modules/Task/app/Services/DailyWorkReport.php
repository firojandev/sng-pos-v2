<?php

namespace Modules\Task\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\Task\Models\Task;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * What each employee did day by day: their clock in/out and the tasks of
 * that day. A task belongs to a day when it was created or due that day,
 * finished that day, or was being worked on (picked up and not yet done).
 */
class DailyWorkReport
{
    public const MAX_DAYS = 92;

    /**
     * The employees the report can be run for.
     *
     * @return Collection<int, string> employee id => label
     */
    public function employees(int $companyId, ?int $shopId): Collection
    {
        return $this->employeeQuery($companyId, $shopId)->orderBy('name')->get(['id', 'employee_code', 'name'])
            ->mapWithKeys(fn (Employee $employee) => [$employee->id => trim(($employee->employee_code ? $employee->employee_code.' — ' : '').$employee->name)]);
    }

    /**
     * The user's own employee record in the company: what someone who
     * doesn't manage tasks may report on.
     */
    public function ownEmployeeId(int $companyId, User $user): ?int
    {
        return Employee::withoutGlobalScopes()->where('company_id', $companyId)->where('user_id', $user->id)->value('id');
    }

    /**
     * Managers report on everyone; an employee on themself.
     */
    public function isAvailableTo(?User $user): bool
    {
        if (! $user || ! Task::isAvailableTo($user)) {
            return false;
        }

        return Task::canManage($user) || $this->ownEmployeeId((int) Task::companyFor($user)?->id, $user) !== null;
    }

    /**
     * One row per employee and day with attendance or tasks, by date.
     *
     * @return Collection<int, array{employee_code: ?string, employee_name: string, date: Carbon, check_in: ?Carbon, check_out: ?Carbon, worked_minutes: int, tasks: Collection<int, Task>}>
     */
    public function rows(int $companyId, ?int $shopId, Carbon $from, Carbon $to, ?int $employeeId = null): Collection
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $employees = $this->employeeQuery($companyId, $shopId)
            ->when($employeeId, fn ($query) => $query->whereKey($employeeId))
            ->orderBy('name')
            ->get(['id', 'employee_code', 'name', 'user_id']);

        $attendances = Attendance::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereIn('employee_id', $employees->modelKeys())
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where(fn ($query) => $query->whereNotNull('check_in')->orWhereNotNull('check_out'))
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->employee_id.'|'.$attendance->date->toDateString());

        $tasksByDay = $this->tasksByUserAndDay($companyId, $employees->pluck('user_id')->filter()->all(), $from, $to);
        $rows = collect();

        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $date = $day->toDateString();

            foreach ($employees as $employee) {
                $attendance = $attendances->get($employee->id.'|'.$date);
                $tasks = $tasksByDay[$employee->user_id.'|'.$date] ?? [];

                if (! $attendance && $tasks === []) {
                    continue;
                }

                $rows->push([
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->name,
                    'date' => $day->copy(),
                    'check_in' => $attendance?->check_in,
                    'check_out' => $attendance?->check_out,
                    'worked_minutes' => (int) $attendance?->worked_minutes,
                    'tasks' => collect($tasks)->sortBy(fn (Task $task) => array_search($task->priority, Task::PRIORITIES, true))->values(),
                ]);
            }
        }

        return $rows;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function spreadsheet(Collection $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Daily Work Report');
        $sheet->fromArray(['SL', 'Employee Id', 'Employee Name', 'Date', 'Clock In', 'Clock Out', 'Worked Hours', 'Tasks Details']);

        foreach ($rows->values() as $index => $row) {
            $sheet->fromArray([
                $index + 1,
                $row['employee_code'],
                $row['employee_name'],
                $row['date']->format('d M, Y'),
                $row['check_in']?->format('h:i A'),
                $row['check_out']?->format('h:i A'),
                self::hours($row['worked_minutes']),
                $row['tasks']->map(fn (Task $task) => '• '.self::taskLine($task).self::descriptionLines($task))->implode("\n"),
            ], null, 'A'.($index + 2), true);
        }

        foreach (['A' => 6, 'B' => 14, 'C' => 26, 'D' => 14, 'E' => 12, 'F' => 12, 'G' => 14, 'H' => 90] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F766E');
        $sheet->getStyle('A1:H'.($rows->count() + 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $sheet->freezePane('A2');

        return $spreadsheet;
    }

    public static function hours(int $minutes): string
    {
        return $minutes > 0 ? intdiv($minutes, 60).'h '.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT).'m' : '';
    }

    /**
     * "Count the stock [High] — In Progress — Deadline 12 Oct, 2026"
     */
    public static function taskLine(Task $task): string
    {
        return $task->title
            .' ['.(Task::priorityLabels()[$task->priority]['en'] ?? 'Medium').']'
            .' — '.Task::statusLabels()[$task->status]['en']
            .($task->deadline ? ' — Deadline '.$task->deadline->format('d M, Y') : '');
    }

    /**
     * The description under its task, indented.
     */
    private static function descriptionLines(Task $task): string
    {
        $description = trim((string) $task->description);

        return $description === '' ? '' : "\n   ".preg_replace('/\s*\R\s*/u', "\n   ", $description);
    }

    /**
     * In a shop, that shop's employees; in the company workspace, everyone.
     */
    private function employeeQuery(int $companyId, ?int $shopId)
    {
        return Employee::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->when($shopId, fn ($query) => $query->where('shop_id', $shopId));
    }

    /**
     * @param  list<int>  $userIds
     * @return array<string, list<Task>> "user id|date" => tasks
     */
    private function tasksByUserAndDay(int $companyId, array $userIds, Carbon $from, Carbon $to): array
    {
        $tasks = Task::query()
            ->where('company_id', $companyId)
            ->whereIn('assigned_to', $userIds)
            ->where(fn ($query) => $query
                ->whereBetween('created_at', [$from, $to])
                ->orWhereBetween('deadline', [$from->toDateString(), $to->toDateString()])
                ->orWhereBetween('finished_at', [$from, $to])
                ->orWhere(fn ($working) => $working->whereNotNull('started_at')->where('started_at', '<=', $to)))
            ->orderBy('id')
            ->get();

        $byDay = [];

        foreach ($tasks as $task) {
            foreach ($this->days($task, $from, $to) as $date) {
                $byDay[$task->assigned_to.'|'.$date][] = $task;
            }
        }

        return $byDay;
    }

    /**
     * The days of the period the task belongs to.
     *
     * @return list<string>
     */
    private function days(Task $task, Carbon $from, Carbon $to): array
    {
        $days = [];

        foreach ([$task->created_at, $task->deadline, $task->finished_at] as $moment) {
            if ($moment && $moment->betweenIncluded($from, $to)) {
                $days[$moment->toDateString()] = true;
            }
        }

        // Being worked on: from when it was picked up until it was finished
        // (or set aside), or until today while still in progress.
        if ($task->started_at) {
            $until = match ($task->status) {
                'in_progress' => now(),
                'completed' => $task->finished_at ?? $task->updated_at,
                default => $task->updated_at,
            };

            // max()/min() can hand back $from/$to themselves: copy before stepping.
            $day = $task->started_at->copy()->startOfDay()->max($from)->copy();
            $last = $until->copy()->endOfDay()->min($to)->copy();

            for (; $day->lte($last); $day->addDay()) {
                $days[$day->toDateString()] = true;
            }
        }

        return array_keys($days);
    }
}
