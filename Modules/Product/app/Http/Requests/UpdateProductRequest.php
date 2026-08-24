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

            'units' => ['required', 'array', 'min:1'],
            'units.*.unit_id' => ['required', 'distinct', 'exists:units,id'],
            'units.*.is_base' => ['nullable', 'boolean'],
            'units.*.conversion_factor' => ['required', 'numeric', 'min:0.0001'],
            'units.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'units.*.sale_price' => ['required', 'numeric', 'min:0'],
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
        });
    }
}
