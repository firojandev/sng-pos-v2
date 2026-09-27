<?php

namespace Modules\Product\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Category;
use Modules\Product\Models\CompanyProduct;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShopProduct;
use Modules\Product\Models\Unit;
use Modules\Shop\Models\Shop;

/**
 * The shop's view of the catalogue: which categories it sells, which of the
 * company's own and shared products it lists, its own prices, and the
 * company's suggestions for the shared catalogue.
 */
class CatalogueService
{
    /**
     * @param  list<int>  $categoryIds
     */
    public function syncShopCategories(Shop $shop, array $categoryIds): void
    {
        $shop->categories()->sync($categoryIds);
    }

    /**
     * Products the shop could add: the company's own and shared products in
     * the categories it sells that it does not list yet.
     *
     * @return Collection<int, Product>
     */
    public function addableProducts(Shop $shop): Collection
    {
        $categoryIds = $shop->categories()->pluck('categories.id');

        return Product::query()
            ->where('status', 'active')
            ->whereIn('category_id', $categoryIds)
            ->whereDoesntHave('shopListings', fn ($listings) => $listings->where('shop_products.shop_id', $shop->id))
            ->with('category:id,name')
            ->orderBy('name')
            ->get();
    }

    /**
     * List every addable product of a category in the shop.
     */
    public function listCategory(Shop $shop, Category $category): int
    {
        $products = $this->addableProducts($shop)->where('category_id', $category->id);

        $products->each(fn (Product $product) => $product->listInShop($shop->id));

        return $products->count();
    }

    /**
     * Save the shop's own values for a listed product; empty values fall
     * back to the company and base values.
     *
     * @param  array<string, mixed>  $values
     */
    public function setShopOverrides(ShopProduct $listing, array $values): void
    {
        $listing->update(array_map(
            fn ($value) => $value === '' ? null : $value,
            array_intersect_key($values, array_flip(ShopProduct::OVERRIDABLE)),
        ));
    }

    public function suggest(Product $product, User $user): void
    {
        $product->update(['suggested_at' => now(), 'suggested_by' => $user->id]);
    }

    public function reject(Product $product): void
    {
        $product->update(['suggested_at' => null, 'suggested_by' => null]);
    }

    /**
     * Move a company's product into the shared catalogue. The company keeps
     * its own prices (copied to its company layer), and the product's
     * category, brand and units are shared with it so every company can use
     * the product.
     */
    public function approve(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $companyId = $product->company_id;

            $ownValues = collect(ShopProduct::OVERRIDABLE)
                ->mapWithKeys(fn (string $key) => [$key => $product->baseValue($key)])
                ->reject(fn ($value) => $value === null)
                ->all();

            if ($companyId && $ownValues !== []) {
                CompanyProduct::updateOrCreate(['company_id' => $companyId, 'product_id' => $product->id], $ownValues);
            }

            Category::withoutGlobalScopes()->whereIn('id', array_filter([$product->category_id, $product->sub_category_id]))->update(['company_id' => null]);
            Brand::withoutGlobalScopes()->whereKey($product->brand_id)->update(['company_id' => null]);
            Unit::withoutGlobalScopes()->whereIn('id', DB::table('product_units')->where('product_id', $product->id)->pluck('unit_id'))->update(['company_id' => null]);

            Product::withoutGlobalScopes()->whereKey($product->id)->update([
                'company_id' => null,
                'suggested_at' => null,
                'suggested_by' => null,
            ]);
        });
    }

    /**
     * Products the company can already see that look like the one being
     * created: the same barcode, or a similar name.
     *
     * @return Collection<int, Product>
     */
    public function findDuplicates(?string $name, ?string $barcode, ?int $ignoreProductId = null): Collection
    {
        $name = trim((string) $name);
        $barcode = trim((string) $barcode);

        if (mb_strlen($name) < 3 && $barcode === '') {
            return new Collection;
        }

        return Product::query()
            ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
            ->where(function ($query) use ($name, $barcode) {
                if ($barcode !== '') {
                    $query->orWhere('barcode', $barcode);
                }
                if (mb_strlen($name) >= 3) {
                    $query->orWhere('name', 'like', '%'.$name.'%');
                }
            })
            ->orderBy('name')
            ->limit(5)
            ->get();
    }
}
