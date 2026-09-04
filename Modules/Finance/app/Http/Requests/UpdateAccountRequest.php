<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Finance\Models\Account;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->route('account');
        if ($account instanceof Account && $account->isCash()) {
            return false;
        }

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
            'type.in' => 'ক্যাশ অ্যাকাউন্ট সম্পাদনা করা যাবে না। কেবল ব্যাংক ও মোবাইল ব্যাংকিং (MFS) অ্যাকাউন্ট সমর্থিত।',
        ];
    }
}
