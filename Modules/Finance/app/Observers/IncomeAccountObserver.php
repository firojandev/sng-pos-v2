<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Income;
use Modules\Finance\Services\AccountTransactionService;

class IncomeAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function saved(Income $income): void
    {
        $this->transactionService->deleteTransactionsFor($income);

        if ($income->amount <= 0) {
            return;
        }

        $account = $income->account_id
            ? Account::withoutGlobalScopes()->find($income->account_id)
            : $this->transactionService->getDefaultAccount($income->shop_id);

        if (! $account) {
            return;
        }

        $this->transactionService->recordTransaction(
            account: $account,
            type: 'in',
            amount: (float) $income->amount,
            source: 'income',
            sourceable: $income,
            note: 'আয়: '.$income->source,
            occurredAt: $income->income_date ? $income->income_date->format('Y-m-d').' '.now()->format('H:i:s') : now(),
            userId: Auth::id()
        );
    }

    public function deleted(Income $income): void
    {
        $this->transactionService->deleteTransactionsFor($income);
    }
}
