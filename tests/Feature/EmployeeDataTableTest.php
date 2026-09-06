<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\DataTables\EmployeesDataTable;
use Modules\Employee\Models\Employee;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'Employee Test Shop',
            'slug' => 'employee-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Employee Manager',
            'email' => 'manager@employeeshop.test',
            'password' => bcrypt('password123'),
            'shop_id' => $this->shop->id,
            'email_verified_at' => now(),
        ]);
        $this->user->syncRoles([$this->adminRole]);
    }

    public function test_employees_datatable_generates_html_builder(): void
    {
        $dataTable = new EmployeesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('employees-data-table', $html->getTableAttribute('id'));
        $this->assertCount(7, $dataTable->getColumns());
    }

    public function test_employees_datatable_query_returns_query_builder(): void
    {
        $this->actingAs($this->user);

        $dataTable = new EmployeesDataTable;
        $query = $dataTable->query(new Employee);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_employees_index_page_loads_with_modals_and_datatable(): void
    {
        $response = $this->actingAs($this->user)->get(route('employees.index'));

        $response->assertOk();
        $response->assertSee('employees-data-table');
        $response->assertSee('createEmployeeModal');
        $response->assertSee('editEmployeeModal');
        $response->assertSee('নতুন কর্মচারী');
        $response->assertSee('সর্বমোট কর্মচারী');
    }

    public function test_employees_datatable_ajax_returns_json(): void
    {
        Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'Rahim Uddin',
            'phone' => '01711000001',
            'email' => 'rahim@test.com',
            'designation' => 'Senior Sales Executive',
            'department' => 'Sales',
            'salary' => 25000.00,
            'joining_date' => now()->subMonths(6)->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('employees.index'), [
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

    public function test_employees_datatable_filters_by_status(): void
    {
        Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'Active Staff',
            'phone' => '01711000002',
            'designation' => 'Cashier',
            'salary' => 18000.00,
            'status' => 'active',
        ]);

        Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'Inactive Staff',
            'phone' => '01711000003',
            'designation' => 'Security',
            'salary' => 12000.00,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('employees.index', ['status' => 'inactive']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Inactive Staff', json_encode($response->json('data')));
    }

    public function test_employees_datatable_filters_by_department(): void
    {
        Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'Sales Rep',
            'phone' => '01711000004',
            'designation' => 'Representative',
            'department' => 'Sales',
            'salary' => 20000.00,
            'status' => 'active',
        ]);

        Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'Warehouse Guard',
            'phone' => '01711000005',
            'designation' => 'Keeper',
            'department' => 'Warehouse',
            'salary' => 15000.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('employees.index', ['department' => 'Sales']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Sales Rep', json_encode($response->json('data')));
    }

    public function test_employees_datatable_filters_by_designation(): void
    {
        Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'Store Manager',
            'phone' => '01711000006',
            'designation' => 'Manager',
            'salary' => 35000.00,
            'status' => 'active',
        ]);

        Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'General Staff',
            'phone' => '01711000007',
            'designation' => 'Assistant',
            'salary' => 16000.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('employees.index', ['designation' => 'Manager']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Store Manager', json_encode($response->json('data')));
    }

    public function test_employee_store_via_ajax(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('employees.store'), [
                'name' => 'Karim Mia',
                'phone' => '01722334455',
                'email' => 'karim@test.com',
                'designation' => 'Accountant',
                'department' => 'Finance',
                'salary' => 30000.00,
                'joining_date' => '2026-01-15',
                'status' => 'active',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('employees', [
            'shop_id' => $this->shop->id,
            'phone' => '01722334455',
            'designation' => 'Accountant',
        ]);
    }

    public function test_employee_update_via_ajax(): void
    {
        $employee = Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'Initial Employee',
            'phone' => '01733445566',
            'designation' => 'Trainee',
            'salary' => 12000.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson(route('employees.update', $employee), [
                'name' => 'Promoted Employee',
                'phone' => '01733445566',
                'designation' => 'Officer',
                'salary' => 18000.00,
                'status' => 'active',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Promoted Employee',
            'designation' => 'Officer',
            'salary' => 18000.00,
        ]);
    }

    public function test_employee_delete_via_ajax(): void
    {
        $employee = Employee::create([
            'shop_id' => $this->shop->id,
            'name' => 'To Delete Employee',
            'phone' => '01799887766',
            'designation' => 'Contractor',
            'salary' => 10000.00,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('employees.destroy', $employee));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
    }
}
