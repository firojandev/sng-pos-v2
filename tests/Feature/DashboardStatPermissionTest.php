<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardStatPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->shop = Shop::create([
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'status' => 'active',
        ]);

        $plan = Plan::where('slug', 'standard')->first();
        if ($plan) {
            $this->shop->subscribe($plan);
        }

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@testshop.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);

        setPermissionsTeamId($this->shop->id);
        $adminRole = Role::where('shop_id', $this->shop->id)->where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->adminUser->assignRole($adminRole);
        }
        setPermissionsTeamId(null);
    }

    public function test_dashboard_is_not_in_features_list(): void
    {
        $this->assertArrayNotHasKey('dashboard', Features::all());
    }

    public function test_shop_owner_automatically_gets_dashboard_access_and_all_stats(): void
    {
        $owner = User::create([
            'name' => 'Shop Owner',
            'email' => 'owner@testshop.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        setPermissionsTeamId($this->shop->id);
        $ownerRole = Role::firstOrCreate([
            'shop_id' => $this->shop->id,
            'name' => 'Owner',
            'guard_name' => 'web',
        ]);
        $owner->assignRole($ownerRole);
        setPermissionsTeamId(null);

        $this->assertTrue($owner->isShopAdmin());

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('মোট ব্যালেন্স:');
        $response->assertSee('বিক্রি');
        $response->assertSee('ক্রয়');
        $response->assertSee('খরচ');
        $response->assertSee('পণ্য লাভ');
        $response->assertSee('মোট লাভ');
        $response->assertSee('ক্যাশ ব্যালেন্স');
        $response->assertDontSee('আপনার দেখার মতো কোনো পরিসংখ্যান নেই');
    }

    public function test_admin_can_view_all_dashboard_stats(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('মোট ব্যালেন্স:');
        $response->assertSee('বিক্রি');
        $response->assertSee('ক্রয়');
        $response->assertSee('খরচ');
        $response->assertSee('পণ্য লাভ');
        $response->assertSee('মোট লাভ');
        $response->assertSee('মোট মজুদ মূল্য');
        $response->assertSee('মোট মজুদ');
        $response->assertSee('মোট পাবো');
        $response->assertSee('মোট দিবো');
        $response->assertSee('ক্যাশ ব্যালেন্স');
        $response->assertSee('ব্যাংক ব্যালেন্স');
        $response->assertSee('মোবাইল ব্যাংকিং (MFS)');
        $response->assertDontSee('আপনার দেখার মতো কোনো পরিসংখ্যান নেই');
    }

    public function test_user_with_specific_dashboard_stat_permissions_only_sees_permitted_stats(): void
    {
        setPermissionsTeamId($this->shop->id);
        $cashierRole = Role::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cashier',
            'guard_name' => 'web',
        ]);
        $cashierRole->syncPermissions([
            'dashboard.view',
            'dashboard.stat-sales',
            'dashboard.stat-cash',
        ]);

        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@testshop.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $cashier->assignRole($cashierRole);
        setPermissionsTeamId(null);

        $response = $this->actingAs($cashier)->get(route('dashboard'));

        $response->assertOk();
        // Permitted stats are visible
        $response->assertSee('বিক্রি');
        $response->assertSee('ক্যাশ ব্যালেন্স');

        // Unpermitted stats are NOT visible
        $response->assertDontSee('মোট ব্যালেন্স:');
        $response->assertDontSee('ক্রয়');
        $response->assertDontSee('খরচ');
        $response->assertDontSee('পণ্য লাভ');
        $response->assertDontSee('মোট লাভ');
        $response->assertDontSee('মোট মজুদ মূল্য');
        $response->assertDontSee('মোট মজুদ');
        $response->assertDontSee('মোট পাবো');
        $response->assertDontSee('মোট দিবো');
        $response->assertDontSee('ব্যাংক ব্যালেন্স');
        $response->assertDontSee('মোবাইল ব্যাংকিং (MFS)');
    }

    public function test_user_with_no_stat_permissions_sees_no_stat_cards(): void
    {
        $viewer = $this->createStaffUser('Limited Viewer', 'viewer@testshop.com', []);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('বিক্রি');
        $response->assertDontSee('মোট ব্যালেন্স:');
    }

    public function test_any_shop_user_can_open_the_dashboard_without_a_dashboard_permission(): void
    {
        $user = $this->createStaffUser('No Dashboard', 'restricted@testshop.com', ['sales.view']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    /**
     * Create a non-owner staff user the way UserController@store does.
     *
     * @param  list<string>  $permissions
     */
    private function createStaffUser(string $roleName, string $email, array $permissions): User
    {
        setPermissionsTeamId($this->shop->id);
        $role = Role::create([
            'shop_id' => $this->shop->id,
            'name' => $roleName,
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissions);

        $user = User::create([
            'name' => $roleName.' User',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $user->assignRole($role);
        $user->shops()->updateExistingPivot($this->shop->id, ['role' => $role->name, 'is_owner' => false]);
        setPermissionsTeamId(null);

        return $user;
    }

    public function test_admin_can_grant_and_revoke_dashboard_stat_permissions_on_roles(): void
    {
        setPermissionsTeamId($this->shop->id);
        $customRole = Role::create([
            'shop_id' => $this->shop->id,
            'name' => 'Staff Role',
            'guard_name' => 'web',
        ]);
        setPermissionsTeamId(null);

        // Admin grants dashboard view and sales & expense stats
        $response = $this->actingAs($this->adminUser)->put(route('roles.update', $customRole), [
            'name' => 'Staff Role',
            'permissions' => [
                'dashboard.view',
                'dashboard.stat-sales',
                'dashboard.stat-expense',
            ],
        ]);

        $response->assertRedirect(route('roles.index'));
        $customRole->refresh();
        $this->assertTrue($customRole->hasPermissionTo('dashboard.view'));
        $this->assertTrue($customRole->hasPermissionTo('dashboard.stat-sales'));
        $this->assertTrue($customRole->hasPermissionTo('dashboard.stat-expense'));
        $this->assertFalse($customRole->hasPermissionTo('dashboard.stat-purchase'));
        $this->assertFalse($customRole->hasPermissionTo('dashboard.stat-balance'));

        // Admin updates to grant purchase and remove expense
        $response = $this->actingAs($this->adminUser)->put(route('roles.update', $customRole), [
            'name' => 'Staff Role',
            'permissions' => [
                'dashboard.view',
                'dashboard.stat-sales',
                'dashboard.stat-purchase',
            ],
        ]);

        $response->assertRedirect(route('roles.index'));
        $customRole->refresh();
        $this->assertTrue($customRole->hasPermissionTo('dashboard.stat-purchase'));
        $this->assertFalse($customRole->hasPermissionTo('dashboard.stat-expense'));
    }
}
