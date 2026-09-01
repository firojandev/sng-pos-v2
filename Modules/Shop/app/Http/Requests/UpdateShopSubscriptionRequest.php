<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', 'in:trial,active,past_due,suspended,cancelled'],
            'trial_ends_at' => ['nullable', 'date'],
            'current_period_start' => ['nullable', 'date'],
            'current_period_end' => ['nullable', 'date', 'after_or_equal:current_period_start'],
        ];
    }
}
