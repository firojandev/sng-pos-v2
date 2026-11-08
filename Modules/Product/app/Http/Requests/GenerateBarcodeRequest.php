<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateBarcodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.view')
            && (! $this->boolean('regenerate') || $this->user()->can('products.edit'));
    }

    public function rules(): array
    {
        return [
            'product_ids' => ['required_unless:all_filtered,1', 'array', 'max:500'],
            'product_ids.*' => ['integer', 'distinct'],
            'all_filtered' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'regenerate' => ['nullable', 'boolean'],
            'format' => ['required', 'in:CODE128,EAN13'],
            'width' => ['required', 'numeric', 'min:30', 'max:100'],
            'height' => ['required', 'numeric', 'min:20', 'max:100'],
            'barcode_height' => ['required', 'numeric', 'min:10', 'lte:height'],
            'copies' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'quantities' => ['sometimes', 'array', 'max:500'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:100'],
            'paper' => ['required', 'in:a4,thermal'],
            'show_name' => ['nullable', 'boolean'],
            'show_sku' => ['nullable', 'boolean'],
            'show_price' => ['nullable', 'boolean'],
            'show_number' => ['nullable', 'boolean'],
        ];
    }
}
