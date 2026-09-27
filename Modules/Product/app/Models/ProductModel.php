<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCatalog;

class ProductModel extends Model
{
    use BelongsToCatalog;

    protected $table = 'product_models';

    protected $fillable = ['company_id', 'shop_id', 'brand_id', 'name'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
