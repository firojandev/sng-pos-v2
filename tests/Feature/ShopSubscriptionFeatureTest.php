<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Models\Plan;
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
        ]);
        $this->subscribeShopToFeatures($shop, ['subscription']);

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
        ]);
        $this->subscribeShopToFeatures($shop, ['sales']); // subscription not included

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

    public function test_reassigning_a_shops_plan_changes_its_feature_access(): void
    {
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $shop = Shop::create([
            'name' => 'Mobile Hub',
            'slug' => 'mobile-hub',
            'status' => 'active',
        ]);
        $this->subscribeShopToFeatures($shop, ['sales', 'subscription']);
        $plan = $shop->activeSubscription->plan;

        // Super Admin removes the 'subscription' feature from the shop's plan.
        $response = $this->actingAs($superAdmin)->put(route('plans.update', $plan), [
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'features' => ['sales'],
        ]);

        $response->assertRedirect(route('plans.index'));
        $shop->refresh()->clearSubscriptionCache();
        $this->assertFalse($shop->hasFeature('subscription'));

        // Super Admin re-adds the 'subscription' feature to the plan.
        $response = $this->actingAs($superAdmin)->put(route('plans.update', $plan), [
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'features' => ['sales', 'subscription'],
        ]);

        $response->assertRedirect(route('plans.index'));
        $shop->refresh()->clearSubscriptionCache();
        $this->assertTrue($shop->hasFeature('subscription'));
    }

    public function test_super_admin_can_see_subscription_link_in_settings_and_dashboard(): void
    {
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $shop = Shop::create([
            'name' => 'Admin Shop',
            'slug' => 'admin-shop',
            'status' => 'active',
        ]);
        $this->subscribeShopToFeatures($shop, ['sales']);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);
        $superAdmin->update(['shop_id' => $shop->id]);

        $response = $this->actingAs($superAdmin)->get(route('settings.index'));
        $response->assertStatus(200);
        $response->assertSee(route('subscription.show'));

        $shopsResponse = $this->actingAs($superAdmin)->get(route('shops.index'));
        $shopsResponse->assertStatus(200);
        $shopsResponse->assertSee(route('subscription.show'));
    }
}
