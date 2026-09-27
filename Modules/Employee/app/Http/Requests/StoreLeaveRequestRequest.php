<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Support\TenantRules;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', TenantRules::companyExists('employees')],
            'leave_type_id' => ['required', 'integer', TenantRules::companyExists('leave_types')],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:5120', 'extensions:pdf,jpg,jpeg,png,webp,doc,docx'],
        ];
    }

    public function authorize(): bool
    {
        // A shop login, or a company user in the company workspace.
        return (bool) ($this->user()?->shop_id || $this->user()?->inCompanyWorkspace());
    }
}
