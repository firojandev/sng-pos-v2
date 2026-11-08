<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Employee\Models\Employee;
use Modules\Payroll\Models\EmployeeTaxDeclaration;
use Modules\Payroll\Models\PayrollSetting;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\TaxYear;

/**
 * Income tax on salary, from the company's tax year rules:
 *
 * taxable income (salary less the exempt share) → slab tax above the
 * category's threshold → less the investment rebate → at least the
 * location's minimum tax → annual tax → monthly deduction.
 *
 * The monthly deduction spreads what is still owed for the year (the tax on
 * the salary paid so far plus the salary still to come, less the tax already
 * deducted) over the months left, so it corrects itself after a raise, a
 * bonus or a new investment declaration.
 */
class IncomeTax
{
    public function yearFor(int $companyId, Carbon $date): ?TaxYear
    {
        return TaxYear::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereDate('starts_on', '<=', $date->toDateString())
            ->whereDate('ends_on', '>=', $date->toDateString())
            ->orderByDesc('starts_on')
            ->first();
    }

    /**
     * The employee's threshold category: set on the profile, or women, third
     * gender and those 65 or over at the start of the year.
     */
    public function categoryOf(Employee $employee, Carbon $yearStart): string
    {
        if ($employee->tax_category && array_key_exists($employee->tax_category, config('payroll.tax_categories', []))) {
            return $employee->tax_category;
        }

        if (in_array($employee->gender, ['female', 'other'], true) || ($employee->date_of_birth && $employee->date_of_birth->diffInYears($yearStart) >= 65)) {
            return 'female_senior';
        }

        return 'general';
    }

    public function locationOf(Employee $employee, ?PayrollSetting $settings = null): string
    {
        $settings ??= PayrollSetting::forCompany($employee->company_id);

        return $employee->tax_location ?: ($settings->default_tax_location ?: 'dhaka_chattogram');
    }

    /**
     * @return array{income: float, exemption: float, taxable_income: float, threshold: float, slab_tax: float, rebate: float, minimum_tax: float, annual_tax: float}
     */
    public function assess(TaxYear $year, float $annualIncome, string $category = 'general', string $location = 'dhaka_chattogram', float $eligibleInvestment = 0): array
    {
        $exemption = round(min($annualIncome * $year->exemption_percent / 100, $year->exemption_cap > 0 ? $year->exemption_cap : PHP_FLOAT_MAX), 2);
        $taxable = round(max($annualIncome - $exemption, 0), 2);
        $threshold = $year->threshold($category);
        $result = ['income' => round($annualIncome, 2), 'exemption' => $exemption, 'taxable_income' => $taxable, 'threshold' => $threshold, 'slab_tax' => 0.0, 'rebate' => 0.0, 'minimum_tax' => 0.0, 'annual_tax' => 0.0];

        if ($taxable <= $threshold) {
            return $result;
        }

        $remaining = $taxable - $threshold;
        $slabTax = 0.0;

        foreach ($year->slabs ?? [] as [$width, $rate]) {
            $portion = $width === null ? $remaining : min($remaining, (float) $width);
            $slabTax += $portion * (float) $rate / 100;
            $remaining -= $portion;

            if ($remaining <= 0) {
                break;
            }
        }

        $rebate = min(
            $taxable * $year->rebate_income_percent / 100,
            $eligibleInvestment * $year->rebate_investment_percent / 100,
            $year->rebate_cap > 0 ? $year->rebate_cap : PHP_FLOAT_MAX,
            $slabTax,
        );
        $minimum = $year->minimumTax($location);

        return array_merge($result, [
            'slab_tax' => round($slabTax, 2),
            'rebate' => round(max($rebate, 0), 2),
            'minimum_tax' => $minimum,
            'annual_tax' => round(max($slabTax - max($rebate, 0), $minimum), 2),
        ]);
    }

    public function annualTax(TaxYear $year, float $annualIncome, string $category = 'general', string $location = 'dhaka_chattogram', float $eligibleInvestment = 0): float
    {
        return $this->assess($year, $annualIncome, $category, $location, $eligibleInvestment)['annual_tax'];
    }

    public function declaration(Employee $employee, TaxYear $year): EmployeeTaxDeclaration
    {
        return EmployeeTaxDeclaration::withoutGlobalScopes()->firstOrNew(
            ['employee_id' => $employee->id, 'tax_year_id' => $year->id],
            ['company_id' => $employee->company_id],
        );
    }

