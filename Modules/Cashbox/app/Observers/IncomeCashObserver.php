<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Finance\Models\Income;

class IncomeCashObserver
{
    public function saved(Income $income): void
    {
        CashTransaction::updateOrCreate(
            ['sourceable_type' => Income::class, 'sourceable_id' => $income->id],
            [
                'shop_id' => $income->shop_id,
                'type' => 'in',
                'source' => 'income',
                'amount' => $income->amount,
                'note' => $income->source,
                'occurred_at' => $income->income_date,
                'created_by' => Auth::id(),
            ]
        );
    }

    public function deleted(Income $income): void
    {
        CashTransaction::where('sourceable_type', Income::class)->where('sourceable_id', $income->id)->delete();
    }
}
