<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\DataTables\CustomersDataTable;
use Modules\Customer\Models\Customer;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    private function createShopUser(): array
    {
        $shop = Shop::create([
            'name' => 'Customer Test Shop',
            'slug' => 'customer-test-shop',
            'status' => 'active',
            'enabled_features' => ['customers'],
        ]);

        Permission::firstOrCreate(['name' => 'customers.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'customers.write', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'customers.delete', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'Shop Admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['customers.view', 'customers.write', 'customers.delete']);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);
        $user->assignRole('Shop Admin');

        return [$user, $shop];
    }

    public function test_customers_datatable_generates_html_builder(): void
    {
        $dataTable = new CustomersDataTable;
        $html = $dataTable->html();

        $this->assertEquals('customers-data-table', $html->getTableAttribute('id'));
        $this->assertCount(6, $dataTable->getColumns());
    }

    public function test_customers_datatable_query_returns_query_builder(): void
    {
        $dataTable = new CustomersDataTable;
        $query = $dataTable->query(new Customer);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_customers_index_page_loads_successfully(): void
    {
        [$user, $shop] = $this->createShopUser();

        $response = $this->actingAs($user)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('customers-data-table');
        $response->assertSee('createCustomerModal');
        $response->assertSee('editCustomerModal');
        $response->assertSee('নতুন গ্রাহক');
    }

    public function test_customers_datatable_ajax_returns_json(): void
    {
        [$user, $shop] = $this->createShopUser();

        Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Karim Rahman',
            'phone' => '01711223344',
            'email' => 'karim@example.com',
            'address' => 'Mirpur, Dhaka',
            'opening_due' => 500,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('customers.index'), [
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
        $this->assertStringContainsString('Karim Rahman', $response->json('data.0.name'));
        $this->assertStringContainsString('500.00', $response->json('data.0.total_due'));
    }

    public function test_customer_can_be_created_via_post_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        // Standard POST
        $response = $this->actingAs($user)->post(route('customers.store'), [
            'name' => 'Rahim Ahmed',
            'phone' => '01811223344',
            'email' => 'rahim@example.com',
            'address' => 'Gulshan, Dhaka',
            'opening_due' => 200,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'shop_id' => $shop->id,
            'name' => 'Rahim Ahmed',
        ]);

        // AJAX POST
        $ajaxResponse = $this->actingAs($user)->postJson(route('customers.store'), [
            'name' => 'Salim Hossain',
            'phone' => '01911223344',
            'email' => 'salim@example.com',
            'address' => 'Banani, Dhaka',
            'opening_due' => 0,
            'status' => 'active',
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('customers', [
            'shop_id' => $shop->id,
            'name' => 'Salim Hossain',
        ]);
    }

    public function test_customer_edit_endpoint_returns_json_for_modal(): void
    {
        [$user, $shop] = $this->createShopUser();

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Jamal Uddin',
            'phone' => '01799887766',
            'email' => 'jamal@example.com',
            'address' => 'Uttara, Dhaka',
            'opening_due' => 150.50,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('customers.edit', $customer));

        $response->assertOk();
        $response->assertJson([
            'id' => $customer->id,
            'name' => 'Jamal Uddin',
            'phone' => '01799887766',
            'email' => 'jamal@example.com',
            'address' => 'Uttara, Dhaka',
            'opening_due' => 150.5,
            'status' => 'active',
            'update_url' => route('customers.update', $customer),
        ]);
    }

    public function test_customer_can_be_updated_via_put_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Old Customer Name',
            'phone' => '01700000000',
            'status' => 'active',
        ]);

        // AJAX PUT
        $response = $this->actingAs($user)->putJson(route('customers.update', $customer), [
            'name' => 'Updated Customer Name',
            'phone' => '01711111111',
            'email' => 'updated@example.com',
            'address' => 'New Address',
            'opening_due' => 300,
            'status' => 'inactive',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer Name',
            'status' => 'inactive',
        ]);
    }

    public function test_customer_can_be_deleted(): void
    {
        [$user, $shop] = $this->createShopUser();

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Customer To Delete',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete(route('customers.destroy', $customer));
        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
