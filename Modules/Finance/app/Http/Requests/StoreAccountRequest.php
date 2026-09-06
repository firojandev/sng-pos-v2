<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,mfs'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'mfs_provider' => ['nullable', 'string', 'max:100'],
            'mfs_type' => ['nullable', 'in:personal,merchant,agent'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.in' => 'ক্যাশ অ্যাকাউন্ট তৈরি করা যাবে না। কেবল ব্যাংক ও মোবাইল ব্যাংকিং (MFS) অ্যাকাউন্ট তৈরি করা সম্ভব।',
        ];
    }
}
