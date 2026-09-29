<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\Holiday;
use Modules\Employee\Models\LeaveRequest;
use Modules\Payroll\Models\EmployeeLoan;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\PayrollSetting;

/**
 * Works out one employee's payslip for a run.
 *
 * Salary: the structure's earnings for the days employed in the month (a
 * salary change inside the month follows the company's policy), plus
 * overtime (hours × basic ÷ 208 × 2); less absent and unpaid-leave days (a
 * day's basic, basic ÷ 30), provident fund, income tax, loan installments
 * and any manual deduction.
 *
 * Festival bonus: a share of the basic for staff with enough service.
 */
class PayrollCalculator
{
    public function __construct(private SalaryStructure $structure, private IncomeTax $tax) {}

    /**
     * The payslip values and lines, or null when the employee has nothing to
     * be paid in this run (not employed that month, no salary, not eligible).
     *
     * @param  array{other_addition?: float, other_deduction?: float, adjustment_note?: ?string}  $adjustments
     * @return array{attributes: array<string, mixed>, items: list<array<string, mixed>>}|null
     */
    public function calculate(PayrollRun $run, Employee $employee, PayrollSetting $settings, array $adjustments = []): ?array
    {
        $monthStart = $run->month->copy()->startOfMonth();
        $monthEnd = $run->month->copy()->endOfMonth()->startOfDay();
        $from = $employee->joining_date && $employee->joining_date->gt($monthStart) ? $employee->joining_date->copy()->startOfDay() : $monthStart->copy();
        $to = $employee->separation_date && $employee->separation_date->lt($monthEnd) ? $employee->separation_date->copy()->startOfDay() : $monthEnd->copy();

        if ($from->gt($to)) {
            return null;
        }

        $segments = array_values(array_filter(
            $run->isBonus() ? [['salary' => $this->structure->salaryOn($employee, $to), 'days' => 0]] : $this->structure->segments($employee, $from, $to, $settings->salary_change_policy),
            fn (array $segment) => $segment['salary'] > 0,
        ));

        if ($segments === []) {
            return null;
        }

        $gross = (float) end($segments)['salary'];
        $breakdown = $this->structure->breakdown($employee, $gross);
        $basic = $breakdown['basic'];
        $nonTaxable = collect($breakdown['lines'])->reject(fn ($line) => $line['taxable'])->pluck('code')->all();

        $attributes = [
            'employee_code' => $employee->employee_code,
            'employee_name' => $employee->name,
            'designation' => $employee->designation,
            'salary' => $gross,
            'basic' => $basic,
            'days_in_month' => $monthStart->daysInMonth,
            'payable_days' => (int) $from->diffInDays($to) + 1,
            'other_addition' => round((float) ($adjustments['other_addition'] ?? 0), 2),
            'other_deduction' => round((float) ($adjustments['other_deduction'] ?? 0), 2),
            'adjustment_note' => $adjustments['adjustment_note'] ?? null,
            'pf_employee' => 0,
            'pf_employer' => 0,
            'tax' => 0,
        ];

        $items = $run->isBonus()
            ? $this->bonusItems($employee, $settings, $basic, $monthEnd)
            : $this->salaryItems($employee, $settings, $segments, $breakdown, $from, $to, $attributes);

        if ($items === null) {
            return null;
        }

        if ($attributes['other_addition'] > 0) {
            $items[] = $this->item('earning', 'ADD', 'অতিরিক্ত প্রদান (Other Addition)', $attributes['other_addition']);
        }

        $attributes['taxable_income'] = round(
            collect($items)->where('type', 'earning')->reject(fn ($item) => in_array($item['code'], $nonTaxable, true))->sum('amount')
            - collect($items)->whereIn('code', ['ABSENT', 'LATE'])->sum('amount'),
            2,
        );

        if (! $run->isBonus() && $settings->tax_enabled) {
            $monthlyTaxable = collect($breakdown['lines'])->where('type', 'earning')->where('taxable', true)->sum('amount');
            $attributes['tax'] = $this->tax->projection($employee, (float) $monthlyTaxable, $basic, $monthStart, $settings)['monthly'];

            if ($attributes['tax'] > 0) {
                $items[] = $this->item('deduction', 'TAX', 'উৎসে আয়কর (Income Tax)', $attributes['tax']);
            }
        }

        if ($attributes['other_deduction'] > 0) {
            $items[] = $this->item('deduction', 'DED', 'অন্যান্য কর্তন (Other Deduction)', $attributes['other_deduction']);
        }

        if (! $run->isBonus()) {
            $items = array_merge($items, $this->loanItems($employee, $monthEnd, $items));
        }

        foreach ($items as $index => &$item) {
            $item['sort_order'] = $index + 1;
        }
        unset($item);

        $earnings = round(collect($items)->where('type', 'earning')->sum('amount'), 2);
        $deductions = round(collect($items)->where('type', 'deduction')->sum('amount'), 2);

        $attributes['earnings_total'] = $earnings;
        $attributes['deductions_total'] = $deductions;
        $attributes['net_pay'] = round($earnings - $deductions, 2);

        return ['attributes' => $attributes, 'items' => $items];
    }

