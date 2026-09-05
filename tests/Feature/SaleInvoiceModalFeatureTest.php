<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleInvoiceModalFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected Product $product;

    protected Account $cashAccount;

    protected Batch $batch;

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
            'name' => 'আলহাজ্ব বস্ত্রালয়',
            'slug' => 'alhaj-textile',
            'phone' => '01777265886',
            'address' => 'বিরামপুর, দিনাজপুর',
            'status' => 'active',
        ]);

        $plan = Plan::where('slug', 'enterprise')->first() ?? Plan::first();
        if ($plan) {
            $this->shop->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addYear(),
                'trial_ends_at' => null,
                'features' => Features::all(),
            ]);
        }

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
            'is_main' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'status' => 'active',
            'is_default' => true,
        ]);

        $this->user = User::factory()->create([
            'shop_id' => $this->shop->id,
            'email' => 'admin@alhaj.com',
        ]);
        $this->user->assignRole($adminRole);

        $this->cashAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash in Hand',
            'type' => 'cash',
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'status' => 'active',
            'is_default' => true,
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'পাঞ্জাবি',
            'slug' => 'punjabi',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'আলহাজ্ব পাঞ্জাবি',
            'barcode' => '24103001',
            'sku' => 'PUN-001',
            'cost_price' => 1200,
            'selling_price' => 1800,
            'status' => 'active',
        ]);

        $this->batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BATCH-PUN-01',
            'quantity' => 100,
            'cost_price' => 1200,
            'selling_price' => 1800,
            'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'মো: জাহিদ',
            'phone' => '01777265886',
            'address' => 'বিরামপুর',
            'opening_due' => 500,
            'status' => 'active',
        ]);
    }

    public function test_sale_store_redirects_with_show_invoice_sale_id_in_session(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'invoice_no' => 'SL-1001',
            'discount' => 0,
            'delivery_charge' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 1800,
                    'total' => 1800,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 1800,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales.store'), $payload);

        $sale = Sale::where('invoice_no', 'SL-1001')->first();
        $this->assertNotNull($sale);

        $response->assertRedirect(route('sales.index'));
        $response->assertSessionHas('show_invoice_sale_id', $sale->id);
    }

    public function test_sales_create_page_renders_invoice_modal_when_session_has_sale_id(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-1002',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1800,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1800,
            'paid_amount' => 1800,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);
        $sale->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 1800,
            'total' => 1800,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['show_invoice_sale_id' => $sale->id])
            ->get(route('sales.index'));

        $response->assertOk();
        $response->assertSee('id="saleInvoiceModal"', false);
        $response->assertSee('openModal(\'saleInvoiceModal\')', false);
        $response->assertSee('আলহাজ্ব বস্ত্রালয়');
        $response->assertSee('SL-1002');
        $response->assertSee('মো: জাহিদ');
    }

    public function test_sale_invoice_modal_endpoint_returns_invoice_modal_html(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-1003',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1800,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1800,
            'paid_amount' => 1800,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);
        $sale->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 1800,
            'total' => 1800,
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.invoice-modal', $sale));

        $response->assertOk();
        $response->assertSee('id="saleInvoiceModal"', false);
        $response->assertSee('Successful');
        $response->assertSee('আলহাজ্ব বস্ত্রালয়');
        $response->assertSee('SL-1003');
        $response->assertSee('মো: জাহিদ');
        $response->assertSee('প্রিন্ট করুন');
    }

    public function test_sale_print_invoice_endpoint_returns_printable_html(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-1004',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1800,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1800,
            'paid_amount' => 1800,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);
        $sale->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 1800,
            'total' => 1800,
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.print-invoice', $sale));

        $response->assertOk();
        $response->assertSee('বিক্রয় ইনভয়েস - #SL-1004');
        $response->assertSee('PDF ডাউনলোড / প্রিন্ট করুন');
        $response->assertSee('আলহাজ্ব বস্ত্রালয়');
    }

    public function test_sale_ledger_contains_invoice_action_button_and_container(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-1005',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1800,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1800,
            'paid_amount' => 1800,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.ledger'));

        $response->assertOk();
        $response->assertSee('btn-show-sale-invoice');
        $response->assertSee(route('sales.invoice-modal', $sale));
        $response->assertSee('id="saleInvoiceModalContainer"', false);
        $response->assertSee('showSaleInvoice', false);
    }
}
