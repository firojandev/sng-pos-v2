<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Task\Models\Task;
use Revoltify\Subscriptionify\Models\Feature;
use Revoltify\Subscriptionify\Services\FeatureResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $admin;

    private User $leader;

    private User $rahim;

    private User $karim;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Task management belongs to companies.
        $company = Company::create(['name' => 'Karim Group', 'slug' => 'karim-group', 'type' => Company::TYPE_COMPANY, 'status' => 'active']);
        $this->shop = Shop::create(['company_id' => $company->id, 'name' => 'Karim Supershop', 'slug' => 'karim-supershop', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->shop, Features::keys());
        $this->admin = User::factory()->create(['shop_id' => $this->shop->id]);
        $this->admin->syncRoles(['Admin']);

        $this->leader = $this->staff('Team Leader', ['tasks.assign']);
        $this->rahim = $this->staff('Cashier', []);
        $this->karim = $this->staff('Cashier', []);
    }

    public function test_a_team_leader_assigns_a_task_and_the_employee_moves_it_to_completed(): void
    {
        $this->actingAs($this->leader)->post(route('tasks.store'), [
            'title' => 'Count the rice stock', 'description' => 'Both warehouses', 'assigned_to' => $this->rahim->id, 'deadline' => now()->addDays(2)->toDateString(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $task = Task::firstOrFail();
        $this->assertSame([$this->leader->id, $this->rahim->id, 'todo'], [(int) $task->reported_by, (int) $task->assigned_to, $task->status]);

        $this->actingAs($this->rahim)->get(route('tasks.index'))->assertOk()->assertSee('Count the rice stock');
        $this->patch(route('tasks.status', $task), ['status' => 'in_progress'])->assertRedirect();
        $this->assertNull($task->fresh()->finished_at);
        $this->patch(route('tasks.status', $task), ['status' => 'completed'])->assertRedirect();
        $this->assertNotNull($task->fresh()->finished_at, 'Completing records when it finished.');

        $this->get(route('tasks.edit', $task))->assertForbidden();
        $this->delete(route('tasks.destroy', $task))->assertForbidden();

        $this->actingAs($this->leader)->get(route('tasks.index', ['tab' => 'reported', 'assignee' => $this->rahim->id, 'status' => '']))
            ->assertOk()->assertSee('Count the rice stock');
        $this->put(route('tasks.update', $task), ['title' => 'Count all stock', 'assigned_to' => $this->karim->id])->assertRedirect();
        $this->assertSame($this->karim->id, (int) $task->fresh()->assigned_to);
    }

    public function test_an_employee_keeps_a_personal_todo_list_but_cannot_assign_to_others(): void
    {
        $this->actingAs($this->rahim)->post(route('tasks.store'), ['title' => 'Call the supplier'])->assertRedirect();
        $todo = Task::firstOrFail();
        $this->assertTrue($todo->isPersonal());

        $this->get(route('tasks.edit', $todo))->assertOk();
        $this->patch(route('tasks.status', $todo), ['status' => 'canceled'])->assertRedirect();
        $this->assertSame('canceled', $todo->fresh()->status);

        $this->post(route('tasks.store'), ['title' => 'Do my work', 'assigned_to' => $this->karim->id])->assertForbidden();
        $this->assertSame(1, Task::count());

        $this->get(route('dashboard'))->assertOk()->assertSee('My Tasks');
    }

    public function test_employees_only_see_their_own_tasks_and_managers_see_all(): void
    {
        $karimsTask = Task::create(['company_id' => $this->shop->company_id, 'title' => 'Clean the shelves', 'reported_by' => $this->leader->id, 'assigned_to' => $this->karim->id]);

        $this->actingAs($this->rahim)->get(route('tasks.index', ['status' => '']))->assertOk()->assertDontSee('Clean the shelves');
        $this->patch(route('tasks.status', $karimsTask), ['status' => 'completed'])->assertForbidden();
        $this->get(route('tasks.index', ['tab' => 'all']))->assertDontSee('Clean the shelves');

        $this->actingAs($this->admin)->get(route('tasks.index', ['tab' => 'all']))->assertOk()->assertSee('Clean the shelves');
    }

    public function test_tasks_are_only_for_companies_on_a_plan_with_tasks(): void
    {
        $this->actingAs($this->rahim)->get(route('dashboard'))->assertOk()->assertSee(route('tasks.index'));

        // A standalone shop's owner doesn't get task management.
        $standalone = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $this->subscribeShopToFeatures($standalone, Features::keys());
        $shopOwner = User::factory()->create(['shop_id' => $standalone->id]);
        $shopOwner->syncRoles(['Admin']);
        $this->actingAs($shopOwner)->get(route('tasks.index'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()->assertDontSee(route('tasks.index'))->assertDontSee('My Tasks');

        // Nor does the super admin.
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']));
        $this->actingAs($superAdmin)->get(route('dashboard'))->assertOk()->assertDontSee(route('tasks.index'));
        $this->get(route('tasks.index'))->assertForbidden();

        // A company whose plan doesn't include Tasks doesn't get it either.
        $plan = $this->shop->billingSubscription()->plan;
        $plan->features()->detach(Feature::where('slug', 'tasks')->value('id'));
        app(FeatureResolver::class)->flush();
        $this->shop->refresh()->clearSubscriptionCache();
        $this->actingAs($this->rahim->fresh())->get(route('tasks.index'))->assertForbidden();
    }

    public function test_the_default_company_gets_tasks_through_its_own_plan(): void
    {
        $default = Company::defaultCompany();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']));
        $defaultAdmin = User::factory()->create();
        $staff = User::factory()->create();
        $default->users()->attach($defaultAdmin->id, ['role' => Company::ROLE_ADMIN, 'is_owner' => false]);
        $default->users()->attach($staff->id, ['role' => Company::ROLE_EMPLOYEE, 'is_owner' => false]);

        // No plan on the Default Company: no tasks.
        $this->actingAs($defaultAdmin)->get(route('tasks.index'))->assertForbidden();
        $this->get(route('default-company.index'))->assertOk()->assertDontSee(route('tasks.index'));

        $this->actingAs($superAdmin)->put(route('companies.plan.update', $default), ['plan_id' => Plan::where('slug', 'standard')->value('id'), 'subscription_status' => 'active'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($defaultAdmin->fresh())->get(route('default-company.index'))->assertOk()->assertSee(route('tasks.index'));
        $this->post(route('tasks.store'), ['title' => 'Call new shop owners', 'assigned_to' => $staff->id])->assertRedirect()->assertSessionHasNoErrors();
        $task = Task::firstOrFail();
        $this->assertSame($default->id, (int) $task->company_id);

        auth()->logout();
        $this->post(route('login.store'), ['login' => $staff->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->actingAs($staff->fresh())->get(route('tasks.index'))->assertOk()->assertSee('Call new shop owners');
        $this->patch(route('tasks.status', $task), ['status' => 'completed'])->assertRedirect();

        // The Default Company's tasks stay with its admin inside a standalone shop.
        $standalone = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $this->subscribeShopToFeatures($standalone, Features::keys());
        $this->actingAs($defaultAdmin->fresh())->post(route('default-company.shops.open', $standalone))->assertRedirect();
        $this->actingAs($defaultAdmin->fresh())->get(route('tasks.index', ['tab' => 'reported', 'status' => '']))->assertOk()->assertSee('Call new shop owners');

        // Other companies' tasks are not visible.
        $this->actingAs($this->leader)->get(route('tasks.edit', $task))->assertNotFound();
        $this->actingAs($this->admin)->get(route('tasks.index', ['tab' => 'all', 'status' => '']))->assertOk()->assertDontSee('Call new shop owners');

        // Take Tasks off the plan: gone.
        Plan::where('slug', 'standard')->firstOrFail()->features()->detach(Feature::where('slug', 'tasks')->value('id'));
        app(FeatureResolver::class)->flush();
        $this->actingAs($defaultAdmin->fresh())->get(route('tasks.index'))->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     */
    private function staff(string $roleName, array $permissions): User
    {
        $user = User::factory()->create(['shop_id' => $this->shop->id]);
        $user->shops()->updateExistingPivot($this->shop->id, ['role' => $roleName, 'is_owner' => false]);
        setPermissionsTeamId($this->shop->id);
        $role = Role::firstOrCreate(['shop_id' => $this->shop->id, 'name' => $roleName, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);
        setPermissionsTeamId(null);

        return $user;
    }
}
