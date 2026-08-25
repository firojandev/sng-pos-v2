<?php

namespace Modules\Cashbox\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Finance\Models\Expense;

class ExpenseCashObserver
{
    public function saved(Expense $expense): void
    {
        CashTransaction::updateOrCreate(
            ['sourceable_type' => Expense::class, 'sourceable_id' => $expense->id],
            [
                'shop_id' => $expense->shop_id,
                'type' => 'out',
                'source' => 'expense',
                'amount' => $expense->amount,
                'note' => $expense->title,
                'occurred_at' => $expense->expense_date,
                'created_by' => Auth::id(),
            ]
        );
    }

    public function deleted(Expense $expense): void
    {
        CashTransaction::where('sourceable_type', Expense::class)->where('sourceable_id', $expense->id)->delete();
    }
}
