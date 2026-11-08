<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Models\Shop;
use PHPUnit\Framework\Attributes\TestWith;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagerPermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_saved_unverified_manager_access_depends_on_assigned_permissions(bool $hasPermission): void
    {
        $permission = Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);
        $shop = Shop::create(['name' => 'Manager Shop', 'slug' => 'manager-shop', 'status' => 'active']);
        $this->subscribeShopToFeatures($shop, ['users']);
        $manager = User::factory()->unverified()->create(['shop_id' => $shop->id]);
        $role = Role::query()->create(['name' => 'Manager', 'guard_name' => 'web', 'shop_id' => $shop->id]);
        if ($hasPermission) {
            $role->givePermissionTo($permission);
        }
        $manager->assignRole($role);
        $manager->shops()->updateExistingPivot($shop->id, ['role' => 'Manager', 'is_owner' => false]);

        $manager->update(['name' => 'Updated Manager']);
        $response = $this->actingAs($manager->fresh())->get(route('roles.index'));

        $this->assertDatabaseHas('shop_user', [
            'user_id' => $manager->id,
            'shop_id' => $shop->id,
            'role' => 'Manager',
            'is_owner' => false,
        ]);
        if ($hasPermission) {
            $response->assertOk();
        } else {
            $response->assertForbidden();
        }
    }

    public function test_saving_unverified_owner_preserves_ownership_and_verification_requirement(): void
    {
        $shop = Shop::create(['name' => 'Owner Shop', 'slug' => 'owner-shop', 'status' => 'active']);
        $owner = User::factory()->unverified()->create(['shop_id' => $shop->id]);
        $owner->shops()->updateExistingPivot($shop->id, ['role' => 'Owner', 'is_owner' => true]);

        $owner->update(['name' => 'Updated Owner']);
        $response = $this->actingAs($owner->fresh())->get(route('dashboard'));

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('shop_user', [
            'user_id' => $owner->id,
            'shop_id' => $shop->id,
            'role' => 'Owner',
            'is_owner' => true,
        ]);
    }
}
