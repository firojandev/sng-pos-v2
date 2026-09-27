<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pricing layers and shop listings for the shared catalogue.
 *
 * - company_products: a company's own values for a product (used for shared
 *   catalogue products, whose base values belong to everyone).
 * - shop_products: the products a shop sells, with optional shop overrides.
 * - shop_category: the product categories a shop sells.
 *
 * Every override column is nullable; null means "use the next layer".
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $this->overrideColumns($table);
            $table->timestamps();
            $table->unique(['company_id', 'product_id']);
        });

        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $this->overrideColumns($table);
            $table->timestamps();
            $table->unique(['shop_id', 'product_id']);
        });

        Schema::create('shop_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['shop_id', 'category_id']);
        });

        $this->listExistingProducts();
        $this->mapExistingCategories();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_category');
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('company_products');
    }

    private function overrideColumns(Blueprint $table): void
    {
        $table->decimal('purchase_price', 12, 2)->nullable();
        $table->decimal('sale_price', 12, 2)->nullable();
        $table->boolean('is_wholesale')->nullable();
        $table->decimal('wholesale_price', 12, 2)->nullable();
        $table->unsignedInteger('wholesale_min_qty')->nullable();
        $table->boolean('has_discount')->nullable();
        $table->string('discount_type')->nullable();
        $table->decimal('discount_value', 12, 2)->nullable();
        $table->unsignedInteger('alert_qty')->nullable();
        $table->boolean('is_vat')->nullable();
        $table->decimal('vat_percentage', 5, 2)->nullable();
    }

    /**
     * A product is listed in the shop that created it and in every shop that
     * already holds, bought or sold it.
     */
    private function listExistingProducts(): void
    {
        $now = now()->toDateTimeString();
        $columns = ['shop_id', 'product_id', 'created_at', 'updated_at'];

        $sources = [
            DB::table('products')->whereNotNull('shop_id')->select('shop_id', 'id as product_id'),
            DB::table('batches')->whereNotNull('shop_id')->select('shop_id', 'product_id'),
            DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->whereNotNull('sales.shop_id')->select('sales.shop_id', 'sale_items.product_id'),
            DB::table('purchase_items')->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->whereNotNull('purchases.shop_id')->select('purchases.shop_id', 'purchase_items.product_id'),
        ];

        foreach ($sources as $source) {
            DB::table('shop_products')->insertOrIgnoreUsing(
                $columns,
                $source->distinct()->addSelect(DB::raw("'{$now}' as created_at"), DB::raw("'{$now}' as updated_at")),
            );
        }
    }

    /**
     * A shop sells the top-level product categories it created and the
     * categories of the products it lists.
     */
    private function mapExistingCategories(): void
    {
        $now = now()->toDateTimeString();
        $columns = ['shop_id', 'category_id', 'created_at', 'updated_at'];

        $sources = [
            DB::table('categories')->where('type', 'product')->whereNull('parent_id')->whereNotNull('shop_id')
                ->select('shop_id', 'id as category_id'),
            DB::table('shop_products')->join('products', 'products.id', '=', 'shop_products.product_id')
                ->whereNotNull('products.category_id')
                ->select('shop_products.shop_id', 'products.category_id'),
        ];

        foreach ($sources as $source) {
            DB::table('shop_category')->insertOrIgnoreUsing(
                $columns,
                $source->distinct()->addSelect(DB::raw("'{$now}' as created_at"), DB::raw("'{$now}' as updated_at")),
            );
        }
    }
};
