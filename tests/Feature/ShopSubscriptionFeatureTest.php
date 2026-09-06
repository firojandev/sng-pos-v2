<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopSubscriptionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_subscription_page_and_see_links_when_feature_enabled(): void
    {
        $shop = Shop::create([
            'name' => 'Tech Zone',
            'slug' => 'tech-zone',
            'status' => 'active',
            'enabled_features' => ['subscription'],
        ]);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);

        $response = $this->actingAs($user)->get(route('subscription.show'));
        $response->assertStatus(200);

        // Sidebar and Topbar dropdown should include subscription
        $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee(route('subscription.show'));
    }

    public function test_user_is_forbidden_and_links_are_hidden_when_feature_disabled(): void
    {
        $shop = Shop::create([
            'name' => 'Fashion House',
            'slug' => 'fashion-house',
            'status' => 'active',
            'enabled_features' => ['sales'], // subscription not included
        ]);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);

        $response = $this->actingAs($user)->get(route('subscription.show'));
        $response->assertStatus(403);

        // Sidebar and Topbar dropdown should NOT include subscription
        $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertDontSee(route('subscription.show'));
    }

    public function test_super_admin_can_toggle_subscription_feature_for_shop(): void
    {
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $shop = Shop::create([
            'name' => 'Mobile Hub',
            'slug' => 'mobile-hub',
            'status' => 'active',
            'enabled_features' => ['sales', 'subscription'],
        ]);

        // Super Admin disables subscription feature
        $response = $this->actingAs($superAdmin)->put(route('shops.update', $shop), [
            'name' => 'Mobile Hub Updated',
            'slug' => 'mobile-hub',
            'status' => 'active',
            'features' => ['sales'], // without subscription
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $shop->refresh();
        $this->assertFalse($shop->hasFeature('subscription'));

        // Super Admin re-enables subscription feature
        $response = $this->actingAs($superAdmin)->put(route('shops.update', $shop), [
            'name' => 'Mobile Hub Updated',
            'slug' => 'mobile-hub',
            'status' => 'active',
            'features' => ['sales', 'subscription'],
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $shop->refresh();
        $this->assertTrue($shop->hasFeature('subscription'));
    }
}
