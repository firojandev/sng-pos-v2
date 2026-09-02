<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\DataTables\ShopsDataTable;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    private function createSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_shops_datatable_generates_html_builder(): void
    {
        $dataTable = new ShopsDataTable;
        $html = $dataTable->html();

        $this->assertEquals('shops-data-table', $html->getTableAttribute('id'));
        $this->assertCount(7, $dataTable->getColumns());
    }

    public function test_shops_datatable_query_returns_query_builder(): void
    {
        $dataTable = new ShopsDataTable;
        $query = $dataTable->query(new Shop);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_shops_index_page_loads_successfully_for_super_admin(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get(route('shops.index'));

        $response->assertOk();
        $response->assertSee('shops-data-table');
        $response->assertSee('নতুন দোকান তৈরি করুন');
    }

    public function test_shops_datatable_ajax_returns_json(): void
    {
        $user = $this->createSuperAdmin();

        Shop::create([
            'name' => 'Test Shop Express',
            'slug' => 'test-shop-express',
            'phone' => '01711223344',
            'address' => 'Dhaka, Bangladesh',
            'status' => 'active',
            'enabled_features' => ['sales', 'stock'],
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('shops.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
        $this->assertEquals(1, $response->json('recordsTotal'));
    }

    public function test_shops_create_page_loads_successfully(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get(route('shops.create'));

        $response->assertOk();
        $response->assertSee('নতুন দোকান তৈরি করুন');
        $response->assertSee('দোকানের প্রাথমিক বিবরণ');
        $response->assertSee('দোকানের মালিক');
        $response->assertSee('লাইভ দোকান প্রিভিউ');
    }

    public function test_shop_can_be_created_via_post(): void
    {
        $user = $this->createSuperAdmin();
        Role::firstOrCreate(['name' => 'Shop Owner', 'guard_name' => 'web']);

        $response = $this->actingAs($user)->post(route('shops.store'), [
            'name' => 'Grand Market',
            'slug' => 'grand-market',
            'phone' => '01888999000',
            'address' => 'Chittagong, Bangladesh',
            'status' => 'active',
            'features' => ['sales', 'products', 'customers'],
            'admin_name' => 'Grand Manager',
            'admin_email' => 'manager@grandmarket.test',
            'admin_password' => 'Secret12345!',
            'admin_password_confirmation' => 'Secret12345!',
            'admin_role' => 'Shop Owner',
        ]);

        $response->assertRedirect(route('shops.index'));
        $this->assertDatabaseHas('shops', [
            'slug' => 'grand-market',
            'name' => 'Grand Market',
            'phone' => '01888999000',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'manager@grandmarket.test',
            'name' => 'Grand Manager',
        ]);
    }

    public function test_shops_edit_page_loads_successfully(): void
    {
        $user = $this->createSuperAdmin();
        $shop = Shop::create([
            'name' => 'Original Shop',
            'slug' => 'original-shop',
            'phone' => '01700000000',
            'address' => 'Sylhet, Bangladesh',
            'status' => 'active',
            'enabled_features' => ['sales'],
        ]);

        $response = $this->actingAs($user)->get(route('shops.edit', $shop));

        $response->assertOk();
        $response->assertSee('Original Shop');
        $response->assertSee('দোকানের বিবরণ ও সক্রিয় ফিচার');
        $response->assertSee('সাবস্ক্রিপশন প্যাকেজ ও মেয়াদ');
        $response->assertSee('দোকানের এডমিনগণ');
        $response->assertSee('দোকানের সংক্ষিপ্ত বিবরণ');
    }

    public function test_shop_can_be_updated_via_put(): void
    {
        $user = $this->createSuperAdmin();
        $shop = Shop::create([
            'name' => 'Old Shop Name',
            'slug' => 'old-shop-name',
            'phone' => '01700000000',
            'address' => 'Old Address',
            'status' => 'active',
            'enabled_features' => ['sales'],
        ]);

        $response = $this->actingAs($user)->put(route('shops.update', $shop), [
            'name' => 'Updated Shop Name',
            'slug' => 'updated-shop-name',
            'phone' => '01999999999',
            'address' => 'New Address, Dhaka',
            'status' => 'inactive',
            'features' => ['sales', 'stock', 'branches'],
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'name' => 'Updated Shop Name',
            'slug' => 'updated-shop-name',
            'phone' => '01999999999',
            'status' => 'inactive',
        ]);
    }

    public function test_shop_admin_can_be_added_and_deleted(): void
    {
        $user = $this->createSuperAdmin();
        Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);

        $shop = Shop::create([
            'name' => 'Admin Test Shop',
            'slug' => 'admin-test-shop',
            'status' => 'active',
        ]);

        // Add admin
        $addResponse = $this->actingAs($user)->post(route('shops.admins.store', $shop), [
            'name' => 'New Admin User',
            'email' => 'newadmin@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'Manager',
        ]);

        $addResponse->assertRedirect(route('shops.edit', $shop));
        $admin = User::where('email', 'newadmin@test.com')->first();
        $this->assertNotNull($admin);
        $this->assertEquals($shop->id, $admin->shop_id);

        // Delete admin
        $deleteResponse = $this->actingAs($user)->delete(route('shops.admins.destroy', [$shop, $admin]));
        $deleteResponse->assertRedirect(route('shops.edit', $shop));
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_shop_subscription_can_be_updated(): void
    {
        $user = $this->createSuperAdmin();
        $plan = Plan::first() ?? Plan::create([
            'name' => 'Starter Pack',
            'slug' => 'starter-pack',
            'price' => 500,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $shop = Shop::create([
            'name' => 'Subscription Test Shop',
            'slug' => 'subscription-test-shop',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->put(route('shops.subscription.update', $shop), [
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now()->format('Y-m-d'),
            'current_period_end' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $this->assertDatabaseHas('subscriptions', [
            'subscribable_type' => Shop::class,
            'subscribable_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_shop_can_be_created_with_store_code(): void
    {
        $user = $this->createSuperAdmin();
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $response = $this->actingAs($user)->post(route('shops.store'), [
            'name' => 'Bismillah Store',
            'slug' => 'bismillah-store',
            'store_code' => 'BISMILLAH-01',
            'phone' => '01711000111',
            'address' => 'Mirpur, Dhaka',
            'status' => 'active',
            'features' => ['sales', 'stock'],
            'admin_name' => 'Bismillah Admin',
            'admin_email' => 'admin@bismillah.test',
            'admin_password' => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
            'admin_role' => 'Owner',
        ]);

        $response->assertRedirect(route('shops.index'));
        $this->assertDatabaseHas('shops', [
            'name' => 'Bismillah Store',
            'slug' => 'bismillah-store',
            'store_code' => 'BISMILLAH-01',
        ]);
    }

    public function test_check_availability_endpoint_checks_slug_and_store_code(): void
    {
        $user = $this->createSuperAdmin();

        Shop::create([
            'name' => 'Existing Mart',
            'slug' => 'existing-mart',
            'store_code' => 'EXIST-01',
            'status' => 'active',
        ]);

        // When slug and store_code are available
        $availableResponse = $this->actingAs($user)->getJson(route('shops.check-availability', [
            'slug' => 'unique-mart',
            'store_code' => 'UNIQUE-01',
        ]));

        $availableResponse->assertOk();
        $availableResponse->assertJson([
            'slug_available' => true,
            'store_code_available' => true,
        ]);

        // When slug and store_code are taken
        $takenResponse = $this->actingAs($user)->getJson(route('shops.check-availability', [
            'slug' => 'existing-mart',
            'store_code' => 'EXIST-01',
        ]));

        $takenResponse->assertOk();
        $takenResponse->assertJson([
            'slug_available' => false,
            'store_code_available' => false,
        ]);
    }

    public function test_store_code_uniqueness_validation_on_create_and_update(): void
    {
        $user = $this->createSuperAdmin();
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $shop1 = Shop::create([
            'name' => 'Shop One',
            'slug' => 'shop-one',
            'store_code' => 'CODE-100',
            'status' => 'active',
        ]);

        // Trying to create duplicate store_code
        $response = $this->actingAs($user)->post(route('shops.store'), [
            'name' => 'Shop Two',
            'slug' => 'shop-two',
            'store_code' => 'CODE-100',
            'status' => 'active',
            'admin_name' => 'Admin Two',
            'admin_email' => 'two@shop.test',
            'admin_password' => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
            'admin_role' => 'Owner',
        ]);

        $response->assertSessionHasErrors('store_code');

        // Update with same shop's code should succeed
        $updateSelfResponse = $this->actingAs($user)->put(route('shops.update', $shop1), [
            'name' => 'Shop One Updated',
            'slug' => 'shop-one',
            'store_code' => 'CODE-100',
            'status' => 'active',
        ]);

        $updateSelfResponse->assertRedirect(route('shops.edit', $shop1));
    }

    public function test_generate_next_store_code_creates_sequential_code(): void
    {
        $code1 = Shop::generateNextStoreCode();
        $this->assertEquals('shop-001', $code1);

        Shop::create([
            'name' => 'First Shop',
            'slug' => 'first-shop',
            'store_code' => 'shop-001',
            'status' => 'active',
        ]);

        $code2 = Shop::generateNextStoreCode();
        $this->assertEquals('shop-002', $code2);

        Shop::create([
            'name' => 'Second Shop',
            'slug' => 'second-shop',
            'store_code' => 'shop-002',
            'status' => 'active',
        ]);

        $code3 = Shop::generateNextStoreCode();
        $this->assertEquals('shop-003', $code3);
    }

    public function test_shops_datatable_can_search_by_store_code(): void
    {
        $user = $this->createSuperAdmin();

        Shop::create([
            'name' => 'Alpha Grocery',
            'slug' => 'alpha-grocery',
            'store_code' => 'shop-010',
            'status' => 'active',
        ]);

        Shop::create([
            'name' => 'Beta Electronics',
            'slug' => 'beta-electronics',
            'store_code' => 'shop-020',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('shops.index', [
                'columns' => [
                    ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'phone', 'name' => 'phone', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'status', 'name' => 'status', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'search' => ['value' => 'shop-020', 'regex' => 'false'],
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $data = $response->json('data');
        $this->assertStringContainsString('Beta Electronics', $data[0]['name']);
        $this->assertStringContainsString('#shop-020', $data[0]['name']);
    }

    public function test_shops_show_endpoint_returns_json_details_for_modal(): void
    {
        $user = $this->createSuperAdmin();
        $shop = Shop::create([
            'name' => 'Modal Test Shop',
            'slug' => 'modal-test-shop',
            'store_code' => 'shop-999',
            'phone' => '01911999999',
            'address' => 'Banani, Dhaka',
            'status' => 'active',
            'enabled_features' => ['sales', 'purchase', 'stock'],
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('shops.show', $shop));

        $response->assertOk();
        $response->assertJson([
            'id' => $shop->id,
            'name' => 'Modal Test Shop',
            'slug' => 'modal-test-shop',
            'store_code' => 'shop-999',
            'phone' => '01911999999',
            'address' => 'Banani, Dhaka',
            'status' => 'active',
            'enabled_features' => ['sales', 'purchase', 'stock'],
        ]);
    }
}
