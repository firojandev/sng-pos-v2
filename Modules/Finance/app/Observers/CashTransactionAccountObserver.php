<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Finance\Services\AccountTransactionService;

class CashTransactionAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function saved(CashTransaction $cashTransaction): void
    {
        $this->transactionService->deleteTransactionsFor($cashTransaction);

        // Only manual Cash In / Cash Out directly from cashbox needs to sync with cash account.
        // Other sources (sale, purchase, expense, income, etc.) have their own dedicated observers.
        if ($cashTransaction->source !== 'manual' || (float) $cashTransaction->amount <= 0) {
            return;
        }

        $account = $this->transactionService->getCashAccount($cashTransaction->shop_id);

        if (! $account) {
            return;
        }

        $type = $cashTransaction->type;
        $source = $type === 'in' ? 'cash_in' : 'cash_out';
        $note = $cashTransaction->note
            ? ($type === 'in' ? 'ক্যাশ ইন: ' : 'ক্যাশ আউট: ').$cashTransaction->note
            : ($type === 'in' ? 'ক্যাশ ইন (ম্যানুয়াল)' : 'ক্যাশ আউট (ম্যানুয়াল)');

        $this->transactionService->recordTransaction(
            account: $account,
            type: $type,
            amount: (float) $cashTransaction->amount,
            source: $source,
            sourceable: $cashTransaction,
            note: $note,
            occurredAt: $cashTransaction->occurred_at ?? now(),
            userId: $cashTransaction->created_by ?? Auth::id()
        );
    }

    public function deleted(CashTransaction $cashTransaction): void
    {
        $this->transactionService->deleteTransactionsFor($cashTransaction);
    }
}
