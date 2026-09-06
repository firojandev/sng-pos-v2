<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\BanglaNumber;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\Unit;
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

class PurchaseInvoiceModalFeatureTest extends TestCase
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
            'name' => 'Gadget Parks',
            'slug' => 'gadget-parks',
            'phone' => '01778623121',
            'address' => 'Shihubon Road, Nobinbag',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gadgetparks.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

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

        $this->supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Sagor',
            'phone' => '01778623121',
            'address' => 'Dhaka',
            'opening_due' => 0,
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'Smartwatch',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Oraimo Watch 5 Lite Smartwatch | 2.01" HD Display & BT Calling',
            'sku' => 'KM-20318',
            'barcode' => 'KM-20318',
            'purchase_price' => 8175,
            'sale_price' => 9500,
            'min_stock' => 5,
            'status' => 'active',
        ]);

        $unit = Unit::firstOrCreate(['name' => 'পিস', 'short_code' => 'pcs']);
        $this->product->units()->attach($unit->id, [
            'conversion_factor' => 1,
            'is_base' => true,
            'is_smaller_unit' => false,
        ]);
    }

    public function test_storing_purchase_flashes_show_invoice_purchase_id_in_session(): void
    {
        $payload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'discount' => 0,
            'delivery_charge' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'purchase_price' => 8175,
                    'sale_price' => 9500,
                    'batch_no' => 'KM-20318',
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 81750,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase.store'), $payload);

        $purchase = Purchase::latest('id')->first();
        $this->assertNotNull($purchase);

        $response->assertRedirect(route('purchase.index'));
        $response->assertSessionHas('show_invoice_purchase_id', $purchase->id);
    }

    public function test_purchase_create_page_shows_invoice_modal_when_session_has_invoice_purchase_id(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => '6X3S799KB8J9NS9',
            'subtotal' => 81750,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 81750,
            'paid_amount' => 81750,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'purchase_price' => 8175,
            'total' => 81750,
            'batch_no' => 'KM-20318',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['show_invoice_purchase_id' => $purchase->id])
            ->get(route('purchase.create'));

        $response->assertOk();
        $response->assertSee('Successful');
        $response->assertSee('ইনভয়েস');
        $response->assertSee('Gadget Parks');
        $response->assertSee('Sagor');
        $response->assertSee('6X3S799KB8J9NS9');
        $response->assertSee('KM-20318');
        $response->assertSee('প্রিন্ট করুন');
        $response->assertSee('openModal(\'purchaseInvoiceModal\')', false);
    }

    public function test_invoice_modal_endpoint_returns_view(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-0001',
            'subtotal' => 13500,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 13500,
            'paid_amount' => 13500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'purchase_price' => 6750,
            'total' => 13500,
            'batch_no' => 'KM-20318',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase.invoice-modal', $purchase));

        $response->assertOk();
        $response->assertSee('Successful');
        $response->assertSee('তেরো হাজার পাঁচ শত টাকা');
        $response->assertSee('প্রিন্ট করুন');
    }

    public function test_print_invoice_endpoint_returns_printable_page(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-0002',
            'subtotal' => 13500,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 13500,
            'paid_amount' => 13500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'purchase_price' => 6750,
            'total' => 13500,
            'batch_no' => 'KM-20318',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase.print-invoice', $purchase));

        $response->assertOk();
        $response->assertSee('ক্রয় ইনভয়েস - #PU-0002');
        $response->assertSee('ইনভয়েস');
        $response->assertSee('PDF ডাউনলোড / প্রিন্ট করুন');
        $response->assertSee('তেরো হাজার পাঁচ শত টাকা');
    }

    public function test_bangla_number_helper_words_and_formatting(): void
    {
        $this->assertEquals('তেরো হাজার পাঁচ শত টাকা', BanglaNumber::toBnWords(13500));
        $this->assertEquals('৮১,৭৫০', BanglaNumber::toBnMoney(81750));
        $this->assertEquals('০', BanglaNumber::toBnMoney(0));
        $this->assertEquals('১০', BanglaNumber::toBn(10));
    }

    public function test_invoice_omits_missing_supplier_phone_and_address_without_showing_dewya_hoyni(): void
    {
        $supplierWithoutDetails = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'General Supplier',
            'phone' => null,
            'address' => null,
            'opening_due' => 0,
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $supplierWithoutDetails->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-0003',
            'subtotal' => 1000,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 1000,
            'paid_amount' => 1000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase.invoice-modal', $purchase));

        $response->assertOk();
        $response->assertDontSee('দেওয়া হয়নি');
        $response->assertDontSee('মোবাইল:');
        $response->assertDontSee('ঠিকানা:');
        $response->assertSee('সাপ্লায়ার:</b> General Supplier', false);
    }

    public function test_invoice_displays_received_and_remaining_quantity_columns_and_values(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-PARTIAL-001',
            'subtotal' => 81750,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => 81750,
            'paid_amount' => 50000,
            'due_amount' => 31750,
            'payment_status' => 'partial',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'received_quantity' => 6, // 6 received, 4 remaining
            'purchase_price' => 8175,
            'total' => 81750,
            'batch_no' => 'KM-20318',
        ]);

        $modalResponse = $this->actingAs($this->user)->get(route('purchase.invoice-modal', $purchase));
        $modalResponse->assertOk();
        $modalResponse->assertSee('অর্ডার');
        $modalResponse->assertSee('গৃহীত');
        $modalResponse->assertSee('বাকি');
        $modalResponse->assertSee(BanglaNumber::toBn(10));
        $modalResponse->assertSee(BanglaNumber::toBn(6));
        $modalResponse->assertSee(BanglaNumber::toBn(4));

        $printResponse = $this->actingAs($this->user)->get(route('purchase.print-invoice', $purchase));
        $printResponse->assertOk();
        $printResponse->assertSee('অর্ডার');
        $printResponse->assertSee('গৃহীত');
        $printResponse->assertSee('বাকি');
        $printResponse->assertSee(BanglaNumber::toBn(10));
        $printResponse->assertSee(BanglaNumber::toBn(6));
        $printResponse->assertSee(BanglaNumber::toBn(4));
    }
}
