<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

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
        ];
    }
}
