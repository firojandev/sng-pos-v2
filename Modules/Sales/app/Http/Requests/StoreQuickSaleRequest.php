<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuickSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $paymentType = $this->input('payment_type');
        if (! $paymentType) {
            $method = (string) $this->input('payment_method', '');
            if ($method === 'ব্যাংক' || $method === 'মোবাইল ব্যাংকিং') {
                $paymentType = 'bank';
            } elseif ($method === 'উভয় (ক্যাশ + ব্যাংক)' || $method === 'both') {
                $paymentType = 'both';
            } else {
                $paymentType = 'cash';
            }
            $this->merge(['payment_type' => $paymentType]);
        }

        if ($paymentType === 'both') {
            $cash = (float) $this->input('cash_amount', 0);
            $bank = (float) $this->input('bank_amount', 0);
            $total = round($cash + $bank, 2);
            if ($total > 0 || empty($this->input('amount'))) {
                $this->merge(['amount' => $total]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sale_date' => ['nullable', 'date'],
            'payment_type' => ['nullable', 'in:cash,bank,both'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'profit' => ['nullable', 'numeric'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('payment_type') === 'both') {
                $cash = (float) $this->input('cash_amount', 0);
                $bank = (float) $this->input('bank_amount', 0);
                if ($cash <= 0 && $bank <= 0) {
                    $validator->errors()->add('cash_amount', 'ক্যাশ অথবা ব্যাংক প্রদানের পরিমাণ লিখুন / Enter cash or bank payment amount.');
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'টাকার পরিমাণ লিখুন / Amount is required.',
            'amount.min' => 'টাকার পরিমাণ শূন্যের বেশি হতে হবে / Amount must be greater than 0.',
            'account_id.exists' => 'নির্বাচিত অ্যাকাউন্টটি সঠিক নয় / Selected account is invalid.',
        ];
    }
}
