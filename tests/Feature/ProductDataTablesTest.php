<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\DataTables\BatchesDataTable;
use Modules\Product\DataTables\ModelsDataTable;
use Modules\Product\DataTables\ProductsDataTable;
use Modules\Product\DataTables\SubCategoriesDataTable;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductModel;
use Modules\Product\Models\SubCategory;
use Modules\Product\Models\Unit;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductDataTablesTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);
    }

    public function test_sub_categories_datatable_generates_html_and_query(): void
    {
        $dataTable = new SubCategoriesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('sub-categories-data-table', $html->getTableAttribute('id'));
        $this->assertInstanceOf(Builder::class, $dataTable->query(new SubCategory));
    }

    public function test_sub_categories_datatable_ajax_returns_data(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Electronics']);
        SubCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Smartphones']);

        $response = $this->actingAs($this->user)
            ->getJson(route('sub-categories.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('Smartphones', $response->json('data.0.name'));
        $this->assertStringContainsString('Electronics', $response->json('data.0.parent_category'));
    }

    public function test_models_datatable_generates_html_and_query(): void
    {
        $dataTable = new ModelsDataTable;
        $html = $dataTable->html();

        $this->assertEquals('models-data-table', $html->getTableAttribute('id'));
        $this->assertInstanceOf(Builder::class, $dataTable->query(new ProductModel));
    }

    public function test_models_datatable_ajax_returns_data(): void
    {
        $brand = Brand::create(['shop_id' => $this->shop->id, 'name' => 'Samsung']);
        ProductModel::create(['shop_id' => $this->shop->id, 'brand_id' => $brand->id, 'name' => 'Galaxy S24']);

        $response = $this->actingAs($this->user)
            ->getJson(route('models.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('Galaxy S24', $response->json('data.0.name'));
        $this->assertStringContainsString('Samsung', $response->json('data.0.brand'));
    }

    public function test_batches_datatable_generates_html_and_query(): void
    {
        $dataTable = new BatchesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('batches-data-table', $html->getTableAttribute('id'));
        $this->assertInstanceOf(Builder::class, $dataTable->query(new Batch));
    }

    public function test_batches_datatable_ajax_returns_data(): void
    {
        $unit = Unit::create(['shop_id' => $this->shop->id, 'name' => 'Pcs', 'short_code' => 'pcs']);
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'General']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'TP-001',
            'purchase_price' => 100,
            'sale_price' => 150,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'batch_no' => 'BT-2026-999',
            'quantity' => 100,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('batches.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('BT-2026-999', $response->json('data.0.batch_no'));
        $this->assertStringContainsString('Test Product', $response->json('data.0.product'));
    }

    public function test_products_datatable_generates_html_and_query(): void
    {
        $dataTable = new ProductsDataTable;
        $html = $dataTable->html();

        $this->assertEquals('products-data-table', $html->getTableAttribute('id'));
        $this->assertInstanceOf(Builder::class, $dataTable->query(new Product));
    }

    public function test_products_datatable_ajax_returns_data(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'General']);
        $brand = Brand::create(['shop_id' => $this->shop->id, 'name' => 'Apple']);
        $unit = Unit::create(['shop_id' => $this->shop->id, 'name' => 'Pcs', 'short_code' => 'pcs']);

        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'iPhone 15',
            'sku' => 'IP-15-PRO',
            'purchase_price' => 900,
            'sale_price' => 1200,
            'status' => 'active',
        ]);
        $product->units()->sync([$unit->id => ['is_base' => true, 'conversion_factor' => 1, 'is_smaller_unit' => false]]);

        $response = $this->actingAs($this->user)
            ->getJson(route('products.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('iPhone 15', $response->json('data.0.name'));
        $this->assertStringContainsString('IP-15-PRO', $response->json('data.0.sku'));
        $this->assertStringContainsString('Apple', $response->json('data.0.brand'));
    }

    public function test_product_datatables_index_views_render_properly(): void
    {
        $responseProducts = $this->actingAs($this->user)->get(route('products.index'));
        $responseProducts->assertOk();
        $responseProducts->assertSee('products-data-table');

        $responseSubs = $this->actingAs($this->user)->get(route('sub-categories.index'));
        $responseSubs->assertOk();
        $responseSubs->assertSee('sub-categories-data-table');
        $responseSubs->assertSee('createSubCategoryModal');

        $responseModels = $this->actingAs($this->user)->get(route('models.index'));
        $responseModels->assertOk();
        $responseModels->assertSee('models-data-table');
        $responseModels->assertSee('createModelModal');

        $responseBatches = $this->actingAs($this->user)->get(route('batches.index'));
        $responseBatches->assertOk();
        $responseBatches->assertSee('batches-data-table');
        $responseBatches->assertSee('createBatchModal');
    }

    public function test_products_datatable_action_column_includes_stock_history_button(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'General']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'MacBook Pro',
            'sku' => 'MBP-14',
            'purchase_price' => 2000,
            'sale_price' => 2500,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('products.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $actionHtml = $response->json('data.0.action');

        $this->assertStringContainsString('btn-stock-history', $actionHtml);
        $this->assertStringContainsString(route('stock.history', ['product_id' => $product->id]), $actionHtml);
        $this->assertStringContainsString(route('products.stock-history', $product), $actionHtml);
    }

    public function test_product_stock_history_modal_returns_modal_view_with_movements(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'General']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'iPad Air',
            'sku' => 'IPAD-AIR',
            'purchase_price' => 600,
            'sale_price' => 750,
            'status' => 'active',
        ]);

        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'batch_no' => 'BT-IPAD-01',
            'quantity' => 15,
        ]);

        $product->stockMovements()->create([
            'shop_id' => $this->shop->id,
            'batch_id' => $batch->id,
            'type' => 'adjustment_increase',
            'quantity_change' => 15,
            'quantity_before' => 0,
            'quantity_after' => 15,
            'note' => 'Initial inventory',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.stock-history', $product));

        $response->assertOk();
        $response->assertSee('stockHistoryModal');
        $response->assertSee('স্টকের ইতিহাস: iPad Air');
        $response->assertSee('SKU: IPAD-AIR');
        $response->assertSee('BT-IPAD-01');
        $response->assertSee('+15');
        $response->assertSee('Initial inventory');
    }

    public function test_stock_history_page_filters_by_product_id(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'General']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'AirPods Pro',
            'sku' => 'APP-2',
            'purchase_price' => 200,
            'sale_price' => 250,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('stock.history', ['product_id' => $product->id]));

        $response->assertOk();
        $response->assertSee('AirPods Pro');
        $response->assertSee('SKU: APP-2');
    }
}
