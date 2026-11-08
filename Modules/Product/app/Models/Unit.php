<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Concerns\BelongsToCatalog;

class Unit extends Model
{
    use BelongsToCatalog;

    protected $fillable = ['company_id', 'shop_id', 'name', 'short_code'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_units')
            ->withPivot(['is_base', 'conversion_factor', 'is_smaller_unit'])
            ->withTimestamps();
    }
}
