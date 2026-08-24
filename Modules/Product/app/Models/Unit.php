<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Concerns\BelongsToShop;

class Unit extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'name', 'short_code'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_units')
            ->withPivot(['is_base', 'conversion_factor', 'purchase_price', 'sale_price'])
            ->withTimestamps();
    }
}
