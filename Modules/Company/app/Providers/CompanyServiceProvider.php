<?php

namespace Modules\Company\Providers;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CompanyServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Company';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'company';

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
     * Permissions of the company workspace's modules, given to a company's
     * owner and admins while they work at company level (no shop).
     *
     * @var list<string>
     */
    private const COMPANY_WORKSPACE_FEATURES = [
        'employees', 'attendance', 'leave', 'hr-setup', 'payroll', 'payroll-setup', 'accounting', 'tasks',
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::before(function (User $user, string $ability) {
            if ($user->shop_id || ! $user->isCompanyAdmin()) {
                return null;
            }

            $feature = explode('.', $ability)[0];

            // Reports and the dashboard's sales/purchase/stock figures belong to
            // the POS: shop logins only.
            return in_array($feature, self::COMPANY_WORKSPACE_FEATURES, true) ? true : null;
        });
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
