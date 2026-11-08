<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\TenantContext;

class StoreJournalEntryRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $companyId = app(TenantContext::class)->companyId();

        return [
            'entry_date' => ['required', 'date'],
            'narration' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'shop_id' => ['nullable', 'integer', Rule::exists('shops', 'id')->where('company_id', $companyId)],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.ledger_account_id' => [
                'required', 'integer',
                Rule::exists('ledger_accounts', 'id')->where('company_id', $companyId)->where('is_group', 0)->where('allow_manual_posting', 1),
            ],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.memo' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lines.min' => 'কমপক্ষে দুটি লাইন দিন (Enter at least two lines)।',
            'lines.*.ledger_account_id.required' => 'প্রতিটি লাইনে অ্যাকাউন্ট নির্বাচন করুন (Choose an account on every line)।',
        ];
    }

    public function authorize(): bool
    {
        // A shop login, or a company user in the company workspace.
        return (bool) ($this->user()?->shop_id || $this->user()?->inCompanyWorkspace());
    }
}
