<?php

namespace Modules\Employee\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\AttendanceLog;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\Holiday;
use Modules\Employee\Models\LeaveRequest;

/**
 * Works out each day's attendance from the punches (entered by hand or from
 * a device), the employee's shift, holidays and approved leave.
 *
 * A day's record is a shift instance: the shift that starts on that date.
 * Its punches are those inside the shift's window, from halfway through the
 * off-hours before the shift to halfway through those after it, so an
 * 8 PM–4 AM shift's 7:55 PM IN and next-morning 4:05 AM OUT are one shift.
 */
class AttendanceService
{
    /**
     * Record a day by hand: in/out times become manual punches; a status on
     * its own (e.g. absent) is set directly.
     */
    public function recordManual(Employee $employee, Carbon $date, ?string $checkIn, ?string $checkOut, ?string $status = null, ?string $note = null): Attendance
    {
        return DB::transaction(function () use ($employee, $date, $checkIn, $checkOut, $status, $note) {
            $date = $date->copy()->startOfDay();

            AttendanceLog::withoutGlobalScopes()
                ->where('employee_id', $employee->id)
                ->where('source', 'manual')
                ->whereBetween('punched_at', $this->shiftWindow($employee, $date))
                ->delete();

            foreach (array_filter($this->manualPunchTimes($employee, $date, $checkIn, $checkOut)) as $punchedAt) {
                // The same time already punched (by device or clock-in) isn't added twice.
                $alreadyPunched = AttendanceLog::withoutGlobalScopes()
                    ->where('employee_id', $employee->id)
                    ->whereBetween('punched_at', [$punchedAt->copy()->startOfMinute(), $punchedAt->copy()->endOfMinute()])
                    ->exists();

                if ($alreadyPunched) {
                    continue;
                }

                AttendanceLog::withoutGlobalScopes()->create([
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'shop_id' => $employee->shop_id,
                    'punched_at' => $punchedAt,
                    'shift_date' => $date->toDateString(),
                    'source' => 'manual',
                    'note' => $note,
                    'created_by' => Auth::id(),
                ]);
            }

            if (! $checkIn && ! $checkOut && $status) {
                return $this->saveDay($employee, $date, $this->blankDay($employee) + ['status' => $status, 'is_manual' => true, 'note' => $note]);
            }

            return $this->process($employee, $date, true, $note);
        });
    }

    /**
     * Where the employee stands in the current shift: not started, working
     * (clocked in) or done (clocked out), with the shift day and punches.
     *
     * @return array{state: string, shift_date: Carbon, punches: Collection<int, Carbon>, clocked_in_at: ?Carbon, clocked_out_at: ?Carbon}
     */
    public function clockState(Employee $employee, ?Carbon $at = null): array
    {
        $at ??= now();
        $shiftDate = $this->shiftDateFor($employee, $at);
        $punches = $this->punchesOn($employee, $shiftDate)->map(fn (AttendanceLog $log) => $log->punched_at->copy())->filter(fn (Carbon $punch) => $punch->lte($at))->values();

        return [
            'state' => match (true) {
                $punches->isEmpty() => 'not_started',
                $punches->count() % 2 === 1 => 'working',
                default => 'done',
            },
            'shift_date' => $shiftDate,
            'punches' => $punches,
            'clocked_in_at' => $punches->first(),
            'clocked_out_at' => $punches->count() > 1 && $punches->count() % 2 === 0 ? $punches->last() : null,
        ];
    }

