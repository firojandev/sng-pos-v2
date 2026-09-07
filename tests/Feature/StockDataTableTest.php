<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\DataTables\StockDataTable;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\Unit;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockDataTableTest extends TestCase
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

    public function test_stock_datatable_generates_html_and_query(): void
    {
        $dataTable = new StockDataTable;
        $html = $dataTable->html();

        $this->assertEquals('stock-data-table', $html->getTableAttribute('id'));
        $this->assertInstanceOf(Builder::class, $dataTable->query(new Product));
    }

    public function test_stock_datatable_ajax_returns_data_with_calculations(): void
    {
        $unit = Unit::create(['shop_id' => $this->shop->id, 'name' => 'Pcs', 'short_code' => 'pcs']);
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Beverages']);

        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Mango Juice',
            'sku' => 'MJ-250',
            'purchase_price' => 30,
            'sale_price' => 45,
            'alert_qty' => 10,
            'status' => 'active',
        ]);
        $product->units()->sync([$unit->id => ['is_base' => true, 'conversion_factor' => 1, 'is_smaller_unit' => false]]);

        Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'batch_no' => 'BATCH-MJ-01',
            'quantity' => 50,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('stock.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('recordsTotal'));

        $data = $response->json('data.0');
        $this->assertStringContainsString('Mango Juice', $data['name']);
        $this->assertStringContainsString('MJ-250', $data['name']);
        $this->assertStringContainsString('Beverages', $data['category']);
        $this->assertStringContainsString('50', $data['total_stock']);
        $this->assertStringContainsString('1,500.00', $data['stock_value']);
        $this->assertStringContainsString('btn-adjust-stock', $data['action']);
        $this->assertStringContainsString('btn-stock-history', $data['action']);
    }

    public function test_stock_index_view_renders_properly_with_metrics(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Snacks']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Potato Crackers',
            'sku' => 'PC-100',
            'purchase_price' => 20,
            'sale_price' => 30,
            'alert_qty' => 5,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'batch_no' => 'BATCH-PC-01',
            'quantity' => 25,
        ]);

        $response = $this->actingAs($this->user)->get(route('stock.index'));

        $response->assertOk();
        $response->assertSee('stock-data-table');
        $response->assertSee('stockAdjustModal');
        $response->assertSee('মোট পণ্য');
        $response->assertSee('মোট মজুদ একক');
        $response->assertSee('মোট মজুদ মূল্য');
        $response->assertSee('Potato Crackers');
    }

    public function test_stock_index_metrics_are_scoped_to_current_shop(): void
    {
        $otherShop = Shop::create([
            'name' => 'Other Shop',
            'slug' => 'other-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $otherCategory = Category::create(['shop_id' => $otherShop->id, 'name' => 'Other Category']);
        $otherProduct = Product::create([
            'shop_id' => $otherShop->id,
            'category_id' => $otherCategory->id,
            'name' => 'Expensive Item',
            'sku' => 'EXP-01',
            'purchase_price' => 1000,
            'sale_price' => 1500,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $otherShop->id,
            'product_id' => $otherProduct->id,
            'batch_no' => 'OTHER-BATCH-01',
            'quantity' => 100,
        ]);

        $myCategory = Category::create(['shop_id' => $this->shop->id, 'name' => 'My Category']);
        Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $myCategory->id,
            'name' => 'Zero Stock Item',
            'sku' => 'ZERO-01',
            'purchase_price' => 50,
            'sale_price' => 80,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('stock.index'));

        $response->assertOk();
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['totalProducts'] === 1
                && (float) $metrics['totalQty'] === 0.0
                && (float) $metrics['totalValue'] === 0.0
                && $metrics['outCount'] === 1;
        });
    }

    public function test_stock_adjustment_via_ajax(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Dairy']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Fresh Milk',
            'sku' => 'FM-1L',
            'purchase_price' => 70,
            'sale_price' => 90,
            'status' => 'active',
        ]);

        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'batch_no' => 'BATCH-MILK-01',
            'quantity' => 20,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('stock.adjust'), [
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'type' => 'increase',
                'quantity' => 10,
                'reason' => 'Inventory count surplus',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertEquals(30, (float) $batch->fresh()->quantity);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'type' => 'increase',
            'quantity' => 10,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'type' => 'adjustment_increase',
            'quantity_change' => 10,
        ]);
    }

    public function test_stock_datatable_filters_by_stock_status(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'General']);

        $inStockProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'In Stock Item',
            'sku' => 'IN-01',
            'purchase_price' => 50,
            'sale_price' => 70,
            'alert_qty' => 5,
            'status' => 'active',
        ]);
        Batch::create(['shop_id' => $this->shop->id, 'product_id' => $inStockProduct->id, 'batch_no' => 'B-IN', 'quantity' => 20]);

        $lowStockProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Low Stock Item',
            'sku' => 'LOW-01',
            'purchase_price' => 50,
            'sale_price' => 70,
            'alert_qty' => 10,
            'status' => 'active',
        ]);
        Batch::create(['shop_id' => $this->shop->id, 'product_id' => $lowStockProduct->id, 'batch_no' => 'B-LOW', 'quantity' => 4]);

        $outStockProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Out Stock Item',
            'sku' => 'OUT-01',
            'purchase_price' => 50,
            'sale_price' => 70,
            'alert_qty' => 5,
            'status' => 'active',
        ]);

        // Filter: low
        $responseLow = $this->actingAs($this->user)->getJson(route('stock.index', ['stock_status' => 'low']), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseLow->assertOk();
        $this->assertEquals(1, $responseLow->json('recordsFiltered'));
        $this->assertStringContainsString('Low Stock Item', $responseLow->json('data.0.name'));

        // Filter: out
        $responseOut = $this->actingAs($this->user)->getJson(route('stock.index', ['stock_status' => 'out']), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseOut->assertOk();
        $this->assertEquals(1, $responseOut->json('recordsFiltered'));
        $this->assertStringContainsString('Out Stock Item', $responseOut->json('data.0.name'));

        // Filter: in
        $responseIn = $this->actingAs($this->user)->getJson(route('stock.index', ['stock_status' => 'in']), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseIn->assertOk();
        $this->assertEquals(2, $responseIn->json('recordsFiltered'));
    }
}
