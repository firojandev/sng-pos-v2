<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Employee\Models\Employee;
use Modules\Payroll\Http\Controllers\Concerns\WorksOnPayroll;
use Modules\Payroll\Models\TaxYear;
use Modules\Payroll\Services\IncomeTax;
use Modules\Payroll\Services\SalaryStructure;

/**
 * Income tax: the company's tax years (thresholds, slabs, rebate and
 * minimum tax rules) and each employee's yearly investment declaration,
 * projected tax and year-end adjustment.
 */
class TaxSetupController extends Controller
{
    use WorksOnPayroll;

    public function __construct(private IncomeTax $tax) {}

    public function index(Request $request, SalaryStructure $structure): View
    {
        $settings = $this->settings();
        $taxYears = TaxYear::orderByDesc('starts_on')->get();
        $year = $taxYears->firstWhere('id', $request->integer('year')) ?? $this->tax->yearFor($this->companyId(), now()) ?? $taxYears->first();

        $rows = collect();
        if ($year) {
            $month = now()->copy()->max($year->starts_on)->min($year->ends_on)->startOfMonth();

            $rows = Employee::workingAtShop()
                ->where(fn ($query) => $query->where('status', 'active')->orWhereDate('separation_date', '>=', $year->starts_on->toDateString()))
                ->orderBy('name')
                ->get()
                ->map(function (Employee $employee) use ($structure, $settings, $year, $month) {
                    $breakdown = $structure->breakdown($employee, $structure->salaryOn($employee, $month));
                    $monthlyTaxable = collect($breakdown['lines'])->where('type', 'earning')->where('taxable', true)->sum('amount');

                    return [
                        'employee' => $employee,
                        'declaration' => $this->tax->declaration($employee, $year),
                        'projection' => $this->tax->projection($employee, (float) $monthlyTaxable, $breakdown['basic'], $month, $settings),
                    ];
                });
        }

        return view('payroll::tax.index', [
            'taxYears' => $taxYears,
            'year' => $year,
            'rows' => $rows,
            'categories' => config('payroll.tax_categories'),
            'locations' => config('payroll.tax_locations'),
        ]);
    }

    public function saveTaxYear(Request $request, ?TaxYear $taxYear = null): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'assessment_year' => ['nullable', 'string', 'max:20'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'thresholds' => ['required', 'array'],
            'thresholds.general' => ['required', 'numeric', 'min:0'],
            'thresholds.*' => ['nullable', 'numeric', 'min:0'],
            'exemption_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'exemption_cap' => ['required', 'numeric', 'min:0'],
            'minimum_taxes' => ['required', 'array'],
            'minimum_taxes.*' => ['nullable', 'numeric', 'min:0'],
            'rebate_income_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'rebate_investment_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'rebate_cap' => ['required', 'numeric', 'min:0'],
            'slab_widths' => ['required', 'array', 'min:1'],
            'slab_widths.*' => ['nullable', 'numeric', 'gt:0'],
            'slab_rates' => ['required', 'array'],
            'slab_rates.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $slabs = [];
        foreach ($validated['slab_rates'] as $index => $rate) {
            $width = $validated['slab_widths'][$index] ?? null;

            if ($rate === null || $rate === '') {
                continue;
            }

            $slabs[] = [$width === null || $width === '' ? null : (float) $width, (float) $rate];
        }

        // Whatever is above the listed slabs is taxed at the last rate.
        if ($slabs && end($slabs)[0] !== null) {
            $slabs[] = [null, end($slabs)[1]];
        }

        $values = collect($validated)->except(['slab_widths', 'slab_rates'])->all();
        $values['thresholds'] = array_map('floatval', array_filter($validated['thresholds'], fn ($value) => $value !== null && $value !== ''));
        $values['minimum_taxes'] = array_map('floatval', array_filter($validated['minimum_taxes'], fn ($value) => $value !== null && $value !== ''));
        $values['slabs'] = $slabs;

        if ($taxYear?->exists) {
            $taxYear->update($values);
        } else {
            $taxYear = TaxYear::create($values + ['company_id' => $this->companyId()]);
        }

        return redirect()->route('payroll.tax.index', ['year' => $taxYear->id])->with('status', 'আয়কর বছর সংরক্ষিত হয়েছে');
    }

    public function updateDeclaration(Request $request, TaxYear $taxYear, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'declared_investment' => ['nullable', 'numeric', 'min:0'],
            'eligible_investment' => ['nullable', 'numeric', 'min:0'],
            'tax_category' => ['nullable', Rule::in(array_keys(config('payroll.tax_categories')))],
            'tax_location' => ['nullable', Rule::in(array_keys(config('payroll.tax_locations')))],
        ]);

        $declaration = $this->tax->declaration($employee, $taxYear);
        $declaration->fill([
            'company_id' => $employee->company_id,
            'declared_investment' => (float) ($validated['declared_investment'] ?? 0),
            'eligible_investment' => min((float) ($validated['eligible_investment'] ?? 0), (float) ($validated['declared_investment'] ?? 0) ?: PHP_FLOAT_MAX),
        ])->save();

        $employee->update(['tax_category' => $validated['tax_category'] ?? null, 'tax_location' => $validated['tax_location'] ?? null]);

        return back()->with('status', $employee->name.' — আয়কর তথ্য সংরক্ষিত হয়েছে');
    }

    /**
     * Close the year for the shop's staff on the salary actually paid.
     */
    public function closeYear(TaxYear $taxYear): RedirectResponse
    {
        $employees = Employee::workingAtShop()->get();

        foreach ($employees as $employee) {
            $this->tax->closeYear($employee, $taxYear);
        }

        return back()->with('status', "{$employees->count()} জনের বছর শেষের হিসাব হয়েছে");
    }
}
