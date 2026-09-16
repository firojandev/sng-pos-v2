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
        $statusRules = ['required', 'in:active,trialing,trial,past_due,suspended,cancelled,expired'];

        if ($this->has('subscription_status')) {
            return [
                'plan_id' => ['required', 'exists:plans,id'],
                'subscription_status' => $statusRules,
                'status' => ['nullable'],
                'trial_ends_at' => ['nullable', 'date'],
                'current_period_start' => ['nullable', 'date'],
                'current_period_end' => ['nullable', 'date', 'after_or_equal:current_period_start'],
            ];
        }

        return [
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => $statusRules,
            'trial_ends_at' => ['nullable', 'date'],
            'current_period_start' => ['nullable', 'date'],
            'current_period_end' => ['nullable', 'date', 'after_or_equal:current_period_start'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'plan_id' => 'প্ল্যান',
            'subscription_status' => 'সাবস্ক্রিপশন অবস্থা',
            'status' => 'সাবস্ক্রিপশন অবস্থা',
            'current_period_start' => 'মেয়াদ শুরু',
            'current_period_end' => 'মেয়াদ সমাপ্তি',
            'trial_ends_at' => 'ট্রায়াল সমাপ্তি',
        ];
    }
}
