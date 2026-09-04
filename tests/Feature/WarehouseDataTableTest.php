<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\Unit;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\DataTables\WarehousesDataTable;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WarehouseDataTableTest extends TestCase
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
            'name' => 'Warehouse Test Shop',
            'slug' => 'warehouse-test-shop',
            'status' => 'active',
            'enabled_features' => ['branches'],
        ]);

        Permission::firstOrCreate(['name' => 'branches.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'branches.write', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'branches.delete', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'Shop Admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['branches.view', 'branches.write', 'branches.delete']);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);
        $user->assignRole('Shop Admin');

        return [$user, $shop];
    }

    public function test_warehouses_datatable_generates_html_builder(): void
    {
        $dataTable = new WarehousesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('warehouses-data-table', $html->getTableAttribute('id'));
        $this->assertCount(7, $dataTable->getColumns());
    }

    public function test_warehouses_datatable_query_returns_query_builder(): void
    {
        $dataTable = new WarehousesDataTable;
        $query = $dataTable->query(new Warehouse);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_warehouses_index_page_loads_successfully(): void
    {
        [$user, $shop] = $this->createShopUser();

        $response = $this->actingAs($user)->get(route('warehouses.index'));

        $response->assertOk();
        $response->assertSee('warehouses-data-table');
        $response->assertSee('createWarehouseModal');
        $response->assertSee('editWarehouseModal');
        $response->assertSee('নতুন গুদাম');
    }

    public function test_warehouses_datatable_ajax_returns_json(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Central Storage',
            'address' => 'Plot 4, Tejgaon',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('warehouses.index'), [
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
        $this->assertStringContainsString('Central Storage', $response->json('data.0.name'));
        $this->assertStringContainsString('Main Branch', $response->json('data.0.branch'));
    }

    public function test_warehouse_can_be_created_via_post_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Savar Branch',
            'status' => 'active',
        ]);

        // Standard POST
        $response = $this->actingAs($user)->post(route('warehouses.store'), [
            'branch_id' => $branch->id,
            'name' => 'Savar Depot',
            'address' => 'Savar Bus Stand',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('warehouses.index'));
        $this->assertDatabaseHas('warehouses', [
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Savar Depot',
        ]);

        // AJAX POST
        $ajaxResponse = $this->actingAs($user)->postJson(route('warehouses.store'), [
            'branch_id' => $branch->id,
            'name' => 'Savar Cold Storage',
            'address' => 'EPZ Road',
            'status' => 'active',
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('warehouses', [
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Savar Cold Storage',
        ]);
    }

    public function test_warehouse_edit_endpoint_returns_json_for_modal(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Gazipur Branch',
            'status' => 'active',
        ]);

        $warehouse = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Gazipur Hub',
            'address' => 'Chowrasta',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('warehouses.edit', $warehouse));

        $response->assertOk();
        $response->assertJson([
            'id' => $warehouse->id,
            'branch_id' => $branch->id,
            'name' => 'Gazipur Hub',
            'address' => 'Chowrasta',
            'status' => 'active',
            'update_url' => route('warehouses.update', $warehouse),
        ]);
    }

    public function test_warehouse_can_be_updated_via_put_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Narayanganj Branch',
            'status' => 'active',
        ]);

        $warehouse = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Old Hub Name',
            'address' => 'Old Address',
            'status' => 'active',
        ]);

        // AJAX PUT
        $response = $this->actingAs($user)->putJson(route('warehouses.update', $warehouse), [
            'branch_id' => $branch->id,
            'name' => 'Updated Hub Name',
            'address' => 'New Riverview Road',
            'status' => 'inactive',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Updated Hub Name',
            'status' => 'inactive',
        ]);
    }

    public function test_warehouse_cannot_be_deleted_when_it_has_batches(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Khulna Branch',
            'status' => 'active',
        ]);

        $warehouse = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Khulna Warehouse',
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $shop->id,
            'name' => 'General',
            'slug' => 'general-khulna',
        ]);

        $unit = Unit::create([
            'shop_id' => $shop->id,
            'name' => 'Pcs',
            'short_code' => 'pcs-khulna',
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Sample Item',
            'sku' => 'SKU-SAMPLE-01',
            'purchase_price' => 100,
            'sale_price' => 150,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'batch_no' => 'BATCH-001',
            'quantity' => 50,
        ]);

        $response = $this->actingAs($user)->delete(route('warehouses.destroy', $warehouse));
        $response->assertRedirect(route('warehouses.index'));
        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id]);

        $ajaxResponse = $this->actingAs($user)->deleteJson(route('warehouses.destroy', $warehouse));
        $ajaxResponse->assertStatus(422);
        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id]);
    }

    public function test_warehouse_can_be_deleted_when_no_batches(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Barisal Branch',
            'status' => 'active',
        ]);

        $warehouse = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Empty Warehouse',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete(route('warehouses.destroy', $warehouse));
        $response->assertRedirect(route('warehouses.index'));
        $this->assertDatabaseMissing('warehouses', ['id' => $warehouse->id]);
    }

    public function test_warehouses_datatable_filters_by_branch_id(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branchA = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Branch A',
            'status' => 'active',
        ]);

        $branchB = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Branch B',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branchA->id,
            'name' => 'Warehouse Alpha',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branchB->id,
            'name' => 'Warehouse Beta',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('warehouses.index', ['branch_id' => $branchA->id]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Warehouse Alpha', $response->json('data.0.name'));
    }

    public function test_warehouses_datatable_filters_by_status(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Filter Branch',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Active Storage',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Inactive Storage',
            'status' => 'inactive',
        ]);

        // Filter active
        $responseActive = $this->actingAs($user)
            ->getJson(route('warehouses.index', ['status' => 'active']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $responseActive->assertOk();
        $this->assertEquals(1, $responseActive->json('recordsFiltered'));
        $this->assertStringContainsString('Active Storage', $responseActive->json('data.0.name'));

        // Filter inactive
        $responseInactive = $this->actingAs($user)
            ->getJson(route('warehouses.index', ['status' => 'inactive']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $responseInactive->assertOk();
        $this->assertEquals(1, $responseInactive->json('recordsFiltered'));
        $this->assertStringContainsString('Inactive Storage', $responseInactive->json('data.0.name'));
    }

    public function test_warehouses_datatable_filters_by_branch_id_and_status_combined(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branchA = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Branch A',
            'status' => 'active',
        ]);

        $branchB = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Branch B',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branchA->id,
            'name' => 'Alpha Active',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branchA->id,
            'name' => 'Alpha Inactive',
            'status' => 'inactive',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branchB->id,
            'name' => 'Beta Active',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('warehouses.index', [
                'branch_id' => $branchA->id,
                'status' => 'active',
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Alpha Active', $response->json('data.0.name'));
    }

    public function test_first_created_warehouse_is_automatically_marked_as_default(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Initial Branch',
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('warehouses.store'), [
            'branch_id' => $branch->id,
            'name' => 'First Warehouse',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);

        $warehouse = Warehouse::where('name', 'First Warehouse')->first();
        $this->assertNotNull($warehouse);
        $this->assertTrue($warehouse->is_default);
    }

    public function test_setting_default_warehouse_unsets_previous_default(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        $wh1 = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Warehouse One',
            'status' => 'active',
            'is_default' => true,
        ]);

        $wh2 = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Warehouse Two',
            'status' => 'active',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->post(route('warehouses.set-default', $wh2));

        $response->assertRedirect(route('warehouses.index'));
        $this->assertFalse($wh1->fresh()->is_default);
        $this->assertTrue($wh2->fresh()->is_default);

        // Also test AJAX setDefault
        $ajaxResponse = $this->actingAs($user)->postJson(route('warehouses.set-default', $wh1));
        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertTrue($wh1->fresh()->is_default);
        $this->assertFalse($wh2->fresh()->is_default);
    }

    public function test_inactive_warehouse_cannot_be_set_as_default(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        $activeWh = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Active WH',
            'status' => 'active',
            'is_default' => true,
        ]);

        $inactiveWh = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Inactive WH',
            'status' => 'inactive',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->post(route('warehouses.set-default', $inactiveWh));
        $response->assertSessionHasErrors('error');
        $this->assertFalse($inactiveWh->fresh()->is_default);
        $this->assertTrue($activeWh->fresh()->is_default);

        // Cannot create inactive warehouse as default
        $createResponse = $this->actingAs($user)->post(route('warehouses.store'), [
            'branch_id' => $branch->id,
            'name' => 'Inactive Default Attempt',
            'status' => 'inactive',
            'is_default' => 1,
        ]);
        $createResponse->assertSessionHasErrors('status');
    }

    public function test_default_warehouse_cannot_be_deactivated(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        $defaultWh = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Default WH',
            'status' => 'active',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->put(route('warehouses.update', $defaultWh), [
            'branch_id' => $branch->id,
            'name' => 'Default WH',
            'status' => 'inactive',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertEquals('active', $defaultWh->fresh()->status);
    }

    public function test_default_warehouse_cannot_be_deleted_when_other_warehouses_exist(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        $defaultWh = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Default WH',
            'status' => 'active',
            'is_default' => true,
        ]);

        $otherWh = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Other WH',
            'status' => 'active',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('warehouses.destroy', $defaultWh));
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('warehouses', ['id' => $defaultWh->id]);

        $ajaxResponse = $this->actingAs($user)->deleteJson(route('warehouses.destroy', $defaultWh));
        $ajaxResponse->assertStatus(422);
        $this->assertDatabaseHas('warehouses', ['id' => $defaultWh->id]);
    }

    public function test_default_warehouse_is_auto_selected_in_create_purchase_and_sale(): void
    {
        [$user, $shop] = $this->createShopUser();
        $shop->update(['enabled_features' => ['branches', 'purchase', 'sales']]);

        Permission::firstOrCreate(['name' => 'purchase.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'purchase.write', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'sales.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'sales.write', 'guard_name' => 'web']);

        $role = Role::findByName('Shop Admin', 'web');
        $role->givePermissionTo(['purchase.view', 'purchase.write', 'sales.view', 'sales.write']);

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        // Create warehouse A (first alphabetically)
        $whA = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'A Central Warehouse',
            'status' => 'active',
            'is_default' => false,
        ]);

        // Create warehouse Z (default warehouse, comes last alphabetically)
        $whZ = Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Z Retail Warehouse',
            'status' => 'active',
            'is_default' => true,
        ]);

        // Test Purchase Create: Z Retail Warehouse should be selected
        $purchaseResponse = $this->actingAs($user)->get(route('purchase.create'));
        $purchaseResponse->assertOk();
        $purchaseResponse->assertSee('value="'.$whZ->id.'" selected', false);

        // Test Sale Create: Z Retail Warehouse should be selected
        $saleResponse = $this->actingAs($user)->get(route('sales.create'));
        $saleResponse->assertOk();
        $saleResponse->assertSee('value="'.$whZ->id.'" selected', false);
        $this->assertEquals($whZ->id, $saleResponse->viewData('warehouseId'));
    }
}
