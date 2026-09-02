<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Modules\Supplier\DataTables\SuppliersDataTable;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierDataTableTest extends TestCase
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
            'name' => 'Supplier Test Shop',
            'slug' => 'supplier-test-shop',
            'status' => 'active',
            'enabled_features' => ['suppliers'],
        ]);

        Permission::firstOrCreate(['name' => 'suppliers.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'suppliers.write', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'suppliers.delete', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'Shop Admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['suppliers.view', 'suppliers.write', 'suppliers.delete']);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);
        $user->assignRole('Shop Admin');

        return [$user, $shop];
    }

    public function test_suppliers_datatable_generates_html_builder(): void
    {
        $dataTable = new SuppliersDataTable;
        $html = $dataTable->html();

        $this->assertEquals('suppliers-data-table', $html->getTableAttribute('id'));
        $this->assertCount(6, $dataTable->getColumns());
    }

    public function test_suppliers_datatable_query_returns_query_builder(): void
    {
        $dataTable = new SuppliersDataTable;
        $query = $dataTable->query(new Supplier);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_suppliers_index_page_loads_successfully(): void
    {
        [$user, $shop] = $this->createShopUser();

        $response = $this->actingAs($user)->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertSee('suppliers-data-table');
        $response->assertSee('createSupplierModal');
        $response->assertSee('editSupplierModal');
        $response->assertSee('নতুন সরবরাহকারী');
    }

    public function test_suppliers_datatable_ajax_returns_json(): void
    {
        [$user, $shop] = $this->createShopUser();

        Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'Abul Kashem Traders',
            'phone' => '01711889900',
            'email' => 'kashem@example.com',
            'address' => 'Khatungonj, Chittagong',
            'opening_due' => 1200,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('suppliers.index'), [
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
        $this->assertStringContainsString('Abul Kashem Traders', $response->json('data.0.name'));
        $this->assertStringContainsString('1,200.00', $response->json('data.0.total_due'));
    }

    public function test_supplier_can_be_created_via_post_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        // Standard POST
        $response = $this->actingAs($user)->post(route('suppliers.store'), [
            'name' => 'Bhuiyan Enterprise',
            'phone' => '01811556677',
            'email' => 'bhuiyan@example.com',
            'address' => 'Agrabad, Chittagong',
            'opening_due' => 450,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'shop_id' => $shop->id,
            'name' => 'Bhuiyan Enterprise',
        ]);

        // AJAX POST
        $ajaxResponse = $this->actingAs($user)->postJson(route('suppliers.store'), [
            'name' => 'Mollah & Co',
            'phone' => '01911998877',
            'email' => 'mollah@example.com',
            'address' => 'Chawkbazar, Dhaka',
            'opening_due' => 0,
            'status' => 'active',
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('suppliers', [
            'shop_id' => $shop->id,
            'name' => 'Mollah & Co',
        ]);
    }

    public function test_supplier_edit_endpoint_returns_json_for_modal(): void
    {
        [$user, $shop] = $this->createShopUser();

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'Apex Suppliers',
            'phone' => '01799112233',
            'email' => 'apex@example.com',
            'address' => 'Motijheel, Dhaka',
            'opening_due' => 750.25,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('suppliers.edit', $supplier));

        $response->assertOk();
        $response->assertJson([
            'id' => $supplier->id,
            'name' => 'Apex Suppliers',
            'phone' => '01799112233',
            'email' => 'apex@example.com',
            'address' => 'Motijheel, Dhaka',
            'opening_due' => 750.25,
            'status' => 'active',
            'update_url' => route('suppliers.update', $supplier),
        ]);
    }

    public function test_supplier_can_be_updated_via_put_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'Old Supplier Name',
            'phone' => '01700000000',
            'status' => 'active',
        ]);

        // AJAX PUT
        $response = $this->actingAs($user)->putJson(route('suppliers.update', $supplier), [
            'name' => 'Updated Supplier Name',
            'phone' => '01711111111',
            'email' => 'updated_sup@example.com',
            'address' => 'New Supplier Address',
            'opening_due' => 600,
            'status' => 'inactive',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier Name',
            'status' => 'inactive',
        ]);
    }

    public function test_supplier_can_be_deleted(): void
    {
        [$user, $shop] = $this->createShopUser();

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'Supplier To Delete',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete(route('suppliers.destroy', $supplier));
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
