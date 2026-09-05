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

class ShopRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shopA;

    protected Shop $shopB;

    protected User $superAdmin;

    protected User $ownerA;

    protected User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Global Super Admin
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web', 'shop_id' => null]);
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@pos.test',
            'password' => bcrypt('password'),
        ]);
        $this->superAdmin->assignRole($superAdminRole);

        // Shop A
        $this->shopA = Shop::create([
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);
        $plan = Plan::where('slug', 'standard')->first();
        if ($plan) {
            $this->shopA->subscribe($plan);
        }

        $this->ownerA = User::create([
            'name' => 'Owner A',
            'email' => 'owner.a@pos.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shopA->id,
        ]);
        setPermissionsTeamId($this->shopA->id);
        $shopARole = Role::where('shop_id', $this->shopA->id)->where('name', 'Admin')->first();
        $this->ownerA->assignRole($shopARole);

        // Shop B
        $this->shopB = Shop::create([
            'name' => 'Shop B',
            'slug' => 'shop-b',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);
        if ($plan) {
            $this->shopB->subscribe($plan);
        }

        $this->ownerB = User::create([
            'name' => 'Owner B',
            'email' => 'owner.b@pos.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shopB->id,
        ]);
        setPermissionsTeamId($this->shopB->id);
        $shopBRole = Role::where('shop_id', $this->shopB->id)->where('name', 'Admin')->first();
        $this->ownerB->assignRole($shopBRole);

        setPermissionsTeamId(null);
    }

    public function test_shop_creation_automatically_provisions_shop_specific_admin_role(): void
    {
        $roleA = Role::where('shop_id', $this->shopA->id)->where('name', 'Admin')->first();
        $roleB = Role::where('shop_id', $this->shopB->id)->where('name', 'Admin')->first();

        $this->assertNotNull($roleA);
        $this->assertNotNull($roleB);
        $this->assertNotEquals($roleA->id, $roleB->id);
        $this->assertEquals($this->shopA->id, $roleA->shop_id);
        $this->assertEquals($this->shopB->id, $roleB->shop_id);

        // Verify permissions are synced
        $this->assertGreaterThan(0, $roleA->permissions()->count());
        $this->assertEquals($roleA->permissions()->count(), $roleB->permissions()->count());
    }

    public function test_multiple_shops_can_have_roles_with_same_name_and_different_permissions(): void
    {
        $managerA = Role::query()->create([
            'shop_id' => $this->shopA->id,
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);
        $managerA->syncPermissions(['sales.view']);

        $managerB = Role::query()->create([
            'shop_id' => $this->shopB->id,
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);
        $managerB->syncPermissions(['sales.view', 'sales.write', 'sales.delete']);

        $this->assertNotEquals($managerA->id, $managerB->id);
        $this->assertCount(1, $managerA->permissions);
        $this->assertCount(3, $managerB->permissions);
    }

    public function test_cannot_create_duplicate_role_name_in_the_same_shop(): void
    {
        $this->actingAs($this->ownerA);

        $response = $this->post(route('roles.store'), [
            'name' => 'Admin', // already exists in Shop A
            'permissions' => ['sales.view'],
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_permissions_are_scoped_to_active_shop(): void
    {
        // In Shop A, ownerA has permissions
        $this->actingAs($this->ownerA);
        setPermissionsTeamId($this->shopA->id);

        $this->assertTrue($this->ownerA->can('sales.view'));

        // In Shop B context, ownerA has NO roles/permissions
        setPermissionsTeamId($this->shopB->id);
        $this->ownerA->unsetRelation('roles');
        $this->ownerA->unsetRelation('permissions');

        $this->assertFalse($this->ownerA->can('sales.view'));
    }

    public function test_super_admin_bypasses_shop_permissions(): void
    {
        $this->actingAs($this->superAdmin);
        setPermissionsTeamId(null);

        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->can('sales.view'));
        $this->assertTrue($this->superAdmin->can('sales.delete'));

        // Even with a team scope set, isSuperAdmin() remains true
        setPermissionsTeamId($this->shopA->id);
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->can('sales.view'));
    }

    public function test_shop_owner_can_create_and_manage_custom_roles(): void
    {
        $this->actingAs($this->ownerA);

        // 1. Create custom role
        $createResponse = $this->post(route('roles.store'), [
            'name' => 'Cashier',
            'permissions' => ['sales.view', 'sales.write'],
        ]);
        $createResponse->assertRedirect(route('roles.index'));

        $cashierRole = Role::where('shop_id', $this->shopA->id)->where('name', 'Cashier')->first();
        $this->assertNotNull($cashierRole);
        $this->assertCount(2, $cashierRole->permissions);

        // 2. Edit custom role
        $editResponse = $this->put(route('roles.update', $cashierRole), [
            'name' => 'Senior Cashier',
            'permissions' => ['sales.view'],
        ]);
        $editResponse->assertRedirect(route('roles.index'));
        $this->assertEquals('Senior Cashier', $cashierRole->fresh()->name);
        $this->assertCount(1, $cashierRole->fresh()->permissions);

        // 3. Delete custom role
        $deleteResponse = $this->delete(route('roles.destroy', $cashierRole));
        $deleteResponse->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $cashierRole->id]);
    }

    public function test_shop_owner_cannot_delete_default_admin_role(): void
    {
        $this->actingAs($this->ownerA);

        $adminRole = Role::where('shop_id', $this->shopA->id)->where('name', 'Admin')->first();

        $response = $this->delete(route('roles.destroy', $adminRole));
        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('status', 'ডিফল্ট এডমিন রোলটি মুছে ফেলা যাবে না');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_shop_owner_cannot_access_or_modify_roles_of_another_shop(): void
    {
        $this->actingAs($this->ownerA);

        $adminRoleB = Role::where('shop_id', $this->shopB->id)->where('name', 'Admin')->first();

        $editResponse = $this->get(route('roles.edit', $adminRoleB));
        $editResponse->assertNotFound();

        $updateResponse = $this->put(route('roles.update', $adminRoleB), [
            'name' => 'Hacked Admin',
            'permissions' => [],
        ]);
        $updateResponse->assertNotFound();

        $deleteResponse = $this->delete(route('roles.destroy', $adminRoleB));
        $deleteResponse->assertNotFound();
    }
}
