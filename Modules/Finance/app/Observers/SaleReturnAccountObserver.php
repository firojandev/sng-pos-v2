<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Sales\Models\SaleReturn;

class SaleReturnAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function created(SaleReturn $saleReturn): void
    {
        if ($saleReturn->refund_amount <= 0) {
            return;
        }

        $account = $this->transactionService->getDefaultAccount($saleReturn->shop_id);
        if (! $account) {
            return;
        }

        $this->transactionService->recordTransaction(
            account: $account,
            type: 'out',
            amount: (float) $saleReturn->refund_amount,
            source: 'sale_return',
            sourceable: $saleReturn,
            note: 'বিক্রয় ফেরত: '.$saleReturn->return_no,
            occurredAt: $saleReturn->return_date ? $saleReturn->return_date->format('Y-m-d').' '.now()->format('H:i:s') : now(),
            userId: Auth::id()
        );
    }

    public function deleted(SaleReturn $saleReturn): void
    {
        $this->transactionService->deleteTransactionsFor($saleReturn);
    }
}
