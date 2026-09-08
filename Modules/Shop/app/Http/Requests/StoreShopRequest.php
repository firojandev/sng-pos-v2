<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('admin_phone') && $this->filled('phone')) {
            $this->merge(['admin_phone' => $this->input('phone')]);
        }
    }

    public function rules(): array
    {
        $isExisting = $this->input('owner_type') === 'existing';

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:shops,slug'],
            'store_code' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:shops,store_code'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],

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
            'admin_phone' => [
                Rule::requiredIf(! $isExisting),
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'phone'),
            ],
            'admin_username' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username'),
            ],
            'admin_email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'admin_password' => [
                Rule::requiredIf(! $isExisting),
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'subscription_status' => ['nullable', 'string', 'in:active,trialing,trial,past_due,suspended,cancelled,expired'],
            'current_period_start' => ['nullable', 'date'],
            'current_period_end' => ['nullable', 'date'],
            'trial_ends_at' => ['nullable', 'date'],

            'cash_account_name' => ['nullable', 'string', 'max:255'],
            'cash_opening_balance' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cash_opening_balance.min' => 'ক্যাশ অ্যাকাউন্টের প্রারম্ভিক ব্যালেন্স ০ বা তার বেশি হতে হবে।',
            'admin_phone.required' => 'মালিকের ফোন নম্বর প্রদান করা আবশ্যক।',
            'admin_phone.unique' => 'এই ফোন নম্বরটি ইতিমধ্যে অন্য একজন ব্যবহারকারী ব্যবহার করছেন।',
            'admin_username.unique' => 'এই ইউজারনেমটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
            'admin_username.alpha_dash' => 'ইউজারনেমে শুধুমাত্র অক্ষর, সংখ্যা, ড্যাশ ও আন্ডারস্কোর ব্যবহার করা যাবে।',
            'admin_email.email' => 'সঠিক ইমেইল অ্যাড্রেস প্রদান করুন।',
            'admin_email.unique' => 'এই ইমেইলটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
            'admin_password.required' => 'মালিক অ্যাকাউন্টের জন্য পাসওয়ার্ড প্রদান আবশ্যক।',
            'admin_password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'admin_password.confirmed' => 'পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।',
        ];
    }
}
