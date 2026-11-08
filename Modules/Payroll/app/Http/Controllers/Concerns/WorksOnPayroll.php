<?php

namespace Modules\Payroll\Http\Controllers\Concerns;

use Illuminate\Support\Collection;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Payroll\Models\PayrollSetting;
use Modules\Payroll\Services\PayrollSetup;
use Modules\Shop\Models\Shop;

trait WorksOnPayroll
{
    protected function companyId(): int
    {
        $companyId = app(TenantContext::class)->companyId();
        abort_unless($companyId, 403, 'কোনো দোকান নির্বাচন করা নেই (No shop selected)।');

        return (int) $companyId;
    }

    protected function shopId(): int
    {
        return (int) app(TenantContext::class)->shopId();
    }

    protected function settings(): PayrollSetting
    {
        return app(PayrollSetup::class)->ensureFor($this->companyId());
    }

    /**
     * The company's active cash, bank and mobile-banking accounts, the
     * current shop's first.
     *
     * @return array<int, string>
     */
    protected function moneyAccounts(): array
    {
        $shops = Shop::where('company_id', $this->companyId())->pluck('name', 'id');

        return Account::withoutGlobalScopes()
            ->whereIn('shop_id', $shops->keys())
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (Account $account) => [(int) $account->shop_id !== $this->shopId(), $account->name])
            ->mapWithKeys(fn (Account $account) => [$account->id => $account->name.($shops->count() > 1 ? ' — '.$shops[$account->shop_id] : '').' ('.number_format((float) $account->current_balance, 2).')'])
            ->all();
    }

    protected function companyAccount(int $accountId): Account
    {
        return Account::withoutGlobalScopes()
            ->whereIn('shop_id', Shop::where('company_id', $this->companyId())->pluck('id'))
            ->where('status', 'active')
            ->findOrFail($accountId);
    }

    /**
     * @return Collection<int, Employee>
     */
    protected function activeEmployees(): Collection
    {
        return Employee::workingAtShop()->where('status', 'active')->orderBy('name')->get();
    }
}
