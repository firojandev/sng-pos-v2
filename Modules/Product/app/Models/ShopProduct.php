<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Shop\Models\Shop;

/**
 * A product listed in a shop, with the shop's own overrides (null = use the
 * company value, then the product's base value).
 */
class ShopProduct extends Model
{
    /**
     * The product fields a pricing layer can override.
     *
     * @var list<string>
     */
    public const OVERRIDABLE = [
        'purchase_price',
        'sale_price',
        'is_wholesale',
        'wholesale_price',
        'wholesale_min_qty',
        'has_discount',
        'discount_type',
        'discount_value',
        'alert_qty',
        'is_vat',
        'vat_percentage',
    ];

    protected $fillable = [
        'shop_id',
        'product_id',
        'purchase_price',
        'sale_price',
        'is_wholesale',
        'wholesale_price',
        'wholesale_min_qty',
        'has_discount',
        'discount_type',
        'discount_value',
        'alert_qty',
        'is_vat',
        'vat_percentage',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_wholesale' => 'boolean',
            'wholesale_price' => 'decimal:2',
            'wholesale_min_qty' => 'integer',
            'has_discount' => 'boolean',
            'discount_value' => 'decimal:2',
            'alert_qty' => 'integer',
            'is_vat' => 'boolean',
            'vat_percentage' => 'decimal:2',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
