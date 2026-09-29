<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Product management is a shop's alone: no shared catalogue, no
 * suggestions to it, no Super Admin review. Shared records (company_id null)
 * a single shop uses become that shop's own; ones several shops use are
 * left as they are (read-only), so no shop loses what it sells.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'suggested_by')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('suggested_by');
                $table->dropColumn('suggested_at');
            });
        }

        $companyOf = DB::table('shops')->pluck('company_id', 'id');

        // Products: the shops that list or stock them.
        foreach (DB::table('products')->whereNull('company_id')->pluck('id') as $productId) {
            $this->giveToSoleShop('products', $productId, DB::table('shop_products')->where('product_id', $productId)->pluck('shop_id')
                ->merge(DB::table('batches')->where('product_id', $productId)->pluck('shop_id')), $companyOf);
        }

        // Categories: the shops whose products use them (or their
        // sub-categories), and the shops that sell them.
        foreach (DB::table('categories')->whereNull('company_id')->where('type', 'product')->get(['id']) as $category) {
            $familyIds = DB::table('categories')->where('parent_id', $category->id)->pluck('id')->push($category->id);
            $this->giveToSoleShop('categories', $category->id, DB::table('products')->whereNotNull('shop_id')
                ->where(fn ($query) => $query->whereIn('category_id', $familyIds)->orWhereIn('sub_category_id', $familyIds))
                ->pluck('shop_id')
                ->merge(DB::table('shop_category')->where('category_id', $category->id)->pluck('shop_id')), $companyOf);
        }

        foreach (DB::table('brands')->whereNull('company_id')->pluck('id') as $brandId) {
            $this->giveToSoleShop('brands', $brandId, DB::table('products')->whereNotNull('shop_id')->where('brand_id', $brandId)->pluck('shop_id'), $companyOf);
        }

        foreach (DB::table('units')->whereNull('company_id')->pluck('id') as $unitId) {
            $this->giveToSoleShop('units', $unitId, DB::table('product_units')
                ->join('products', 'products.id', '=', 'product_units.product_id')
                ->where('product_units.unit_id', $unitId)
                ->whereNotNull('products.shop_id')
                ->pluck('products.shop_id'), $companyOf);
        }

        // A model goes with its brand.
        foreach (DB::table('product_models')->whereNull('company_id')->whereNotNull('brand_id')->get(['id', 'brand_id']) as $model) {
            $brand = DB::table('brands')->where('id', $model->brand_id)->whereNotNull('shop_id')->first(['shop_id', 'company_id']);

            if ($brand) {
                DB::table('product_models')->where('id', $model->id)->update(['shop_id' => $brand->shop_id, 'company_id' => $brand->company_id]);
            }
        }
    }

    /**
     * The shop's catalogue can't be shared again; the columns come back
     * empty.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('suggested_at')->nullable()->after('company_id');
            $table->foreignId('suggested_by')->nullable()->after('suggested_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * @param  Collection<int, int|null>  $shopIds
     * @param  Collection<int, int|null>  $companyOf
     */
    private function giveToSoleShop(string $table, int $id, Collection $shopIds, Collection $companyOf): void
    {
        $shopIds = $shopIds->filter()->unique()->values();

        if ($shopIds->count() === 1) {
            DB::table($table)->where('id', $id)->update(['shop_id' => $shopIds->first(), 'company_id' => $companyOf[$shopIds->first()] ?? null]);
        }
    }
};
