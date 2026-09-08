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
        $targetUser = $this->route('user');
        $userModel = $targetUser instanceof User ? $targetUser : User::find($targetUser);
        $userId = $userModel ? $userModel->id : (int) $targetUser;
        $isOwner = (bool) $userModel?->isShopOwner();

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.$userId],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => $isOwner ? ['nullable', 'string'] : ['nullable', 'string', 'max:30', 'unique:users,phone,'.$userId],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'pin' => ['nullable', 'digits:4'],
            'role' => ['required', 'string', 'max:255'],
            'regenerate_support_pin' => ['nullable', 'boolean'],
        ];
    }
}
