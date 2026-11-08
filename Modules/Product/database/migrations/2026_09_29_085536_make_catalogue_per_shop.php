<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The catalogue becomes per shop: each shop keeps its own products,
 * categories, brands, units and models.
 *
 * - A barcode or SKU is unique within a shop (two shops may carry the same
 *   item), not across all shops.
 * - A company's records without a shop go to its shop when it has just one.
 * - Records a shop created that another shop already uses (a product listed
 *   there, its category, brand or units, a category it sells) stay at
 *   company level (no shop), so no shop loses what it uses today; new
 *   records always belong to one shop.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = ['products', 'categories', 'brands', 'units', 'product_models'];

    public function up(): void
    {
        if (Schema::hasIndex('products', ['sku'], 'unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['sku']);
                $table->dropUnique(['barcode']);
                $table->unique(['shop_id', 'sku']);
                $table->unique(['shop_id', 'barcode']);
            });
        }

        $this->keepSharedRecordsAtCompanyLevel();

        $singleShopCompanies = DB::table('shops')
            ->whereNotNull('company_id')
            ->groupBy('company_id')
            ->havingRaw('COUNT(*) = 1')
            ->selectRaw('company_id, MIN(id) as shop_id')
            ->get();

        foreach ($singleShopCompanies as $company) {
            foreach ($this->tables as $table) {
                DB::table($table)
                    ->where('company_id', $company->company_id)
                    ->whereNull('shop_id')
                    ->when($table === 'categories', fn ($query) => $query->where('type', 'product'))
                    ->update(['shop_id' => $company->shop_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'sku']);
            $table->dropUnique(['shop_id', 'barcode']);
            $table->unique('sku');
            $table->unique('barcode');
        });
    }

    private function keepSharedRecordsAtCompanyLevel(): void
    {
        $this->toCompanyLevel('products', DB::table('shop_products')
            ->join('products', 'products.id', '=', 'shop_products.product_id')
            ->whereNotNull('products.company_id')
            ->whereNotNull('products.shop_id')
            ->whereColumn('shop_products.shop_id', '!=', 'products.shop_id')
            ->distinct()
            ->pluck('products.id')
            ->all());

        $companyProducts = fn () => DB::table('products')->whereNotNull('company_id')->whereNull('shop_id');
        $shopProducts = fn () => DB::table('products')->whereNotNull('products.shop_id');

        $categoryIds = collect()
            ->merge($companyProducts()->pluck('category_id'))
            ->merge($companyProducts()->pluck('sub_category_id'))
            ->merge($shopProducts()->join('categories', 'categories.id', '=', 'products.category_id')->whereColumn('categories.shop_id', '!=', 'products.shop_id')->pluck('categories.id'))
            ->merge($shopProducts()->join('categories', 'categories.id', '=', 'products.sub_category_id')->whereColumn('categories.shop_id', '!=', 'products.shop_id')->pluck('categories.id'))
            ->merge(DB::table('shop_category')->join('categories', 'categories.id', '=', 'shop_category.category_id')->whereColumn('categories.shop_id', '!=', 'shop_category.shop_id')->pluck('categories.id'))
            ->filter()
            ->unique();
        $categoryIds = $categoryIds->merge(DB::table('categories')->whereIn('id', $categoryIds)->whereNotNull('parent_id')->pluck('parent_id'))->unique();
        $this->toCompanyLevel('categories', $categoryIds->values()->all());

        $this->toCompanyLevel('brands', collect()
            ->merge($companyProducts()->pluck('brand_id'))
            ->merge($shopProducts()->join('brands', 'brands.id', '=', 'products.brand_id')->whereColumn('brands.shop_id', '!=', 'products.shop_id')->pluck('brands.id'))
            ->filter()
            ->unique()
            ->values()
            ->all());

        $this->toCompanyLevel('units', collect()
            ->merge(DB::table('product_units')->whereIn('product_id', $companyProducts()->select('id'))->pluck('unit_id'))
            ->merge(DB::table('product_units')
                ->join('products', 'products.id', '=', 'product_units.product_id')
                ->join('units', 'units.id', '=', 'product_units.unit_id')
                ->whereNotNull('products.shop_id')
                ->whereColumn('units.shop_id', '!=', 'products.shop_id')
                ->pluck('units.id'))
            ->filter()
            ->unique()
            ->values()
            ->all());
    }

    /**
     * @param  list<int>  $ids
     */
    private function toCompanyLevel(string $table, array $ids): void
    {
        foreach (array_chunk($ids, 500) as $chunk) {
            DB::table($table)->whereIn('id', $chunk)->whereNotNull('company_id')->update(['shop_id' => null]);
        }
    }
};
