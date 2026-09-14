<?php

namespace Modules\FinanceManagement\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\FinanceManagement\Models\Lend;

class LendAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function saved(Lend $lend): void
    {
        $this->transactionService->deleteTransactionsFor($lend);

        if ($lend->amount <= 0) {
            return;
        }

        $account = $lend->account_id
            ? Account::withoutGlobalScopes()->find($lend->account_id)
            : $this->transactionService->getDefaultAccount($lend->shop_id);

        if (! $account) {
            return;
        }

        $occurredAt = $lend->date ? $lend->date->format('Y-m-d').' '.now()->format('H:i:s') : now();

        // Lending money out is always a cash outflow at the time it's recorded.
        $this->transactionService->recordTransaction(
            account: $account,
            type: 'out',
            amount: (float) $lend->amount,
            source: 'lend_given',
            sourceable: $lend,
            note: 'ধার প্রদান: '.$lend->borrower_name,
            occurredAt: $occurredAt,
            userId: Auth::id()
        );

        // A lend already marked received also records the repayment inflow.
        if ($lend->status === 'received') {
            $this->transactionService->recordTransaction(
                account: $account,
                type: 'in',
                amount: (float) $lend->amount,
                source: 'lend_repaid',
                sourceable: $lend,
                note: 'ধার ফেরত: '.$lend->borrower_name,
                occurredAt: $occurredAt,
                userId: Auth::id()
            );
        }
    }

    public function deleted(Lend $lend): void
    {
        $this->transactionService->deleteTransactionsFor($lend);
    }
}
