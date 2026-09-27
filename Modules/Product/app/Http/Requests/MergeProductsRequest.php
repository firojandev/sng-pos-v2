<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MergeProductsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'duplicate_id' => ['required', 'integer', 'exists:products,id', 'different:keep_id'],
            'keep_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }
}
