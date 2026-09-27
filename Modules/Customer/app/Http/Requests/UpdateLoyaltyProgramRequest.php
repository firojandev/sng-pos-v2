<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoyaltyProgramRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'is_enabled' => ['boolean'],
            'shop_participates' => ['boolean'],
            'spend_amount' => ['required', 'numeric', 'min:1'],
            'points_per_spend' => ['required', 'integer', 'min:1'],
            'point_value' => ['required', 'numeric', 'min:0.01'],
            'min_redeem_points' => ['required', 'integer', 'min:0'],
            'max_redeem_percent' => ['required', 'integer', 'between:1,100'],
            'points_expire_after_days' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'spend_amount.min' => 'খরচের পরিমাণ কমপক্ষে ৳১ হতে হবে (Spend amount must be at least 1)।',
            'points_per_spend.min' => 'পয়েন্ট কমপক্ষে ১ হতে হবে (Points must be at least 1)।',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_enabled' => $this->boolean('is_enabled'),
            'shop_participates' => $this->boolean('shop_participates'),
        ]);
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->shop_id;
    }
}
