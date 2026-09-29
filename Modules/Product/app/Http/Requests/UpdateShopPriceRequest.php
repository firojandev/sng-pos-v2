<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The shop's own values for a listed product. Every field is optional:
 * left empty, the company value (then the product's base value) applies.
 */
class UpdateShopPriceRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'alert_qty' => ['nullable', 'integer', 'min:0'],
            'vat_percentage' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->shop_id;
    }
}
