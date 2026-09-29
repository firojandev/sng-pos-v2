<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Payroll\Http\Controllers\Concerns\WorksOnPayroll;
use Modules\Payroll\Models\PayrollSetting;
use Modules\Payroll\Models\SalaryComponent;

/**
 * The company's payroll rules and salary structure.
 */
class PayrollSetupController extends Controller
{
    use WorksOnPayroll;

    public function index(): View
    {
        $settings = $this->settings();
        $components = SalaryComponent::orderBy('sort_order')->orderBy('id')->get();

        return view('payroll::setup.index', [
            'settings' => $settings,
            'components' => $components,
            'grossSplit' => $components->where('is_active', true)->filter->splitsGross()->sum('value'),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'overtime_enabled' => ['required', 'boolean'],
            'overtime_multiplier' => ['required', 'numeric', 'min:0', 'max:10'],
            'overtime_hours_base' => ['required', 'integer', 'min:1', 'max:400'],
            'attendance_deductions' => ['required', 'boolean'],
            'absence_deduction_basis' => ['required', Rule::in(['basic', 'gross'])],
            'late_days_per_deduction' => ['nullable', 'integer', 'min:1', 'max:31'],
            'pf_enabled' => ['required', 'boolean'],
            'pf_employee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'pf_employer_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'pf_eligible_after_months' => ['required', 'integer', 'min:0', 'max:120'],
            'pf_employer_vesting_years' => ['required', 'integer', 'min:0', 'max:50'],
            'salary_change_policy' => ['required', Rule::in(PayrollSetting::SALARY_CHANGE_POLICIES)],
            'tax_enabled' => ['required', 'boolean'],
            'default_tax_location' => ['required', Rule::in(array_keys(config('payroll.tax_locations')))],
            'festival_bonuses_per_year' => ['required', 'integer', 'min:0', 'max:12'],
            'festival_bonus_percent' => ['required', 'numeric', 'min:0', 'max:1000'],
            'festival_bonus_min_months' => ['required', 'integer', 'min:0', 'max:120'],
        ]);

        $this->settings()->update($validated);

        return back()->with('status', 'পে-রোল সেটিংস সংরক্ষিত হয়েছে');
    }

    public function storeComponent(Request $request): RedirectResponse
    {
        $validated = $this->validateComponent($request);
        SalaryComponent::create($validated + ['company_id' => $this->companyId(), 'sort_order' => (int) SalaryComponent::max('sort_order') + 1]);

        return back()->with('status', 'বেতনের অংশ যোগ হয়েছে');
    }

    public function updateComponent(Request $request, SalaryComponent $component): RedirectResponse
    {
        $validated = $this->validateComponent($request, $component);

        DB::transaction(function () use ($component, $validated) {
            if (! empty($validated['is_basic'])) {
                SalaryComponent::whereKeyNot($component->id)->update(['is_basic' => false]);
            }

            $component->update($validated);
        });

        return back()->with('status', 'বেতনের অংশ হালনাগাদ হয়েছে');
    }

    public function destroyComponent(SalaryComponent $component): RedirectResponse
    {
        if ($component->is_basic) {
            return back()->withErrors(['component' => 'মূল বেতনের অংশ মুছে ফেলা যায় না (The basic component can\'t be deleted)।']);
        }

        $component->delete();

        return back()->with('status', 'বেতনের অংশ মুছে ফেলা হয়েছে');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateComponent(Request $request, ?SalaryComponent $component = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('salary_components', 'code')->where('company_id', $this->companyId())->ignore($component)],
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'calculation' => ['required', Rule::in(SalaryComponent::CALCULATIONS)],
            'value' => ['required', 'numeric', 'min:0'],
            'is_basic' => ['sometimes', 'boolean'],
            'is_taxable' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
