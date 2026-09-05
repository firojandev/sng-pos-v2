<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Sales\DataTables\SalesDataTable;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected Product $product;

    protected Batch $batch;

    protected Account $cashAccount;

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
            'name' => 'Sales Test Shop',
            'slug' => 'sales-test-shop',
            'phone' => '01711000000',
            'address' => 'ঢাকা, বাংলাদেশ',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'is_main' => true,
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'shop_id' => $this->shop->id,
            'email' => 'sales-admin@example.com',
        ]);
        $this->user->assignRole($adminRole);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'রহিম আহমেদ',
            'phone' => '01811223344',
            'address' => 'ধানমন্ডি, ঢাকা',
            'opening_due' => 0,
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'ইলেকট্রনিক্স',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'স্মার্ট ওয়াচ',
            'sku' => 'SW-001',
            'cost_price' => 1500,
            'selling_price' => 2500,
            'status' => 'active',
        ]);

        $this->batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BAT-SW-101',
            'quantity' => 50,
            'cost_price' => 1500,
            'selling_price' => 2500,
            'status' => 'active',
        ]);

        $this->cashAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash in Hand',
            'type' => 'cash',
            'opening_balance' => 100000,
            'current_balance' => 100000,
            'status' => 'active',
            'is_default' => true,
        ]);
    }

    public function test_sales_datatable_generates_html_builder(): void
    {
        $dataTable = new SalesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('sales-data-table', $html->getTableAttribute('id'));
        $this->assertCount(10, $dataTable->getColumns());
    }

    public function test_sales_datatable_query_returns_query_builder(): void
    {
        $dataTable = new SalesDataTable;
        $query = $dataTable->query(new Sale);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_sales_ledger_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('sales.ledger'));

        $response->assertOk();
        $response->assertSee('sales-data-table');
        $response->assertSee('btn-reset-filters');
        $response->assertSee('filter-from');
        $response->assertSee('filter-to');
        $response->assertSee('filter-status');
        $response->assertSee('total-sale-amount');
        $response->assertSee('total-paid-amount');
        $response->assertSee('total-due-amount');
        $response->assertSee('total-invoice-count');
        $response->assertSee('btn-print-ledger');
        $response->assertSee('saleDetailDrawer');
        $response->assertSee('saleInvoiceModalContainer');
    }

    public function test_sales_datatable_ajax_returns_json_and_records(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-7001',
            'sale_date' => now()->toDateString(),
            'subtotal' => 2500,
            'discount' => 100,
            'delivery_charge' => 50,
            'total' => 2450,
            'paid_amount' => 2000,
            'due_amount' => 450,
            'payment_status' => 'partial',
            'note' => 'টেস্ট বিক্রয় নোট',
        ]);

        $sale->items()->create([
            'product_id' => $this->product->id,
            'batch_id' => $this->batch->id,
            'quantity' => 1,
            'unit_price' => 2500,
            'discount' => 100,
            'total' => 2400,
        ]);

        $sale->payments()->create([
            'account_id' => $this->cashAccount->id,
            'method' => 'cash',
            'amount' => 2000,
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('sales.ledger'), [
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
        $this->assertStringContainsString('SL-7001', $json['data'][0]['invoice_no']);
        $this->assertStringContainsString('রহিম আহমেদ', $json['data'][0]['customer']);
        $this->assertStringContainsString('BAT-SW-101', $json['data'][0]['batch_no']);
        $this->assertStringContainsString('2,450.00', $json['data'][0]['total']);
        $this->assertStringContainsString('2,000.00', $json['data'][0]['paid_amount']);
        $this->assertStringContainsString('450.00', $json['data'][0]['due_amount']);
        $this->assertEquals('2,450.00', $json['totalAmount']);
        $this->assertEquals('2,000.00', $json['totalPaid']);
        $this->assertEquals('450.00', $json['totalDue']);
        $this->assertEquals(1, $json['totalCount']);
    }

    public function test_sales_datatable_filters_by_status_and_dates(): void
    {
        Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-PAID-01',
            'sale_date' => '2026-05-10',
            'subtotal' => 1000,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1000,
            'paid_amount' => 1000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-DUE-01',
            'sale_date' => '2026-05-15',
            'subtotal' => 2000,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 2000,
            'paid_amount' => 0,
            'due_amount' => 2000,
            'payment_status' => 'due',
        ]);

        // Filter by paid
        $response = $this->actingAs($this->user)
            ->getJson(route('sales.ledger', ['status' => 'paid']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $json = $response->json();
        $this->assertEquals(1, $json['recordsFiltered']);
        $this->assertStringContainsString('SL-PAID-01', $json['data'][0]['invoice_no']);
        $this->assertEquals('1,000.00', $json['totalAmount']);

        // Filter by date range
        $responseDate = $this->actingAs($this->user)
            ->getJson(route('sales.ledger', ['from' => '2026-05-12', 'to' => '2026-05-20']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $jsonDate = $responseDate->json();
        $this->assertEquals(1, $jsonDate['recordsFiltered']);
        $this->assertStringContainsString('SL-DUE-01', $jsonDate['data'][0]['invoice_no']);
    }

    public function test_sales_datatable_search_filters_records(): void
    {
        Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-TARGET-99',
            'sale_date' => now()->toDateString(),
            'subtotal' => 500,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 500,
            'paid_amount' => 500,
            'due_amount' => 0,
            'payment_status' => 'paid',
            'note' => 'UniqueKeywordX',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('sales.ledger', ['search' => ['value' => 'UniqueKeywordX']]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $json = $response->json();
        $this->assertEquals(1, $json['recordsFiltered']);
        $this->assertStringContainsString('SL-TARGET-99', $json['data'][0]['invoice_no']);
    }

    public function test_sales_show_returns_detail_drawer_html(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-DRAWER-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 2500,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 2500,
            'paid_amount' => 2500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $sale->items()->create([
            'product_id' => $this->product->id,
            'batch_id' => $this->batch->id,
            'quantity' => 1,
            'unit_price' => 2500,
            'total' => 2500,
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.show', $sale));

        $response->assertOk();
        $response->assertSee('SL-DRAWER-01');
        $response->assertSee('রহিম আহমেদ');
        $response->assertSee('স্মার্ট ওয়াচ');
        $response->assertSee('BAT-SW-101');
        $response->assertSee('Main Warehouse');
    }

    public function test_sales_ledger_print_page_loads_successfully(): void
    {
        Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-PRINT-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 3000,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 3000,
            'paid_amount' => 3000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.ledger.print'));

        $response->assertOk();
        $response->assertSee('বিক্রয় খাতা প্রতিবেদন');
        $response->assertSee('Sales Test Shop');
        $response->assertSee('SL-PRINT-01');
        $response->assertSee('3,000.00');
    }
}
