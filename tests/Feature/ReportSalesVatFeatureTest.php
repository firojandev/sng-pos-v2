<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\Category;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\Models\SaleReturn;
use Modules\Sales\Models\SaleReturnItem;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportSalesVatFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'VAT Test Shop',
            'slug' => 'vat-test-shop',
            'status' => 'active',
        ]);

        $this->subscribeShopToFeatures($this->shop, Features::keys());

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'status' => 'active',
            'is_default' => true,
        ]);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Sample Customer',
            'phone' => '01700000000',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'VAT Admin',
            'email' => 'admin@vat.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);
    }

    public function test_sales_vat_report_calculates_test_case_accurately(): void
    {
        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'General Category',
            'type' => 'product',
        ]);

        // Product 1: 15% VAT
        $product1 = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Product 15% VAT',
            'sale_price' => 10000,
            'purchase_price' => 7000,
            'is_vat' => true,
            'vat_percentage' => 15,
            'status' => 'active',
        ]);

        // Product 2: 10% VAT
        $product2 = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Product 10% VAT',
            'sale_price' => 20000,
            'purchase_price' => 14000,
            'is_vat' => true,
            'vat_percentage' => 10,
            'status' => 'active',
        ]);

        $today = now()->toDateString();

        // Sale 1: Taxable 10,000, VAT 15% (1,500)
        $sale1 = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'INV-0001',
            'sale_date' => $today,
            'subtotal' => 10000,
            'discount' => 0,
            'tax' => 1500,
            'total' => 11500,
            'paid_amount' => 11500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);
        $sale1Item = SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'discount' => 0,
            'total' => 10000,
        ]);

        // Sale 2: Taxable 20,000, VAT 10% (2,000)
        $sale2 = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'INV-0002',
            'sale_date' => $today,
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 2000,
            'total' => 22000,
            'paid_amount' => 22000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 20000,
            'discount' => 0,
            'total' => 20000,
        ]);

        // Sales Return: Taxable 2,000 on Product 1 (15% VAT => 300)
        $saleReturn = SaleReturn::create([
            'shop_id' => $this->shop->id,
            'sale_id' => $sale1->id,
            'return_no' => 'RT-0001',
            'return_date' => $today,
            'subtotal' => 2000,
            'refund_amount' => 2000,
            'created_by' => $this->user->id,
        ]);
        SaleReturnItem::create([
            'sale_return_id' => $saleReturn->id,
            'sale_item_id' => $sale1Item->id,
            'product_id' => $product1->id,
            'quantity' => 0.2,
            'unit_price' => 10000,
            'total' => 2000,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.sales-vat', ['range' => 'today']));
        $response->assertOk();

        // 1. Check Summary
        $summary = $response->viewData('summary');
        $this->assertEqualsWithDelta(33500, $summary['gross_sales'], 0.01);
        $this->assertEqualsWithDelta(2000, $summary['sales_return'], 0.01);
        $this->assertEqualsWithDelta(31500, $summary['net_sales'], 0.01);
        $this->assertEqualsWithDelta(30000, $summary['gross_taxable_sales'], 0.01);
        $this->assertEqualsWithDelta(2000, $summary['return_taxable_sales'], 0.01);
        $this->assertEqualsWithDelta(28000, $summary['taxable_sales_value'], 0.01);
        $this->assertEqualsWithDelta(3500, $summary['gross_output_vat'], 0.01);
        $this->assertEqualsWithDelta(300, $summary['return_output_vat'], 0.01);
        $this->assertEqualsWithDelta(3200, $summary['output_vat'], 0.01);

        // 2. Check VAT Rate-wise Summary
        $rateWiseSummary = collect($response->viewData('rateWiseSummary'))->keyBy('rate');
        $this->assertTrue($rateWiseSummary->has('15%'));
        $this->assertTrue($rateWiseSummary->has('10%'));

        // 15% rate: Taxable Sales = 8,000, Output VAT = 1,200
        $this->assertEqualsWithDelta(8000, $rateWiseSummary['15%']['taxable_sales'], 0.01);
        $this->assertEqualsWithDelta(1200, $rateWiseSummary['15%']['output_vat'], 0.01);

        // 10% rate: Taxable Sales = 20,000, Output VAT = 2,000
        $this->assertEqualsWithDelta(20000, $rateWiseSummary['10%']['taxable_sales'], 0.01);
        $this->assertEqualsWithDelta(2000, $rateWiseSummary['10%']['output_vat'], 0.01);

        // Rate-wise totals
        $this->assertEqualsWithDelta(28000, $response->viewData('rateWiseTotalTaxable'), 0.01);
        $this->assertEqualsWithDelta(3200, $response->viewData('rateWiseTotalVat'), 0.01);

        // 3. Check Reconciliation
        $reconciliation = $response->viewData('reconciliation');
        $this->assertTrue($reconciliation['is_valid']);
        $this->assertTrue($reconciliation['net_sales_reconciled']);
        $this->assertTrue($reconciliation['taxable_sales_reconciled']);
        $this->assertTrue($reconciliation['output_vat_reconciled']);

        // Check view rendering
        $response->assertSee('INV-0001');
        $response->assertSee('INV-0002');
        $response->assertSee('RT-0001');
        $response->assertSee('15%');
        $response->assertSee('10%');
        $response->assertSee('৳3,200.00');
        $response->assertSee('৳28,000.00');
    }

    public function test_sales_vat_report_excludes_transactions_outside_date_range(): void
    {
        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'General Category',
            'type' => 'product',
        ]);

        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Product 15%',
            'sale_price' => 10000,
            'is_vat' => true,
            'vat_percentage' => 15,
            'status' => 'active',
        ]);

        $pastDate = now()->subMonth()->toDateString();

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'INV-OLD-01',
            'sale_date' => $pastDate,
            'subtotal' => 10000,
            'tax' => 1500,
            'total' => 11500,
            'payment_status' => 'paid',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'total' => 10000,
        ]);

        // Querying for today should not find old sale
        $response = $this->actingAs($this->user)->get(route('reports.sales-vat', ['range' => 'today']));
        $response->assertOk();

        $summary = $response->viewData('summary');
        $this->assertEqualsWithDelta(0, $summary['gross_sales'], 0.01);
        $this->assertEqualsWithDelta(0, $summary['output_vat'], 0.01);
        $response->assertDontSee('INV-OLD-01');

        // Querying for custom range including pastDate should find it
        $responseRange = $this->actingAs($this->user)->get(route('reports.sales-vat', [
            'range' => 'custom',
            'from' => $pastDate,
            'to' => $pastDate,
        ]));
        $responseRange->assertOk();
        $responseRange->assertSee('INV-OLD-01');
        $summaryRange = $responseRange->viewData('summary');
        $this->assertEqualsWithDelta(1500, $summaryRange['output_vat'], 0.01);
    }

    public function test_user_without_permission_cannot_access_sales_vat_report(): void
    {
        $role = Role::create(['name' => 'NoVatReportRole', 'guard_name' => 'web']);
        $limitedUser = User::create([
            'name' => 'Limited Staff',
            'email' => 'limited@vat.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $limitedUser->syncRoles([$role]);

        $this->actingAs($limitedUser)->get(route('reports.sales-vat'))->assertForbidden();
    }

    public function test_exempt_and_zero_tax_items_appear_in_rate_wise_summary_correctly(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Exempt Cat', 'type' => 'product']);

        $exemptProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Rice 50kg (Exempt)',
            'sale_price' => 3000,
            'purchase_price' => 2500,
            'is_vat' => false,
            'vat_percentage' => 0,
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'INV-EXEMPT-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 3000,
            'tax' => 0,
            'total' => 3000,
            'payment_status' => 'paid',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $exemptProduct->id,
            'quantity' => 1,
            'unit_price' => 3000,
            'discount' => 0,
            'total' => 3000,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.sales-vat', ['range' => 'today']));
        $response->assertOk();

        $summary = $response->viewData('summary');
        $this->assertEqualsWithDelta(3000, $summary['gross_sales'], 0.01);
        $this->assertEqualsWithDelta(0, $summary['taxable_sales_value'], 0.01);
        $this->assertEqualsWithDelta(0, $summary['output_vat'], 0.01);

        $rateWiseSummary = collect($response->viewData('rateWiseSummary'))->keyBy('rate');
        $this->assertTrue($rateWiseSummary->has('0% / Exempt'));
        $this->assertEqualsWithDelta(3000, $rateWiseSummary['0% / Exempt']['taxable_sales'], 0.01);
        $this->assertEqualsWithDelta(0, $rateWiseSummary['0% / Exempt']['output_vat'], 0.01);
    }

    public function test_soft_deleted_sales_and_returns_are_excluded(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Cat', 'type' => 'product']);
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Taxable Item',
            'sale_price' => 5000,
            'is_vat' => true,
            'vat_percentage' => 15,
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'INV-DEL-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 5000,
            'tax' => 750,
            'total' => 5750,
            'payment_status' => 'paid',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 5000,
            'total' => 5000,
        ]);

        $sale->delete(); // Soft delete

        $response = $this->actingAs($this->user)->get(route('reports.sales-vat', ['range' => 'today']));
        $response->assertOk();

        $summary = $response->viewData('summary');
        $this->assertEqualsWithDelta(0, $summary['gross_sales'], 0.01);
        $this->assertEqualsWithDelta(0, $summary['output_vat'], 0.01);
        $response->assertDontSee('INV-DEL-01');
    }

    public function test_print_export_button_visibility_controlled_by_print_permission(): void
    {
        // User with view and print permission
        $response = $this->actingAs($this->user)->get(route('reports.sales-vat', ['range' => 'today']));
        $response->assertOk();
        $response->assertSee('btn-report-export-pdf');

        // User with only view permission
        $viewOnlyRole = Role::create(['name' => 'ViewOnlyVatRole', 'guard_name' => 'web']);
        $viewOnlyRole->givePermissionTo('report-sales-vat.view');
        $viewOnlyUser = User::create([
            'name' => 'View Only User',
            'email' => 'viewonly@vat.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $viewOnlyUser->syncRoles([$viewOnlyRole]);

        $responseViewOnly = $this->actingAs($viewOnlyUser)->get(route('reports.sales-vat', ['range' => 'today']));
        $responseViewOnly->assertOk();
        $responseViewOnly->assertDontSee('btn-report-export-pdf');
    }
}
