<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Sales\Mail\SaleInvoiceMail;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicSaleInvoiceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected Product $product;

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
            'name' => 'মদিনা ট্রেডার্স',
            'slug' => 'madina-traders',
            'phone' => '01712345678',
            'address' => 'ঢাকা, বাংলাদেশ',
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
            'email' => 'admin@madina.com',
        ]);
        $this->user->assignRole($adminRole);

        $this->cashAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Account',
            'type' => 'cash',
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'status' => 'active',
            'is_default' => true,
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'General',
            'slug' => 'general',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'সুপার বাসমতী চাল',
            'barcode' => '89012345',
            'sku' => 'RICE-001',
            'cost_price' => 80,
            'selling_price' => 100,
            'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'আব্দুর রহিম',
            'phone' => '01812345678',
            'email' => 'rahim@example.com',
            'address' => 'মিরপুর, ঢাকা',
            'status' => 'active',
        ]);
    }

    public function test_sale_creation_generates_unique_public_token(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-PUB-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 500,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 500,
            'paid_amount' => 500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $this->assertNotEmpty($sale->public_token);
        $this->assertEquals(32, strlen($sale->public_token));
        $this->assertStringContainsString('/invoice/'.$sale->public_token, $sale->public_url);
    }

    public function test_unauthenticated_user_can_view_public_invoice_via_token(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-PUB-02',
            'sale_date' => now()->toDateString(),
            'subtotal' => 500,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 500,
            'paid_amount' => 500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $sale->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 100,
            'total' => 500,
        ]);

        $response = $this->get(route('sales.public-invoice', $sale->public_token));

        $response->assertOk();
        $response->assertSee('মদিনা ট্রেডার্স');
        $response->assertSee('SL-PUB-02');
        $response->assertSee('আব্দুর রহিম');
        $response->assertSee('সুপার বাসমতী চাল');
        $response->assertSee('প্রিন্ট / PDF ডাউনলোড');
    }

    public function test_invalid_public_token_returns_404(): void
    {
        $response = $this->get(route('sales.public-invoice', 'non-existent-token-123456789012'));
        $response->assertNotFound();
    }

    public function test_send_invoice_email_successfully_sends_to_customer_email(): void
    {
        Mail::fake();

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-PUB-03',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1000,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1000,
            'paid_amount' => 1000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('sales.send-email', $sale));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        Mail::assertSent(SaleInvoiceMail::class, function ($mail) {
            return $mail->hasTo('rahim@example.com') &&
                   $mail->sale->invoice_no === 'SL-PUB-03';
        });
    }

    public function test_send_invoice_email_with_provided_email_updates_customer_if_empty(): void
    {
        Mail::fake();

        $customerWithoutEmail = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'করিম সাহেব',
            'phone' => '01987654321',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customerWithoutEmail->id,
            'invoice_no' => 'SL-PUB-04',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1500,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1500,
            'paid_amount' => 1500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('sales.send-email', $sale), [
            'email' => 'karim@gmail.com',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        Mail::assertSent(SaleInvoiceMail::class, function ($mail) {
            return $mail->hasTo('karim@gmail.com');
        });

        $this->assertEquals('karim@gmail.com', $customerWithoutEmail->fresh()->email);
    }

    public function test_send_invoice_email_fails_when_no_email_provided_and_customer_has_none(): void
    {
        Mail::fake();

        $customerWithoutEmail = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'নো ইমেইল কাস্টমার',
            'phone' => '01511223344',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customerWithoutEmail->id,
            'invoice_no' => 'SL-PUB-05',
            'sale_date' => now()->toDateString(),
            'subtotal' => 800,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 800,
            'paid_amount' => 800,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('sales.send-email', $sale));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        Mail::assertNothingSent();
    }

    public function test_invoice_modal_renders_email_copy_and_whatsapp_buttons(): void
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-PUB-06',
            'sale_date' => now()->toDateString(),
            'subtotal' => 2000,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 2000,
            'paid_amount' => 2000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.invoice-modal', $sale));

        $response->assertOk();
        $response->assertSee('id="btnSendSaleEmail"', false);
        $response->assertSee('ইমেইল পাঠান');
        $response->assertSee('id="btnCopyPublicInvoiceLink"', false);
        $response->assertSee('লিংক কপি');
        $response->assertSee('id="btnShareWhatsApp"', false);
        $response->assertSee('হোয়াটসঅ্যাপ');
        $response->assertSee($sale->public_url, false);
    }
}
