<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Support\TenantRules;

class StoreExpenseSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required_without:parent_id', 'nullable', TenantRules::catalogExists('categories')],
            'parent_id' => ['required_without:category_id', 'nullable', TenantRules::catalogExists('categories')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
