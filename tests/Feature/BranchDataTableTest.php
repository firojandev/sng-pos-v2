<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\DataTables\BranchesDataTable;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchDataTableTest extends TestCase
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
            'name' => 'Branch Test Shop',
            'slug' => 'branch-test-shop',
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

    public function test_branches_datatable_generates_html_builder(): void
    {
        $dataTable = new BranchesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('branches-data-table', $html->getTableAttribute('id'));
        $this->assertCount(6, $dataTable->getColumns());
    }

    public function test_branches_datatable_query_returns_query_builder(): void
    {
        $dataTable = new BranchesDataTable;
        $query = $dataTable->query(new Branch);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_branches_index_page_loads_successfully(): void
    {
        [$user, $shop] = $this->createShopUser();

        $response = $this->actingAs($user)->get(route('branches.index'));

        $response->assertOk();
        $response->assertSee('branches-data-table');
        $response->assertSee('createBranchModal');
        $response->assertSee('editBranchModal');
        $response->assertSee('নতুন শাখা');
    }

    public function test_branches_datatable_ajax_returns_json(): void
    {
        [$user, $shop] = $this->createShopUser();

        Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Dhanmondi Branch',
            'phone' => '01700112233',
            'address' => 'Road 27, Dhanmondi',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('branches.index'), [
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
        $this->assertStringContainsString('Dhanmondi Branch', $response->json('data.0.name'));
    }

    public function test_branch_can_be_created_via_post_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        // Standard POST
        $response = $this->actingAs($user)->post(route('branches.store'), [
            'name' => 'Uttara Branch',
            'phone' => '01811223344',
            'address' => 'Sector 3, Uttara',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'shop_id' => $shop->id,
            'name' => 'Uttara Branch',
        ]);

        // AJAX POST
        $ajaxResponse = $this->actingAs($user)->postJson(route('branches.store'), [
            'name' => 'Mirpur Branch',
            'phone' => '01911223344',
            'address' => 'Mirpur 10',
            'status' => 'active',
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('branches', [
            'shop_id' => $shop->id,
            'name' => 'Mirpur Branch',
        ]);
    }

    public function test_branch_edit_endpoint_returns_json_for_modal(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Gulshan Branch',
            'phone' => '01799887766',
            'address' => 'Gulshan 2',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('branches.edit', $branch));

        $response->assertOk();
        $response->assertJson([
            'id' => $branch->id,
            'name' => 'Gulshan Branch',
            'phone' => '01799887766',
            'address' => 'Gulshan 2',
            'status' => 'active',
            'update_url' => route('branches.update', $branch),
        ]);
    }

    public function test_branch_can_be_updated_via_put_and_ajax(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Old Branch Name',
            'phone' => '01700000000',
            'address' => 'Old Address',
            'status' => 'active',
        ]);

        // AJAX PUT
        $response = $this->actingAs($user)->putJson(route('branches.update', $branch), [
            'name' => 'Updated Branch Name',
            'phone' => '01711111111',
            'address' => 'New Address',
            'status' => 'inactive',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Updated Branch Name',
            'status' => 'inactive',
        ]);
    }

    public function test_branch_cannot_be_deleted_when_it_has_warehouses(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Branch With Warehouse',
            'status' => 'active',
        ]);

        Warehouse::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'name' => 'Attached Warehouse',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete(route('branches.destroy', $branch));
        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', ['id' => $branch->id]);

        $ajaxResponse = $this->actingAs($user)->deleteJson(route('branches.destroy', $branch));
        $ajaxResponse->assertStatus(422);
        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    public function test_branch_can_be_deleted_when_no_warehouses(): void
    {
        [$user, $shop] = $this->createShopUser();

        $branch = Branch::create([
            'shop_id' => $shop->id,
            'name' => 'Empty Branch',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete(route('branches.destroy', $branch));
        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }
}
