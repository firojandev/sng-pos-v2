<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleSettingsSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    public function test_users_roles_and_shop_settings_are_separated_and_have_no_tabbar(): void
    {
        $shop = Shop::create([
            'name' => 'সেপারেশন টেস্ট শপ',
            'slug' => 'separation-test-shop',
            'store_code' => 'STS-01',
            'status' => 'active',
        ]);

        $plan = Plan::where('slug', 'standard')->first();
        if ($plan) {
            $shop->subscribe($plan);
        }

        $adminRole = Role::firstOrCreate([
            'shop_id' => $shop->id,
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $permission = Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($permission);

        $user = User::create([
            'name' => 'অ্যাডমিন ইউজার',
            'email' => 'admin@separation.test',
            'password' => Hash::make('password'),
            'shop_id' => $shop->id,
        ]);
        $user->assignRole($adminRole);

        // 1. Users page check
        $usersResponse = $this->actingAs($user)->get(route('users.index'));
        $usersResponse->assertStatus(200);
        $usersResponse->assertSee('ইউজার ব্যবস্থাপনা');
        $usersResponse->assertDontSee('class="tabbar"', false);

        // 2. Roles page check
        $rolesResponse = $this->actingAs($user)->get(route('roles.index'));
        $rolesResponse->assertStatus(200);
        $rolesResponse->assertSee('রোল ও পারমিশন');
        $rolesResponse->assertDontSee('class="tabbar"', false);

        // 3. Shop settings page check
        $settingsResponse = $this->actingAs($user)->get(route('settings.index'));
        $settingsResponse->assertStatus(200);
        $settingsResponse->assertSee('দোকান সেটিংস');
        $settingsResponse->assertDontSee('class="tabbar"', false);

        // 4. Sidebar contains all three separate links
        $usersResponse->assertSee(route('users.index'));
        $usersResponse->assertSee(route('roles.index'));
        $usersResponse->assertSee(route('settings.index'));
    }
}
