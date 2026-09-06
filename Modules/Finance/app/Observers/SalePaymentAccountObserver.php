<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Sales\Models\SalePayment;

class SalePaymentAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function created(SalePayment $payment): void
    {
        $this->handlePayment($payment);
    }

    public function updated(SalePayment $payment): void
    {
        $this->transactionService->deleteTransactionsFor($payment);
        $this->handlePayment($payment);
    }

    public function deleted(SalePayment $payment): void
    {
        $this->transactionService->deleteTransactionsFor($payment);
    }

    protected function handlePayment(SalePayment $payment): void
    {
        if ($payment->amount <= 0) {
            return;
        }

        $sale = $payment->sale()->withoutGlobalScopes()->first();
        if (! $sale) {
            return;
        }

        $account = $payment->account_id
            ? Account::withoutGlobalScopes()->find($payment->account_id)
            : $this->transactionService->getDefaultAccount($sale->shop_id);

        if (! $account) {
            return;
        }

        $paymentDate = $payment->payment_date
            ? Carbon::parse($payment->payment_date)->format('Y-m-d')
            : ($sale->sale_date ? $sale->sale_date->format('Y-m-d') : now()->format('Y-m-d'));

        $note = $payment->note ?: ('বিক্রয় ইনভয়েস: '.$sale->invoice_no);

        $this->transactionService->recordTransaction(
            account: $account,
            type: 'in',
            amount: (float) $payment->amount,
            source: 'sale',
            sourceable: $payment,
            note: $note,
            occurredAt: $paymentDate.' '.now()->format('H:i:s'),
            userId: Auth::id()
        );
    }
}
