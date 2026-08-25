<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Purchase\Models\Purchase;

class PurchaseCashObserver
{
    public function saved(Purchase $purchase): void
    {
        if ($purchase->paid_amount <= 0) {
            CashTransaction::where('sourceable_type', Purchase::class)->where('sourceable_id', $purchase->id)->delete();

            return;
        }

        CashTransaction::updateOrCreate(
            ['sourceable_type' => Purchase::class, 'sourceable_id' => $purchase->id],
            [
                'shop_id' => $purchase->shop_id,
                'type' => 'out',
                'source' => 'purchase',
                'amount' => $purchase->paid_amount,
                'note' => $purchase->invoice_no,
                'occurred_at' => $purchase->purchase_date,
                'created_by' => Auth::id(),
            ]
        );
    }

    public function deleted(Purchase $purchase): void
    {
        CashTransaction::where('sourceable_type', Purchase::class)->where('sourceable_id', $purchase->id)->delete();
    }
}
