<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Purchase\Models\PurchasePayment;

class PurchasePaymentAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function created(PurchasePayment $payment): void
    {
        $this->handlePayment($payment);
    }

    public function updated(PurchasePayment $payment): void
    {
        $this->transactionService->deleteTransactionsFor($payment);
        $this->handlePayment($payment);
    }

    public function deleted(PurchasePayment $payment): void
    {
        $this->transactionService->deleteTransactionsFor($payment);
    }

    protected function handlePayment(PurchasePayment $payment): void
    {
        if ($payment->amount <= 0) {
            return;
        }

        $purchase = $payment->purchase()->withoutGlobalScopes()->first();
        if (! $purchase) {
            return;
        }

        $account = $payment->account_id
            ? Account::withoutGlobalScopes()->find($payment->account_id)
            : $this->transactionService->getDefaultAccount($purchase->shop_id);

        if (! $account) {
            return;
        }

        $this->transactionService->recordTransaction(
            account: $account,
            type: 'out',
            amount: (float) $payment->amount,
            source: 'purchase',
            sourceable: $payment,
            note: 'ক্রয় ইনভয়েস: '.$purchase->invoice_no,
            occurredAt: $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d').' '.now()->format('H:i:s') : now(),
            userId: Auth::id()
        );
    }
}
