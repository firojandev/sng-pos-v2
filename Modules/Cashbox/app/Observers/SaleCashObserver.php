<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Sales\Models\Sale;

class SaleCashObserver
{
    public function saved(Sale $sale): void
    {
        if ($sale->paid_amount <= 0) {
            CashTransaction::where('sourceable_type', Sale::class)->where('sourceable_id', $sale->id)->delete();

            return;
        }

        $latestPayment = $sale->payments()->latest('id')->first();
        $occurredAt = $latestPayment?->payment_date
            ? Carbon::parse($latestPayment->payment_date)->format('Y-m-d')
            : ($sale->sale_date ? $sale->sale_date->format('Y-m-d') : now()->toDateString());

        CashTransaction::updateOrCreate(
            ['sourceable_type' => Sale::class, 'sourceable_id' => $sale->id],
            [
                'shop_id' => $sale->shop_id,
                'type' => 'in',
                'source' => 'sale',
                'amount' => $sale->paid_amount,
                'note' => $sale->invoice_no,
                'occurred_at' => $occurredAt,
                'created_by' => Auth::id(),
            ]
        );
    }

    public function deleted(Sale $sale): void
    {
        CashTransaction::where('sourceable_type', Sale::class)->where('sourceable_id', $sale->id)->delete();
    }
}
