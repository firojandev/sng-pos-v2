<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Modules\Shop\Services\ShopProvisioner;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CompanyWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Shop $dhaka;

    private Shop $ctg;

    private User $owner;

    private User $employee;

    private User $shopAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->company = Company::create(['name' => 'Karim Group', 'slug' => 'karim-group', 'type' => Company::TYPE_COMPANY, 'status' => 'active']);
        $this->dhaka = Shop::create(['company_id' => $this->company->id, 'name' => 'Karim Dhaka', 'slug' => 'karim-dhaka', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $this->company->id, 'name' => 'Karim Ctg', 'slug' => 'karim-ctg', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->dhaka, Features::keys());

        $this->owner = User::factory()->create();
        $this->company->users()->attach($this->owner->id, ['role' => Company::ROLE_OWNER, 'is_owner' => true]);
        $this->employee = User::factory()->create();
        $this->company->users()->attach($this->employee->id, ['role' => Company::ROLE_EMPLOYEE, 'is_owner' => false]);

        $this->shopAdmin = User::factory()->create(['shop_id' => $this->dhaka->id]);
        $provisioner = app(ShopProvisioner::class);
        $provisioner->provision($this->dhaka);
        $provisioner->assignAdmin($this->dhaka, $this->shopAdmin, isOwner: true);
        setPermissionsTeamId($this->dhaka->id);
        $provisioner->adminRole($this->dhaka)->syncPermissions(Permission::all());
        setPermissionsTeamId(null);

        Employee::withoutGlobalScopes()->create(['company_id' => $this->company->id, 'shop_id' => $this->dhaka->id, 'name' => 'Dhaka Cashier', 'phone' => '01711000001', 'designation' => 'Cashier', 'salary' => 15000, 'status' => 'active']);
        Employee::withoutGlobalScopes()->create(['company_id' => $this->company->id, 'shop_id' => $this->ctg->id, 'name' => 'Ctg Cashier', 'phone' => '01711000002', 'designation' => 'Cashier', 'salary' => 15000, 'status' => 'active']);
        $other = Shop::create(['name' => 'Other Store', 'slug' => 'other-store', 'status' => 'active']);
        Employee::withoutGlobalScopes()->create(['company_id' => $other->company_id, 'shop_id' => $other->id, 'name' => 'Stranger', 'phone' => '01711000003', 'designation' => 'Cashier', 'salary' => 15000, 'status' => 'active']);
    }

    public function test_the_company_owner_works_in_the_company_workspace_without_pos(): void
    {
        $this->post(route('login.store'), ['login' => $this->owner->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertNull($this->owner->fresh()->shop_id);

        $dashboard = $this->actingAs($this->owner->fresh())->get(route('dashboard'))->assertOk();
        foreach (['employees.index', 'attendance.sheet', 'payroll.runs.index', 'ledger-accounts.index', 'tasks.index', 'company-settings.edit'] as $route) {
            $dashboard->assertSee(route($route), false);
        }
        foreach (['sales.index', 'quick-sale.create', 'purchase.index', 'stock.index', 'products.index', 'accounts.index', 'income.index', 'assets.index', 'debts.index', 'default-company.index', 'reports.sales', 'reports.stock', 'reports.profit-loss'] as $route) {
            $dashboard->assertDontSee('href="'.route($route).'"', false);
        }

        $this->get(route('employees.index'))->assertOk();
        $this->get(route('attendance.sheet'))->assertOk()->assertSee('Dhaka Cashier')->assertSee('Ctg Cashier')->assertDontSee('Stranger');
        $this->get(route('payroll.runs.index'))->assertOk();
        $this->get(route('ledger-accounts.index'))->assertOk();
        $this->get(route('reports.sales'))->assertForbidden();
        $this->get(route('sales.index'))->assertForbidden();

        // Company-level forms save from the workspace too.
        $cashier = Employee::withoutGlobalScopes()->where('name', 'Ctg Cashier')->firstOrFail();
        $sick = LeaveType::withoutGlobalScopes()->where('company_id', $this->company->id)->where('code', 'SL')->firstOrFail();
        $this->post(route('leave-requests.store'), ['employee_id' => $cashier->id, 'leave_type_id' => $sick->id, 'from_date' => '2026-10-05', 'to_date' => '2026-10-05'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('shops.switch', $this->dhaka))->assertForbidden();
    }

    public function test_a_company_employee_gets_the_workspace_without_admin_sections(): void
    {
        $this->post(route('login.store'), ['login' => $this->employee->email, 'password' => 'password'])->assertRedirect(route('dashboard'));

        $dashboard = $this->actingAs($this->employee->fresh())->get(route('dashboard'))->assertOk()->assertSee(route('tasks.index'), false);
        $dashboard->assertDontSee('href="'.route('company-settings.edit').'"', false)->assertDontSee('href="'.route('sales.index').'"', false);

        $this->get(route('company-settings.edit'))->assertForbidden();
        $this->get(route('payroll.runs.index'))->assertForbidden();
    }

    public function test_a_shop_admin_works_the_pos_of_their_shop(): void
    {
        $dashboard = $this->actingAs($this->shopAdmin)->get(route('dashboard'))->assertOk();
        $dashboard->assertSee(route('sales.index'), false)->assertSee(route('reports.sales'), false)
            ->assertDontSee('href="'.route('company-settings.edit').'"', false)
            ->assertDontSee('href="'.route('ledger-accounts.index').'"', false);

        $this->get(route('sales.index'))->assertOk();
        $this->get(route('reports.sales'))->assertOk();
        $this->get(route('company-settings.edit'))->assertForbidden();
        $this->get(route('ledger-accounts.index'))->assertForbidden();

        // A company employee doesn't get accounting either; the owner does.
        $this->actingAs($this->employee)->get(route('ledger-accounts.index'))->assertForbidden();
        $this->actingAs($this->owner)->get(route('ledger-accounts.index'))->assertOk();
    }

    public function test_the_company_owner_adds_company_users_and_shop_admins(): void
    {
        $this->actingAs($this->owner)->get(route('company-settings.edit'))->assertOk()->assertSee('Company Users');

        $this->post(route('company.users.store'), [
            'role' => 'Employee', 'name' => 'HR Officer', 'phone' => '01799000001', 'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $officer = User::where('phone', '01799000001')->firstOrFail();
        $this->assertTrue($officer->isCompanyLevelUser());
        $this->assertNull($officer->shop_id);

        $this->post(route('company.shop-admins.store'), [
            'admin_type' => 'new', 'shop_ids' => [$this->ctg->id], 'name' => 'Ctg Manager', 'phone' => '01799000002',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertSessionHasNoErrors();
        $manager = User::where('phone', '01799000002')->firstOrFail();
        $this->assertTrue($manager->isShopAdmin($this->ctg));
        $this->assertFalse($manager->isCompanyLevelUser(), 'A shop admin is a shop-level (POS) login.');

        $this->delete(route('company.users.destroy', $officer))->assertRedirect();
        $this->assertFalse($officer->fresh()->isCompanyLevelUser());
    }

    public function test_a_company_user_applies_for_leave_and_sees_the_status_and_balance(): void
    {
        Storage::fake('local');

        $dashboard = $this->actingAs($this->employee)->get(route('dashboard'))->assertOk();
        $dashboard->assertSee('My Workspace')->assertSee('Leave Balance')->assertSee('href="'.route('my.leave.index').'"', false);

        $record = Employee::withoutGlobalScopes()->where('user_id', $this->employee->id)->firstOrFail();
        $this->assertSame($this->company->id, (int) $record->company_id, 'A company user gets an employee record in their company.');
        $this->assertNull($record->shop_id);

        $casual = LeaveType::withoutGlobalScopes()->where('company_id', $this->company->id)->where('code', 'CL')->firstOrFail();
        $this->get(route('my.leave.index'))->assertOk()->assertSee($casual->name)->assertSee('No leave applications');

        $this->post(route('my.leave.store'), [
            'leave_type_id' => $casual->id, 'from_date' => '2026-10-04', 'to_date' => '2026-10-05', 'reason' => 'Family wedding',
            'attachment' => UploadedFile::fake()->create('card.pdf', 50, 'application/pdf'),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->get(route('my.leave.index'))->assertOk()->assertSee('Family wedding')->assertSee('Pending')->assertSee('card.pdf');

        $leave = LeaveRequest::withoutGlobalScopes()->firstOrFail();
        $this->actingAs($this->owner)->get(route('leave-requests.index'))->assertOk()->assertSee('Family wedding');
        $this->post(route('leave-requests.approve', $leave))->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($this->employee->fresh())->get(route('my.leave.index'))->assertOk()->assertSee('Approved');
    }

    public function test_the_default_company_admins_dashboard_is_the_standalone_shop_list(): void
    {
        $admin = User::factory()->create();
        Company::defaultCompany()->users()->attach($admin->id, ['role' => Company::ROLE_ADMIN, 'is_owner' => false]);
        $standalone = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $this->subscribeShopToFeatures($standalone, Features::keys());

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('default-company.index'));
        $list = $this->get(route('default-company.index'))->assertOk()->assertSee('Rahim Store');
        $list->assertDontSee('href="'.route('default-company.index').'"', false)->assertDontSee('href="'.route('sales.index').'"', false);

        $this->post(route('default-company.shops.open', $standalone))->assertRedirect(route('dashboard'));
        $this->actingAs($admin->fresh())->get(route('dashboard'))->assertOk()->assertSee('href="'.route('default-company.index').'"', false);

        $this->get(route('default-company.index'))->assertOk();
        $this->assertNull($admin->fresh()->shop_id, 'Back at the company level.');
    }
}
