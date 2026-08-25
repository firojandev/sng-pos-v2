<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\Features;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('shop_id', auth()->user()->shop_id)
                    ->ignore($this->route('role')->id),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Features::keys())],
        ];
    }
}
