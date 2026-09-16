<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Modules\Core\Models\Setting;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemSettingsNewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web', 'shop_id' => null]);
        $this->superAdmin = User::factory()->create([
            'email' => 'admin@pos.test',
        ]);
        $this->superAdmin->assignRole($superAdminRole);
    }

    public function test_setting_model_helpers_for_new_settings(): void
    {
        // 1. Terms & Policy toggle
        $this->assertTrue(Setting::isTermsAndPolicyEnabled()); // default true
        Setting::setTermsAndPolicyEnabled(false);
        $this->assertFalse(Setting::isTermsAndPolicyEnabled());
        Setting::setTermsAndPolicyEnabled(true);
        $this->assertTrue(Setting::isTermsAndPolicyEnabled());

        // 2. Show Credit Text toggle
        $this->assertFalse(Setting::isCreditTextEnabled()); // default false
        Setting::setCreditTextEnabled(true);
        $this->assertTrue(Setting::isCreditTextEnabled());
        Setting::setCreditTextEnabled(false);
        $this->assertFalse(Setting::isCreditTextEnabled());

        // 3. Credit text string
        $defaultCredit = 'Design and developed by SoftNGear | সফটএনগিয়ার দ্বারা ডিজাইন ও ডেভেলপ করা হয়েছে';
        $this->assertEquals($defaultCredit, Setting::getCreditText());
        Setting::setCreditText('Custom Powered By SoftNGear');
        $this->assertEquals('Custom Powered By SoftNGear', Setting::getCreditText());
    }

    public function test_footer_renders_or_hides_terms_and_policy_links(): void
    {
        Setting::setTermsAndPolicyEnabled(true);
        $renderedEnabled = Blade::render('<x-core::footer />');
        $this->assertStringContainsString('privacy-policy', $renderedEnabled);
        $this->assertStringContainsString('terms', $renderedEnabled);

        Setting::setTermsAndPolicyEnabled(false);
        $renderedDisabled = Blade::render('<x-core::footer />');
        $this->assertStringNotContainsString('privacy-policy', $renderedDisabled);
        $this->assertStringNotContainsString('terms', $renderedDisabled);
    }

    public function test_footer_renders_or_hides_credit_text(): void
    {
        Setting::setCreditTextEnabled(false);
        Setting::setCreditText('Powered by Softngear Custom');
        $renderedDisabled = Blade::render('<x-core::footer />');
        $this->assertStringNotContainsString('Powered by Softngear Custom', $renderedDisabled);

        Setting::setCreditTextEnabled(true);
        $renderedEnabled = Blade::render('<x-core::footer />');
        $this->assertStringContainsString('Powered by Softngear Custom', $renderedEnabled);
    }

    public function test_ajax_toggle_terms_and_policy(): void
    {
        $response = $this->actingAs($this->superAdmin)->postJson(route('system-settings.toggle-terms-policy'), [
            'state' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'enabled' => false]);
        $this->assertFalse(Setting::isTermsAndPolicyEnabled());

        $response2 = $this->actingAs($this->superAdmin)->postJson(route('system-settings.toggle-terms-policy'), [
            'state' => 1,
        ]);

        $response2->assertOk();
        $response2->assertJson(['success' => true, 'enabled' => true]);
        $this->assertTrue(Setting::isTermsAndPolicyEnabled());
    }

    public function test_ajax_toggle_credit_text(): void
    {
        $response = $this->actingAs($this->superAdmin)->postJson(route('system-settings.toggle-credit-text'), [
            'state' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'enabled' => true]);
        $this->assertTrue(Setting::isCreditTextEnabled());

        $response2 = $this->actingAs($this->superAdmin)->postJson(route('system-settings.toggle-credit-text'), [
            'state' => 0,
        ]);

        $response2->assertOk();
        $response2->assertJson(['success' => true, 'enabled' => false]);
        $this->assertFalse(Setting::isCreditTextEnabled());
    }

    public function test_update_settings_form_saves_all_new_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('system-settings.update'), [
            'show_terms_and_policy' => 0,
            'show_credit_text' => 1,
            'credit_text' => 'Custom Credit by Team Softngear',
        ]);

        $response->assertRedirect();
        $this->assertFalse(Setting::isTermsAndPolicyEnabled());
        $this->assertTrue(Setting::isCreditTextEnabled());
        $this->assertEquals('Custom Credit by Team Softngear', Setting::getCreditText());
    }

    public function test_terms_and_privacy_pages_return_404_when_disabled(): void
    {
        Setting::setTermsAndPolicyEnabled(true);
        $this->get(route('terms'))->assertOk();
        $this->get(route('privacy-policy'))->assertOk();

        Setting::setTermsAndPolicyEnabled(false);
        $this->get(route('terms'))->assertNotFound();
        $this->get(route('privacy-policy'))->assertNotFound();
        $this->get('/terms')->assertNotFound();
        $this->get('/privacy')->assertNotFound();
    }

    public function test_landing_page_hides_or_shows_terms_and_policy(): void
    {
        Setting::setLandingPageEnabled(true);

        Setting::setTermsAndPolicyEnabled(false);
        $resDisabled = $this->get('/');
        $resDisabled->assertOk();
        $resDisabled->assertDontSee(route('terms'));
        $resDisabled->assertDontSee(route('privacy-policy'));

        Setting::setTermsAndPolicyEnabled(true);
        $resEnabled = $this->get('/');
        $resEnabled->assertOk();
        $resEnabled->assertSee(route('terms'));
        $resEnabled->assertSee(route('privacy-policy'));
    }

    public function test_login_page_hides_or_shows_terms_and_policy(): void
    {
        Setting::setTermsAndPolicyEnabled(false);
        $resDisabled = $this->get('/login');
        $resDisabled->assertOk();
        $resDisabled->assertDontSee(route('terms'));
        $resDisabled->assertDontSee(route('privacy-policy'));

        Setting::setTermsAndPolicyEnabled(true);
        $resEnabled = $this->get('/login');
        $resEnabled->assertOk();
        $resEnabled->assertSee(route('terms'));
        $resEnabled->assertSee(route('privacy-policy'));
    }
}
