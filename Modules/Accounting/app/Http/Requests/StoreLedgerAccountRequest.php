<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\TenantContext;

class StoreLedgerAccountRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $companyId = app(TenantContext::class)->companyId();

        return [
            'parent_id' => ['required', 'integer', Rule::exists('ledger_accounts', 'id')->where('company_id', $companyId)->where('is_group', 1)],
            'code' => ['required', 'string', 'max:20', Rule::unique('ledger_accounts', 'code')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'is_group' => ['boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_group' => $this->boolean('is_group')]);
    }

    public function authorize(): bool
    {
        // A shop login, or a company user in the company workspace.
        return (bool) ($this->user()?->shop_id || $this->user()?->inCompanyWorkspace());
    }
}
