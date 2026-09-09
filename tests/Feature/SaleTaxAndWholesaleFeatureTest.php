<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\BanglaNumber;
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

class SaleTaxAndWholesaleFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected Product $taxProduct;

    protected Product $wholesaleProduct;

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
            'name' => 'টেস্ট শপ',
            'slug' => 'test-shop',
            'phone' => '01711111111',
            'address' => 'ঢাকা',
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
            'email' => 'admin@testshop.com',
        ]);
        $this->user->assignRole($adminRole);

        $this->cashAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Account',
            'type' => 'cash',
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'status' => 'active',
            'is_default' => true,
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'ইলেকট্রনিক্স',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $this->taxProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'ট্যাক্স পণ্য',
            'barcode' => '111222333',
            'sku' => 'TAX-001',
            'purchase_price' => 800,
            'sale_price' => 1000,
            'is_vat' => true,
            'vat_percentage' => 15.00,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->taxProduct->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BATCH-TAX-01',
            'quantity' => 100,
            'purchase_price' => 800,
            'sale_price' => 1000,
            'status' => 'active',
        ]);

        $this->wholesaleProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'পাইকারি পণ্য',
            'barcode' => '444555666',
            'sku' => 'WHOLE-001',
            'purchase_price' => 600,
            'sale_price' => 900,
            'is_wholesale' => true,
            'wholesale_price' => 750,
            'wholesale_min_qty' => 10,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->wholesaleProduct->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BATCH-WHOLE-01',
            'quantity' => 100,
            'purchase_price' => 600,
            'sale_price' => 900,
            'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'করিম সাহেব',
            'phone' => '01888888888',
            'address' => 'ঢাকা',
            'opening_due' => 0,
            'status' => 'active',
        ]);
    }

    public function test_sale_creation_form_exposes_tax_and_wholesale_data(): void
    {
        $response = $this->actingAs($this->user)->get(route('sales.index'));
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('isVat', $content);
        $this->assertStringContainsString('vatPercentage', $content);
        $this->assertStringContainsString('isWholesale', $content);
        $this->assertStringContainsString('wholesalePrice', $content);
        $this->assertStringContainsString('wholesaleMinQty', $content);
        $this->assertStringContainsString('ci-wholesale-chk', $content);
        $this->assertStringContainsString('cart-tax-row', $content);
    }

    public function test_sale_stores_with_tax_calculated_and_added_to_total(): void
    {
        $payload = [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'discount' => 0,
            'tax' => 300.00,
            'delivery_charge' => 0,
            'items' => [
                [
                    'product_id' => $this->taxProduct->id,
                    'quantity' => 2,
                    'unit_price' => 1000.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 2300.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales.store'), $payload);
        $response->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(2000.00, (float) $sale->subtotal);
        $this->assertEquals(300.00, (float) $sale->tax);
        $this->assertEquals(2300.00, (float) $sale->total);
        $this->assertEquals(2300.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);

        // Verify invoice shows the tax amount
        $invoiceResponse = $this->actingAs($this->user)->get(route('sales.invoice-modal', $sale));
        $invoiceResponse->assertOk();
        $this->assertStringContainsString(BanglaNumber::toBn('300.00'), $invoiceResponse->getContent());
    }

    public function test_sale_stores_with_wholesale_pricing(): void
    {
        $payload = [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'discount' => 0,
            'delivery_charge' => 0,
            'items' => [
                [
                    'product_id' => $this->wholesaleProduct->id,
                    'quantity' => 10,
                    'unit_price' => 750.00, // Wholesale price
                    'discount' => 0,
                    'is_wholesale' => 1,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 7500.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales.store'), $payload);
        $response->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(7500.00, (float) $sale->subtotal);
        $this->assertEquals(0.00, (float) $sale->tax);
        $this->assertEquals(7500.00, (float) $sale->total);
        $this->assertEquals(7500.00, (float) $sale->paid_amount);
    }

    public function test_sale_creation_form_exposes_product_discount_data(): void
    {
        $discountProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $this->taxProduct->category_id,
            'name' => 'ডিসকাউন্ট পণ্য',
            'barcode' => '777888999',
            'sku' => 'DISC-001',
            'purchase_price' => 400,
            'sale_price' => 500,
            'has_discount' => true,
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $discountProduct->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BATCH-DISC-01',
            'quantity' => 50,
            'purchase_price' => 400,
            'sale_price' => 500,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.create'));
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('hasDiscount', $content);
        $this->assertStringContainsString('discountType', $content);
        $this->assertStringContainsString('discountValue', $content);
        $this->assertStringContainsString('percentage', $content);
        $this->assertStringContainsString('ci-discount-raw', $content);
        $this->assertStringContainsString('ci-discount-type', $content);
    }

    public function test_sale_stores_with_item_product_discount(): void
    {
        $discountProduct = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $this->taxProduct->category_id,
            'name' => 'ডিসকাউন্ট পণ্য ফ্ল্যাট',
            'barcode' => '1234567890',
            'sku' => 'DISC-FLAT-001',
            'purchase_price' => 300,
            'sale_price' => 500,
            'has_discount' => true,
            'discount_type' => 'flat',
            'discount_value' => 50.00,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $discountProduct->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BATCH-DISC-FLAT-01',
            'quantity' => 50,
            'purchase_price' => 300,
            'sale_price' => 500,
            'status' => 'active',
        ]);

        $payload = [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'discount' => 0,
            'delivery_charge' => 0,
            'items' => [
                [
                    'product_id' => $discountProduct->id,
                    'quantity' => 2,
                    'unit_price' => 500.00,
                    'discount' => 100.00, // 50 flat x 2
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 900.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales.store'), $payload);
        $response->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(900.00, (float) $sale->subtotal);
        $this->assertEquals(100.00, (float) $sale->product_discount);
        $this->assertEquals(900.00, (float) $sale->total);
        $this->assertEquals(900.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);

        $item = $sale->items()->first();
        $this->assertEquals(100.00, (float) $item->discount);
        $this->assertEquals(900.00, (float) $item->total);
    }
}
