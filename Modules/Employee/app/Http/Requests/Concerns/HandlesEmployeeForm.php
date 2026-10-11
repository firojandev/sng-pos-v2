<?php

namespace Modules\Employee\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Core\Support\BanglaNumber;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Models\Employee;
use Modules\Employee\Services\EmployeeLogin;

/**
 * What the quick add and edit forms share: the designation and department
 * dropdowns (or a new name typed in), and the "create a login" option.
 */
trait HandlesEmployeeForm
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->filled('phone')) {
            $merge['phone'] = BanglaNumber::toEn(trim((string) $this->input('phone')));
        }

        // "Add new" in a dropdown: the name typed beside it is used.
        foreach (['designation_id', 'department_id'] as $key) {
            if ($this->has($key) && ! is_numeric($this->input($key))) {
                $merge[$key] = null;
            }
        }

        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    protected function namedRecordRules(): array
    {
        $companyId = app(TenantContext::class)->companyId();

        return [
            'designation_id' => ['nullable', 'integer', Rule::exists('designations', 'id')->where('company_id', $companyId)],
            'designation' => ['nullable', 'required_without:designation_id', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'department' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * With an email the password may be left blank: the employee sets their
     * own from an emailed link.
     *
     * @return array<string, mixed>
     */
    protected function loginRules(): array
    {
        $login = app(EmployeeLogin::class);

        return [
            'create_user' => ['nullable', 'boolean'],
            'user_role' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('create_user') && $login->roleRequired()),
                Rule::in(array_keys($login->roleOptions())),
            ],
            'user_password' => ['nullable', Rule::requiredIf(fn () => $this->boolean('create_user') && ! $this->filled('email')), 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'designation.required_without' => 'পদবি নির্বাচন করুন (Designation is required)।',
            'user_role.required' => 'ইউজার রোল নির্বাচন করুন (User role is required)।',
            'user_password.required' => 'ইমেইল না থাকলে পাসওয়ার্ড দিতে হবে (Without an email, a password is required)।',
            'user_password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে (Password must be at least 8 characters)।',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('create_user') || $validator->errors()->isNotEmpty()) {
                return;
            }

            $login = app(EmployeeLogin::class);
            $employee = $this->route('employee');

            if (! $login->allowed()) {
                $validator->errors()->add('create_user', 'ইউজার তৈরির অনুমতি আপনার নেই (You are not allowed to create users)।');
            } elseif ($employee instanceof Employee && $employee->user_id) {
                $validator->errors()->add('create_user', 'এই কর্মচারীর লগইন অ্যাকাউন্ট আগে থেকেই আছে (This employee already has a login account)।');
            } elseif ($blocker = $login->blocker($this->input('phone'), $this->input('email'))) {
                $validator->errors()->add('create_user', $blocker.' প্রয়োজনে নিচের তালিকা থেকে সেটি সংযুক্ত করুন (link it from the list below instead)।');
            }
        });
    }

    /**
     * The employee's own columns: the dropdown choice wins over a typed
     * name, and a new login replaces any chosen one.
     *
     * @return array<string, mixed>
     */
    public function employeeData(): array
    {
        $data = collect($this->validated())->except(['create_user', 'user_role', 'user_password'])->all();

        foreach (['designation', 'department'] as $name) {
            if (! empty($data[$name.'_id'])) {
                unset($data[$name]);
            } elseif (array_key_exists($name.'_id', $data)) {
                unset($data[$name.'_id']);
                $data[$name] ??= null;
            }
        }

        if ($this->boolean('create_user')) {
            unset($data['user_id']);
        }

        return $data;
    }
}
