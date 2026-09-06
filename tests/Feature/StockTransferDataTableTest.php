<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\DataTables\StockTransfersDataTable;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockTransfer;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockTransferDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse1;

    protected Warehouse $warehouse2;

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

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'is_main' => true,
            'status' => 'active',
        ]);

        $this->warehouse1 = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Central Warehouse',
            'status' => 'active',
        ]);

        $this->warehouse2 = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Outlet Depot',
            'status' => 'active',
        ]);
    }

    public function test_stock_transfers_datatable_generates_html_and_query(): void
    {
        $dataTable = new StockTransfersDataTable;
        $html = $dataTable->html();

        $this->assertEquals('stock-transfers-data-table', $html->getTableAttribute('id'));
        $this->assertInstanceOf(Builder::class, $dataTable->query(new StockTransfer));
    }

    public function test_stock_transfers_datatable_ajax_returns_data(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Electronics']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Wireless Keyboard',
            'sku' => 'KB-WL',
            'purchase_price' => 1500,
            'sale_price' => 2000,
            'status' => 'active',
        ]);

        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'batch_no' => 'BATCH-KB-01',
            'quantity' => 100,
        ]);

        $transfer = StockTransfer::create([
            'shop_id' => $this->shop->id,
            'transfer_no' => 'TR-0001',
            'from_warehouse_id' => $this->warehouse1->id,
            'to_warehouse_id' => $this->warehouse2->id,
            'status' => 'pending',
            'requested_by' => $this->user->id,
            'note' => 'Inter-branch replenishment',
        ]);

        $transfer->items()->create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'batch_no' => $batch->batch_no,
            'quantity' => 25,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('stock-transfers.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('recordsTotal'));

        $data = $response->json('data.0');
        $this->assertStringContainsString('TR-0001', $data['transfer_no']);
        $this->assertStringContainsString('Central Warehouse', $data['from_warehouse']);
        $this->assertStringContainsString('Outlet Depot', $data['to_warehouse']);
        $this->assertStringContainsString('1 টি আইটেম', $data['items_summary']);
        $this->assertStringContainsString('25', $data['items_summary']);
        $this->assertStringContainsString('btn-view-transfer-detail', $data['action']);
    }

    public function test_stock_transfers_index_view_renders_properly_with_metrics(): void
    {
        $response = $this->actingAs($this->user)->get(route('stock-transfers.index'));

        $response->assertOk();
        $response->assertSee('stock-transfers-data-table');
        $response->assertSee('transferDetailDrawer');
        $response->assertSee('মোট ট্রান্সফার');
        $response->assertSee('অপেক্ষমাণ অনুমোদন');
        $response->assertSee('নতুন ট্রান্সফার');
    }

    public function test_stock_transfers_detail_drawer_ajax(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'General']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Wireless Mouse',
            'sku' => 'MS-WL',
            'purchase_price' => 500,
            'sale_price' => 800,
            'status' => 'active',
        ]);

        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'batch_no' => 'BATCH-MS-01',
            'quantity' => 60,
        ]);

        $transfer = StockTransfer::create([
            'shop_id' => $this->shop->id,
            'transfer_no' => 'TR-0002',
            'from_warehouse_id' => $this->warehouse1->id,
            'to_warehouse_id' => $this->warehouse2->id,
            'status' => 'pending',
            'requested_by' => $this->user->id,
            'note' => 'Branch stock request',
        ]);

        $transfer->items()->create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'batch_no' => $batch->batch_no,
            'quantity' => 15,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('stock-transfers.show', $transfer), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertSee('TR-0002');
        $response->assertSee('Central Warehouse');
        $response->assertSee('Outlet Depot');
        $response->assertSee('Wireless Mouse');
        $response->assertSee('15');
        $response->assertSee('অনুমোদন করুন');
    }

    public function test_stock_transfer_workflow_actions_via_ajax(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Hardware']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'RAM 16GB',
            'sku' => 'RAM-16',
            'purchase_price' => 4000,
            'sale_price' => 5000,
            'status' => 'active',
        ]);

        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'batch_no' => 'BATCH-RAM-01',
            'quantity' => 40,
        ]);

        $transfer = StockTransfer::create([
            'shop_id' => $this->shop->id,
            'transfer_no' => 'TR-0003',
            'from_warehouse_id' => $this->warehouse1->id,
            'to_warehouse_id' => $this->warehouse2->id,
            'status' => 'pending',
            'requested_by' => $this->user->id,
        ]);

        $transfer->items()->create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'batch_no' => $batch->batch_no,
            'quantity' => 10,
        ]);

        // 1. Approve via AJAX
        $responseApprove = $this->actingAs($this->user)
            ->postJson(route('stock-transfers.approve', $transfer));
        $responseApprove->assertOk();
        $responseApprove->assertJson(['success' => true]);
        $this->assertEquals('approved', $transfer->fresh()->status);

        // 2. Dispatch via AJAX
        $responseDispatch = $this->actingAs($this->user)
            ->postJson(route('stock-transfers.dispatch', $transfer));
        $responseDispatch->assertOk();
        $responseDispatch->assertJson(['success' => true]);
        $this->assertEquals('dispatched', $transfer->fresh()->status);
        $this->assertEquals(30, (float) $batch->fresh()->quantity);

        // 3. Receive via AJAX
        $responseReceive = $this->actingAs($this->user)
            ->postJson(route('stock-transfers.receive', $transfer));
        $responseReceive->assertOk();
        $responseReceive->assertJson(['success' => true]);
        $this->assertEquals('received', $transfer->fresh()->status);

        $destBatch = Batch::where('warehouse_id', $this->warehouse2->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertNotNull($destBatch);
        $this->assertEquals(10, (float) $destBatch->quantity);
    }
}
