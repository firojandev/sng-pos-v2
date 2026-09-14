<?php

namespace Modules\FinanceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['nullable', 'exists:accounts,id'],
            'lender_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['unpaid', 'paid'])],
            'note' => ['nullable', 'string'],
        ];
    }
}
