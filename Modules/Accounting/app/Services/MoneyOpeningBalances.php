<?php

namespace Modules\Accounting\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;

/**
 * Opening cash and bank balances. The counted amount of each account is
 * never written over its balance: the difference is recorded as an
 * opening-balance money movement, so the account's history explains it and
 * the ledger gets Dr Cash / Cr Opening Balance Equity (through the opening
 * entry, or posted on its own once the ledger is running).
 */
class MoneyOpeningBalances
{
    public function __construct(private AccountTransactionService $money) {}

    /**
     * @return Collection<int, Account>
     */
    public function accounts(int $companyId): Collection
    {
        return Account::withoutGlobalScopes()
            ->whereIn('shop_id', DB::table('shops')->where('company_id', $companyId)->pluck('id'))
            ->where('status', 'active')
            ->orderBy('shop_id')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * Bring each account to its counted balance.
     *
     * @param  array<int|string, float|string|null>  $counted  account id => counted balance (blank = leave)
     * @return int the number of accounts adjusted
     */
    public function set(int $companyId, array $counted, Carbon $date): int
    {
        $adjusted = 0;

        DB::transaction(function () use ($companyId, $counted, $date, &$adjusted) {
            foreach ($this->accounts($companyId) as $account) {
                $value = $counted[$account->id] ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                $difference = round((float) $value - (float) $account->current_balance, 2);

                if (abs($difference) < 0.005) {
                    continue;
                }

                $this->money->recordTransaction(
                    $account,
                    $difference > 0 ? 'in' : 'out',
                    abs($difference),
                    'opening_balance',
                    null,
                    'প্রারম্ভিক ব্যালেন্স সমন্বয় (Opening balance): '.number_format((float) $value, 2),
                    $date->copy()->setTimeFrom(now()),
                );
                $adjusted++;
            }
        });

        return $adjusted;
    }
}
