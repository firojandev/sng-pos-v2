<?php

namespace Modules\Company\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Company profile update, shared by the super-admin company screen and the
 * owner's company settings page. Only a super admin may change the status.
 */
class UpdateCompanyRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'trade_license_no' => ['nullable', 'string', 'max:100'],
            'tin' => ['nullable', 'string', 'max:50'],
            'bin' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'fiscal_year_start_month' => ['required', 'integer', 'between:1,12'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'কোম্পানির নাম আবশ্যক (Company name is required)।',
            'email.email' => 'সঠিক ইমেইল অ্যাড্রেস প্রদান করুন (Please provide a valid email address)।',
            'fiscal_year_start_month.between' => 'অর্থবছরের শুরুর মাস সঠিক নয় (Invalid fiscal year start month)।',
            'currency.size' => 'মুদ্রার কোড ৩ অক্ষরের হতে হবে, যেমন BDT (Currency must be a 3-letter code)।',
        ];
    }

    /**
     * Normalise the currency code before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('currency')) {
            $this->merge(['currency' => strtoupper(trim((string) $this->input('currency')))]);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user();
    }
}
