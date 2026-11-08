<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Support\TenantRules;

class UpdateShopCategoriesRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                TenantRules::catalogExists('categories')->where('type', 'product')->whereNull('parent_id'),
            ],
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->shop_id;
    }
}
