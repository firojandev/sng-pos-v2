<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ErpPlanFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The ERP modules added to the system, each a plan feature.
     *
     * @var list<string>
     */
    private const ERP_FEATURES = ['accounting', 'loyalty', 'attendance', 'leave', 'hr-setup', 'payroll', 'payroll-setup', 'tasks'];

    public function test_the_super_admin_sees_the_erp_modules_on_the_plan_form(): void
    {
        $superAdmin = $this->superAdmin();
        $shop = Shop::create(['name' => 'Karim Store', 'slug' => 'karim-store', 'status' => 'active']);
        $this->subscribeShopToFeatures($shop, Features::keys());
        $plan = $shop->activeSubscription->plan;

        $page = $this->actingAs($superAdmin)->get(route('plans.edit', $plan))->assertOk()->assertSee('HR & Payroll')->assertSee('Accounting & Tasks');

        foreach (self::ERP_FEATURES as $feature) {
            $page->assertSee('value="'.$feature.'"', false);
        }

        $this->get(route('plans.create'))->assertOk()->assertSee('value="payroll"', false);
    }

    public function test_removing_erp_modules_from_a_plan_turns_them_off_and_adding_them_back_on(): void
    {
        (new SubscriptionifySeeder)->run();
        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $superAdmin = $this->superAdmin();
        // A company shop (task management is for companies).
        $company = Company::create(['name' => 'Karim Group', 'slug' => 'karim-group', 'type' => Company::TYPE_COMPANY, 'status' => 'active']);
        $shop = Shop::create(['company_id' => $company->id, 'name' => 'Karim Store', 'slug' => 'karim-store', 'status' => 'active']);
        $this->subscribeShopToFeatures($shop, Features::keys());
        $plan = $shop->activeSubscription->plan;
        $owner = User::factory()->create(['shop_id' => $shop->id]);
        $owner->syncRoles(['Admin']);

        $pages = [
            'accounting' => route('ledger-accounts.index'),
            'loyalty' => route('loyalty.index'),
            'attendance' => route('attendance.sheet'),
            'leave' => route('leave-requests.index'),
            'hr-setup' => route('hr-setup.index'),
            'payroll' => route('payroll.runs.index'),
            'payroll-setup' => route('payroll-setup.index'),
            'tasks' => route('tasks.index'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }

        $this->updatePlan($superAdmin, $plan, array_values(array_diff(Features::keys(), self::ERP_FEATURES)));
        $shop->refresh()->clearSubscriptionCache();

        foreach ($pages as $feature => $url) {
            $this->assertFalse($shop->hasFeature($feature));
            $this->actingAs($owner->fresh())->get($url)->assertForbidden();
        }

        $this->actingAs($owner->fresh())->get(route('dashboard'))->assertOk()
            ->assertDontSee(route('tasks.index'))
            ->assertDontSee(route('payroll.runs.index'));
        $this->get(route('employees.index'))->assertOk()->assertDontSee(route('attendance.sheet'))->assertDontSee(route('leave-requests.index'));

        $this->updatePlan($superAdmin, $plan, Features::keys());
        $shop->refresh()->clearSubscriptionCache();

        foreach ($pages as $url) {
            $this->actingAs($owner->fresh())->get($url)->assertOk();
        }
    }

    /**
     * @param  list<string>  $features
     */
    private function updatePlan(User $superAdmin, $plan, array $features): void
    {
        $this->actingAs($superAdmin)->put(route('plans.update', $plan), [
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'features' => $features,
        ])->assertRedirect(route('plans.index'));
    }

    private function superAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
