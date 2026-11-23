<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Finance\Services\CashReconciliationService;

class OpenCashSessionRequest extends FormRequest
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
            'count_opening' => ['nullable', 'boolean'],
            'counts' => ['nullable', 'array:'.implode(',', CashReconciliationService::DENOMINATIONS)],
            'counts.*' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'other_amount' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Denomination counts when the cashier chose to count the drawer, otherwise
     * null so the expected balance is carried forward.
     *
     * @return array<int|string, int>|null
     */
    public function openingCounts(): ?array
    {
        if (! $this->boolean('count_opening')) {
            return null;
        }

        return array_map('intval', array_filter((array) $this->validated('counts', []), fn ($quantity) => $quantity !== null && $quantity !== ''));
    }

    public function otherAmount(): float
    {
        return $this->boolean('count_opening') ? (float) $this->validated('other_amount', 0) : 0.0;
    }
}
