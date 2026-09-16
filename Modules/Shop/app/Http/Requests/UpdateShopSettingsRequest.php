<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->shop_id !== null || auth()->user()->isSuperAdmin());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'invoice_footer' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'দোকানের নাম আবশ্যক (Shop name is required)।',
            'email.email' => 'সঠিক ইমেইল অ্যাড্রেস প্রদান করুন (Please provide a valid email address)।',
            'logo.image' => 'লোগো ফাইলটি অবশ্যই একটি বৈধ ছবি হতে হবে (Logo must be a valid image)।',
            'logo.mimes' => 'লোগো শুধুমাত্র JPG, PNG, WEBP, GIF বা SVG ফরম্যাটের হতে পারবে।',
            'logo.max' => 'লোগো ফাইলের সাইজ সর্বোচ্চ ২ মেগাবাইট (2MB) হতে পারবে।',
        ];
    }
}
