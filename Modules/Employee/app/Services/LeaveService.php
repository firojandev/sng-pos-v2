<?php

namespace Modules\Employee\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;

/**
 * Leave days, balances and decisions.
 *
 * A yearly leave type allows days_per_year per calendar year. Earned leave
 * accrues one day per `earn_one_day_per` days actually worked. With carry
 * forward, unused days move into the next year following the type's
 * policy (maximum carried, optional expiry).
 */
class LeaveService
{
    public function __construct(private AttendanceService $attendance) {}

    /**
     * Leave days in a range: weekends and holidays aren't counted.
     */
    public function countDays(Employee $employee, Carbon $from, Carbon $to): int
    {
        $shift = $employee->effectiveShift();
        $days = 0;

        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            if (! ($shift?->isWeekend($day) ?? false) && ! $this->attendance->isHoliday($employee, $day)) {
                $days++;
            }
        }

        return $days;
    }

    /**
     * A leave type's balance for a year: what the year gives (days_per_year,
     * or one day per N days worked), plus what is carried from last year
     * (up to the type's maximum) less any carried days that expired unused.
     *
     * @return array{entitled: int, used: int, available: int, carried: int, expired: int}
     */
    public function balance(Employee $employee, LeaveType $type, ?int $year = null, ?Carbon $asOf = null): array
    {
        $year ??= now()->year;
        $asOf ??= now();

        if ($type->gender && $employee->gender !== $type->gender) {
            return ['entitled' => 0, 'used' => 0, 'available' => 0, 'carried' => 0, 'expired' => 0];
        }

        $carried = $type->carry_forward ? $this->carriedInto($employee, $type, $year) : 0;
        $expired = $this->expiredCarry($employee, $type, $year, $carried, $asOf);
        $entitled = $this->yearAllowance($employee, $type, $year) + $carried - $expired;
        $used = $this->usedIn($employee, $type, $year);

        return ['entitled' => $entitled, 'used' => $used, 'available' => max($entitled - $used, 0), 'carried' => $carried, 'expired' => $expired];
    }

    /**
     * Days carried into a year: each earlier year's unused days (from the
     * first year the employee worked), capped at the type's maximum.
     */
    private function carriedInto(Employee $employee, LeaveType $type, int $year): int
    {
        $firstAttendance = Attendance::withoutGlobalScopes()->where('employee_id', $employee->id)->min('date');
        $firstYear = min($employee->joining_date?->year ?? $year, $firstAttendance ? Carbon::parse($firstAttendance)->year : $year);
        $carried = 0;

        for ($previous = max($firstYear, $year - 30); $previous < $year; $previous++) {
            $expired = $this->expiredCarry($employee, $type, $previous, $carried, Carbon::create($previous, 12, 31)->endOfDay());
            $closing = max($carried - $expired + $this->yearAllowance($employee, $type, $previous) - $this->usedIn($employee, $type, $previous), 0);
            $carried = $type->max_carry_forward !== null ? min($closing, $type->max_carry_forward) : $closing;
        }

        return $carried;
    }

    /**
     * Carried days that lapsed: those not used by the expiry date, once it
     * has passed.
     */
    private function expiredCarry(Employee $employee, LeaveType $type, int $year, int $carried, Carbon $asOf): int
    {
        if ($carried <= 0 || ! $type->carry_forward_expires || ! $type->carry_forward_expiry_months) {
            return 0;
        }

        $expiresOn = Carbon::create($year, 1, 1)->addMonths($type->carry_forward_expiry_months);

        if ($asOf->lt($expiresOn)) {
            return 0;
        }

        $usedBeforeExpiry = (int) LeaveRequest::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $type->id)
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->whereDate('from_date', '<', $expiresOn->toDateString())
            ->sum('days');

        return max($carried - $usedBeforeExpiry, 0);
    }

    private function yearAllowance(Employee $employee, LeaveType $type, int $year): int
    {
        if (! $type->isEarned()) {
            return (int) $type->days_per_year;
        }

        $workedDays = Attendance::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->whereYear('date', $year)
            ->count();

        return intdiv($workedDays, (int) $type->earn_one_day_per);
    }

    private function usedIn(Employee $employee, LeaveType $type, int $year): int
    {
        return (int) LeaveRequest::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $type->id)
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->sum('days');
    }

    /**
     * File a leave application (by an admin for an employee, or by the
     * employee themself), with an optional supporting document.
     *
     * @param  array{leave_type_id: int|string, from_date: string, to_date: string, reason?: ?string}  $data
     */
    public function apply(Employee $employee, array $data, ?UploadedFile $attachment = null, bool $selfApplied = false): LeaveRequest
    {
        $type = LeaveType::withoutGlobalScopes()->where('company_id', $employee->company_id)->where('is_active', true)->findOrFail($data['leave_type_id']);
        $from = Carbon::parse($data['from_date'])->startOfDay();
        $to = Carbon::parse($data['to_date'])->startOfDay();

        if ($type->gender && $employee->gender !== $type->gender) {
            throw ValidationException::withMessages(['leave_type_id' => 'এই ছুটি এই কর্মচারীর জন্য প্রযোজ্য নয় (This leave type does not apply to this employee)।']);
        }

        $days = $this->countDays($employee, $from, $to);
        if ($days === 0) {
            throw ValidationException::withMessages(['to_date' => 'নির্বাচিত দিনগুলো সাপ্তাহিক বা সরকারি ছুটি (The chosen days are all weekends or holidays)।']);
        }

        $overlaps = LeaveRequest::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('from_date', '<=', $to->toDateString())
            ->whereDate('to_date', '>=', $from->toDateString())
            ->exists();
        if ($overlaps) {
            throw ValidationException::withMessages(['from_date' => 'এই দিনগুলোতে ইতিমধ্যে ছুটির আবেদন আছে (There is already a leave application for these days)।']);
        }

        return LeaveRequest::withoutGlobalScopes()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'attachment_path' => $attachment?->store("leave-attachments/{$employee->company_id}", LeaveRequest::ATTACHMENT_DISK),
            'attachment_name' => $attachment?->getClientOriginalName(),
            'is_self_applied' => $selfApplied,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);
    }

    public function approve(LeaveRequest $request, ?string $note = null): void
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'শুধুমাত্র অপেক্ষমাণ আবেদন অনুমোদন করা যায় (Only pending requests can be approved)।']);
        }

        $employee = $request->employee;
        $type = $request->leaveType;

        if ($type->is_paid) {
            $available = $this->balance($employee, $type, $request->from_date->year)['available'];

            if ($request->days > $available) {
                throw ValidationException::withMessages(['days' => "ছুটির পর্যাপ্ত ব্যালেন্স নেই (Only {$available} days available)।"]);
            }
        }

        DB::transaction(function () use ($request, $note, $employee) {
            $request->update(['status' => 'approved', 'decided_by' => Auth::id(), 'decided_at' => now(), 'decision_note' => $note]);
            $this->refreshAttendance($employee, $request);
        });
    }

    public function reject(LeaveRequest $request, ?string $note = null): void
    {
        $request->update(['status' => 'rejected', 'decided_by' => Auth::id(), 'decided_at' => now(), 'decision_note' => $note]);
    }

    /**
     * Cancelling approved leave turns those days back into ordinary days.
     */
    public function cancel(LeaveRequest $request): void
    {
        $wasApproved = $request->status === 'approved';
        $request->update(['status' => 'cancelled', 'decided_by' => Auth::id(), 'decided_at' => now()]);

        if ($wasApproved) {
            $this->refreshAttendance($request->employee, $request);
        }
    }

    private function refreshAttendance(Employee $employee, LeaveRequest $request): void
    {
        $until = $request->to_date->copy()->min(now());

        for ($day = $request->from_date->copy(); $day->lte($until); $day->addDay()) {
            Attendance::withoutGlobalScopes()->where('employee_id', $employee->id)->whereDate('date', $day->toDateString())->update(['is_manual' => false]);
            $this->attendance->process($employee, $day);
        }
    }
}
