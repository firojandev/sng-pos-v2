<?php

namespace Modules\Finance\Providers;

use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Income;
use Modules\Finance\Observers\ExpenseAccountObserver;
use Modules\Finance\Observers\IncomeAccountObserver;
use Modules\Finance\Observers\PurchasePaymentAccountObserver;
use Modules\Finance\Observers\PurchaseReturnAccountObserver;
use Modules\Finance\Observers\SalePaymentAccountObserver;
use Modules\Finance\Observers\SaleReturnAccountObserver;
use Modules\Purchase\Models\PurchasePayment;
use Modules\Purchase\Models\PurchaseReturn;
use Modules\Sales\Models\SalePayment;
use Modules\Sales\Models\SaleReturn;
use Nwidart\Modules\Support\ModuleServiceProvider;

class FinanceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Finance';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'finance';

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

        SalePayment::observe(SalePaymentAccountObserver::class);
        PurchasePayment::observe(PurchasePaymentAccountObserver::class);
        Expense::observe(ExpenseAccountObserver::class);
        Income::observe(IncomeAccountObserver::class);
        SaleReturn::observe(SaleReturnAccountObserver::class);
        PurchaseReturn::observe(PurchaseReturnAccountObserver::class);
    }
}
