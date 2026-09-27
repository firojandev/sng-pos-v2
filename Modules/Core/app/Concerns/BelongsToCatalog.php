<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TenantContext;

/**
 * Catalogue records (products, categories, brands, units, product models)
 * are either shared by every company (company_id is null) or private to one
 * company. A company sees its own records plus the shared catalogue.
 *
 * New records are private to the company they are created in; shop_id keeps
 * the shop that created them. Only a Super Admin working outside any shop
 * creates shared records.
 */
trait BelongsToCatalog
{
    public static function bootBelongsToCatalog(): void
    {
        static::addGlobalScope('catalog', function (Builder $builder) {
            $user = Auth::user();

            if (! $user || $user->isSuperAdmin()) {
                return;
            }

            $column = $builder->getModel()->getTable().'.company_id';
            $companyId = app(TenantContext::class)->companyId();

            $builder->where(function (Builder $query) use ($column, $companyId) {
                $query->whereNull($column);

                if ($companyId) {
                    $query->orWhere($column, $companyId);
                }
            });
        });

        static::creating(function ($model) {
            if (empty($model->shop_id) && Auth::user()) {
                $model->shop_id = app(TenantContext::class)->shopId();
            }

            if (! empty($model->company_id)) {
                return;
            }

            if (! empty($model->shop_id)) {
                $model->company_id = DB::table('shops')->where('id', $model->shop_id)->value('company_id');
            }
        });
    }

    public function isShared(): bool
    {
        return $this->company_id === null;
    }
}
