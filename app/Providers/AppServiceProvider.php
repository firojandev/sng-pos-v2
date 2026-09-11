<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Models\Setting;
use Revoltify\Subscriptionify\Subscriptionify;
use Yajra\DataTables\Html\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::useVite();

        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        Paginator::defaultView('vendor.pagination.custom');

        Subscriptionify::resolveSubscribableUsing(function () {
            return auth()->user()?->shop;
        });

        // Dynamic Site Branding from Settings
        try {
            $siteTitle = Setting::getSiteTitle();
            $brandTag = Setting::get('brand_tag', 'Cloud POS & ERP');
            config(['app.name' => $siteTitle]);
        } catch (\Throwable) {
            $siteTitle = config('app.name', 'SNGPOS');
            $brandTag = 'Cloud POS & ERP';
        }

        view()->composer('*', function ($view) {
            try {
                $siteTitle = Setting::getSiteTitle();
                $brandTag = Setting::get('brand_tag', 'Cloud POS & ERP');
                $siteTitleBn = $siteTitle === 'SNGPOS' ? 'এসএনজিপস' : $siteTitle;
            } catch (\Throwable) {
                $siteTitle = config('app.name', 'SNGPOS');
                $brandTag = 'Cloud POS & ERP';
                $siteTitleBn = 'এসএনজিপস';
            }

            $view->with([
                'siteTitle' => $siteTitle,
                'siteTitleBn' => $siteTitleBn,
                'brandTag' => $brandTag,
            ]);
        });
    }
}
