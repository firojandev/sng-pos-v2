<?php

namespace Modules\Purchase\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePurchaseDeliveryOrderRequest extends FormRequest
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
            'delivery_date' => ['required', 'date'],
            'challan_no' => ['nullable', 'string', 'max:100'],
            'delivery_person_name' => ['nullable', 'string', 'max:255'],
            'delivery_person_phone' => ['nullable', 'string', 'max:30'],
            'vehicle_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:purchase_delivery_order_items,id'],
            'items.*.received_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.batch_no' => ['nullable', 'string', 'max:255'],
            'items.*.mfg_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ];
    }
}
