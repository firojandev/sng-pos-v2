<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Purchase\Models\PurchaseReturn;

class PurchaseReturnAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function created(PurchaseReturn $purchaseReturn): void
    {
        if ($purchaseReturn->refund_amount <= 0) {
            return;
        }

        $account = $this->transactionService->getDefaultAccount($purchaseReturn->shop_id);
        if (! $account) {
            return;
        }

        $this->transactionService->recordTransaction(
            account: $account,
            type: 'in',
            amount: (float) $purchaseReturn->refund_amount,
            source: 'purchase_return',
            sourceable: $purchaseReturn,
            note: 'ক্রয় ফেরত: '.$purchaseReturn->return_no,
            occurredAt: $purchaseReturn->return_date ? $purchaseReturn->return_date->format('Y-m-d').' '.now()->format('H:i:s') : now(),
            userId: Auth::id()
        );
    }

    public function deleted(PurchaseReturn $purchaseReturn): void
    {
        $this->transactionService->deleteTransactionsFor($purchaseReturn);
    }
}
