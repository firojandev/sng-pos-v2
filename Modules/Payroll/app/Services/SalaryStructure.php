<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Employee;
use Modules\Payroll\Models\EmployeeSalaryItem;
use Modules\Payroll\Models\SalaryComponent;
use Modules\Payroll\Models\SalaryRevision;

/**
 * Splits an employee's gross salary into its components and keeps the
 * salary history (joining salary, increments, promotions).
 */
class SalaryStructure
{
    /**
     * The monthly lines for a gross salary: the gross split by percentage
     * (anything left over as "Other Allowance"), then the fixed and
     * basic-based earnings and deductions. An employee's own amount for a
     * component replaces the standard one.
     *
     * @return array{basic: float, lines: list<array{component_id: ?int, code: string, name: string, type: string, amount: float, taxable: bool}>}
     */
    public function breakdown(Employee $employee, float $gross): array
    {
        $components = SalaryComponent::withoutGlobalScopes()
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $overrides = EmployeeSalaryItem::where('employee_id', $employee->id)->pluck('amount', 'salary_component_id');
        $amountOf = fn (SalaryComponent $component, float $standard) => round($overrides->has($component->id) ? (float) $overrides[$component->id] : $standard, 2);

        $lines = [];
        $split = 0.0;

        foreach ($components->filter->splitsGross() as $component) {
            $amount = $amountOf($component, $gross * $component->value / 100);
            $split += $amount;
            $lines[] = $this->line($component, $amount);
        }

        $remainder = round($gross - $split, 2);
        if ($remainder > 0) {
            $lines[] = ['component_id' => null, 'code' => 'OTHER', 'name' => 'অন্যান্য ভাতা (Other Allowance)', 'type' => 'earning', 'amount' => $remainder, 'taxable' => true];
        }

        $basicComponent = $components->firstWhere('is_basic', true);
        $basic = $basicComponent ? (float) (collect($lines)->firstWhere('component_id', $basicComponent->id)['amount'] ?? 0) : 0.0;

        if ($basicComponent && ! $basicComponent->splitsGross()) {
            $basic = $amountOf($basicComponent, $basicComponent->calculation === 'fixed' ? $basicComponent->value : $gross * $basicComponent->value / 100);
            $lines[] = $this->line($basicComponent, $basic);
        }

        foreach ($components->reject(fn ($component) => $component->splitsGross() || $component->is_basic) as $component) {
            $standard = $component->calculation === 'fixed' ? $component->value : ($component->calculation === 'percent_of_basic' ? $basic : $gross) * $component->value / 100;
            $amount = $amountOf($component, $standard);

            if ($amount > 0) {
                $lines[] = $this->line($component, $amount);
            }
        }

        return ['basic' => round($basic, 2), 'lines' => array_values(array_filter($lines, fn ($line) => $line['amount'] > 0))];
    }

    /**
     * The gross salary in force on a date.
     */
    public function salaryOn(Employee $employee, Carbon $date): float
    {
        $revisions = SalaryRevision::withoutGlobalScopes()->where('employee_id', $employee->id)->orderBy('effective_from')->orderBy('id')->get();

        if ($revisions->isEmpty()) {
            return (float) $employee->salary;
        }

        $inForce = $revisions->filter(fn ($revision) => $revision->effective_from->lte($date))->last();

        return (float) ($inForce ? $inForce->new_salary : ($revisions->first()->previous_salary ?: $employee->salary));
    }

    /**
     * The salary for each part of a pay period, following the company's
     * policy for a change inside the month.
     *
     * @return list<array{salary: float, from: Carbon, to: Carbon, days: int}>
     */
    public function segments(Employee $employee, Carbon $from, Carbon $to, string $policy = 'prorated'): array
    {
        $segment = fn (float $salary, Carbon $start, Carbon $end) => ['salary' => $salary, 'from' => $start->copy(), 'to' => $end->copy(), 'days' => (int) $start->diffInDays($end) + 1];

        if ($policy === 'full_month') {
            return [$segment($this->salaryOn($employee, $to), $from, $to)];
        }

        if ($policy === 'next_month') {
            return [$segment($this->salaryOn($employee, $from), $from, $to)];
        }

        $changes = SalaryRevision::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereDate('effective_from', '>', $from->toDateString())
            ->whereDate('effective_from', '<=', $to->toDateString())
            ->orderBy('effective_from')
            ->pluck('effective_from')
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->unique(fn ($date) => $date->toDateString())
            ->values();

        $segments = [];
        $start = $from->copy();

        foreach ($changes as $changeDate) {
            $segments[] = $segment($this->salaryOn($employee, $start), $start, $changeDate->copy()->subDay());
            $start = $changeDate->copy();
        }

        $segments[] = $segment($this->salaryOn($employee, $start), $start, $to);

        return $segments;
    }

    /**
     * Record a salary change. The employee's current salary follows the
     * latest revision already in force.
     *
     * @param  array{effective_from: string, new_salary: float|string, type: string, designation_id?: ?int, note?: ?string}  $data
     */
    public function revise(Employee $employee, array $data): SalaryRevision
    {
        return DB::transaction(function () use ($employee, $data) {
            $effectiveFrom = Carbon::parse($data['effective_from'])->startOfDay();

            $revision = SalaryRevision::withoutGlobalScopes()->create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'effective_from' => $effectiveFrom->toDateString(),
                'previous_salary' => $this->salaryOn($employee, $effectiveFrom->copy()->subDay()),
                'new_salary' => $data['new_salary'],
                'type' => $data['type'],
                'designation_id' => $data['designation_id'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            if ($effectiveFrom->lte(now())) {
                $employee->salary = $this->salaryOn($employee, now());

                if (! empty($data['designation_id'])) {
                    $employee->designation_id = $data['designation_id'];
                }

                $employee->save();
            }

            return $revision;
        });
    }

    /**
     * @return array{component_id: int, code: string, name: string, type: string, amount: float, taxable: bool}
     */
    private function line(SalaryComponent $component, float $amount): array
    {
        return [
            'component_id' => $component->id,
            'code' => $component->code,
            'name' => $component->name,
            'type' => $component->type,
            'amount' => round($amount, 2),
            'taxable' => $component->is_taxable,
        ];
    }
}
