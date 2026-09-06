<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Expense;
use Modules\Finance\Services\AccountTransactionService;

class ExpenseAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function saved(Expense $expense): void
    {
        $this->transactionService->deleteTransactionsFor($expense);

        if ($expense->amount <= 0) {
            return;
        }

        $account = $expense->account_id
            ? Account::withoutGlobalScopes()->find($expense->account_id)
            : $this->transactionService->getDefaultAccount($expense->shop_id);

        if (! $account) {
            return;
        }

        $this->transactionService->recordTransaction(
            account: $account,
            type: 'out',
            amount: (float) $expense->amount,
            source: 'expense',
            sourceable: $expense,
            note: 'ব্যয়: '.$expense->title,
            occurredAt: $expense->expense_date ? $expense->expense_date->format('Y-m-d').' '.now()->format('H:i:s') : now(),
            userId: Auth::id()
        );
    }

    public function deleted(Expense $expense): void
    {
        $this->transactionService->deleteTransactionsFor($expense);
    }
}
