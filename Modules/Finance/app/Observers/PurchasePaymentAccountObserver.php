<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Carbon;
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

        $paymentDate = $payment->payment_date
            ? Carbon::parse($payment->payment_date)->format('Y-m-d')
            : ($purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : now()->format('Y-m-d'));

        $note = $payment->note ?: ('ক্রয় ইনভয়েস: '.$purchase->invoice_no);

        $this->transactionService->recordTransaction(
            account: $account,
            type: 'out',
            amount: (float) $payment->amount,
            source: 'purchase',
            sourceable: $payment,
            note: $note,
            occurredAt: $paymentDate.' '.now()->format('H:i:s'),
            userId: Auth::id()
        );
    }
}
