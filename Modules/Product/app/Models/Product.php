<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Modules\Company\Models\Company;
use Modules\Core\Concerns\BelongsToCatalog;
use Modules\Core\Observers\AuditObserver;
use Modules\Core\Support\TenantContext;

/**
 * A catalogue product. Price, VAT, discount, wholesale and alert values
 * resolve through three layers: the current shop's override, then the
 * current company's value, then the product's own base value. Reading
 * `$product->sale_price` always returns the resolved value.
 */
class Product extends Model
{
    use BelongsToCatalog;

    /**
     * The pricing layers of the current shop and company are needed wherever
     * a product's price is read.
     *
     * @var list<string>
     */
    protected $with = ['currentShopListing', 'currentCompanyPricing'];

    /**
     * The layers are folded into the resolved values; they are not output.
     *
     * @var list<string>
     */
    protected $hidden = ['currentShopListing', 'currentCompanyPricing'];

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);

        // A product is sold by the shop it is created in.
        static::created(function (Product $product) {
            $product->listInShop($product->shop_id ?? app(TenantContext::class)->shopId());
        });
    }

    protected $fillable = [
        'company_id',
        'suggested_at',
        'suggested_by',
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
        'suggested_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

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

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function shopListings(): HasMany
    {
        return $this->hasMany(ShopProduct::class);
    }

    public function companyPricings(): HasMany
    {
        return $this->hasMany(CompanyProduct::class);
    }

    /**
     * The current shop's listing (and overrides) of this product.
     */
    public function currentShopListing(): HasOne
    {
        return $this->hasOne(ShopProduct::class)
            ->where('shop_products.shop_id', app(TenantContext::class)->shopId());
    }

    /**
     * The current company's own values for this product.
     */
    public function currentCompanyPricing(): HasOne
    {
        return $this->hasOne(CompanyProduct::class)
            ->where('company_products.company_id', app(TenantContext::class)->companyId());
    }

    /**
     * Products the given shop sells (defaults to the current shop).
     */
    #[Scope]
    protected function listedInShop(Builder $query, ?int $shopId = null): void
    {
        $shopId ??= app(TenantContext::class)->shopId();

        if (! $shopId) {
            return;
        }

        $query->whereHas('shopListings', fn (Builder $listings) => $listings->where('shop_products.shop_id', $shopId));
    }

    public function listInShop(?int $shopId): void
    {
        if (! $shopId) {
            return;
        }

        ShopProduct::firstOrCreate(['shop_id' => $shopId, 'product_id' => $this->getKey()]);
        $this->unsetRelation('currentShopListing');

        // Selling a product means selling its category.
        if ($this->category_id) {
            DB::table('shop_category')->insertOrIgnore([
                'shop_id' => $shopId,
                'category_id' => $this->category_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * The product's own stored value, ignoring shop and company overrides.
     */
    public function baseValue(string $key): mixed
    {
        return parent::getAttributeFromArray($key);
    }

    /**
     * The company-wide value, ignoring the current shop's override.
     */
    public function companyValue(string $key): mixed
    {
        return $this->getRelationValue('currentCompanyPricing')?->getAttribute($key) ?? $this->baseValue($key);
    }

    /**
     * Resolve overridable fields before casts are applied, so reads keep the
     * usual formatting (e.g. "500.00") while returning the effective value.
     */
    protected function getAttributeFromArray($key)
    {
        $value = parent::getAttributeFromArray($key);

        if (! in_array($key, ShopProduct::OVERRIDABLE, true) || ! $this->exists) {
            return $value;
        }

        foreach (['currentShopListing', 'currentCompanyPricing'] as $layer) {
            $override = $this->getRelationValue($layer)?->getAttributes()[$key] ?? null;

            if ($override !== null) {
                return $override;
            }
        }

        return $value;
    }

    /**
     * Arrays and JSON carry the resolved values too.
     *
     * @return array<string, mixed>
     */
    protected function getArrayableAttributes()
    {
        $attributes = parent::getArrayableAttributes();

        foreach (ShopProduct::OVERRIDABLE as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->getAttributeFromArray($key);
            }
        }

        return $attributes;
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
