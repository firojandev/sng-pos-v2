<?php

namespace Modules\FinanceManagement\Providers;

use Modules\FinanceManagement\Models\Debt;
use Modules\FinanceManagement\Models\Lend;
use Modules\FinanceManagement\Models\SecurityMoney;
use Modules\FinanceManagement\Observers\DebtAccountObserver;
use Modules\FinanceManagement\Observers\LendAccountObserver;
use Modules\FinanceManagement\Observers\SecurityMoneyAccountObserver;
use Nwidart\Modules\Support\ModuleServiceProvider;

class FinanceManagementServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'FinanceManagement';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'financemanagement';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Boot the module, wiring up account ledger observers.
     */
    public function boot(): void
    {
        parent::boot();

        Debt::observe(DebtAccountObserver::class);
        Lend::observe(LendAccountObserver::class);
        SecurityMoney::observe(SecurityMoneyAccountObserver::class);
    }
}
