<?php

namespace Modules\Shop\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\Features;

class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isExisting = $this->input('owner_type') === 'existing';
        $adminEmail = $this->input('admin_email');
        $userExists = $adminEmail && User::where('email', $adminEmail)->exists();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:shops,slug'],
            'store_code' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:shops,store_code'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'features' => ['nullable', 'array'],
            'features.*' => [Rule::in(Features::keys())],

            'owner_type' => ['nullable', 'in:new,existing'],
            'existing_user_id' => [
                Rule::requiredIf($isExisting),
                'nullable',
                'exists:users,id',
            ],

            'admin_name' => [
                Rule::requiredIf(! $isExisting),
                'nullable',
                'string',
                'max:255',
            ],
            'admin_email' => [
                Rule::requiredIf(! $isExisting),
                'nullable',
                'email',
                'max:255',
            ],
            'admin_password' => [
                Rule::requiredIf(! $isExisting && ! $userExists),
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'admin_role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')->whereNull('shop_id')->whereNot('name', 'Super Admin')],

            'plan_id' => ['nullable', 'exists:plans,id'],
            'subscription_status' => ['nullable', 'string', 'in:active,trialing,trial,past_due,suspended,cancelled,expired'],
            'current_period_start' => ['nullable', 'date'],
            'current_period_end' => ['nullable', 'date'],
            'trial_ends_at' => ['nullable', 'date'],
        ];
    }
}
