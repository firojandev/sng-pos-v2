<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Carbon;
use Modules\Cashbox\Concerns\SyncsCashByShop;
use Modules\Sales\Models\Sale;

class SaleCashObserver
{
    use SyncsCashByShop;

    public function saved(Sale $sale): void
    {
        $latestPayment = $sale->payments()->latest('id')->first();
        $occurredAt = $latestPayment?->payment_date
            ? Carbon::parse($latestPayment->payment_date)->format('Y-m-d')
            : ($sale->sale_date ? $sale->sale_date->format('Y-m-d') : now()->toDateString());

        $this->syncCashByShop($sale, 'sale_payments', 'sale_id', 'in', 'sale', $occurredAt);
    }

    public function deleted(Sale $sale): void
    {
        $this->removeCash($sale);
    }
}
