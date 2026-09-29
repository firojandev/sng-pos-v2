<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Support\TenantRules;

class StoreProductModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', TenantRules::catalogExists('brands')],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
