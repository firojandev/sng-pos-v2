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
        'size',
        'purchase_price',
        'sale_price',
        'is_wholesale',
        'wholesale_price',
        'wholesale_min_qty',
        'has_discount',
        'discount_type',
        'discount_value',
        'has_barcode',
        'barcode',
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
        'is_wholesale' => 'boolean',
        'has_discount' => 'boolean',
        'has_barcode' => 'boolean',
        'vat_percentage' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
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
            ->withPivot(['is_base', 'conversion_factor', 'is_smaller_unit'])
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

    public function unitConversionFactor(?int $unitId): float
    {
        if (! $unitId) {
            return 1.0;
        }

        $unit = $this->relationLoaded('units')
            ? $this->units->firstWhere('id', $unitId)
            : $this->units()->where('units.id', $unitId)->first();

        $factor = $unit ? (float) $unit->pivot->conversion_factor : 0.0;

        if ($factor <= 0) {
            return 1.0;
        }

        return $unit->pivot->is_smaller_unit ? 1 / $factor : $factor;
    }
}
