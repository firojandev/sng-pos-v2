<?php

namespace Modules\FinanceManagement\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\FinanceManagement\Models\SecurityMoney;

class SecurityMoneyAccountObserver
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function saved(SecurityMoney $securityMoney): void
    {
        $this->transactionService->deleteTransactionsFor($securityMoney);

        if ($securityMoney->amount <= 0) {
            return;
        }

        $account = $securityMoney->account_id
            ? Account::withoutGlobalScopes()->find($securityMoney->account_id)
            : $this->transactionService->getDefaultAccount($securityMoney->shop_id);

        if (! $account) {
            return;
        }

        $occurredAt = $securityMoney->date ? $securityMoney->date->format('Y-m-d').' '.now()->format('H:i:s') : now();

        // A single record represents one event only: either a deposit paid out,
        // or a deposit received/refunded back — never both.
        if ($securityMoney->status === 'paid') {
            $this->transactionService->recordTransaction(
                account: $account,
                type: 'out',
                amount: (float) $securityMoney->amount,
                source: 'security_money_paid',
                sourceable: $securityMoney,
                note: 'জামানত প্রদান: '.$securityMoney->receiver_name,
                occurredAt: $occurredAt,
                userId: Auth::id()
            );
        } else {
            $this->transactionService->recordTransaction(
                account: $account,
                type: 'in',
                amount: (float) $securityMoney->amount,
                source: 'security_money_received',
                sourceable: $securityMoney,
                note: 'জামানত ফেরত/গ্রহণ: '.$securityMoney->receiver_name,
                occurredAt: $occurredAt,
                userId: Auth::id()
            );
        }
    }

    public function deleted(SecurityMoney $securityMoney): void
    {
        $this->transactionService->deleteTransactionsFor($securityMoney);
    }
}
