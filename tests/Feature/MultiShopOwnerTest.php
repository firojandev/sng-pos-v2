<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\Features;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiShopOwnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
    }

    public function test_user_can_belong_to_multiple_shops(): void
    {
        $shop1 = Shop::create([
            'name' => 'First Mart',
            'slug' => 'first-mart',
            'store_code' => 'FM-01',
            'status' => 'active',
        ]);

        $shop2 = Shop::create([
            'name' => 'Second Mart',
            'slug' => 'second-mart',
            'store_code' => 'SM-02',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Shop Owner',
            'email' => 'owner@multishop.test',
            'password' => Hash::make('password'),
            'shop_id' => $shop1->id,
        ]);

        $user->shops()->syncWithoutDetaching([
            $shop1->id => ['role' => 'Owner', 'is_owner' => true],
            $shop2->id => ['role' => 'Owner', 'is_owner' => true],
        ]);

        $this->assertTrue($user->hasMultipleShops());
        $this->assertCount(2, $user->shops);
        $this->assertTrue($user->belongsToShop($shop1));
        $this->assertTrue($user->belongsToShop($shop2));
    }

    public function test_single_shop_user_redirects_directly_to_dashboard_after_login(): void
    {
        $shop = Shop::create([
            'name' => 'Single Shop',
            'slug' => 'single-shop',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Single Owner',
            'email' => 'single@owner.test',
            'password' => Hash::make('password123'),
            'shop_id' => $shop->id,
        ]);
        $user->assignRole('Admin');

        $response = $this->post('/login', [
            'email' => 'single@owner.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($shop->id, $user->fresh()->shop_id);
    }

    public function test_multiple_shop_user_redirects_to_select_shop_page_after_login(): void
    {
        $shop1 = Shop::create([
            'name' => 'Shop Alpha',
            'slug' => 'shop-alpha',
            'status' => 'active',
        ]);

        $shop2 = Shop::create([
            'name' => 'Shop Beta',
            'slug' => 'shop-beta',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Multi Owner',
            'email' => 'multi@owner.test',
            'password' => Hash::make('password123'),
            'shop_id' => $shop1->id,
        ]);
        $user->assignRole('Admin');

        $user->shops()->syncWithoutDetaching([
            $shop1->id => ['role' => 'Owner', 'is_owner' => true],
            $shop2->id => ['role' => 'Owner', 'is_owner' => true],
        ]);

        $response = $this->post('/login', [
            'email' => 'multi@owner.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('shops.select'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_super_admin_redirects_directly_to_dashboard_after_login(): void
    {
        $superAdmin = User::create([
            'name' => 'Master Super Admin',
            'email' => 'super@masterpos.test',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $response = $this->post('/login', [
            'email' => 'super@masterpos.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_select_shop_page_renders_all_user_shops(): void
    {
        $shop1 = Shop::create([
            'name' => 'City Store 1',
            'slug' => 'city-store-1',
            'store_code' => 'CS-01',
            'status' => 'active',
        ]);

        $shop2 = Shop::create([
            'name' => 'City Store 2',
            'slug' => 'city-store-2',
            'store_code' => 'CS-02',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'City Owner',
            'email' => 'city@owner.test',
            'password' => Hash::make('password123'),
            'shop_id' => $shop1->id,
        ]);
        $user->assignRole('Admin');

        $user->shops()->syncWithoutDetaching([
            $shop1->id => ['role' => 'Owner', 'is_owner' => true],
            $shop2->id => ['role' => 'Owner', 'is_owner' => true],
        ]);

        $response = $this->actingAs($user)->get(route('shops.select'));

        $response->assertOk();
        $response->assertSee('দোকান নির্বাচন করুন');
        $response->assertSee('City Store 1');
        $response->assertSee('City Store 2');
        $response->assertSee('#CS-01');
        $response->assertSee('#CS-02');
    }

    public function test_user_can_switch_shop_successfully(): void
    {
        $shop1 = Shop::create([
            'name' => 'Outlet North',
            'slug' => 'outlet-north',
            'status' => 'active',
        ]);

        $shop2 = Shop::create([
            'name' => 'Outlet South',
            'slug' => 'outlet-south',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'North South Owner',
            'email' => 'northsouth@owner.test',
            'password' => Hash::make('password123'),
            'shop_id' => $shop1->id,
        ]);
        $user->assignRole('Admin');

        $user->shops()->syncWithoutDetaching([
            $shop1->id => ['role' => 'Owner', 'is_owner' => true],
            $shop2->id => ['role' => 'Owner', 'is_owner' => true],
        ]);

        $response = $this->actingAs($user)->post(route('shops.switch', $shop2));

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($shop2->id, $user->fresh()->shop_id);
    }

    public function test_user_cannot_switch_to_unauthorized_shop(): void
    {
        $shop1 = Shop::create([
            'name' => 'My Shop',
            'slug' => 'my-shop',
            'status' => 'active',
        ]);

        $shop2 = Shop::create([
            'name' => 'Other Persons Shop',
            'slug' => 'other-persons-shop',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Private Owner',
            'email' => 'private@owner.test',
            'password' => Hash::make('password123'),
            'shop_id' => $shop1->id,
        ]);
        $user->assignRole('Admin');

        $user->shops()->syncWithoutDetaching([
            $shop1->id => ['role' => 'Owner', 'is_owner' => true],
        ]);

        $response = $this->actingAs($user)->post(route('shops.switch', $shop2));

        $response->assertStatus(403);
        $this->assertEquals($shop1->id, $user->fresh()->shop_id);
    }

    public function test_shop_can_be_created_for_existing_owner(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pos.test',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $existingOwner = User::create([
            'name' => 'Chain Owner',
            'email' => 'chain@owner.test',
            'password' => Hash::make('existingpassword'),
        ]);
        $existingOwner->assignRole('Owner');

        // Create first shop
        $this->actingAs($superAdmin)->post(route('shops.store'), [
            'name' => 'Chain Branch 1',
            'slug' => 'chain-branch-1',
            'store_code' => 'CHAIN-01',
            'phone' => '01711111111',
            'status' => 'active',
            'features' => Features::keys(),
            'admin_name' => 'Chain Owner',
            'admin_email' => 'chain@owner.test',
            'admin_role' => 'Owner',
        ]);

        // Create second shop with same owner email
        $this->actingAs($superAdmin)->post(route('shops.store'), [
            'name' => 'Chain Branch 2',
            'slug' => 'chain-branch-2',
            'store_code' => 'CHAIN-02',
            'phone' => '01722222222',
            'status' => 'active',
            'features' => Features::keys(),
            'admin_name' => 'Chain Owner',
            'admin_email' => 'chain@owner.test',
            'admin_role' => 'Owner',
        ]);

        $this->assertCount(2, $existingOwner->fresh()->shops);
        $this->assertTrue($existingOwner->fresh()->hasMultipleShops());
    }

    public function test_destroy_admin_from_one_shop_keeps_user_if_has_other_shops(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin2@pos.test',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $shop1 = Shop::create([
            'name' => 'Shop 1',
            'slug' => 'shop-1',
            'status' => 'active',
        ]);

        $shop2 = Shop::create([
            'name' => 'Shop 2',
            'slug' => 'shop-2',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Dual Owner',
            'email' => 'dual@owner.test',
            'password' => Hash::make('password123'),
            'shop_id' => $shop1->id,
        ]);
        $owner->assignRole('Owner');

        $shop1->users()->syncWithoutDetaching([$owner->id => ['role' => 'Owner', 'is_owner' => true]]);
        $shop2->users()->syncWithoutDetaching([$owner->id => ['role' => 'Owner', 'is_owner' => true]]);

        // Delete from shop1
        $response = $this->actingAs($superAdmin)->delete(route('shops.admins.destroy', [$shop1, $owner]));
        $response->assertRedirect(route('shops.edit', $shop1));

        // User should still exist and belong to shop2
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
        $this->assertCount(1, $owner->fresh()->shops);
        $this->assertEquals($shop2->id, $owner->fresh()->shop_id);
    }

    public function test_shop_can_be_created_by_selecting_existing_owner(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin3@pos.test',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $existingOwner = User::create([
            'name' => 'Selected Owner',
            'email' => 'selected@owner.test',
            'password' => Hash::make('password123'),
        ]);
        $existingOwner->assignRole('Owner');

        $response = $this->actingAs($superAdmin)->post(route('shops.store'), [
            'name' => 'Shop for Selected Owner',
            'slug' => 'shop-selected-owner',
            'store_code' => 'SEL-01',
            'status' => 'active',
            'features' => Features::keys(),
            'owner_type' => 'existing',
            'existing_user_id' => $existingOwner->id,
            'admin_role' => 'Owner',
        ]);

        $response->assertRedirect(route('shops.index'));
        $shop = Shop::where('slug', 'shop-selected-owner')->first();
        $this->assertNotNull($shop);
        $this->assertTrue($existingOwner->fresh()->belongsToShop($shop));
        $this->assertTrue($shop->users()->where('users.id', $existingOwner->id)->exists());
    }

    public function test_superadmin_can_assign_monthly_package_during_shop_creation(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin_pkg@pos.test',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $monthlyPlan = Plan::firstOrCreate(['slug' => 'monthly-test-plan'], [
            'name' => 'Monthly Pro Plan',
            'price' => 1200.00,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('shops.store'), [
            'name' => 'Monthly Shop',
            'slug' => 'monthly-shop',
            'store_code' => 'MON-01',
            'status' => 'active',
            'features' => Features::keys(),
            'owner_type' => 'new',
            'admin_name' => 'Monthly Owner',
            'admin_email' => 'monthly@owner.test',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
            'admin_role' => 'Owner',
            'plan_id' => $monthlyPlan->id,
            'subscription_status' => 'active',
        ]);

        $response->assertRedirect(route('shops.index'));
        $shop = Shop::where('slug', 'monthly-shop')->first();
        $this->assertNotNull($shop);

        $subscription = $shop->activeSubscription;
        $this->assertNotNull($subscription);
        $this->assertEquals($monthlyPlan->id, $subscription->plan_id);
        $this->assertEquals('active', $subscription->status instanceof \BackedEnum ? $subscription->status->value : (string) $subscription->status);
        $this->assertEquals(now()->toDateString(), $subscription->starts_at->toDateString());
        $this->assertEquals(now()->addDays(30)->toDateString(), $subscription->ends_at->toDateString());
    }

    public function test_superadmin_can_assign_yearly_package_during_shop_creation(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin_pkg2@pos.test',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $yearlyPlan = Plan::firstOrCreate(['slug' => 'yearly-test-plan'], [
            'name' => 'Yearly Enterprise Plan',
            'price' => 12000.00,
            'billing_cycle' => 'yearly',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('shops.store'), [
            'name' => 'Yearly Shop',
            'slug' => 'yearly-shop',
            'store_code' => 'YR-01',
            'status' => 'active',
            'features' => Features::keys(),
            'owner_type' => 'new',
            'admin_name' => 'Yearly Owner',
            'admin_email' => 'yearly@owner.test',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
            'admin_role' => 'Owner',
            'plan_id' => $yearlyPlan->id,
            'subscription_status' => 'active',
        ]);

        $response->assertRedirect(route('shops.index'));
        $shop = Shop::where('slug', 'yearly-shop')->first();
        $this->assertNotNull($shop);

        $subscription = $shop->activeSubscription;
        $this->assertNotNull($subscription);
        $this->assertEquals($yearlyPlan->id, $subscription->plan_id);
        $this->assertEquals('active', $subscription->status instanceof \BackedEnum ? $subscription->status->value : (string) $subscription->status);
        $this->assertEquals(now()->toDateString(), $subscription->starts_at->toDateString());
        $this->assertEquals(now()->addDays(365)->toDateString(), $subscription->ends_at->toDateString());
    }

    public function test_superadmin_can_update_subscription_on_edit_shop(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin_edit@pos.test',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $shop = Shop::create([
            'name' => 'Editable Shop',
            'slug' => 'editable-shop',
            'status' => 'active',
        ]);

        $monthlyPlan = Plan::firstOrCreate(['slug' => 'monthly-edit-plan'], [
            'name' => 'Monthly Plan',
            'price' => 1500.00,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->put(route('shops.subscription.update', $shop), [
            'plan_id' => $monthlyPlan->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $subscription = $shop->fresh()->activeSubscription;
        $this->assertNotNull($subscription);
        $this->assertEquals($monthlyPlan->id, $subscription->plan_id);
        $this->assertEquals(now()->toDateString(), $subscription->starts_at->toDateString());
        $this->assertEquals(now()->addDays(30)->toDateString(), $subscription->ends_at->toDateString());
    }
}