    /**
     * @param  list<array{salary: float, days: int}>  $segments
     * @param  array{basic: float, lines: list<array<string, mixed>>}  $breakdown  the structure at the period's latest salary
     * @param  array<string, mixed>  $attributes
     * @return list<array<string, mixed>>
     */
    private function salaryItems(Employee $employee, PayrollSetting $settings, array $segments, array $breakdown, Carbon $from, Carbon $to, array &$attributes): array
    {
        $basic = $breakdown['basic'];
        $earned = [];
        $earnedBasic = 0.0;

        foreach ($segments as $segment) {
            $share = $segment['days'] / $attributes['days_in_month'];
            $segmentBreakdown = $this->structure->breakdown($employee, $segment['salary']);
            $earnedBasic += $segmentBreakdown['basic'] * $share;

            foreach ($segmentBreakdown['lines'] as $line) {
                $earned[$line['code']] ??= $this->item($line['type'], $line['code'], $line['name'], 0);
                $earned[$line['code']]['amount'] += $line['amount'] * $share;
            }
        }

        $items = array_values(array_map(fn (array $item) => ['amount' => round($item['amount'], 2)] + $item, $earned));

        $days = $this->attendanceSummary($employee, $from, $to);
        $attributes = array_merge($attributes, $days);

        if ($settings->overtime_enabled && $days['overtime_minutes'] > 0 && $settings->overtime_hours_base > 0) {
            $hours = $days['overtime_minutes'] / 60;
            $items[] = $this->item('earning', 'OT', sprintf('ওভারটাইম (Overtime) %.1f h', $hours), $hours * $basic / $settings->overtime_hours_base * $settings->overtime_multiplier);
        }

        $earnedTotal = collect($items)->where('type', 'earning')->sum('amount');

        if ($settings->attendance_deductions) {
            $perDay = ($settings->absence_deduction_basis === 'gross' ? (float) $attributes['salary'] : $basic) / 30;
            $absentDays = $days['absent_days'] + $days['unpaid_leave_days'];
            $lateDays = $settings->late_days_per_deduction ? intdiv($days['late_days'], $settings->late_days_per_deduction) : 0;

            if ($absentDays > 0) {
                $items[] = $this->item('deduction', 'ABSENT', 'অনুপস্থিতি (Absence) '.$this->days($absentDays).' দিন', min($absentDays * $perDay, $earnedTotal));
            }
            if ($lateDays > 0) {
                $items[] = $this->item('deduction', 'LATE', "বিলম্ব (Late) {$days['late_days']} দিন", min($lateDays * $perDay, $earnedTotal));
            }
        }

        $monthsOfService = $employee->joining_date ? (int) $employee->joining_date->diffInMonths($to) : PHP_INT_MAX;

        if ($settings->pf_enabled && $monthsOfService >= $settings->pf_eligible_after_months) {
            $attributes['pf_employee'] = round($earnedBasic * $settings->pf_employee_percent / 100, 2);
            $attributes['pf_employer'] = round($earnedBasic * $settings->pf_employer_percent / 100, 2);

            if ($attributes['pf_employee'] > 0) {
                $items[] = $this->item('deduction', 'PF', 'প্রভিডেন্ট ফান্ড (Provident Fund)', $attributes['pf_employee']);
            }
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function bonusItems(Employee $employee, PayrollSetting $settings, float $basic, Carbon $asOf): ?array
    {
        $months = $employee->joining_date ? (int) $employee->joining_date->diffInMonths($asOf) : PHP_INT_MAX;

        if ($months < $settings->festival_bonus_min_months || $settings->festival_bonus_percent <= 0) {
            return null;
        }

        return [$this->item('earning', 'BONUS', 'উৎসব ভাতা (Festival Bonus)', $basic * $settings->festival_bonus_percent / 100)];
    }

    /**
     * This month's installment of each running loan, as far as the pay left
     * after the other deductions allows.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function loanItems(Employee $employee, Carbon $monthEnd, array $items): array
    {
        $available = collect($items)->where('type', 'earning')->sum('amount') - collect($items)->where('type', 'deduction')->sum('amount');

        $loans = EmployeeLoan::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->whereDate('deduct_from', '<=', $monthEnd->toDateString())
            ->orderBy('issued_on')
            ->get();

        $lines = [];
        foreach ($loans as $loan) {
            $amount = round(min((float) $loan->installment, $loan->balance(), max($available, 0)), 2);

            if ($amount <= 0) {
                continue;
            }

            $available -= $amount;
            $label = $loan->type === 'loan' ? 'ঋণ কিস্তি (Loan Installment)' : 'অগ্রিম সমন্বয় (Advance Recovery)';
            $lines[] = ['employee_loan_id' => $loan->id] + $this->item('deduction', 'LOAN', $label, $amount);
        }

        return $lines;
    }

    /**
     * Each calendar day from the later of joining and the period start to
     * the period end (days still to come are not counted):
     *
     * - holiday or weekly off → not counted;
     * - approved leave → leave (paid or unpaid by its type);
     * - attendance recorded → present 1, half day 0.5 (the other 0.5 absent);
     * - no attendance → absent.
     *
     * Overtime is the sum recorded on any day, off days included.
     *
     * @return array{present_days: float, absent_days: float, paid_leave_days: float, unpaid_leave_days: float, late_days: int, overtime_minutes: int}
     */
    private function attendanceSummary(Employee $employee, Carbon $from, Carbon $to): array
    {
        $records = Attendance::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get()
            ->keyBy(fn ($record) => $record->date->toDateString());

        $holidays = Holiday::withoutGlobalScopes()
            ->where('company_id', $employee->company_id)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->pluck('date')
            ->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true]);

        $leave = $this->approvedLeaveDates($employee, $from, $to);
        $shift = $employee->effectiveShift();
        $summary = ['present_days' => 0.0, 'absent_days' => 0.0, 'paid_leave_days' => 0.0, 'unpaid_leave_days' => 0.0, 'late_days' => 0, 'overtime_minutes' => (int) $records->sum('overtime_minutes')];
        $lastDay = $to->copy()->min(now()->startOfDay());

        for ($day = $from->copy(); $day->lte($lastDay); $day->addDay()) {
            $date = $day->toDateString();
            $record = $records->get($date);

            if ($holidays->has($date) || ($shift?->isWeekend($day) ?? false)) {
                continue;
            }

            if (array_key_exists($date, $leave)) {
                $summary[$leave[$date] ? 'paid_leave_days' : 'unpaid_leave_days'] += 1;

                continue;
            }

            match ($record?->status) {
                'present' => $summary['present_days'] += 1,
                'late' => [$summary['present_days'] += 1, $summary['late_days'] += 1],
                'half_day' => [$summary['present_days'] += 0.5, $summary['absent_days'] += 0.5],
                'leave' => $summary['paid_leave_days'] += 1,
                default => $summary['absent_days'] += 1,
            };
        }

        return $summary;
    }

    /**
     * @return array<string, bool> date => whether the leave is paid
     */
    private function approvedLeaveDates(Employee $employee, Carbon $from, Carbon $to): array
    {
        $dates = [];

        $requests = LeaveRequest::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $to->toDateString())
            ->whereDate('to_date', '>=', $from->toDateString())
            ->with(['leaveType' => fn ($query) => $query->withoutGlobalScopes()])
            ->get();

        foreach ($requests as $request) {
            for ($day = $request->from_date->copy(); $day->lte($request->to_date); $day->addDay()) {
                $dates[$day->toDateString()] = (bool) ($request->leaveType?->is_paid ?? true);
            }
        }

        return $dates;
    }

    private function days(float $days): string
    {
        return rtrim(rtrim(number_format($days, 1), '0'), '.');
    }

    /**
     * @return array{type: string, code: string, name: string, amount: float, employee_loan_id: ?int}
     */
    private function item(string $type, string $code, string $name, float $amount): array
    {
        return ['type' => $type, 'code' => $code, 'name' => $name, 'amount' => round($amount, 2), 'employee_loan_id' => null];
    }
}
