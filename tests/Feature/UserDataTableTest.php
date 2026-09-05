<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\User\DataTables\UsersDataTable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $adminUser;

    protected Role $adminRole;

    protected Role $cashierRole;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->cashierRole = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);

        $this->shop = Shop::create([
            'name' => 'User Test Shop',
            'slug' => 'user-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->adminUser = User::create([
            'name' => 'Shop Admin',
            'email' => 'admin@usershop.test',
            'password' => bcrypt('password123'),
            'shop_id' => $this->shop->id,
            'email_verified_at' => now(),
        ]);
        $this->adminUser->syncRoles([$this->adminRole]);
    }

    public function test_users_datatable_generates_html_builder(): void
    {
        $dataTable = new UsersDataTable;
        $html = $dataTable->html();

        $this->assertEquals('users-data-table', $html->getTableAttribute('id'));
        $this->assertCount(6, $dataTable->getColumns());
    }

    public function test_users_datatable_query_returns_query_builder(): void
    {
        $this->actingAs($this->adminUser);

        $dataTable = new UsersDataTable;
        $query = $dataTable->query(new User);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_users_index_page_loads_with_modals_and_datatable(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('users-data-table');
        $response->assertSee('createUserModal');
        $response->assertSee('editUserModal');
        $response->assertSee('নতুন ইউজার');
        $response->assertSee('প্ল্যান ক্যাপাসিটি');
    }

    public function test_users_datatable_ajax_returns_json(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('users.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json('recordsTotal'));
    }

    public function test_users_datatable_filters_by_role(): void
    {
        $staff = User::create([
            'name' => 'Staff Cashier',
            'email' => 'cashier@usershop.test',
            'password' => bcrypt('password123'),
            'shop_id' => $this->shop->id,
        ]);
        $staff->syncRoles([$this->cashierRole]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('users.index', ['role' => 'Cashier']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Staff Cashier', json_encode($response->json('data')));
    }

    public function test_users_datatable_filters_by_verification_status(): void
    {
        User::create([
            'name' => 'Unverified User',
            'email' => 'unverified@usershop.test',
            'password' => bcrypt('password123'),
            'shop_id' => $this->shop->id,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('users.index', ['status' => 'unverified']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Unverified User', json_encode($response->json('data')));
    }

    public function test_user_store_via_ajax(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('users.store'), [
                'name' => 'New Staff Member',
                'email' => 'newstaff@usershop.test',
                'password' => 'secret1234',
                'password_confirmation' => 'secret1234',
                'role' => 'Admin',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newstaff@usershop.test',
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_user_update_via_ajax(): void
    {
        $member = User::create([
            'name' => 'Old Name',
            'email' => 'oldemail@usershop.test',
            'password' => bcrypt('password123'),
            'shop_id' => $this->shop->id,
        ]);
        $member->syncRoles([$this->adminRole]);

        $response = $this->actingAs($this->adminUser)
            ->putJson(route('users.update', $member), [
                'name' => 'Updated Name',
                'email' => 'updatedemail@usershop.test',
                'role' => 'Admin',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Updated Name',
            'email' => 'updatedemail@usershop.test',
        ]);
    }

    public function test_user_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('users.destroy', $this->adminUser));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'নিজের অ্যাকাউন্ট মুছে ফেলা যাবে না',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
        ]);
    }

    public function test_user_delete_via_ajax(): void
    {
        $member = User::create([
            'name' => 'To Delete',
            'email' => 'todelete@usershop.test',
            'password' => bcrypt('password123'),
            'shop_id' => $this->shop->id,
        ]);
        $member->syncRoles([$this->adminRole]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('users.destroy', $member));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $member->id,
        ]);
    }
}
