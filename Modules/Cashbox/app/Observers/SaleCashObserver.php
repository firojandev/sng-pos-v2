<?php

namespace Modules\Cashbox\Observers;

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

        CashTransaction::updateOrCreate(
            ['sourceable_type' => Sale::class, 'sourceable_id' => $sale->id],
            [
                'shop_id' => $sale->shop_id,
                'type' => 'in',
                'source' => 'sale',
                'amount' => $sale->paid_amount,
                'note' => $sale->invoice_no,
                'occurred_at' => $sale->sale_date,
                'created_by' => Auth::id(),
            ]
        );
    }

    public function deleted(Sale $sale): void
    {
        CashTransaction::where('sourceable_type', Sale::class)->where('sourceable_id', $sale->id)->delete();
    }
}
