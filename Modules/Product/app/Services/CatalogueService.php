<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShopProduct;
use Modules\Product\Models\Unit;
use Modules\Shop\Models\Shop;

/**
 * The shop's catalogue: which categories it sells, which of its products it
 * lists, its own prices, and the receiving shop's copy of a product another
 * shop transfers stock of (each shop keeps its own catalogue).
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
     * Products the shop could list: its products in the categories it sells
     * that it does not list yet.
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

    /**
     * The receiving shop's own product for one another shop sends it (a
     * stock transfer): the one it already has with the same barcode or name,
     * or a copy — with its category, sub-category, brand and units, matched
     * by name in the shop or copied too.
     */
    public function productForShop(Product $source, Shop $shop): Product
    {
        if ($source->isShared() || (int) $source->shop_id === $shop->id) {
            return $source;
        }

        $existing = Product::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where(fn ($query) => $source->barcode
                ? $query->where('barcode', $source->barcode)
                : $query->where('name', $source->name))
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($source, $shop) {
            $category = $this->categoryForShop($source->category_id, $shop);
            $subCategory = $this->categoryForShop($source->sub_category_id, $shop, $category?->id);
            $brand = $source->brand_id ? Brand::withoutGlobalScopes()->find($source->brand_id) : null;

            $copy = new Product;
            $copy->forceFill([
                ...collect($source->getAttributes())->except(['id', 'company_id', 'shop_id', 'category_id', 'sub_category_id', 'brand_id', 'sku', 'created_at', 'updated_at'])->all(),
                'shop_id' => $shop->id,
                'company_id' => $shop->company_id,
                'category_id' => $category?->id,
                'sub_category_id' => $subCategory?->id,
                'brand_id' => $brand ? $this->ownRecord(Brand::class, $brand, $shop, ['name' => $brand->name])->id : null,
            ])->save();

            $units = DB::table('product_units')->where('product_id', $source->id)->get();
            foreach ($units as $row) {
                $unit = Unit::withoutGlobalScopes()->find($row->unit_id);

                if ($unit) {
                    $copy->units()->attach($this->ownRecord(Unit::class, $unit, $shop, ['short_code' => $unit->short_code])->id, [
                        'is_base' => $row->is_base,
                        'conversion_factor' => $row->conversion_factor,
                        'is_smaller_unit' => $row->is_smaller_unit,
                    ]);
                }
            }

            return $copy;
        });
    }

    private function categoryForShop(?int $categoryId, Shop $shop, ?int $parentId = null): ?Category
    {
        $source = $categoryId ? Category::withoutGlobalScopes()->find($categoryId) : null;

        if (! $source) {
            return null;
        }

        return $this->ownRecord(Category::class, $source, $shop, ['name' => $source->name, 'type' => 'product', 'parent_id' => $parentId]);
    }

    /**
     * The shop's record matching another shop's (by the given columns), or a
     * copy of it; shared records are used as they are.
     *
     * @param  class-string<Model>  $class
     * @param  array<string, mixed>  $match
     */
    private function ownRecord(string $class, $source, Shop $shop, array $match)
    {
        if ($source->company_id === null || (int) $source->shop_id === $shop->id) {
            return $source;
        }

        $record = $class::withoutGlobalScopes()->where('shop_id', $shop->id)->where($match)->first();

        if ($record) {
            return $record;
        }

        $copy = new $class;
        $copy->forceFill([
            ...collect($source->getAttributes())->except(['id', 'company_id', 'shop_id', 'created_at', 'updated_at'])->all(),
            ...$match,
            'shop_id' => $shop->id,
            'company_id' => $shop->company_id,
        ])->save();

        return $copy;
    }
}
