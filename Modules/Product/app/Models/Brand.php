<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;

class Brand extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'name', 'description'];

    public function models(): HasMany
    {
        return $this->hasMany(ProductModel::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
