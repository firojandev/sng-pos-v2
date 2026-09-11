<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Setting;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DynamicSiteTitleTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web', 'shop_id' => null]);
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'phone' => '01700000000',
        ]);
        $this->superAdmin->assignRole($role);
    }

    public function test_setting_get_site_title_returns_dynamic_value(): void
    {
        $this->assertEquals('SNGPOS', Setting::getSiteTitle());

        Setting::set('site_title', 'Nova POS ERP');

        $this->assertEquals('Nova POS ERP', Setting::getSiteTitle());
    }

    public function test_login_page_uses_dynamic_site_title(): void
    {
        Setting::set('site_title', 'Falcon POS');

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Falcon POS-এ লগইন করুন');
        $response->assertSee('Sign in to Falcon POS');
        $response->assertSee('Falcon POS');
        $response->assertDontSee('SNGPOS');
        $response->assertDontSee('এসএনজিপস');
    }

    public function test_dashboard_and_sidebar_use_dynamic_site_title(): void
    {
        Setting::set('site_title', 'Smart Counter POS');
        Setting::set('brand_tag', 'Modern POS Solution');

        $response = $this->actingAs($this->superAdmin)->get(route('shops.index'));

        $response->assertOk();
        $response->assertSee('Smart Counter POS');
        $response->assertSee('Modern POS Solution');
        $response->assertDontSee('এসএনজিপস');
        $response->assertDontSee('SNGPOS');
    }

    public function test_landing_page_uses_dynamic_site_title(): void
    {
        Setting::setLandingPageEnabled(true);
        Setting::set('site_title', 'Titan POS');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Titan POS');
        $response->assertSee('কেন Titan POS');
        $response->assertSee('Titan POS স্মার্ট অটোমেশন');
        $response->assertSee('Titan POS-এ');
        $response->assertDontSee('SNGPOS');
    }

    public function test_super_admin_can_update_site_title_and_affects_whole_application(): void
    {
        Setting::setLandingPageEnabled(true);

        $response = $this->actingAs($this->superAdmin)->post(route('system-settings.update'), [
            'site_title' => 'Nexus Retail POS',
            'brand_tag' => 'Next-Gen POS',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Nexus Retail POS', Setting::get('site_title'));
        $this->assertEquals('Nexus Retail POS', Setting::getSiteTitle());

        // 1. Check Login as Guest
        auth()->logout();
        $loginResponse = $this->get('/login');
        $loginResponse->assertOk();
        $loginResponse->assertSee('Nexus Retail POS');
        $loginResponse->assertDontSee('SNGPOS');
        $loginResponse->assertDontSee('এসএনজিপস');

        // 2. Check Admin Panel Layout
        $dashResponse = $this->actingAs($this->superAdmin)->get(route('shops.index'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Nexus Retail POS');
        $dashResponse->assertSee('Next-Gen POS');
        $dashResponse->assertDontSee('SNGPOS');
        $dashResponse->assertDontSee('এসএনজিপস');

        // 3. Check Landing Page
        $landingResponse = $this->get('/');
        $landingResponse->assertOk();
        $landingResponse->assertSee('Nexus Retail POS');
        $landingResponse->assertDontSee('SNGPOS');
    }
}
