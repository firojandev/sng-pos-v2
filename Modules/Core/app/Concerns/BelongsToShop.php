<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Support\TenantContext;

trait BelongsToShop
{
    public static function bootBelongsToShop(): void
    {
        static::addGlobalScope('shop', function (Builder $builder) {
            $user = Auth::user();

            if (! $user || $user->isSuperAdmin()) {
                return;
            }

            // The user's shop; in the company workspace, the company's shops;
            // with neither, nothing (never every shop's data).
            $shopIds = $user->shop_id ? [(int) $user->shop_id] : app(TenantContext::class)->visibleShopIds();
            $builder->whereIn($builder->getModel()->getTable().'.shop_id', $shopIds ?: [0]);
        });

        static::creating(function ($model) {
            $user = Auth::user();

            if (empty($model->shop_id) && $user && $user->shop_id) {
                $model->shop_id = $user->shop_id;
            }
        });
    }
}
