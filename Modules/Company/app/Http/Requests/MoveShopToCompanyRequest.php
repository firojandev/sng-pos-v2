<?php

namespace Modules\Company\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveShopToCompanyRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id')->whereNull('deleted_at')->where('type', 'company'),
                Rule::notIn([$this->route('shop')?->company_id]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'একটি কোম্পানি নির্বাচন করুন (Please select a company)।',
            'company_id.exists' => 'নির্বাচিত কোম্পানিটি পাওয়া যায়নি (The selected company was not found)।',
            'company_id.not_in' => 'দোকানটি ইতিমধ্যে এই কোম্পানির অধীনে রয়েছে (The shop already belongs to this company)।',
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }
}