    /**
     * Clock in or out now (from the app), then work out the shift's day.
     * Clocking in again after clocking out (back from a break) is allowed.
     */
    public function clock(Employee $employee, string $direction, ?string $ipAddress = null, string $source = 'web'): Attendance
    {
        $now = now()->startOfMinute();
        $state = $this->clockState($employee, $now)['state'];

        if ($direction === 'in' && $state === 'working') {
            throw ValidationException::withMessages(['clock' => 'আপনি ইতিমধ্যে ক্লক ইন করেছেন (You are already clocked in)।']);
        }

        if ($direction === 'out' && $state !== 'working') {
            throw ValidationException::withMessages(['clock' => 'আগে ক্লক ইন করুন (Clock in first)।']);
        }

        return DB::transaction(function () use ($employee, $now, $ipAddress, $source) {
            $shiftDate = $this->shiftDateFor($employee, $now);

            AttendanceLog::withoutGlobalScopes()->create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'shop_id' => $employee->shop_id,
                'punched_at' => $now,
                'shift_date' => $shiftDate->toDateString(),
                'source' => $source,
                'ip_address' => $ipAddress,
                'created_by' => Auth::id(),
            ]);

            return $this->process($employee, $shiftDate);
        });
    }

    /**
     * Work out one employee's day. A day set by hand (without punches) is
     * left as it is unless it's being re-entered.
     */
    public function process(Employee $employee, Carbon $date, bool $manual = false, ?string $note = null): Attendance
    {
        $date = $date->copy()->startOfDay();
        $existing = Attendance::withoutGlobalScopes()->where('employee_id', $employee->id)->whereDate('date', $date->toDateString())->first();
        $logs = $this->punchesOn($employee, $date);
        $punches = $logs->map(fn (AttendanceLog $log) => $log->punched_at->copy());

        if ($logs->isNotEmpty()) {
            AttendanceLog::withoutGlobalScopes()->whereIn('id', $logs->pluck('id'))->update(['shift_date' => $date->toDateString()]);
        }

        if ($existing?->is_manual && $punches->isEmpty() && ! $manual) {
            return $existing;
        }

        $shift = $employee->effectiveShift();
        $values = $this->blankDay($employee) + ['shift_id' => $shift?->id, 'is_manual' => $manual || (bool) $existing?->is_manual, 'note' => $note ?? $existing?->note];

        if ($punches->isNotEmpty()) {
            $checkIn = $punches->first();
            $checkOut = $punches->count() > 1 ? $punches->last() : null;
            $worked = $checkOut ? (int) $checkIn->diffInMinutes($checkOut) : 0;
            $late = $shift ? max(0, (int) $shift->startsOn($date)->addMinutes($shift->grace_minutes)->diffInMinutes($checkIn, false)) : 0;
            $earlyLeave = $shift && $checkOut ? max(0, (int) $checkOut->diffInMinutes($shift->endsOn($date), false)) : 0;
            $overtime = $shift && $checkOut ? max(0, (int) $shift->endsOn($date)->diffInMinutes($checkOut, false)) : 0;
            $isOffDay = $this->isHoliday($employee, $date) || ($shift?->isWeekend($date) ?? false);

            $status = match (true) {
                $isOffDay => 'present',
                $checkOut && $shift && $worked < $shift->lengthInMinutes() / 2 => 'half_day',
                $late > 0 => 'late',
                default => 'present',
            };

            $values = array_merge($values, [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'worked_minutes' => $worked,
                'late_minutes' => $isOffDay ? 0 : $late,
                'early_leave_minutes' => $isOffDay ? 0 : $earlyLeave,
                // Work on a weekend or holiday is all overtime.
                'overtime_minutes' => $isOffDay ? $worked : $overtime,
                'status' => $status,
            ]);
        } else {
            $values['status'] = match (true) {
                $this->isOnLeave($employee, $date) => 'leave',
                $this->isHoliday($employee, $date) => 'holiday',
                (bool) $shift?->isWeekend($date) => 'weekend',
                default => 'absent',
            };
        }

        return $this->saveDay($employee, $date, $values);
    }

    /**
     * Create or update the employee's record for the day (matched by date,
     * whatever time part the database stores).
     *
     * @param  array<string, mixed>  $values
     */
    private function saveDay(Employee $employee, Carbon $date, array $values): Attendance
    {
        $day = Attendance::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereDate('date', $date->toDateString())
            ->first() ?? new Attendance(['employee_id' => $employee->id, 'date' => $date->toDateString()]);

        $day->fill($values)->save();

        return $day;
    }

    /**
     * Work out the days in a range for every active employee (e.g. after
     * device punches arrive, or to mark absences at the end of a day).
     */
    public function processRange(int $companyId, Carbon $from, Carbon $to, ?int $shopId = null): int
    {
        $count = 0;
        $employees = Employee::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->when($shopId, fn ($query) => $query->where('shop_id', $shopId))
            ->get();

        foreach ($employees as $employee) {
            for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
                if ($employee->joining_date && $day->lt($employee->joining_date)) {
                    continue;
                }

                $this->process($employee, $day);
                $count++;
            }
        }

        return $count;
    }

    /**
     * The time span whose punches belong to the shift starting on a date:
     * from halfway through the off-hours before the shift to halfway through
     * those after it (the calendar day when there is no shift).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function shiftWindow(Employee $employee, Carbon $date): array
    {
        $date = $date->copy()->startOfDay();
        $shift = $employee->effectiveShift();

        if (! $shift) {
            return [$date, $date->copy()->endOfDay()];
        }

        $halfOffHours = intdiv(max(1440 - $shift->lengthInMinutes(), 0), 2);

        return [
            $shift->startsOn($date)->subMinutes($halfOffHours),
            $shift->endsOn($date)->addMinutes($halfOffHours)->subSecond(),
        ];
    }

    /**
     * The day of the shift instance a punch belongs to.
     */
    public function shiftDateFor(Employee $employee, Carbon $punchedAt): Carbon
    {
        $date = $punchedAt->copy()->startOfDay();
        [$from, $to] = $this->shiftWindow($employee, $date);

        return match (true) {
            $punchedAt->lt($from) => $date->subDay(),
            $punchedAt->gt($to) => $date->addDay(),
            default => $date,
        };
    }

    /**
     * Times entered by hand for a shift day: a check-out at or before the
     * check-in (or, alone, before an overnight shift's start) is the next
     * morning.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function manualPunchTimes(Employee $employee, Carbon $date, ?string $checkIn, ?string $checkOut): array
    {
        $in = $checkIn ? $date->copy()->setTimeFromTimeString($checkIn) : null;
        $out = $checkOut ? $date->copy()->setTimeFromTimeString($checkOut) : null;
        $shift = $employee->effectiveShift();

        if ($out && (($in && $out->lte($in)) || (! $in && $shift && $shift->endsOn($date)->isAfter($date->copy()->endOfDay()) && $out->lt($shift->startsOn($date))))) {
            $out->addDay();
        }

        return [$in, $out];
    }

    /**
     * @return Collection<int, AttendanceLog>
     */
    private function punchesOn(Employee $employee, Carbon $date): Collection
    {
        return AttendanceLog::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereBetween('punched_at', $this->shiftWindow($employee, $date))
            ->orderBy('punched_at')
            ->get(['id', 'punched_at']);
    }

    public function isHoliday(Employee $employee, Carbon $date): bool
    {
        return Holiday::withoutGlobalScopes()->where('company_id', $employee->company_id)->whereDate('date', $date->toDateString())->exists();
    }

    private function isOnLeave(Employee $employee, Carbon $date): bool
    {
        return LeaveRequest::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $date->toDateString())
            ->whereDate('to_date', '>=', $date->toDateString())
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function blankDay(Employee $employee): array
    {
        return [
            'company_id' => $employee->company_id,
            'shop_id' => $employee->shop_id,
            'check_in' => null,
            'check_out' => null,
            'worked_minutes' => 0,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 0,
        ];
    }
}
