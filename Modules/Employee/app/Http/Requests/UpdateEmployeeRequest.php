<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Employee\Models\Employee;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeParam = $this->route('employee');
        $employeeId = $employeeParam instanceof Employee ? $employeeParam->id : $employeeParam;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:employees,phone,'.$employeeId],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email,'.$employeeId],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'salary' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
