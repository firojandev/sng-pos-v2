<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Task\Models\Task;
use Modules\Task\Services\DailyWorkReport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HR → Reporting: the daily work report (attendance and tasks, day by day).
 * Those who manage the company's tasks see every employee; an employee sees
 * and exports their own.
 */
class TaskReportController extends Controller implements HasMiddleware
{
    public function __construct(private DailyWorkReport $report) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                abort_unless(app(DailyWorkReport::class)->isAvailableTo($request->user()), 403, 'এই রিপোর্ট দেখতে আপনার কর্মচারী রেকর্ড থাকতে হবে (You need an employee record to see this report)।');

                return $next($request);
            }),
        ];
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('task::reports.daily-work', [
            ...$filters,
            'employees' => $this->employees(),
            'canManage' => $this->canManage(),
            'rows' => $this->rows($filters),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $spreadsheet = $this->report->spreadsheet($this->rows($filters));

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            'daily-work-report-'.$filters['from']->toDateString().'-to-'.$filters['to']->toDateString().'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    /**
     * This month so far unless dates are given; at most MAX_DAYS days.
     *
     * @return array{from: Carbon, to: Carbon, employeeId: ?int}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'employee_id' => ['nullable', 'integer', Rule::in($this->employees()->keys()->all())],
        ]);

        // An employee always gets their own report.
        if (! $this->canManage()) {
            $validated['employee_id'] = $this->report->ownEmployeeId($this->companyId(), Auth::user());
        }

        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : today();
        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : $to->copy()->startOfMonth();

        if ($from->diffInDays($to) >= DailyWorkReport::MAX_DAYS) {
            $from = $to->copy()->subDays(DailyWorkReport::MAX_DAYS - 1);
        }

        return ['from' => $from, 'to' => $to, 'employeeId' => isset($validated['employee_id']) ? (int) $validated['employee_id'] : null];
    }

    /**
     * @param  array{from: Carbon, to: Carbon, employeeId: ?int}  $filters
     */
    private function rows(array $filters): Collection
    {
        return $this->report->rows($this->companyId(), $this->shopId(), $filters['from'], $filters['to'], $filters['employeeId']);
    }

    /**
     * The employees the user may report on: everyone in scope, or themself.
     *
     * @return Collection<int, string>
     */
    private function employees(): Collection
    {
        $employees = $this->report->employees($this->companyId(), $this->shopId());

        return $this->canManage() ? $employees : $employees->only([$this->report->ownEmployeeId($this->companyId(), Auth::user())]);
    }

    private function canManage(): bool
    {
        return Task::canManage(Auth::user());
    }

    private function companyId(): int
    {
        return (int) Task::companyFor(Auth::user())?->id;
    }

    /**
     * The user's shop when it belongs to the task company (a shop admin
     * reports on their own shop's employees).
     */
    private function shopId(): ?int
    {
        $user = Auth::user();

        // Their own record, wherever in the company they work.
        if (! $this->canManage()) {
            return null;
        }

        return $user->shop_id && (int) $user->shop?->company_id === $this->companyId() ? (int) $user->shop_id : null;
    }
}
