<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterShopOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('shop_phone') && $this->filled('phone')) {
            $this->merge(['shop_phone' => $this->input('phone')]);
        }
    }

    public function rules(): array
    {
        return [
            // Step 1: Owner Details
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],

            // Step 2: Shop Details
            'shop_name' => ['required', 'string', 'max:255'],
            'shop_slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:shops,slug'],
            'shop_phone' => ['nullable', 'string', 'max:30'],
            'shop_address' => ['nullable', 'string', 'max:500'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],

            // Step 3: Setup & Initial Cash
            'branch_name' => ['nullable', 'string', 'max:255'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'opening_cash_balance' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'আপনার নাম প্রদান করা আবশ্যক।',
            'phone.required' => 'মোবাইল নম্বর প্রদান করা আবশ্যক।',
            'phone.unique' => 'এই মোবাইল নম্বরটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
            'email.email' => 'সঠিক ইমেইল ঠিকানা প্রদান করুন।',
            'email.unique' => 'এই ইমেইলটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
            'username.unique' => 'এই ইউজারনেমটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
            'username.alpha_dash' => 'ইউজারনেমে শুধুমাত্র ইংরেজি অক্ষর, সংখ্যা, হাইফেন এবং আন্ডারস্কোর ব্যবহার করুন।',
            'password.required' => 'পাসওয়ার্ড প্রদান করা আবশ্যক।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।',

            'shop_name.required' => 'দোকানের নাম প্রদান করা আবশ্যক।',
            'shop_slug.required' => 'দোকানের স্লাগ (URL) আবশ্যক।',
            'shop_slug.unique' => 'এই স্লাগটি ইতিমধ্যে অন্য কোনো দোকান ব্যবহার করছে। ভিন্ন একটি দিন।',
            'shop_slug.alpha_dash' => 'স্লাগে শুধুমাত্র ইংরেজি অক্ষর, সংখ্যা, ড্যাশ বা আন্ডারস্কোর ব্যবহার করুন।',

            'opening_cash_balance.min' => 'প্রারম্ভিক ক্যাশ ব্যালেন্স ০ বা তার বেশি হতে হবে।',
        ];
    }
}
