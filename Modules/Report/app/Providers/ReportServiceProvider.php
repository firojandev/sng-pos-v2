<?php

namespace Modules\Report\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class ReportServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Report';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'report';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
