<?php

namespace Modules\Purchase\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\PaymentMethods;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'supplier_phone' => ['nullable', 'string', 'max:30'],
            'supplier_address' => ['nullable', 'string', 'max:255'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purchase_date' => ['required', 'date'],
            'invoice_no' => ['nullable', 'string', 'max:255', Rule::unique('purchases', 'invoice_no')->ignore($this->route('purchase')->id)],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0'],
            'employee_name' => ['nullable', 'string', 'max:255'],
            'employee_phone' => ['nullable', 'string', 'max:30'],
            'payments' => ['nullable', 'array'],
            'payments.*.method' => ['required', 'in:'.implode(',', PaymentMethods::keys())],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_no' => ['nullable', 'string', 'max:255'],
            'items.*.barcode' => ['nullable', 'string', 'max:64'],
            'items.*.mfg_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['nullable', 'date', 'after_or_equal:items.*.mfg_date'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
