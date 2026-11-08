<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\TenantContext;
use Modules\Core\Support\TenantRules;

class EnrollLoyaltyMemberRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', TenantRules::companyExists('customers')],
            'card_no' => [
                'nullable', 'string', 'max:50',
                Rule::unique('customer_memberships', 'card_no')
                    ->where('company_id', app(TenantContext::class)->companyId())
                    ->ignore($this->input('customer_id'), 'customer_id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'card_no.unique' => 'এই কার্ড নম্বরটি ইতিমধ্যে ব্যবহৃত হয়েছে (This card number is already in use)।',
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->shop_id;
    }
}
