<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\PrinterSetting;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrinterAndWhatsAppFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    private function createSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    private function createShopWithPlan(array $features = []): array
    {
        $shop = Shop::create([
            'name' => 'টেস্ট ডিজিটাল শপ',
            'slug' => 'test-digital-shop',
            'store_code' => 'shop-999',
            'phone' => '01700112233',
            'email' => 'test@digitalshop.com',
            'address' => 'ঢাকা',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'name' => 'কাস্টম টেস্ট প্ল্যান',
            'slug' => 'custom-test-plan-'.uniqid(),
            'price' => 1000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        // Sync given features
        $planFeatures = collect($features);
        $featureIds = $planFeatures->mapWithKeys(function ($slug) {
            $labels = Features::all();
            $feature = Feature::firstOrCreate(
                ['slug' => $slug],
                ['name' => $labels[$slug]['en'] ?? $slug, 'type' => FeatureType::Toggle]
            );

            return [$feature->id => ['value' => '0']];
        });
        $plan->features()->sync($featureIds);

        $shop->subscribe($plan);

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
            'is_main' => true,
        ]);

        $warehouse = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'status' => 'active',
            'is_default' => true,
        ]);

        $adminRole = Role::firstOrCreate([
            'shop_id' => $shop->id,
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $user = User::factory()->create([
            'name' => 'শপ ম্যানেজার',
            'email' => 'manager@digitalshop.com',
            'password' => Hash::make('password'),
            'shop_id' => $shop->id,
        ]);
        $user->assignRole($adminRole);

        return [$shop, $user, $warehouse, $plan];
    }

    public function test_features_list_contains_printer_and_whatsapp_settings(): void
    {
        $allFeatures = Features::all();

        $this->assertArrayHasKey('printer-settings', $allFeatures);
        $this->assertSame('প্রিন্টার সেটিংস', $allFeatures['printer-settings']['bn']);
        $this->assertSame('Printer Settings', $allFeatures['printer-settings']['en']);

        $this->assertArrayHasKey('whatsapp-settings', $allFeatures);
        $this->assertSame('হোয়াটসঅ্যাপ সেটিংস', $allFeatures['whatsapp-settings']['bn']);
        $this->assertSame('WhatsApp Settings', $allFeatures['whatsapp-settings']['en']);

        $this->assertContains('printer-settings', Features::keys());
        $this->assertContains('whatsapp-settings', Features::keys());
    }

    public function test_permissions_include_printer_and_whatsapp_actions(): void
    {
        $allPermissions = Permissions::all();

        $this->assertContains('printer-settings.view', $allPermissions);
        $this->assertContains('printer-settings.edit', $allPermissions);
        $this->assertContains('whatsapp-settings.view', $allPermissions);
        $this->assertContains('whatsapp-settings.edit', $allPermissions);
    }

    public function test_super_admin_can_assign_printer_and_whatsapp_settings_to_plan(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post(route('plans.store'), [
            'name' => 'Enterprise Plus',
            'slug' => 'enterprise-plus',
            'price' => 5000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'features' => ['sales', 'printer-settings', 'whatsapp-settings'],
        ]);

        $response->assertRedirect(route('plans.index'));

        $plan = Plan::where('slug', 'enterprise-plus')->firstOrFail();
        $this->assertContains('printer-settings', $plan->features->pluck('slug')->all());
        $this->assertContains('whatsapp-settings', $plan->features->pluck('slug')->all());
    }

    public function test_super_admin_can_disable_whatsapp_settings_from_plan(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $plan = Plan::create([
            'name' => 'Editable Plan',
            'slug' => 'editable-plan',
            'price' => 2000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        // Initially with both
        $response = $this->actingAs($superAdmin)->put(route('plans.update', $plan), [
            'name' => 'Editable Plan',
            'slug' => 'editable-plan',
            'price' => 2000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'features' => ['sales', 'printer-settings', 'whatsapp-settings'],
        ]);
        $response->assertRedirect(route('plans.index'));
        $plan->refresh();
        $this->assertContains('whatsapp-settings', $plan->features->pluck('slug')->all());

        // Now remove whatsapp-settings
        $response2 = $this->actingAs($superAdmin)->put(route('plans.update', $plan), [
            'name' => 'Editable Plan',
            'slug' => 'editable-plan',
            'price' => 2000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'features' => ['sales', 'printer-settings'],
        ]);
        $response2->assertRedirect(route('plans.index'));
        $plan->refresh();
        $this->assertNotContains('whatsapp-settings', $plan->features->pluck('slug')->all());
        $this->assertContains('printer-settings', $plan->features->pluck('slug')->all());
    }

    public function test_sale_invoice_modal_shows_whatsapp_button_when_feature_is_enabled(): void
    {
        [$shop, $user, $warehouse] = $this->createShopWithPlan(['sales', 'whatsapp-settings']);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'করিম সাহেব',
            'phone' => '01711223344',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'SL-WA-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1000,
            'total' => 1000,
            'paid_amount' => 1000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($user)->get(route('sales.invoice-modal', $sale));

        $response->assertOk();
        $response->assertSee('id="btnShareWhatsApp"', false);
        $response->assertSee('হোয়াটসঅ্যাপ');
    }

    public function test_sale_invoice_modal_hides_whatsapp_button_when_feature_is_disabled(): void
    {
        // Shop without whatsapp-settings
        [$shop, $user, $warehouse] = $this->createShopWithPlan(['sales', 'printer-settings']);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'রহিম সাহেব',
            'phone' => '01811223344',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'SL-NO-WA-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1000,
            'total' => 1000,
            'paid_amount' => 1000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($user)->get(route('sales.invoice-modal', $sale));

        $response->assertOk();
        $response->assertDontSee('id="btnShareWhatsApp"', false);
    }

    public function test_send_whatsapp_invoice_returns_403_when_feature_is_disabled(): void
    {
        [$shop, $user, $warehouse] = $this->createShopWithPlan(['sales', 'printer-settings']);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'করিম সাহেব',
            'phone' => '01711223344',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'SL-WA-DENIED-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1000,
            'total' => 1000,
            'paid_amount' => 1000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($user)->postJson(route('sales.send-whatsapp', $sale), [
            'phone' => '01711223344',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_printer_settings_route_is_gated_by_feature(): void
    {
        // Shop without printer-settings feature
        [$shop, $user] = $this->createShopWithPlan(['sales', 'whatsapp-settings']);

        $response = $this->actingAs($user)->get(route('printer-settings.index'));

        $response->assertStatus(403);
    }

    public function test_whatsapp_settings_route_is_gated_by_feature(): void
    {
        // Shop without whatsapp-settings feature
        [$shop, $user] = $this->createShopWithPlan(['sales', 'printer-settings']);

        $response = $this->actingAs($user)->get(route('whatsapp-settings.index'));

        $response->assertStatus(403);
    }

    public function test_shop_without_printer_settings_feature_falls_back_to_a4_for_sale_invoice(): void
    {
        // Shop with thermal settings saved in database, but plan does NOT have printer-settings feature
        [$shop, $user, $warehouse] = $this->createShopWithPlan(['sales']);

        PrinterSetting::create([
            'shop_id' => $shop->id,
            'printer_type' => PrinterSetting::TYPE_THERMAL,
            'paper_width' => 80.0,
            'orientation' => PrinterSetting::ORIENTATION_PORTRAIT,
        ]);

        $setting = $shop->getEffectivePrinterSetting();
        $this->assertFalse($setting->isThermal());
        $this->assertTrue($setting->isA4());
        $this->assertFalse($setting->isA5());
        $this->assertStringContainsString('210mm 297mm', $setting->getCssPageSize());

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'করিম সাহেব',
            'phone' => '01711223344',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'SL-A4-FALLBACK-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1500,
            'total' => 1500,
            'paid_amount' => 1500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        // Sale Invoice Modal should render standard A4 modal (width:760px) and not thermal (width:500px)
        $modalResponse = $this->actingAs($user)->get(route('sales.invoice-modal', $sale));
        $modalResponse->assertOk();
        $modalResponse->assertSee('width:760px', false);
        $modalResponse->assertDontSee('width:500px', false);
        $modalResponse->assertSee('sale-invoice-sheet', false);
        $modalResponse->assertDontSee('thermal-receipt-sheet', false);

        // Sale Print Page should render standard A4 sheet
        $printResponse = $this->actingAs($user)->get(route('sales.print-invoice', $sale));
        $printResponse->assertOk();
        $printResponse->assertSee('size: A4 portrait;', false);
        $printResponse->assertDontSee('size: 80mm', false);
    }

    public function test_shop_without_printer_settings_feature_falls_back_to_a4_for_purchase_invoice(): void
    {
        // Shop with thermal settings saved in database, but plan does NOT have printer-settings feature
        [$shop, $user, $warehouse] = $this->createShopWithPlan(['purchase']);

        PrinterSetting::create([
            'shop_id' => $shop->id,
            'printer_type' => PrinterSetting::TYPE_THERMAL,
            'paper_width' => 80.0,
            'orientation' => PrinterSetting::ORIENTATION_PORTRAIT,
        ]);

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'প্রিমিয়াম সাপ্লায়ার',
            'phone' => '01900112233',
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'shop_id' => $shop->id,
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'invoice_no' => 'PUR-A4-FALLBACK-01',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 2000,
            'total' => 2000,
            'paid_amount' => 2000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        // Purchase Invoice Modal should render standard A4 modal (width:760px) and not thermal (width:460px)
        $modalResponse = $this->actingAs($user)->get(route('purchase.invoice-modal', $purchase));
        $modalResponse->assertOk();
        $modalResponse->assertSee('width:760px', false);
        $modalResponse->assertDontSee('width:460px', false);
        $modalResponse->assertSee('purchase-invoice-sheet', false);
        $modalResponse->assertDontSee('thermal-receipt-sheet', false);

        // Purchase Print Page should render standard A4 sheet
        $printResponse = $this->actingAs($user)->get(route('purchase.print-invoice', $purchase));
        $printResponse->assertOk();
        $printResponse->assertSee('size: A4 portrait;', false);
        $printResponse->assertDontSee('size: 80mm', false);
    }

    public function test_shop_with_printer_settings_feature_uses_configured_thermal_settings(): void
    {
        // Shop with printer-settings feature enabled
        [$shop, $user, $warehouse] = $this->createShopWithPlan(['sales', 'purchase', 'printer-settings']);

        PrinterSetting::create([
            'shop_id' => $shop->id,
            'printer_type' => PrinterSetting::TYPE_THERMAL,
            'paper_width' => 80.0,
            'orientation' => PrinterSetting::ORIENTATION_PORTRAIT,
        ]);

        $setting = $shop->getEffectivePrinterSetting();
        $this->assertTrue($setting->isThermal());
        $this->assertFalse($setting->isA4());

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'করিম সাহেব',
            'phone' => '01711223344',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'SL-THERMAL-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 1500,
            'total' => 1500,
            'paid_amount' => 1500,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        // Sale Invoice Modal should render thermal modal (width:500px)
        $modalResponse = $this->actingAs($user)->get(route('sales.invoice-modal', $sale));
        $modalResponse->assertOk();
        $modalResponse->assertSee('width:500px', false);
        $modalResponse->assertSee('thermal-receipt-sheet', false);

        // Sale Print Page should render thermal size
        $printResponse = $this->actingAs($user)->get(route('sales.print-invoice', $sale));
        $printResponse->assertOk();
        $printResponse->assertSee('80mm auto', false);
    }
}
