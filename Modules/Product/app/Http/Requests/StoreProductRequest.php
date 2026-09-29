<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Support\TenantRules;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', TenantRules::uniqueInShopCatalogue('sku')],
            'size' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'category_id' => ['required', TenantRules::catalogExists('categories')],
            'sub_category_id' => ['nullable', TenantRules::catalogExists('categories')],
            'brand_id' => ['nullable', TenantRules::catalogExists('brands')],
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
            'barcode' => ['nullable', 'string', 'max:255', TenantRules::uniqueInShopCatalogue('barcode'), 'required_if:has_barcode,1'],

            'units' => ['required', 'array', 'min:1'],
            'units.*.unit_id' => ['required', 'distinct', TenantRules::catalogExists('units')],
            'units.*.is_base' => ['nullable', 'boolean'],
            'units.*.conversion_factor' => ['required', 'numeric', 'min:0.0001'],
            'units.*.is_smaller_unit' => ['nullable', 'boolean'],
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

            // A shop adds products only in the categories it sells.
            $shopCategoryIds = $this->user()?->shop?->categories()->pluck('categories.id')->all() ?? [];
            if ($shopCategoryIds !== [] && $this->filled('category_id') && ! in_array((int) $this->input('category_id'), $shopCategoryIds, true)) {
                $validator->errors()->add('category_id', 'এই দোকান এই ক্যাটাগরির পণ্য বিক্রি করে না (This shop doesn\'t sell this category)।');
            }

            if ($this->input('discount_type') === 'percentage' && (float) $this->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', 'ছাড়ের হার ১০০% এর বেশি হতে পারবে না');
            }
        });
    }
}
