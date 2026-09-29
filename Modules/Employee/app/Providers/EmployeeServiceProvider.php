<?php

namespace Modules\Employee\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Modules\Employee\Services\SelfService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class EmployeeServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Employee';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'employee';

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

    public function boot(): void
    {
        parent::boot();

        // Employee self-service on the dashboard, for a user linked to an employee.
        View::composer('core::dashboard', function ($view) {
            $user = auth()->user();
            // The shop's plan, or in the company workspace the company's.
            $subscriber = $user?->shop ?? $user?->companyLevelCompany();

            $data = $subscriber && ($subscriber->hasFeature('attendance') || $subscriber->hasFeature('leave')) ? app(SelfService::class)->dashboard($user) : null;

            $view->with('selfService', $data ? $data + ['canClock' => $subscriber->hasFeature('attendance'), 'canApplyLeave' => $subscriber->hasFeature('leave')] : null);
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
