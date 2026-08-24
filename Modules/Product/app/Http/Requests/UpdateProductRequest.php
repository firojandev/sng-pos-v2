<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku,' . $productId],
            'size' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'short_description' => ['nullable', 'string'],
            'alert_qty' => ['required', 'integer', 'min:0'],
            'is_vat' => ['nullable', 'boolean'],
            'vat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:is_vat,1'],
            'status' => ['required', 'in:active,inactive'],
            'has_warranty' => ['nullable', 'boolean'],
            'warranty_duration' => ['nullable', 'integer', 'min:1', 'required_if:has_warranty,1'],
            'warranty_type' => ['nullable', 'in:day,month,year', 'required_if:has_warranty,1'],
            'has_expiry' => ['nullable', 'boolean'],
            'expiry_date' => ['nullable', 'date', 'required_if:has_expiry,1'],
            'is_wholesale' => ['nullable', 'boolean'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0', 'required_if:is_wholesale,1'],
            'wholesale_min_qty' => ['nullable', 'integer', 'min:1', 'required_if:is_wholesale,1'],
            'has_discount' => ['nullable', 'boolean'],
            'discount_type' => ['nullable', 'in:flat,percentage', 'required_if:has_discount,1'],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'required_if:has_discount,1'],
            'has_barcode' => ['nullable', 'boolean'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode,' . $productId, 'required_if:has_barcode,1'],

            'units' => ['required', 'array', 'min:1'],
            'units.*.unit_id' => ['required', 'distinct', 'exists:units,id'],
            'units.*.is_base' => ['nullable', 'boolean'],
            'units.*.conversion_factor' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $units = $this->input('units', []);
            $baseCount = collect($units)->filter(fn ($row) => (bool) ($row['is_base'] ?? false))->count();

            if ($baseCount !== 1) {
                $validator->errors()->add('units', 'ঠিক একটি ইউনিটকে বেস ইউনিট হিসেবে নির্বাচন করতে হবে');
            }

            if ($this->input('discount_type') === 'percentage' && (float) $this->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', 'ছাড়ের হার ১০০% এর বেশি হতে পারবে না');
            }
        });
    }
}
