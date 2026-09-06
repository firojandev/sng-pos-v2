<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DueLedgerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Branch $branch;

    protected Warehouse $warehouse;

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
            'name' => 'Due Ledger Test Shop',
            'slug' => 'due-ledger-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Due Ledger Admin',
            'email' => 'admin@dueledger.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $this->branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'code' => 'BR-01',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-01',
            'status' => 'active',
        ]);
    }

    public function test_sales_due_ledger_page_loads_with_datatable_and_totals(): void
    {
        Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Rahim Customer',
            'phone' => '01711000111',
            'opening_due' => 500.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.sales'));

        $response->assertOk();
        $response->assertSee('sales-due-data-table');
        $response->assertSee('customerDetailDrawer');
        $response->assertSee('500.00');
    }

    public function test_sales_due_ledger_datatable_ajax_returns_rich_customer_data(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Karim Customer',
            'phone' => '01822000222',
            'email' => 'karim@test.com',
            'address' => 'Mirpur-10, Dhaka',
            'opening_due' => 300.00,
            'status' => 'active',
        ]);

        Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-TEST-001',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1200.00,
            'tax' => 0,
            'discount' => 0,
            'shipping' => 0,
            'total' => 1200.00,
            'paid_amount' => 200.00,
            'due_amount' => 1000.00,
            'payment_status' => 'partial',
            'order_status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('due-ledger.sales'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
            'totalDue',
        ]);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $data = $response->json('data.0');

        $this->assertStringContainsString('Karim Customer', $data['name']);
        $this->assertStringContainsString('01822000222', $data['name']);
        $this->assertStringContainsString('Mirpur-10, Dhaka', $data['name']);
        $this->assertStringContainsString('1 টি বিক্রয়', $data['sales_summary']);
        $this->assertStringContainsString('300.00', $data['opening_due']);
        $this->assertStringContainsString('1,000.00', $data['sales_due']);
        $this->assertStringContainsString('1,300.00', $data['total_due']);
        $this->assertStringContainsString('INV-TEST-001', $data['last_sale']);
        $this->assertStringContainsString('btn-view-customer-due', $data['action']);
        $this->assertEquals('1,300.00', $response->json('totalDue'));
    }

    public function test_purchase_due_ledger_page_loads_with_datatable_and_totals(): void
    {
        Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Bengal Traders',
            'phone' => '01933000333',
            'opening_due' => 750.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.purchase'));

        $response->assertOk();
        $response->assertSee('purchase-due-data-table');
        $response->assertSee('supplierDetailDrawer');
        $response->assertSee('750.00');
    }

    public function test_purchase_due_ledger_datatable_ajax_returns_rich_supplier_data(): void
    {
        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Padma Suppliers',
            'phone' => '01644000444',
            'email' => 'padma@test.com',
            'address' => 'Motijheel, Dhaka',
            'opening_due' => 400.00,
            'status' => 'active',
        ]);

        Purchase::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'PUR-TEST-001',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 2500.00,
            'tax' => 0,
            'discount' => 0,
            'shipping' => 0,
            'total' => 2500.00,
            'paid_amount' => 500.00,
            'due_amount' => 2000.00,
            'payment_status' => 'partial',
            'order_status' => 'received',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('due-ledger.purchase'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
            'totalDue',
        ]);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $data = $response->json('data.0');

        $this->assertStringContainsString('Padma Suppliers', $data['name']);
        $this->assertStringContainsString('01644000444', $data['name']);
        $this->assertStringContainsString('Motijheel, Dhaka', $data['name']);
        $this->assertStringContainsString('1 টি ক্রয়', $data['purchase_summary']);
        $this->assertStringContainsString('400.00', $data['opening_due']);
        $this->assertStringContainsString('2,000.00', $data['purchase_due']);
        $this->assertStringContainsString('2,400.00', $data['total_due']);
        $this->assertStringContainsString('PUR-TEST-001', $data['last_purchase']);
        $this->assertStringContainsString('btn-view-supplier-due', $data['action']);
        $this->assertEquals('2,400.00', $response->json('totalDue'));
    }

    public function test_customer_details_drawer_endpoint_returns_unpaid_sales_breakdown(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Drawer Customer',
            'phone' => '01755667788',
            'address' => 'Banani, Dhaka',
            'opening_due' => 250.00,
            'status' => 'active',
        ]);

        Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-DRAWER-999',
            'sale_date' => now()->toDateString(),
            'subtotal' => 800.00,
            'tax' => 0,
            'discount' => 0,
            'shipping' => 0,
            'total' => 800.00,
            'paid_amount' => 100.00,
            'due_amount' => 700.00,
            'payment_status' => 'partial',
            'order_status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.customer.details', $customer));

        $response->assertOk();
        $response->assertSee('Drawer Customer');
        $response->assertSee('01755667788');
        $response->assertSee('Banani, Dhaka');
        $response->assertSee('250.00');
        $response->assertSee('INV-DRAWER-999');
        $response->assertSee('700.00');
        $response->assertSee('950.00'); // Total due = 250 + 700
    }

    public function test_supplier_details_drawer_endpoint_returns_unpaid_purchases_breakdown(): void
    {
        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Drawer Supplier',
            'phone' => '01899001122',
            'address' => 'Tejgaon, Dhaka',
            'opening_due' => 350.00,
            'status' => 'active',
        ]);

        Purchase::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'PUR-DRAWER-888',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 1500.00,
            'tax' => 0,
            'discount' => 0,
            'shipping' => 0,
            'total' => 1500.00,
            'paid_amount' => 300.00,
            'due_amount' => 1200.00,
            'payment_status' => 'partial',
            'order_status' => 'received',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.supplier.details', $supplier));

        $response->assertOk();
        $response->assertSee('Drawer Supplier');
        $response->assertSee('01899001122');
        $response->assertSee('Tejgaon, Dhaka');
        $response->assertSee('350.00');
        $response->assertSee('PUR-DRAWER-888');
        $response->assertSee('1,200.00');
        $response->assertSee('1,550.00'); // Total due = 350 + 1200
    }

    public function test_due_ledger_index_delegates_to_sales_or_purchase(): void
    {
        $salesResponse = $this->actingAs($this->user)->get(route('due-ledger.index'));
        $salesResponse->assertOk();
        $salesResponse->assertSee('sales-due-data-table');

        $purchaseResponse = $this->actingAs($this->user)->get(route('due-ledger.index', ['type' => 'supplier']));
        $purchaseResponse->assertOk();
        $purchaseResponse->assertSee('purchase-due-data-table');
    }

    public function test_sidebar_contains_separated_due_ledger_links(): void
    {
        $response = $this->actingAs($this->user)->get(route('due-ledger.sales'));

        $response->assertOk();
        $response->assertSee(route('due-ledger.sales'));
        $response->assertSee(route('due-ledger.purchase'));
    }

    public function test_customer_payment_modal_loads_with_invoices_and_accounts(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'current_balance' => 10000.00,
            'status' => 'active',
            'is_default' => true,
        ]);

        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Modal Test Customer',
            'phone' => '01700112233',
            'opening_due' => 500.00,
            'status' => 'active',
        ]);

        Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-MODAL-01',
            'sale_date' => now()->subDays(2)->toDateString(),
            'subtotal' => 3000.00,
            'total' => 3000.00,
            'paid_amount' => 1000.00,
            'due_amount' => 2000.00,
            'payment_status' => 'partial',
            'order_status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.customer.payment-modal', $customer));

        $response->assertOk();
        $response->assertSee('Modal Test Customer');
        $response->assertSee('INV-MODAL-01');
        $response->assertSee('Main Cash');
        $response->assertSee('500.00'); // opening due
        $response->assertSee('2,500.00'); // total due = 500 + 2000
    }

    public function test_supplier_payment_modal_loads_with_purchases_and_accounts(): void
    {
        Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Bank Account',
            'type' => 'bank',
            'opening_balance' => 50000.00,
            'current_balance' => 50000.00,
            'status' => 'active',
            'is_default' => true,
        ]);

        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Modal Test Supplier',
            'phone' => '01800223344',
            'opening_due' => 800.00,
            'status' => 'active',
        ]);

        Purchase::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'PUR-MODAL-01',
            'purchase_date' => now()->subDays(3)->toDateString(),
            'subtotal' => 4000.00,
            'total' => 4000.00,
            'paid_amount' => 1000.00,
            'due_amount' => 3000.00,
            'payment_status' => 'partial',
            'order_status' => 'received',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.supplier.payment-modal', $supplier));

        $response->assertOk();
        $response->assertSee('Modal Test Supplier');
        $response->assertSee('PUR-MODAL-01');
        $response->assertSee('Bank Account');
        $response->assertSee('800.00'); // opening due
        $response->assertSee('3,800.00'); // total due = 800 + 3000
    }

    public function test_customer_payment_store_auto_allocates_fifo_across_opening_and_invoices(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Box',
            'type' => 'cash',
            'opening_balance' => 0,
            'current_balance' => 0,
            'status' => 'active',
            'is_default' => true,
        ]);

        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'FIFO Customer',
            'opening_due' => 500.00,
            'status' => 'active',
        ]);

        $sale1 = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-FIFO-01',
            'sale_date' => now()->subDays(5)->toDateString(),
            'subtotal' => 1000.00,
            'total' => 1000.00,
            'paid_amount' => 0,
            'due_amount' => 1000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        $sale2 = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-FIFO-02',
            'sale_date' => now()->subDays(1)->toDateString(),
            'subtotal' => 2000.00,
            'total' => 2000.00,
            'paid_amount' => 0,
            'due_amount' => 2000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        // Total due = 500 + 1000 + 2000 = 3500.
        // User pays 1200:
        // 500 goes to opening due -> opening_due becomes 0.
        // Remaining 700 goes to sale 1 -> sale 1 due becomes 300, paid becomes 700, status becomes partial.
        // Sale 2 remains untouched (due 2000).
        $response = $this->actingAs($this->user)->postJson(route('due-ledger.customer.payment.store', $customer), [
            'payment_date' => now()->toDateString(),
            'account_id' => $account->id,
            'payment_method' => 'cash',
            'total_amount' => 1200.00,
            'note' => 'Partial settlement test',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $customer->refresh();
        $sale1->refresh();
        $sale2->refresh();
        $account->refresh();

        $this->assertEquals(0.00, (float) $customer->opening_due);
        $this->assertEquals(700.00, (float) $sale1->paid_amount);
        $this->assertEquals(300.00, (float) $sale1->due_amount);
        $this->assertEquals('partial', $sale1->payment_status);
        $this->assertEquals(2000.00, (float) $sale2->due_amount);
        $this->assertEquals(1200.00, (float) $account->current_balance);
    }

    public function test_supplier_payment_store_auto_allocates_fifo_across_opening_and_purchases(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Supplier Payment Cash',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'current_balance' => 10000.00,
            'status' => 'active',
            'is_default' => true,
        ]);

        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'FIFO Supplier',
            'opening_due' => 400.00,
            'status' => 'active',
        ]);

        $purchase1 = Purchase::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'PUR-FIFO-01',
            'purchase_date' => now()->subDays(4)->toDateString(),
            'subtotal' => 1500.00,
            'total' => 1500.00,
            'paid_amount' => 0,
            'due_amount' => 1500.00,
            'payment_status' => 'due',
            'order_status' => 'received',
        ]);

        $purchase2 = Purchase::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'PUR-FIFO-02',
            'purchase_date' => now()->subDays(2)->toDateString(),
            'subtotal' => 2000.00,
            'total' => 2000.00,
            'paid_amount' => 0,
            'due_amount' => 2000.00,
            'payment_status' => 'due',
            'order_status' => 'received',
        ]);

        // Total due = 400 + 1500 + 2000 = 3900.
        // Pay 2500:
        // 400 to opening due -> opening becomes 0
        // 1500 to purchase 1 -> purchase 1 fully paid (due 0, status paid)
        // 600 to purchase 2 -> purchase 2 paid 600, due 1400, status partial
        $response = $this->actingAs($this->user)->postJson(route('due-ledger.supplier.payment.store', $supplier), [
            'payment_date' => now()->toDateString(),
            'account_id' => $account->id,
            'payment_method' => 'cash',
            'total_amount' => 2500.00,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $supplier->refresh();
        $purchase1->refresh();
        $purchase2->refresh();
        $account->refresh();

        $this->assertEquals(0.00, (float) $supplier->opening_due);
        $this->assertEquals(1500.00, (float) $purchase1->paid_amount);
        $this->assertEquals(0.00, (float) $purchase1->due_amount);
        $this->assertEquals('paid', $purchase1->payment_status);
        $this->assertEquals(600.00, (float) $purchase2->paid_amount);
        $this->assertEquals(1400.00, (float) $purchase2->due_amount);
        $this->assertEquals('partial', $purchase2->payment_status);
        $this->assertEquals(7500.00, (float) $account->current_balance); // 10000 - 2500
    }

    public function test_exact_user_scenario_five_invoices_150k_pay_12k_two_fully_paid_one_partial(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Account',
            'type' => 'cash',
            'opening_balance' => 0,
            'current_balance' => 0,
            'status' => 'active',
            'is_default' => true,
        ]);

        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Scenario Customer',
            'opening_due' => 0.00,
            'status' => 'active',
        ]);

        // 5 invoices totaling 150,000 due:
        // Invoice 1: 4,000
        // Invoice 2: 5,000
        // Invoice 3: 6,000
        // Invoice 4: 50,000
        // Invoice 5: 85,000
        $inv1 = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-001',
            'sale_date' => now()->subDays(10)->toDateString(),
            'subtotal' => 4000.00,
            'total' => 4000.00,
            'paid_amount' => 0,
            'due_amount' => 4000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        $inv2 = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-002',
            'sale_date' => now()->subDays(8)->toDateString(),
            'subtotal' => 5000.00,
            'total' => 5000.00,
            'paid_amount' => 0,
            'due_amount' => 5000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        $inv3 = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-003',
            'sale_date' => now()->subDays(6)->toDateString(),
            'subtotal' => 6000.00,
            'total' => 6000.00,
            'paid_amount' => 0,
            'due_amount' => 6000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        $inv4 = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-004',
            'sale_date' => now()->subDays(4)->toDateString(),
            'subtotal' => 50000.00,
            'total' => 50000.00,
            'paid_amount' => 0,
            'due_amount' => 50000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        $inv5 = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-005',
            'sale_date' => now()->subDays(2)->toDateString(),
            'subtotal' => 85000.00,
            'total' => 85000.00,
            'paid_amount' => 0,
            'due_amount' => 85000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        $totalDueBefore = $inv1->due_amount + $inv2->due_amount + $inv3->due_amount + $inv4->due_amount + $inv5->due_amount;
        $this->assertEquals(150000.00, $totalDueBefore);

        // User pays 12,000:
        $response = $this->actingAs($this->user)->postJson(route('due-ledger.customer.payment.store', $customer), [
            'payment_date' => now()->toDateString(),
            'account_id' => $account->id,
            'payment_method' => 'cash',
            'total_amount' => 12000.00,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $inv1->refresh();
        $inv2->refresh();
        $inv3->refresh();
        $inv4->refresh();
        $inv5->refresh();

        // 1st invoice: 4,000 paid -> fully paid
        $this->assertEquals(4000.00, (float) $inv1->paid_amount);
        $this->assertEquals(0.00, (float) $inv1->due_amount);
        $this->assertEquals('paid', $inv1->payment_status);

        // 2nd invoice: 5,000 paid -> fully paid
        $this->assertEquals(5000.00, (float) $inv2->paid_amount);
        $this->assertEquals(0.00, (float) $inv2->due_amount);
        $this->assertEquals('paid', $inv2->payment_status);

        // 3rd invoice: remaining 3,000 paid -> partially paid (due 3,000 left)
        $this->assertEquals(3000.00, (float) $inv3->paid_amount);
        $this->assertEquals(3000.00, (float) $inv3->due_amount);
        $this->assertEquals('partial', $inv3->payment_status);

        // 4th and 5th invoices: untouched
        $this->assertEquals(50000.00, (float) $inv4->due_amount);
        $this->assertEquals('due', $inv4->payment_status);
        $this->assertEquals(85000.00, (float) $inv5->due_amount);
        $this->assertEquals('due', $inv5->payment_status);
    }

    public function test_customer_payment_modal_loads_successfully(): void
    {
        $cashAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Drawer Cash Account',
            'type' => 'cash',
            'opening_balance' => 1000,
            'current_balance' => 1000,
            'status' => 'active',
            'is_default' => true,
        ]);

        $bankAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'City Bank',
            'type' => 'bank',
            'opening_balance' => 5000,
            'current_balance' => 5000,
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Modal Test Customer',
            'opening_due' => 500.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.customer.payment-modal', $customer));

        $response->assertOk();
        $response->assertSee('বাকি আদায় / পেমেন্ট গ্রহণ');
        $response->assertSee('Drawer Cash Account');
        $response->assertSee('City Bank');
        $response->assertSee('customer-payment-type-select');
        $response->assertSee('customer-mode-both');
    }

    public function test_supplier_payment_modal_loads_successfully(): void
    {
        $cashAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash Drawer',
            'type' => 'cash',
            'opening_balance' => 1000,
            'current_balance' => 1000,
            'status' => 'active',
            'is_default' => true,
        ]);

        $bankAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'bKash Merchant',
            'type' => 'mfs',
            'opening_balance' => 3000,
            'current_balance' => 3000,
            'status' => 'active',
        ]);

        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Modal Test Supplier',
            'opening_due' => 1200.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('due-ledger.supplier.payment-modal', $supplier));

        $response->assertOk();
        $response->assertSee('সরবরাহকারী বাকি পরিশোধ / Pay Due');
        $response->assertSee('Main Cash Drawer');
        $response->assertSee('bKash Merchant');
        $response->assertSee('supplier-payment-type-select');
        $response->assertSee('supplier-mode-both');
    }

    public function test_customer_split_payment_both_cash_and_bank(): void
    {
        $cashAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Shop Cash',
            'type' => 'cash',
            'opening_balance' => 0,
            'current_balance' => 0,
            'status' => 'active',
            'is_default' => true,
        ]);

        $bankAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Brac Bank',
            'type' => 'bank',
            'opening_balance' => 0,
            'current_balance' => 0,
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Split Pay Customer',
            'opening_due' => 1000.00,
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'INV-SPLIT-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 2000.00,
            'total' => 2000.00,
            'paid_amount' => 0,
            'due_amount' => 2000.00,
            'payment_status' => 'due',
            'order_status' => 'completed',
        ]);

        // Total due = 1000 (opening) + 2000 (sale) = 3000.
        // Customer pays 2500 total: 1500 Cash, 1000 Bank
        // Allocation FIFO:
        // - 1000 to Opening Due from Cash (cash remaining: 500)
        // - 500 to Sale from Cash (cash remaining: 0)
        // - 1000 to Sale from Bank (bank remaining: 0)
        // Result:
        // - Opening Due becomes 0
        // - Sale paid = 1500 (two SalePayments: 500 cash, 1000 bank), due = 500, status = partial
        // - Cash account balance = 1500
        // - Bank account balance = 1000
        $response = $this->actingAs($this->user)->postJson(route('due-ledger.customer.payment.store', $customer), [
            'payment_date' => now()->toDateString(),
            'payment_type' => 'both',
            'cash_account_id' => $cashAcc->id,
            'both_cash_amount' => 1500.00,
            'both_bank_account_id' => $bankAcc->id,
            'both_bank_amount' => 1000.00,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $customer->refresh();
        $sale->refresh();
        $cashAcc->refresh();
        $bankAcc->refresh();

        $this->assertEquals(0.00, (float) $customer->opening_due);
        $this->assertEquals(1500.00, (float) $sale->paid_amount);
        $this->assertEquals(500.00, (float) $sale->due_amount);
        $this->assertEquals('partial', $sale->payment_status);

        $this->assertCount(2, $sale->payments);
        $cashPayment = $sale->payments->firstWhere('account_id', $cashAcc->id);
        $bankPayment = $sale->payments->firstWhere('account_id', $bankAcc->id);

        $this->assertNotNull($cashPayment);
        $this->assertEquals(500.00, (float) $cashPayment->amount);
        $this->assertEquals('cash', $cashPayment->method);

        $this->assertNotNull($bankPayment);
        $this->assertEquals(1000.00, (float) $bankPayment->amount);
        $this->assertEquals('bank', $bankPayment->method);

        $this->assertEquals(1500.00, (float) $cashAcc->current_balance);
        $this->assertEquals(1000.00, (float) $bankAcc->current_balance);
    }

    public function test_supplier_split_payment_both_cash_and_bank(): void
    {
        $cashAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Shop Cash Supplier',
            'type' => 'cash',
            'opening_balance' => 5000.00,
            'current_balance' => 5000.00,
            'status' => 'active',
            'is_default' => true,
        ]);

        $bankAcc = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Islami Bank',
            'type' => 'bank',
            'opening_balance' => 5000.00,
            'current_balance' => 5000.00,
            'status' => 'active',
        ]);

        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Split Pay Supplier',
            'opening_due' => 1000.00,
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'invoice_no' => 'PUR-SPLIT-01',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 2000.00,
            'total' => 2000.00,
            'paid_amount' => 0,
            'due_amount' => 2000.00,
            'payment_status' => 'due',
            'order_status' => 'received',
        ]);

        // Total due = 1000 (opening) + 2000 (purchase) = 3000.
        // Pay 2500 total: 1500 Cash, 1000 Bank
        // Allocation FIFO:
        // - 1000 to Opening Due from Cash (cash remaining: 500)
        // - 500 to Purchase from Cash (cash remaining: 0)
        // - 1000 to Purchase from Bank (bank remaining: 0)
        // Result:
        // - Opening Due becomes 0
        // - Purchase paid = 1500 (two PurchasePayments: 500 cash, 1000 bank), due = 500, status = partial
        // - Cash account balance = 5000 - 1500 = 3500
        // - Bank account balance = 5000 - 1000 = 4000
        $response = $this->actingAs($this->user)->postJson(route('due-ledger.supplier.payment.store', $supplier), [
            'payment_date' => now()->toDateString(),
            'payment_type' => 'both',
            'cash_account_id' => $cashAcc->id,
            'both_cash_amount' => 1500.00,
            'both_bank_account_id' => $bankAcc->id,
            'both_bank_amount' => 1000.00,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $supplier->refresh();
        $purchase->refresh();
        $cashAcc->refresh();
        $bankAcc->refresh();

        $this->assertEquals(0.00, (float) $supplier->opening_due);
        $this->assertEquals(1500.00, (float) $purchase->paid_amount);
        $this->assertEquals(500.00, (float) $purchase->due_amount);
        $this->assertEquals('partial', $purchase->payment_status);

        $this->assertCount(2, $purchase->payments);
        $cashPayment = $purchase->payments->firstWhere('account_id', $cashAcc->id);
        $bankPayment = $purchase->payments->firstWhere('account_id', $bankAcc->id);

        $this->assertNotNull($cashPayment);
        $this->assertEquals(500.00, (float) $cashPayment->amount);
        $this->assertEquals('cash', $cashPayment->method);

        $this->assertNotNull($bankPayment);
        $this->assertEquals(1000.00, (float) $bankPayment->amount);
        $this->assertEquals('bank', $bankPayment->method);

        $this->assertEquals(3500.00, (float) $cashAcc->current_balance);
        $this->assertEquals(4000.00, (float) $bankAcc->current_balance);
    }
}
