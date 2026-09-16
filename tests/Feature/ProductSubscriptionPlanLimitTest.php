<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Category;
use Modules\Product\Models\Unit;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Support\PlanLimits;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductSubscriptionPlanLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    private function createShopWithUser(Plan $plan): array
    {
        $shop = Shop::create([
            'name' => 'Test Gadget Shop',
            'slug' => 'test-gadget-shop-'.uniqid(),
            'status' => 'active',
        ]);

        $shop->subscribe($plan);

        setPermissionsTeamId($shop->id);

        $adminRole = Role::firstOrCreate([
            'shop_id' => $shop->id,
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions(
            Permission::where('guard_name', 'web')->get()
        );

        $user = User::create([
            'name' => 'Shop Owner',
            'email' => 'owner-'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'shop_id' => $shop->id,
        ]);

        $user->assignRole($adminRole);

        $shop->users()->syncWithoutDetaching([
            $user->id => ['role' => 'Admin', 'is_owner' => true],
        ]);

        setPermissionsTeamId(null);

        return [$shop, $user];
    }

    public function test_standard_plan_shop_can_create_product(): void
    {
        $standardPlan = Plan::where('slug', 'standard')->firstOrFail();
        [$shop, $user] = $this->createShopWithUser($standardPlan);

        $category = Category::create(['shop_id' => $shop->id, 'name' => 'Electronics']);
        $unit = Unit::create(['shop_id' => $shop->id, 'name' => 'Piece', 'short_code' => 'pc']);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Smart Watch Pro',
            'category_id' => $category->id,
            'purchase_price' => 1200,
            'sale_price' => 1800,
            'alert_qty' => 5,
            'status' => 'active',
            'units' => [
                ['unit_id' => $unit->id, 'is_base' => true, 'conversion_factor' => 1],
            ],
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('status', 'পণ্য সফলভাবে যোগ করা হয়েছে');

        $this->assertDatabaseHas('products', [
            'shop_id' => $shop->id,
            'name' => 'Smart Watch Pro',
        ]);
    }

    public function test_plan_limits_check_blocks_when_limit_is_reached(): void
    {
        $starterPlan = Plan::where('slug', 'starter')->firstOrFail();
        [$shop, $user] = $this->createShopWithUser($starterPlan);

        // Starter plan max_products is 200
        $this->assertNull(PlanLimits::check($shop->id, 'max_products', 0));
        $this->assertNull(PlanLimits::check($shop->id, 'max_products', 199));

        $exceededMessage = PlanLimits::check($shop->id, 'max_products', 200);
        $this->assertNotNull($exceededMessage);
        $this->assertStringContainsString('প্ল্যানে', $exceededMessage);
    }

    public function test_enterprise_unlimited_plan_allows_creating_products_indefinitely(): void
    {
        $enterprisePlan = Plan::where('slug', 'enterprise')->firstOrFail();
        [$shop, $user] = $this->createShopWithUser($enterprisePlan);

        $this->assertNull(PlanLimits::check($shop->id, 'max_products', 0));
        $this->assertNull(PlanLimits::check($shop->id, 'max_products', 10000));
        $this->assertNull(PlanLimits::check($shop->id, 'max_products', 500000));
    }
}
