<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'exists:accounts,id'],
            'to_account_id' => ['required', 'exists:accounts,id', 'different:from_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'charge' => ['nullable', 'numeric', 'min:0'],
            'transfer_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'to_account_id.different' => 'উৎস অ্যাকাউন্ট এবং গন্তব্য অ্যাকাউন্ট একই হতে পারবে না (Source and destination accounts must be different).',
        ];
    }
}
