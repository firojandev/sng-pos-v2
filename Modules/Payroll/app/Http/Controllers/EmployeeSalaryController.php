<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Core\Support\TenantRules;
use Modules\Employee\Models\Designation;
use Modules\Employee\Models\Employee;
use Modules\Payroll\Http\Controllers\Concerns\WorksOnPayroll;
use Modules\Payroll\Models\EmployeeLoan;
use Modules\Payroll\Models\EmployeeSalaryItem;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\SalaryComponent;
use Modules\Payroll\Models\SalaryRevision;
use Modules\Payroll\Services\IncomeTax;
use Modules\Payroll\Services\SalaryStructure;

/**
 * An employee's salary: its breakdown, their own component amounts, the
 * salary history, loans and payslips.
 */
class EmployeeSalaryController extends Controller
{
    use WorksOnPayroll;

    public function __construct(private SalaryStructure $structure) {}

    public function show(Employee $employee, IncomeTax $tax): View
    {
        $settings = $this->settings();
        $gross = $this->structure->salaryOn($employee, now());
        $breakdown = $this->structure->breakdown($employee, $gross);
        $taxable = collect($breakdown['lines'])->where('type', 'earning')->where('taxable', true)->sum('amount');

        return view('payroll::salary.show', [
            'employee' => $employee,
            'gross' => $gross,
            'breakdown' => $breakdown,
            'monthlyTax' => $settings->tax_enabled ? $tax->projection($employee, (float) $taxable, $breakdown['basic'], now(), $settings)['monthly'] : 0,
            'components' => SalaryComponent::where('is_active', true)->orderBy('sort_order')->get(),
            'overrides' => EmployeeSalaryItem::where('employee_id', $employee->id)->pluck('amount', 'salary_component_id'),
            'revisions' => SalaryRevision::where('employee_id', $employee->id)->with(['designation:id,name', 'creator:id,name'])->latest('effective_from')->latest('id')->get(),
            'loans' => EmployeeLoan::where('employee_id', $employee->id)->withSum('recoveries', 'amount')->latest('issued_on')->get(),
            'payslips' => Payslip::where('employee_id', $employee->id)->with('run')->latest('id')->limit(24)->get(),
            'designations' => Designation::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function storeRevision(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'effective_from' => ['required', 'date'],
            'new_salary' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(SalaryRevision::TYPES)],
            'designation_id' => ['nullable', 'integer', TenantRules::companyExists('designations')],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->structure->revise($employee, $validated);

        return back()->with('status', 'বেতন পরিবর্তন সংরক্ষিত হয়েছে');
    }

    public function updateStructure(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'amounts' => ['array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $componentIds = SalaryComponent::pluck('id');

        foreach ($validated['amounts'] ?? [] as $componentId => $amount) {
            if (! $componentIds->contains((int) $componentId)) {
                continue;
            }

            if ($amount === null || $amount === '') {
                EmployeeSalaryItem::where('employee_id', $employee->id)->where('salary_component_id', $componentId)->delete();
            } else {
                EmployeeSalaryItem::updateOrCreate(['employee_id' => $employee->id, 'salary_component_id' => $componentId], ['amount' => $amount]);
            }
        }

        return back()->with('status', 'বেতন কাঠামো হালনাগাদ হয়েছে');
    }
}
