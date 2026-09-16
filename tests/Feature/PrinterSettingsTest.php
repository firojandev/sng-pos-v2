<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\Category;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\PrinterSetting;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrinterSettingsTest extends TestCase
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

    protected function createShopAndAdmin(): array
    {
        $shop = Shop::create([
            'name' => 'আলতাব ডিজিটাল স্টোর',
            'slug' => 'altab-digital-store',
            'store_code' => 'shop-101',
            'phone' => '01711002233',
            'email' => 'store@altab.test',
            'address' => 'ঢাকা, বাংলাদেশ',
            'currency_symbol' => '৳',
            'status' => 'active',
        ]);

        $plan = Plan::where('slug', 'enterprise')->first() ?? Plan::first();
        if ($plan) {
            $shop->subscribe($plan);
        }

        $adminRole = Role::firstOrCreate([
            'shop_id' => $shop->id,
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $user = User::factory()->create([
            'name' => 'ম্যানেজার সাহেব',
            'email' => 'manager@altab.test',
            'password' => Hash::make('password'),
            'shop_id' => $shop->id,
        ]);
        $user->assignRole($adminRole);

        return [$shop, $user];
    }

    public function test_guest_cannot_access_printer_settings(): void
    {
        $response = $this->get(route('printer-settings.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_shop_receives_403(): void
    {
        $user = User::factory()->create([
            'shop_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('printer-settings.index'));

        $response->assertStatus(403);
    }

    public function test_authenticated_shop_admin_can_view_printer_settings(): void
    {
        [$shop, $user] = $this->createShopAndAdmin();

        $response = $this->actingAs($user)->get(route('printer-settings.index'));

        $response->assertStatus(200);
        $response->assertSee('প্রিন্টার ও পেপার সেটিংস');
        $response->assertSee('থার্মাল প্রিন্টার');
        $response->assertSee('A4 প্রিন্টার');
        $response->assertSee('A5 প্রিন্টার');
        $response->assertSee('58 mm');
        $response->assertSee('80 mm');
        $response->assertSee('100 mm');
        $response->assertSee('href="'.route('printer-settings.index').'" class="nav-item active"', false);
    }

    public function test_admin_can_update_printer_settings_to_a4(): void
    {
        [$shop, $user] = $this->createShopAndAdmin();

        $response = $this->actingAs($user)->put(route('printer-settings.update'), [
            'printer_type' => PrinterSetting::TYPE_A4,
            'orientation' => PrinterSetting::ORIENTATION_LANDSCAPE,
            'paper_width' => 210,
            'paper_height' => 297,
            'unit' => PrinterSetting::UNIT_MM,
            'page_margin' => 8,
            'auto_print' => 1,
            'show_header_logo' => 1,
            'show_shop_info' => 1,
            'show_customer_due' => 1,
            'show_footer_note' => 1,
            'print_copies' => 2,
        ]);

        $response->assertRedirect(route('printer-settings.index'));
        $response->assertSessionHas('status');

        $setting = PrinterSetting::where('shop_id', $shop->id)->first();
        $this->assertNotNull($setting);
        $this->assertSame(PrinterSetting::TYPE_A4, $setting->printer_type);
        $this->assertSame(PrinterSetting::ORIENTATION_LANDSCAPE, $setting->orientation);
        $this->assertEquals(210.0, $setting->paper_width);
        $this->assertEquals(297.0, $setting->paper_height);
        $this->assertSame(PrinterSetting::UNIT_MM, $setting->unit);
        $this->assertTrue($setting->auto_print);
        $this->assertEquals(2, $setting->print_copies);
        $this->assertSame('297mm 210mm landscape', $setting->getCssPageSize());
    }

    public function test_admin_can_update_printer_settings_to_a5(): void
    {
        [$shop, $user] = $this->createShopAndAdmin();

        $response = $this->actingAs($user)->put(route('printer-settings.update'), [
            'printer_type' => PrinterSetting::TYPE_A5,
            'orientation' => PrinterSetting::ORIENTATION_PORTRAIT,
            'paper_width' => 148,
            'paper_height' => 210,
            'unit' => PrinterSetting::UNIT_MM,
            'page_margin' => 5,
            'auto_print' => 0,
            'show_header_logo' => 1,
            'show_shop_info' => 1,
            'show_customer_due' => 0,
            'show_footer_note' => 1,
            'print_copies' => 1,
        ]);

        $response->assertRedirect(route('printer-settings.index'));

        $setting = PrinterSetting::where('shop_id', $shop->id)->first();
        $this->assertNotNull($setting);
        $this->assertSame(PrinterSetting::TYPE_A5, $setting->printer_type);
        $this->assertSame(PrinterSetting::ORIENTATION_PORTRAIT, $setting->orientation);
        $this->assertEquals(148.0, $setting->paper_width);
        $this->assertEquals(210.0, $setting->paper_height);
        $this->assertSame('148mm 210mm portrait', $setting->getCssPageSize());
    }

    public function test_admin_can_update_printer_settings_to_thermal(): void
    {
        [$shop, $user] = $this->createShopAndAdmin();

        $response = $this->actingAs($user)->put(route('printer-settings.update'), [
            'printer_type' => PrinterSetting::TYPE_THERMAL,
            'orientation' => PrinterSetting::ORIENTATION_PORTRAIT,
            'paper_width' => 80,
            'paper_height' => 200,
            'unit' => PrinterSetting::UNIT_MM,
            'page_margin' => 2,
            'auto_print' => 1,
            'show_header_logo' => 1,
            'show_shop_info' => 1,
            'show_customer_due' => 1,
            'show_footer_note' => 1,
            'print_copies' => 1,
        ]);

        $response->assertRedirect(route('printer-settings.index'));

        $setting = PrinterSetting::where('shop_id', $shop->id)->first();
        $this->assertNotNull($setting);
        $this->assertSame(PrinterSetting::TYPE_THERMAL, $setting->printer_type);
        $this->assertEquals(80.0, $setting->paper_width);
        $this->assertEquals(200.0, $setting->paper_height);
        $this->assertSame(PrinterSetting::UNIT_MM, $setting->unit);
        $this->assertSame('80mm 200mm', $setting->getCssPageSize());
    }

    public function test_validation_rejects_invalid_printer_type(): void
    {
        [$shop, $user] = $this->createShopAndAdmin();

        $response = $this->actingAs($user)->put(route('printer-settings.update'), [
            'printer_type' => 'invalid-printer',
            'orientation' => 'portrait',
            'paper_width' => 80,
            'unit' => 'mm',
        ]);

        $response->assertSessionHasErrors('printer_type');
    }

    public function test_print_invoice_applies_thermal_settings_and_layout(): void
    {
        [$shop, $user] = $this->createShopAndAdmin();

        PrinterSetting::create([
            'shop_id' => $shop->id,
            'printer_type' => PrinterSetting::TYPE_THERMAL,
            'orientation' => PrinterSetting::ORIENTATION_PORTRAIT,
            'paper_width' => 80.0,
            'paper_height' => null,
            'unit' => PrinterSetting::UNIT_MM,
            'page_margin' => 2.0,
            'auto_print' => true,
            'show_header_logo' => true,
            'show_shop_info' => true,
            'show_customer_due' => true,
            'show_footer_note' => true,
            'print_copies' => 1,
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'করিম সাহেব',
            'phone' => '01811223344',
            'opening_due' => 100,
        ]);

        $category = Category::create([
            'shop_id' => $shop->id,
            'name' => 'General Category',
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'থার্মাল টেস্ট প্রোডাক্ট',
            'sku' => 'TP-001',
            'price' => 500,
            'cost' => 350,
            'stock' => 50,
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'INV-TEST-001',
            'subtotal' => 1000,
            'total' => 1000,
            'paid_amount' => 800,
            'due_amount' => 200,
            'status' => 'completed',
            'sale_date' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 500,
            'total' => 1000,
        ]);

        $response = $this->actingAs($user)->get(route('sales.print-invoice', $sale));

        $response->assertStatus(200);
        $response->assertSee('80mm auto');
        $response->assertSee('id="thermalReceiptSheet"', false);
        $response->assertSee('INV-TEST-001');
        $response->assertSee('করিম সাহেব');
        $response->assertSee('window.print()', false);
    }
}
