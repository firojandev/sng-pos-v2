<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Product\Models\Batch;

class Warehouse extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'branch_id', 'name', 'address', 'status', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function makeDefault(): void
    {
        DB::transaction(function () {
            static::withoutGlobalScopes()
                ->where('shop_id', $this->shop_id)
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);

            $this->update(['is_default' => true]);
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
