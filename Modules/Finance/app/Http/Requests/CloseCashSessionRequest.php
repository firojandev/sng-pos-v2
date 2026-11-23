<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Finance\Services\CashReconciliationService;

class CloseCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'counts' => ['nullable', 'array:'.implode(',', CashReconciliationService::DENOMINATIONS)],
            'counts.*' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'other_amount' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<int|string, int>
     */
    public function closingCounts(): array
    {
        return array_map('intval', array_filter((array) $this->validated('counts', []), fn ($quantity) => $quantity !== null && $quantity !== ''));
    }

    public function otherAmount(): float
    {
        return (float) $this->validated('other_amount', 0);
    }
}
