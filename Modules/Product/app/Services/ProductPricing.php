<?php

namespace Modules\Product\Services;

use Modules\Core\Support\TenantContext;
use Modules\Product\Models\CompanyProduct;
use Modules\Product\Models\Product;

/**
 * Writes product values at the right layer. A company's own product keeps
 * its company-wide values on the product itself; a shared catalogue product
 * belongs to every company, so a company's values go to its own
 * company_products row and never change the shared product.
 */
class ProductPricing
{
    public function __construct(private TenantContext $tenant) {}

    /**
     * Set company-wide values, e.g. the latest purchase price.
     *
     * @param  array<string, mixed>  $values  keys from ShopProduct::OVERRIDABLE
     */
    public function setCompanyValues(int $productId, array $values): void
    {
        $companyId = Product::withoutGlobalScopes()->whereKey($productId)->value('company_id');

        if ($companyId !== null) {
            Product::withoutGlobalScopes()->whereKey($productId)->update($values);

            return;
        }

        if ($this->tenant->companyId()) {
            CompanyProduct::updateOrCreate(
                ['company_id' => $this->tenant->companyId(), 'product_id' => $productId],
                $values,
            );
        }
    }

    /**
     * Give a company-owned product a barcode captured at the counter, unless
     * any product (in any company) already uses it. Shared products keep the
     * barcode set by the catalogue.
     */
    public function assignBarcode(int $productId, string $barcode): void
    {
        // A barcode is unique within the product's shop.
        $shopId = Product::withoutGlobalScopes()->whereKey($productId)->value('shop_id');
        $barcodeTaken = Product::withoutGlobalScopes()
            ->where('barcode', $barcode)
            ->where(fn ($query) => $shopId ? $query->where('shop_id', $shopId) : $query->whereNull('shop_id'))
            ->whereKeyNot($productId)
            ->exists();

        if ($barcodeTaken) {
            return;
        }

        Product::withoutGlobalScopes()
            ->whereKey($productId)
            ->whereNotNull('company_id')
            ->where(fn ($query) => $query->whereNull('barcode')->orWhere('barcode', '!=', $barcode))
            ->update(['has_barcode' => true, 'barcode' => $barcode]);
    }
}
