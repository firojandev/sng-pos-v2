<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Purchase\DataTables\PurchasesDataTable;
use Modules\Purchase\Models\Purchase;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchasesDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Product $product;

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
            'name' => 'Purchase Test Shop',
            'slug' => 'purchase-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Purchase Admin',
            'email' => 'admin@purchase.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'code' => 'BR-01',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-01',
            'status' => 'active',
        ]);

        $this->supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Acme Supplies',
            'phone' => '01700112233',
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'General',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Premium Oil',
            'sku' => 'OIL-001',
            'purchase_price' => 100,
            'sale_price' => 120,
            'status' => 'active',
        ]);
    }

    public function test_purchases_datatable_generates_html_builder(): void
    {
        $dataTable = new PurchasesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('purchases-data-table', $html->getTableAttribute('id'));
        $this->assertCount(8, $dataTable->getColumns());
    }

    public function test_purchases_datatable_query_returns_query_builder(): void
    {
        $dataTable = new PurchasesDataTable;
        $query = $dataTable->query(new Purchase);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_purchase_ledger_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase.ledger'));

        $response->assertOk();
        $response->assertSee('purchases-data-table');
        $response->assertSee('btn-reset-filters');
        $response->assertSee('filter-from');
        $response->assertSee('filter-to');
        $response->assertSee('filter-status');
        $response->assertSee('total-purchase-amount');
        $response->assertSee('total-paid-amount');
        $response->assertSee('total-due-amount');
        $response->assertSee('total-invoice-count');
        $response->assertSee('btn-print-ledger');
        $response->assertSee('purchaseDetailDrawer');
    }

    public function test_purchases_datatable_ajax_returns_json_and_records(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'INV-9001',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 500,
            'discount' => 50,
            'delivery_charge' => 20,
            'total' => 470,
            'paid_amount' => 470,
            'due_amount' => 0,
            'payment_status' => 'paid',
            'note' => 'Test Purchase Note',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'batch_no' => 'BAT-01',
            'quantity' => 5,
            'purchase_price' => 100,
            'total' => 500,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('purchase.ledger'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
            'totalAmount',
            'totalPaid',
            'totalDue',
            'totalCount',
        ]);

        $json = $response->json();
        $this->assertEquals(1, $json['recordsTotal']);
        $this->assertStringContainsString('INV-9001', $json['data'][0]['invoice_no']);
        $this->assertStringContainsString('Acme Supplies', $json['data'][0]['supplier']);
        $this->assertStringContainsString('470.00', $json['data'][0]['total']);
        $this->assertEquals('470.00', $json['totalAmount']);
        $this->assertEquals('470.00', $json['totalPaid']);
        $this->assertEquals('0.00', $json['totalDue']);
        $this->assertEquals(1, $json['totalCount']);
    }

    public function test_purchases_datatable_filters_by_status_and_dates(): void
    {
        Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'INV-PAID',
            'purchase_date' => '2026-09-01',
            'subtotal' => 300,
            'total' => 300,
            'paid_amount' => 300,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'INV-DUE',
            'purchase_date' => '2026-09-10',
            'subtotal' => 400,
            'total' => 400,
            'paid_amount' => 0,
            'due_amount' => 400,
            'payment_status' => 'due',
        ]);

        // Filter status = due
        $response = $this->actingAs($this->user)
            ->getJson(route('purchase.ledger', ['status' => 'due']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $json = $response->json();
        $this->assertEquals(1, $json['recordsFiltered']);
        $this->assertStringContainsString('INV-DUE', $json['data'][0]['invoice_no']);

        // Filter date range
        $dateResponse = $this->actingAs($this->user)
            ->getJson(route('purchase.ledger', [
                'from' => '2026-09-01',
                'to' => '2026-09-05',
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $dateResponse->assertOk();
        $dateJson = $dateResponse->json();
        $this->assertEquals(1, $dateJson['recordsFiltered']);
        $this->assertStringContainsString('INV-PAID', $dateJson['data'][0]['invoice_no']);
    }

    public function test_purchase_show_endpoint_returns_detail_drawer(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'INV-DETAIL',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 200,
            'total' => 200,
            'paid_amount' => 200,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'batch_no' => 'BAT-02',
            'quantity' => 2,
            'purchase_price' => 100,
            'total' => 200,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase.show', $purchase));

        $response->assertOk();
        $response->assertSee('INV-DETAIL');
        $response->assertSee('Acme Supplies');
        $response->assertSee('Premium Oil');
        $response->assertSee('closeModal');
    }

    public function test_purchase_destroy_ajax_returns_json_response(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'INV-DELETE',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 100,
            'total' => 100,
            'paid_amount' => 100,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('purchase.destroy', $purchase));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'ক্রয় বাতিল করা হয়েছে',
        ]);

        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
    }

    public function test_purchase_ledger_print_page_loads_with_filters_and_totals(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'INV-PRINT-01',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 800,
            'total' => 800,
            'paid_amount' => 500,
            'due_amount' => 300,
            'payment_status' => 'partial',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'batch_no' => 'BAT-PRINT',
            'quantity' => 2,
            'purchase_price' => 400,
            'total' => 800,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase.ledger.print', [
            'status' => 'partial',
            'q' => 'INV-PRINT-01',
        ]));

        $response->assertOk();
        $response->assertSee('ক্রয় খাতা প্রতিবেদন');
        $response->assertSee('INV-PRINT-01');
        $response->assertSee('Acme Supplies');
        $response->assertSee('BAT-PRINT');
        $response->assertSee('800.00');
        $response->assertSee('500.00');
        $response->assertSee('300.00');
    }
}
