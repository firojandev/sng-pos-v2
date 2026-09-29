<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Models\Company;
use Modules\Company\Models\CompanyRole;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Services\HrSetup;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CompanyRolesTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->company = Company::create(['name' => 'Karim Group', 'slug' => 'karim-group', 'type' => Company::TYPE_COMPANY, 'status' => 'active']);
        $shop = Shop::create(['company_id' => $this->company->id, 'name' => 'Karim Dhaka', 'slug' => 'karim-dhaka', 'status' => 'active']);
        $this->subscribeShopToFeatures($shop, Features::keys());

        $this->owner = User::factory()->create();
        $this->company->users()->attach($this->owner->id, ['role' => Company::ROLE_OWNER, 'is_owner' => true]);
    }

    public function test_the_company_admin_manages_roles_and_users_from_the_menu(): void
    {
        $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()
            ->assertSee('href="'.route('company.users.index').'"', false)
            ->assertSee('href="'.route('company.roles.index').'"', false);
        $this->get(route('company.roles.create'))->assertOk()->assertSee('Payroll')->assertSee('Accounting');

        $this->post(route('company.roles.store'), [
            'name' => 'HR Officer',
            'permissions' => ['employees.view', 'attendance.view', 'attendance.create', 'leave.view', 'leave.approve'],
        ])->assertRedirect(route('company.roles.index'));
        $role = CompanyRole::where('name', 'HR Officer')->firstOrFail();

        $this->post(route('company.users.store'), [
            'role' => 'Employee', 'company_role_id' => $role->id, 'name' => 'Nasrin', 'phone' => '01711000010',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $nasrin = User::where('phone', '01711000010')->firstOrFail();
        $this->get(route('company.users.index'))->assertOk()->assertSee('Nasrin');

        // The employee gets what the role allows, and no more.
        $menu = $this->actingAs($nasrin)->get(route('dashboard'))->assertOk();
        $menu->assertSee('href="'.route('employees.index').'"', false)->assertSee('href="'.route('attendance.sheet').'"', false)
            ->assertDontSee('href="'.route('payroll.runs.index').'"', false)->assertDontSee('href="'.route('ledger-accounts.index').'"', false)
            ->assertDontSee('href="'.route('company.users.index').'"', false);
        $this->get(route('employees.index'))->assertOk();
        $this->get(route('attendance.sheet'))->assertOk();
        $this->get(route('payroll.runs.index'))->assertForbidden();
        $this->get(route('ledger-accounts.index'))->assertForbidden();
        $this->get(route('company.users.index'))->assertForbidden();
        $this->get(route('company.roles.index'))->assertForbidden();

        // Widen the role: payroll opens up.
        $this->actingAs($this->owner)->put(route('company.roles.update', $role), [
            'name' => 'HR Officer', 'permissions' => [...$role->permissions, 'payroll.view'],
        ])->assertRedirect();
        $this->actingAs($nasrin->fresh())->get(route('payroll.runs.index'))->assertOk();

        // Made a company admin: every company module, including accounting.
        $this->actingAs($this->owner)->put(route('company.users.update', $nasrin), [
            'role' => 'Admin', 'name' => 'Nasrin', 'phone' => '01711000010',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($nasrin->fresh())->get(route('ledger-accounts.index'))->assertOk();
        $this->get(route('company.users.index'))->assertOk();

        // Deleting a role takes its permissions away from its employees.
        $this->actingAs($this->owner)->put(route('company.users.update', $nasrin), [
            'role' => 'Employee', 'company_role_id' => $role->id, 'name' => 'Nasrin', 'phone' => '01711000010',
        ]);
        $this->delete(route('company.roles.destroy', $role))->assertRedirect();
        $this->actingAs($nasrin->fresh())->get(route('employees.index'))->assertForbidden();
    }

    public function test_a_company_cannot_touch_another_companys_roles_or_users(): void
    {
        $other = Company::create(['name' => 'Other Group', 'slug' => 'other-group', 'type' => Company::TYPE_COMPANY, 'status' => 'active']);
        $otherRole = $other->roles()->create(['name' => 'Clerk', 'permissions' => ['employees.view']]);
        $otherUser = User::factory()->create();
        $other->users()->attach($otherUser->id, ['role' => Company::ROLE_EMPLOYEE, 'is_owner' => false]);

        $this->actingAs($this->owner)->get(route('company.roles.edit', $otherRole))->assertNotFound();
        $this->delete(route('company.roles.destroy', $otherRole))->assertNotFound();
        $this->delete(route('company.users.destroy', $otherUser))->assertNotFound();
        $this->post(route('company.users.store'), [
            'role' => 'Employee', 'company_role_id' => $otherRole->id, 'name' => 'X', 'phone' => '01711000011', 'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors('company_role_id');
    }

    public function test_a_company_user_who_cannot_approve_leave_only_handles_their_own(): void
    {
        $role = $this->company->roles()->create(['name' => 'Staff', 'permissions' => ['leave.view', 'leave.create']]);
        $staff = User::factory()->create();
        $this->company->users()->attach($staff->id, ['role' => Company::ROLE_EMPLOYEE, 'is_owner' => false, 'company_role_id' => $role->id]);
        $colleague = Employee::withoutGlobalScopes()->create(['company_id' => $this->company->id, 'name' => 'Colleague', 'phone' => '01711000020', 'designation' => 'Clerk', 'salary' => 15000, 'status' => 'active']);
        app(HrSetup::class)->ensureFor($this->company->id);
        $casual = LeaveType::withoutGlobalScopes()->where('company_id', $this->company->id)->firstOrFail();

        $this->actingAs($staff)->get(route('dashboard'))->assertOk()
            ->assertSee('href="'.route('my.leave.index').'"', false)
            ->assertDontSee('href="'.route('leave-requests.index').'"', false);
        $this->get(route('leave-requests.index'))->assertRedirect(route('my.leave.index'));
        $this->get(route('my.leave.index'))->assertOk()->assertDontSee('Colleague');

        $this->post(route('leave-requests.store'), [
            'employee_id' => $colleague->id, 'leave_type_id' => $casual->id, 'from_date' => now()->addDay()->toDateString(), 'to_date' => now()->addDay()->toDateString(),
        ])->assertForbidden();
        $this->assertSame(0, LeaveRequest::withoutGlobalScopes()->count());

        // The owner (who approves leave) keeps HR's page with everyone.
        $this->actingAs($this->owner)->get(route('leave-requests.index'))->assertOk()->assertSee('Colleague');
    }
}
