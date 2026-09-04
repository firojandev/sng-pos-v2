<?php

namespace Modules\Purchase\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePurchaseRemainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'do_number' => ['required', 'string', 'max:255'],
            'do_date' => ['nullable', 'date'],
            'vehicle_number' => ['nullable', 'string', 'max:255'],
            'delivery_person_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
            'items.*.batch_no' => ['nullable', 'string', 'max:255'],
            'items.*.mfg_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['nullable', 'date', 'after_or_equal:items.*.mfg_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'do_number.required' => 'ডিও নম্বর (D.O. Number) প্রদান করা আবশ্যক।',
            'items.required' => 'অন্তত একটি পণ্যের তথ্য প্রদান করুন।',
            'items.*.received_qty.min' => 'গ্রহণের পরিমাণ ঋণাত্মক হতে পারবে না।',
        ];
    }
}
