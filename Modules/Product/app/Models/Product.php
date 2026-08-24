<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;

class Product extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'shop_id',
        'name',
        'sku',
        'image_url',
        'category_id',
        'sub_category_id',
        'brand_id',
        'short_description',
        'alert_qty',
        'is_vat',
        'vat_percentage',
        'status',
        'has_warranty',
        'warranty_duration',
        'warranty_type',
        'has_expiry',
        'expiry_date',
    ];

    protected $casts = [
        'is_vat' => 'boolean',
        'has_warranty' => 'boolean',
        'has_expiry' => 'boolean',
        'vat_percentage' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'product_units')
            ->withPivot(['is_base', 'conversion_factor', 'purchase_price', 'sale_price'])
            ->withTimestamps();
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function baseUnit(): ?Unit
    {
        return $this->units->firstWhere('pivot.is_base', true);
    }
}