    /**
     * Taxable salary paid and tax deducted in the year before a month, from
     * approved payrolls.
     *
     * @return array{income: float, tax: float, bonuses: int}
     */
    public function yearToDate(Employee $employee, TaxYear $year, Carbon $before): array
    {
        $payslips = Payslip::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereHas('run', fn ($query) => $query->withoutGlobalScopes()
                ->where('status', 'approved')
                ->whereDate('month', '>=', $year->starts_on->toDateString())
                ->whereDate('month', '<', $before->copy()->startOfMonth()->toDateString()))
            ->with('run:id,type')
            ->get(['id', 'payroll_run_id', 'taxable_income', 'tax']);

        return [
            'income' => round((float) $payslips->sum('taxable_income'), 2),
            'tax' => round((float) $payslips->sum('tax'), 2),
            'bonuses' => $payslips->filter(fn ($payslip) => $payslip->run?->type === 'bonus')->count(),
        ];
    }

    /**
     * The year's projected tax and this month's deduction.
     *
     * @return array{year: ?TaxYear, category: string, location: string, eligible_investment: float, ytd_income: float, ytd_tax: float, projected_income: float, remaining_months: int, assessment: ?array<string, float>, monthly: float}
     */
    public function projection(Employee $employee, float $monthlyTaxable, float $basic, Carbon $month, ?PayrollSetting $settings = null): array
    {
        $settings ??= PayrollSetting::forCompany($employee->company_id);
        $month = $month->copy()->startOfMonth();
        $year = $this->yearFor($employee->company_id, $month);
        $location = $this->locationOf($employee, $settings);
        $empty = ['year' => $year, 'category' => 'general', 'location' => $location, 'eligible_investment' => 0.0, 'ytd_income' => 0.0, 'ytd_tax' => 0.0, 'projected_income' => 0.0, 'remaining_months' => 0, 'assessment' => null, 'monthly' => 0.0];

        if (! $year) {
            return $empty;
        }

        $category = $this->categoryOf($employee, $year->starts_on);
        $eligible = (float) $this->declaration($employee, $year)->eligible_investment;
        $ytd = $this->yearToDate($employee, $year, $month);
        $remaining = max(($year->ends_on->year * 12 + $year->ends_on->month) - ($month->year * 12 + $month->month) + 1, 1);
        $bonusesLeft = max($settings->festival_bonuses_per_year - $ytd['bonuses'], 0);
        $projected = $ytd['income'] + $monthlyTaxable * $remaining + $bonusesLeft * $basic * $settings->festival_bonus_percent / 100;
        $assessment = $this->assess($year, $projected, $category, $location, $eligible);

        return [
            'year' => $year,
            'category' => $category,
            'location' => $location,
            'eligible_investment' => $eligible,
            'ytd_income' => $ytd['income'],
            'ytd_tax' => $ytd['tax'],
            'projected_income' => round($projected, 2),
            'remaining_months' => $remaining,
            'assessment' => $assessment,
            'monthly' => round(max($assessment['annual_tax'] - $ytd['tax'], 0) / $remaining, 0),
        ];
    }

    /**
     * Close an employee's year on what was actually paid: the final tax,
     * the tax deducted and the difference still owed (+) or to refund (−).
     */
    public function closeYear(Employee $employee, TaxYear $year): EmployeeTaxDeclaration
    {
        $declaration = $this->declaration($employee, $year);
        $ytd = $this->yearToDate($employee, $year, $year->ends_on->copy()->addDay());
        $assessment = $this->assess($year, $ytd['income'], $this->categoryOf($employee, $year->starts_on), $this->locationOf($employee), (float) $declaration->eligible_investment);

        $declaration->fill([
            'company_id' => $employee->company_id,
            'calculated_rebate' => $assessment['rebate'],
            'taxable_income' => $assessment['taxable_income'],
            'annual_tax' => $assessment['annual_tax'],
            'tax_deducted' => $ytd['tax'],
            'year_end_adjustment' => round($assessment['annual_tax'] - $ytd['tax'], 2),
            'closed_at' => now(),
            'closed_by' => Auth::id(),
        ])->save();

        return $declaration;
    }
}
