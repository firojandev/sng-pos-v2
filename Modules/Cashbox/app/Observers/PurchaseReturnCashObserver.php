<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Purchase\Models\PurchaseReturn;

class PurchaseReturnCashObserver
{
    public function created(PurchaseReturn $purchaseReturn): void
    {
        if ($purchaseReturn->refund_amount <= 0) {
            return;
        }

        CashTransaction::create([
            'shop_id' => $purchaseReturn->shop_id,
            'type' => 'in',
            'source' => 'purchase_return',
            'sourceable_type' => PurchaseReturn::class,
            'sourceable_id' => $purchaseReturn->id,
            'amount' => $purchaseReturn->refund_amount,
            'note' => $purchaseReturn->return_no,
            'occurred_at' => $purchaseReturn->return_date,
            'created_by' => Auth::id(),
        ]);
    }
}
