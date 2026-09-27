<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\TenantContext;
use Modules\Core\Support\TenantRules;
use Modules\Employee\Http\Controllers\EmployeeProfileController;
use Modules\Employee\Models\Employee;
use Modules\Shop\Models\Shop;

class UpdateEmployeeProfileRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $companyId = app(TenantContext::class)->companyId();

        return [
            'employee_code' => ['required', 'string', 'max:30', Rule::unique('employees', 'employee_code')->where('company_id', $companyId)->ignore($employee)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('employees', 'phone')->ignore($employee)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee)],
            'shop_id' => ['required', 'integer', Rule::in(Shop::where('company_id', $companyId)->pluck('id')->all())],
            'department_id' => ['nullable', 'integer', TenantRules::companyExists('departments')],
            'designation_id' => ['nullable', 'integer', TenantRules::companyExists('designations')],
            'shift_id' => ['nullable', 'integer', TenantRules::companyExists('shifts')],
            'employment_type' => ['required', 'in:permanent,probation,contract,casual'],
            'status' => ['required', Rule::in(Employee::STATUSES)],
            'salary' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['nullable', 'date'],
            'confirmation_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'nid' => ['nullable', 'string', 'max:30'],
            'tin' => ['nullable', 'string', 'max:30'],
            'tax_category' => ['nullable', Rule::in(array_keys(config('payroll.tax_categories', [])))],
            'tax_location' => ['nullable', Rule::in(array_keys(config('payroll.tax_locations', [])))],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'address' => ['nullable', 'string', 'max:255'],
            'permanent_address' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'mfs_number' => ['nullable', 'string', 'max:20'],
            'user_id' => ['nullable', 'integer', Rule::in(EmployeeProfileController::linkableUsers((int) $companyId, $employee)->pluck('id')->all())],
            'device_user_id' => ['nullable', 'string', 'max:30', Rule::unique('employees', 'device_user_id')->where('company_id', $companyId)->ignore($employee)],
            'separation_date' => ['nullable', 'date', 'required_if:status,resigned,terminated'],
            'separation_reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->shop_id;
    }
}
