<?php

namespace Modules\FinanceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecurityMoneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['nullable', 'exists:accounts,id'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['paid', 'received'])],
            'note' => ['nullable', 'string'],
        ];
    }
}
