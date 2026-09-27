<?php

namespace Modules\Customer\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Customer\Console\ExpireLoyaltyPoints;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CustomerServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Customer';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'customer';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        ExpireLoyaltyPoints::class,
    ];

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
     * Define module schedules.
     */
    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('loyalty:expire-points')->dailyAt('00:30');
    }
}
