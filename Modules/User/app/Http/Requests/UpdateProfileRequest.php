<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = (int) auth()->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.$userId],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone,'.$userId],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'pin' => ['nullable', 'digits:4'],
            'regenerate_support_pin' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।',
            'current_password.required_with' => 'নতুন পাসওয়ার্ড সেট করতে বর্তমান পাসওয়ার্ড প্রদান করুন।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।',
            'pin.digits' => 'পিন অবশ্যই ৪ ডিজিটের সংখ্যা হতে হবে।',
            'username.unique' => 'এই ইউজারনেমটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
            'email.unique' => 'এই ইমেইল অ্যাড্রেসটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
            'phone.unique' => 'এই ফোন নম্বরটি ইতিমধ্যে ব্যবহৃত হয়েছে।',
        ];
    }
}
