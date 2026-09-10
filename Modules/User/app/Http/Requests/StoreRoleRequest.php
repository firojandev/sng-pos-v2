<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\Permissions;

class StoreRoleRequest extends FormRequest
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
                Rule::notIn(['Admin', 'admin', 'Super Admin', 'super admin']),
                Rule::unique('roles', 'name')->where('guard_name', 'web')->where('shop_id', auth()->user()->shop_id),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Permissions::all())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.not_in' => 'ডিফল্ট বা সংরক্ষিত নামের রোল তৈরি করা যাবে না।',
        ];
    }
}
