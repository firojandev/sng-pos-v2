<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Setting;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web', 'shop_id' => null]);
        $userRole = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web', 'shop_id' => null]);

        $this->superAdmin = User::factory()->create([
            'email' => 'super@pos.test',
        ]);
        $this->superAdmin->assignRole($superAdminRole);

        $this->regularUser = User::factory()->create([
            'email' => 'user@pos.test',
        ]);
        $this->regularUser->assignRole($userRole);
    }

    public function test_root_renders_landing_page_when_enabled(): void
    {
        Setting::setLandingPageEnabled(true);
        Setting::setRegistrationEnabled(true);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('MasterPOS');
        $response->assertSee('Registration');
        $response->assertSee('Demo Simulator');
        $response->assertSee(route('register'));
    }

    public function test_landing_page_hides_registration_button_when_registration_disabled(): void
    {
        Setting::setLandingPageEnabled(true);
        Setting::setRegistrationEnabled(false);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee(route('register'));
        $response->assertSee(route('login'));
    }

    public function test_preview_route_always_renders_landing_page(): void
    {
        // Even when landing page is disabled
        Setting::setLandingPageEnabled(false);

        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('MasterPOS');
        $response->assertSee('Registration');
        $response->assertSee('Preview Mode');
    }

    public function test_root_redirects_guest_to_login_when_landing_page_is_disabled(): void
    {
        Setting::setLandingPageEnabled(false);

        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_root_redirects_authenticated_user_to_dashboard_when_disabled(): void
    {
        Setting::setLandingPageEnabled(false);

        $response = $this->actingAs($this->regularUser)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_super_admin_can_access_system_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('system-settings.index'));

        $response->assertOk();
        $response->assertSee('System Settings');
        $response->assertSee('Public Landing Page');
        $response->assertSee('landing_page_toggle');
    }

    public function test_non_super_admin_cannot_access_system_settings(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('system-settings.index'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_system_settings(): void
    {
        $response = $this->get(route('system-settings.index'));

        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_toggle_landing_page_via_ajax(): void
    {
        Setting::setLandingPageEnabled(true);

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('system-settings.toggle-landing'), [
                'state' => false,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'enabled' => false,
        ]);

        $this->assertFalse(Setting::isLandingPageEnabled());

        // Toggle back to true
        $response2 = $this->actingAs($this->superAdmin)
            ->postJson(route('system-settings.toggle-landing'), [
                'state' => true,
            ]);

        $response2->assertOk();
        $response2->assertJson([
            'success' => true,
            'enabled' => true,
        ]);

        $this->assertTrue(Setting::isLandingPageEnabled());
    }

    public function test_super_admin_can_update_system_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('system-settings.update'), [
                'landing_page_enabled' => '1',
                'site_title' => 'MasterPOS Pro',
                'support_phone' => '01700000000',
                'support_email' => 'support@masterpos.test',
                'office_address' => 'Test Address, Dhaka',
                'meta_description' => 'Accounting & POS software for retail',
            ]);

        $response->assertRedirect(route('system-settings.index'));
        $response->assertSessionHas('status', 'সিস্টেম সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে (System settings saved successfully)');

        $this->assertEquals('MasterPOS Pro', Setting::get('site_title'));
        $this->assertEquals('01700000000', Setting::get('support_phone'));
        $this->assertEquals('support@masterpos.test', Setting::get('support_email'));
    }

    public function test_artisan_landing_toggle_command(): void
    {
        // Toggle to disabled
        $this->artisan('landing:toggle', ['state' => 'disable'])
            ->expectsOutputToContain('Landing page is now DISABLED')
            ->assertSuccessful();

        $this->assertFalse(Setting::isLandingPageEnabled());

        // Toggle to enabled
        $this->artisan('landing:toggle', ['state' => 'enable'])
            ->expectsOutputToContain('Landing page is now ENABLED')
            ->assertSuccessful();

        $this->assertTrue(Setting::isLandingPageEnabled());

        // Check status
        $this->artisan('landing:status')
            ->expectsOutputToContain('Landing page is currently')
            ->assertSuccessful();
    }
}
