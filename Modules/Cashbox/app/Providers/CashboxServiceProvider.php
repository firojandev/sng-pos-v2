<?php

namespace Modules\Cashbox\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Cashbox\Observers\ExpenseCashObserver;
use Modules\Cashbox\Observers\IncomeCashObserver;
use Modules\Cashbox\Observers\PurchaseCashObserver;
use Modules\Cashbox\Observers\SaleCashObserver;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Income;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;

class CashboxServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Cashbox';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'cashbox';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

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
     * Boot the module, wiring up cash-ledger observers on the other
     * modules' models so every sale/purchase/income/expense stays
     * reflected in the cashbox without those modules knowing about it.
     */
    public function boot(): void
    {
        parent::boot();

        Sale::observe(SaleCashObserver::class);
        Purchase::observe(PurchaseCashObserver::class);
        Income::observe(IncomeCashObserver::class);
        Expense::observe(ExpenseCashObserver::class);
    }

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
