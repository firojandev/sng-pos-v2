<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Product\Models\Batch;

class Warehouse extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'branch_id', 'name', 'address', 'status'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
