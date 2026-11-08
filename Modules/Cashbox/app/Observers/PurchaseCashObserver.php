<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Carbon;
use Modules\Cashbox\Concerns\SyncsCashByShop;
use Modules\Purchase\Models\Purchase;

class PurchaseCashObserver
{
    use SyncsCashByShop;

    public function saved(Purchase $purchase): void
    {
        $latestPayment = $purchase->payments()->latest('id')->first();
        $occurredAt = $latestPayment?->payment_date
            ? Carbon::parse($latestPayment->payment_date)->format('Y-m-d')
            : ($purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : now()->toDateString());

        $this->syncCashByShop($purchase, 'purchase_payments', 'purchase_id', 'out', 'purchase', $occurredAt);
    }

    public function deleted(Purchase $purchase): void
    {
        $this->removeCash($purchase);
    }
}
