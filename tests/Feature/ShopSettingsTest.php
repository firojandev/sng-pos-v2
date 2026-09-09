<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_shop_settings(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_shop_receives_403(): void
    {
        $user = User::factory()->create([
            'shop_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_authenticated_shop_user_can_view_shop_settings(): void
    {
        $shop = Shop::create([
            'name' => 'আলতাব জেনারেল স্টোর',
            'slug' => 'altab-general-store',
            'store_code' => 'shop-001',
            'phone' => '01711223344',
            'email' => 'altab@example.com',
            'address' => 'মিরপুর ১২, ঢাকা',
            'currency_symbol' => '৳',
            'invoice_footer' => 'বিক্রীত পণ্য ফেরতযোগ্য নয়। ধন্যবাদ!',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'name' => 'আলতাব হোসাইন',
            'email' => 'admin@altab.test',
            'password' => Hash::make('password'),
            'shop_id' => $shop->id,
        ]);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('দোকান সেটিংস');
        $response->assertSee('Shop Settings');
        $response->assertSee('আলতাব জেনারেল স্টোর');
        $response->assertSee('shop-001');
        $response->assertSee('01711223344');
        $response->assertSee('altab@example.com');
        $response->assertSee('মিরপুর ১২, ঢাকা');
        $response->assertSee('বিক্রীত পণ্য ফেরতযোগ্য নয়। ধন্যবাদ!');
        $response->assertDontSee('class="tabbar"', false);
    }

    public function test_user_can_update_shop_settings(): void
    {
        $shop = Shop::create([
            'name' => 'পুরোনো দোকান',
            'slug' => 'purono-dokan',
            'store_code' => 'shop-002',
            'phone' => '01700000000',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'name' => 'দোকানদার',
            'email' => 'dokandar@test.com',
            'shop_id' => $shop->id,
        ]);

        $response = $this->actingAs($user)->put(route('settings.update'), [
            'name' => 'হালনাগাদকৃত সুপার শপ',
            'phone' => '01899887766',
            'email' => 'supershop@test.com',
            'address' => 'উত্তরা সেক্টর ৩, ঢাকা',
            'currency_symbol' => '৳',
            'invoice_footer' => 'পণ্য ৭ দিনের মধ্যে পরিবর্তনযোগ্য।',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('status');

        $shop->refresh();
        $this->assertSame('হালনাগাদকৃত সুপার শপ', $shop->name);
        $this->assertSame('01899887766', $shop->phone);
        $this->assertSame('supershop@test.com', $shop->email);
        $this->assertSame('উত্তরা সেক্টর ৩, ঢাকা', $shop->address);
        $this->assertSame('৳', $shop->currency_symbol);
        $this->assertSame('পণ্য ৭ দিনের মধ্যে পরিবর্তনযোগ্য।', $shop->invoice_footer);
    }

    public function test_user_can_upload_and_remove_shop_logo(): void
    {
        Storage::fake('public');

        $shop = Shop::create([
            'name' => 'ব্র্যান্ড শপ',
            'slug' => 'brand-shop',
            'store_code' => 'shop-003',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);

        $logoFile = UploadedFile::fake()->image('logo.png', 100, 100);

        $uploadResponse = $this->actingAs($user)->put(route('settings.update'), [
            'name' => 'ব্র্যান্ড শপ',
            'logo' => $logoFile,
        ]);

        $uploadResponse->assertRedirect(route('settings.index'));

        $shop->refresh();
        $this->assertNotNull($shop->logo);
        Storage::disk('public')->assertExists($shop->logo);

        $storedPath = $shop->logo;

        // Now remove logo
        $removeResponse = $this->actingAs($user)->put(route('settings.update'), [
            'name' => 'ব্র্যান্ড শপ',
            'remove_logo' => '1',
        ]);

        $removeResponse->assertRedirect(route('settings.index'));

        $shop->refresh();
        $this->assertNull($shop->logo);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_validation_requires_name_and_valid_email(): void
    {
        $shop = Shop::create([
            'name' => 'টেস্ট শপ',
            'slug' => 'test-shop',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);

        $response = $this->actingAs($user)->put(route('settings.update'), [
            'name' => '',
            'email' => 'invalid-email-string',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_non_admin_cannot_access_or_update_shop_settings(): void
    {
        $shop = Shop::create([
            'name' => 'স্টোর',
            'slug' => 'store-009',
            'status' => 'active',
        ]);

        $cashierRole = Role::firstOrCreate([
            'shop_id' => $shop->id,
            'name' => 'Cashier',
            'guard_name' => 'web',
        ]);

        $cashier = User::factory()->create([
            'shop_id' => $shop->id,
        ]);
        $cashier->assignRole($cashierRole);

        // Cannot view settings
        $response = $this->actingAs($cashier)->get(route('settings.index'));
        $response->assertStatus(403);

        // Cannot update settings
        $updateResponse = $this->actingAs($cashier)->put(route('settings.update'), [
            'name' => 'অননুমোদিত পরিবর্তন',
        ]);
        $updateResponse->assertStatus(403);
    }
}
