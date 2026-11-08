<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TenantContext;

/**
 * Scopes a model to the company of the user's current shop, the company-level
 * counterpart of BelongsToShop. Super Admins see every company's records;
 * any other user without a current company sees none.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $user = Auth::user();

            if (! $user || $user->isSuperAdmin()) {
                return;
            }

            $companyId = app(TenantContext::class)->companyId();

            // Without a resolved company nothing is visible, never everything.
            $builder->where($builder->getModel()->getTable().'.company_id', $companyId ?? 0);
        });

        static::creating(function ($model) {
            if (! empty($model->company_id)) {
                return;
            }

            // A record created for a specific shop belongs to that shop's company.
            if (! empty($model->shop_id)) {
                $model->company_id = DB::table('shops')->where('id', $model->shop_id)->value('company_id');
            } elseif (Auth::user()) {
                $model->company_id = app(TenantContext::class)->companyId();
            }
        });
    }
}
