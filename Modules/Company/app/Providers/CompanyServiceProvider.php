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

        // In the company workspace (no shop): the company's owner and admins
        // get every company-module permission; company employees get what
        // their company role grants. Reports and the dashboard's sales /
        // purchase / stock figures belong to the POS: shop logins only.
        Gate::before(function (User $user, string $ability) {
            if ($user->shop_id || $user->isSuperAdmin()) {
                return null;
            }

            $feature = explode('.', $ability)[0];

            if (! in_array($feature, self::COMPANY_WORKSPACE_FEATURES, true)) {
                return null;
            }

            // Worked out once per request.
            $cache = request()->attributes;
            $key = 'company_workspace_access.'.$user->id;

            if (! $cache->has($key)) {
                $cache->set($key, (function () use ($user) {
                    $company = $user->companyLevelCompany();

                    if (! $company) {
                        return [];
                    }

                    if ($company->isBusiness() && $company->isAdministeredBy($user)) {
                        return ['*'];
                    }

                    return $company->roleOf($user)?->permissions ?? [];
                })());
            }

            $access = $cache->get($key);

            return in_array('*', $access, true) || in_array($ability, $access, true) ? true : null;
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
