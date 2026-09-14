<?php

namespace Modules\FinanceManagement\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\FinanceManagement\Models\Debt;

class DebtAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function saved(Debt $debt): void
    {
        $this->transactionService->deleteTransactionsFor($debt);

        if ($debt->amount <= 0) {
            return;
        }

        $account = $debt->account_id
            ? Account::withoutGlobalScopes()->find($debt->account_id)
            : $this->transactionService->getDefaultAccount($debt->shop_id);

        if (! $account) {
            return;
        }

        $occurredAt = $debt->date ? $debt->date->format('Y-m-d').' '.now()->format('H:i:s') : now();

        // Taking a loan is always a cash inflow at the time it's recorded.
        $this->transactionService->recordTransaction(
            account: $account,
            type: 'in',
            amount: (float) $debt->amount,
            source: 'debt_received',
            sourceable: $debt,
            note: 'ঋণ গ্রহণ: '.$debt->lender_name,
            occurredAt: $occurredAt,
            userId: Auth::id()
        );

        // A debt already marked paid also records the repayment outflow.
        if ($debt->status === 'paid') {
            $this->transactionService->recordTransaction(
                account: $account,
                type: 'out',
                amount: (float) $debt->amount,
                source: 'debt_repaid',
                sourceable: $debt,
                note: 'ঋণ পরিশোধ: '.$debt->lender_name,
                occurredAt: $occurredAt,
                userId: Auth::id()
            );
        }
    }

    public function deleted(Debt $debt): void
    {
        $this->transactionService->deleteTransactionsFor($debt);
    }
}
