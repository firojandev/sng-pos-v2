<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Sales\Models\SaleReturn;

class SaleReturnCashObserver
{
    public function created(SaleReturn $saleReturn): void
    {
        if ($saleReturn->refund_amount <= 0) {
            return;
        }

        CashTransaction::create([
            'shop_id' => $saleReturn->shop_id,
            'type' => 'out',
            'source' => 'sale_return',
            'sourceable_type' => SaleReturn::class,
            'sourceable_id' => $saleReturn->id,
            'amount' => $saleReturn->refund_amount,
            'note' => $saleReturn->return_no,
            'occurred_at' => $saleReturn->return_date,
            'created_by' => Auth::id(),
        ]);
    }
}
