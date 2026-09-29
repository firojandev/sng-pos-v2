<?php

namespace Modules\Core\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CatalogVisibility;
use Modules\Core\Support\TenantContext;
use Modules\Shop\Models\Shop;

/**
 * Catalogue records (products, categories, brands, units, product models)
 * belong to the shop that creates them — each shop manages its own
 * catalogue (see CatalogVisibility). Records without a shop are older ones
 * that several shops still use; they are read only.
 */
trait BelongsToCatalog
{
    public static function bootBelongsToCatalog(): void
    {
        static::addGlobalScope('catalog', function (Builder $builder) {
            CatalogVisibility::constrain($builder, $builder->getModel()->getTable());
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

    /**
     * The shop that owns the record.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isShared(): bool
    {
        return $this->company_id === null;
    }

    /**
     * Whether the user may change or delete the record: a shop its own
     * records (and its company's older records without a shop). Shared
     * records from before are read only.
     */
    public function isEditableBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->company_id === null || ! $user->shop_id) {
            return false;
        }

        if ($this->shop_id !== null) {
            return (int) $this->shop_id === (int) $user->shop_id;
        }

        return (int) $this->company_id === (int) $user->shop?->company_id;
    }
}
