<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToShop
{
    public static function bootBelongsToShop(): void
    {
        static::addGlobalScope('shop', function (Builder $builder) {
            $user = Auth::user();

            if ($user && ! $user->isSuperAdmin() && $user->shop_id) {
                $builder->where($builder->getModel()->getTable().'.shop_id', $user->shop_id);
            }
        });

        static::creating(function ($model) {
            $user = Auth::user();

            if (empty($model->shop_id) && $user && $user->shop_id) {
                $model->shop_id = $user->shop_id;
            }
        });
    }
}
