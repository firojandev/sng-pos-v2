<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Support\BanglaNumber;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone') && $this->phone !== null) {
            $this->merge([
                'phone' => BanglaNumber::toEn(trim($this->phone)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin' => ['nullable', 'digits:4'],
            'role' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'পুরো নাম আবশ্যক (Full name is required)।',
            'phone.required' => 'ফোন নম্বর আবশ্যক (Phone number is required)।',
            'phone.unique' => 'এই ফোন নম্বরটি ইতিমধ্যে ব্যবহৃত হয়েছে (This phone number is already registered)।',
            'email.email' => 'সঠিক ইমেইল অ্যাড্রেস প্রদান করুন (Please provide a valid email address)।',
            'email.unique' => 'এই ইমেইল অ্যাড্রেসটি ইতিমধ্যে ব্যবহৃত হয়েছে (This email is already registered)।',
            'password.required' => 'পাসওয়ার্ড আবশ্যক (Password is required)।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে (Password must be at least 8 characters)।',
            'password.confirmed' => 'পাসওয়ার্ড নিশ্চিতকরণ মিলছে না (Password confirmation does not match)।',
            'role.required' => 'ইউজার রোল নির্বাচন করুন (User role is required)।',
        ];
    }
}
