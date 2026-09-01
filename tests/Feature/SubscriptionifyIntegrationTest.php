<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Support\PlanLimits;
use Tests\TestCase;

class SubscriptionifyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    private function createShop(string $slug = 'test-pos-shop'): Shop
    {
        return Shop::create([
            'name' => 'Test POS Shop',
            'slug' => $slug,
            'phone' => '+8801700000001',
            'status' => 'active',
        ]);
    }

    public function test_shop_can_subscribe_to_plan_and_check_status(): void
    {
        $shop = $this->createShop();
        $starterPlan = Plan::where('slug', 'starter')->firstOrFail();

        $subscription = $shop->subscribe($starterPlan);

        $this->assertTrue($shop->subscribed());
        $this->assertEquals($starterPlan->id, $shop->subscription()->plan_id);
        $this->assertTrue($shop->onTrial());
        $this->assertEquals('starter', $shop->subscription()->getPlan()->getSlug());
    }

    public function test_shop_feature_toggles_and_limits_via_subscriptionify(): void
    {
        $shop = $this->createShop();
        $starterPlan = Plan::where('slug', 'starter')->firstOrFail();

        $shop->subscribe($starterPlan);

        // Toggle feature included in starter
        $this->assertTrue($shop->hasFeature('sales'));
        $this->assertTrue($shop->hasFeature('quick-sale'));

        // Feature not in starter plan
        $this->assertFalse($shop->hasFeature('tax'));

        // Limit feature checks
        $this->assertTrue($shop->canConsume('users', 1));
        $this->assertTrue($shop->canConsume('products', 1));
    }

    public function test_shop_can_grant_and_revoke_custom_direct_features(): void
    {
        $shop = $this->createShop();
        $starterPlan = Plan::where('slug', 'starter')->firstOrFail();

        $shop->subscribe($starterPlan);

        $this->assertFalse($shop->hasFeature('tax'));

        // Directly grant 'tax' feature to this shop
        $shop->grantFeature('tax');
        $this->assertTrue($shop->hasFeature('tax'));

        // Revoke direct feature
        $shop->revokeFeature('tax');
        $this->assertFalse($shop->hasFeature('tax'));
    }

    public function test_plan_limits_check_integrates_with_subscriptionify(): void
    {
        $shop = $this->createShop();
        $starterPlan = Plan::where('slug', 'starter')->firstOrFail();

        $shop->subscribe($starterPlan);

        // Starter plan limit for branches is 1
        $noLimitMessage = PlanLimits::check($shop->id, 'max_branches', 0);
        $this->assertNull($noLimitMessage);

        $limitExceededMessage = PlanLimits::check($shop->id, 'max_branches', 1);
        $this->assertNotNull($limitExceededMessage);
        $this->assertStringContainsString('প্ল্যানে', $limitExceededMessage);
    }

    public function test_blade_directives_resolve_subscribable_shop(): void
    {
        $shop = $this->createShop();
        $standardPlan = Plan::where('slug', 'standard')->firstOrFail();

        $shop->subscribe($standardPlan);

        $user = User::create([
            'email' => 'shopadmin@test.com',
            'name' => 'Shop Admin',
            'password' => bcrypt('password'),
            'shop_id' => $shop->id,
        ]);

        $this->actingAs($user);

        $rendered = Blade::render('
            @subscribed
                <span>SUBSCRIBED_OK</span>
            @endsubscribed

            @feature("sales")
                <span>SALES_FEATURE_ACTIVE</span>
            @endfeature
        ');

        $this->assertStringContainsString('SUBSCRIBED_OK', $rendered);
        $this->assertStringContainsString('SALES_FEATURE_ACTIVE', $rendered);
    }
}
