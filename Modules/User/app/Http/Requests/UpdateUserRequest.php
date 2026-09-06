<?php

namespace Modules\User\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') instanceof User
            ? $this->route('user')->id
            : (int) $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.$userId],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone,'.$userId],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'pin' => ['nullable', 'digits:4'],
            'role' => ['required', 'string', 'max:255'],
            'regenerate_support_pin' => ['nullable', 'boolean'],
        ];
    }
}
