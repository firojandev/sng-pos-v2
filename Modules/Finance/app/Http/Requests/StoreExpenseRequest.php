<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Support\TenantRules;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['nullable', 'exists:accounts,id'],
            'expense_category_id' => ['nullable', TenantRules::catalogExists('categories')],
            'expense_sub_category_id' => ['nullable', TenantRules::catalogExists('categories')],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }
}
