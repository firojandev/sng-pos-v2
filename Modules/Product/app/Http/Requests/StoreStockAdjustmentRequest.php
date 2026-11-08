<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\TenantRules;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', TenantRules::catalogExists('products')],
            'batch_id' => [
                'required',
                Rule::exists('batches', 'id')->where('product_id', $this->input('product_id')),
            ],
            'type' => ['required', 'in:increase,decrease'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
