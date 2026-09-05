<?php

namespace Modules\Shop\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $email = $this->input('email');
        $userExists = $email && User::where('email', $email)->exists();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [
                Rule::requiredIf(! $userExists),
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => ['required', 'string', 'max:255', Rule::notIn(['Super Admin'])],
        ];
    }
}
