<?php

namespace Modules\Task\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Modules\Task\Models\Task;
use Nwidart\Modules\Support\ModuleServiceProvider;

class TaskServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Task';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'task';

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

        // The user's open tasks on the dashboard.
        View::composer('core::dashboard', function ($view) {
            $user = auth()->user();

            if (! Task::isAvailableTo($user)) {
                $view->with('myTasks', null);

                return;
            }

            $open = Task::query()->where('company_id', Task::companyFor($user)->id)->where('assigned_to', $user->id)->whereIn('status', Task::OPEN_STATUSES);

            $view->with('myTasks', [
                'open' => (clone $open)->count(),
                'overdue' => (clone $open)->whereDate('deadline', '<', now()->toDateString())->count(),
                'tasks' => (clone $open)->with('reporter:id,name')->orderByRaw('deadline IS NULL')->orderBy('deadline')->latest('id')->limit(6)->get(),
            ]);
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
