<?php

namespace Modules\User\Http\Requests;

use App\Models\User;
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
        /** @var User|null $user */
        $user = auth()->user();
        $userId = (int) ($user?->id ?? 0);
        $isOwner = (bool) $user?->isShopOwner();

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.$userId],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => $isOwner ? ['nullable', 'string'] : ['nullable', 'string', 'max:30', 'unique:users,phone,'.$userId],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'pin' => ['nullable', 'digits:4'],
            'regenerate_support_pin' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
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
            'avatar.image' => 'প্রোফাইল ছবিটি একটি বৈধ ছবি ফাইল হতে হবে।',
            'avatar.mimes' => 'ছবিটি অবশ্যই jpeg, png, jpg, webp বা gif ফরম্যাটের হতে হবে।',
            'avatar.max' => 'ছবির সাইজ সর্বোচ্চ ২ মেগাবাইট (2MB) হতে পারবে।',
        ];
    }
}
