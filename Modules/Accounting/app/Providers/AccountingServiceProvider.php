<?php

namespace Modules\Accounting\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Accounting\Observers\AutoPostingObserver;
use Modules\Accounting\Services\AutoPosting;
use Modules\Customer\Models\LoyaltyPointTransaction;
use Modules\Finance\Models\AccountTransaction;
use Modules\Finance\Models\AccountTransfer;
use Modules\FinanceManagement\Models\AssetDepreciation;
use Modules\Payroll\Models\FinalSettlement;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\StatutoryPayment;
use Modules\Product\Models\StockAdjustment;
use Modules\Product\Models\StockTransfer;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchaseReturn;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleReturn;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AccountingServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Accounting';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'accounting';

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
     * Records that post to the ledger automatically.
     *
     * @var list<class-string>
     */
    private const POSTED_RECORDS = [
        Sale::class,
        SaleReturn::class,
        Purchase::class,
        PurchaseReturn::class,
        AccountTransaction::class,
        AccountTransfer::class,
        StockAdjustment::class,
        StockTransfer::class,
        LoyaltyPointTransaction::class,
        PayrollRun::class,
        FinalSettlement::class,
        StatutoryPayment::class,
        AssetDepreciation::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->scoped(AutoPosting::class);
    }

    public function boot(): void
    {
        parent::boot();

        foreach (self::POSTED_RECORDS as $record) {
            $record::observe(AutoPostingObserver::class);
        }
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
