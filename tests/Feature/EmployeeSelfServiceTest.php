<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\AttendanceLog;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Services\HrSetup;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $admin;

    private User $staffUser;

    private Employee $karim;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-24 09:20:00'); // a Thursday
        Storage::fake('local');
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create(['name' => 'Karim Supershop', 'slug' => 'karim-supershop', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->shop, Features::keys());
        $this->admin = User::factory()->create(['shop_id' => $this->shop->id]);
        $this->admin->syncRoles(['Admin']);
        $this->actingAs($this->admin);
        app(HrSetup::class)->ensureFor($this->shop->company_id);

        $this->staffUser = $this->staff('Cashier', ['sales.view']);
        $this->karim = Employee::create(['name' => 'Karim', 'phone' => '01711000001', 'designation' => 'Cashier', 'salary' => 15000, 'status' => 'active', 'gender' => 'male', 'user_id' => $this->staffUser->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_linked_employee_clocks_in_and_out_from_the_dashboard(): void
    {
        $this->actingAs($this->staffUser)->get(route('dashboard'))->assertOk()->assertSee('My Workspace')->assertSee('Clock In');

        $this->post(route('my.clock'), ['direction' => 'in'])->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('my.clock'), ['direction' => 'in'])->assertSessionHasErrors('clock');

        $day = Attendance::where('employee_id', $this->karim->id)->whereDate('date', '2026-09-24')->firstOrFail();
        $this->assertSame(['late', 10], [$day->status, $day->late_minutes]);
        $this->assertSame('web', AttendanceLog::firstOrFail()->source);
        $this->get(route('dashboard'))->assertSee('Clock Out');

        Carbon::setTestNow('2026-09-24 18:30:00');
        $this->post(route('my.clock'), ['direction' => 'out'])->assertRedirect()->assertSessionHasNoErrors();

        $day->refresh();
        $this->assertSame('18:30', $day->check_out->format('H:i'));
        $this->assertSame(30, $day->overtime_minutes);
        $this->get(route('dashboard'))->assertSee('Clocked out');
        $this->post(route('my.clock'), ['direction' => 'out'])->assertSessionHasErrors('clock');
    }

    public function test_a_linked_employee_applies_for_leave_with_an_attachment_and_can_withdraw_it(): void
    {
        $casual = LeaveType::where('code', 'CL')->firstOrFail();
        $this->actingAs($this->staffUser);

        $this->post(route('my.leave.store'), [
            'leave_type_id' => $casual->id, 'from_date' => '2026-09-27', 'to_date' => '2026-09-28', 'reason' => 'Family event',
            'attachment' => UploadedFile::fake()->create('invitation.pdf', 120, 'application/pdf'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $leave = LeaveRequest::firstOrFail();
        $this->assertSame(['pending', 2, true, 'invitation.pdf'], [$leave->status, $leave->days, $leave->is_self_applied, $leave->attachment_name]);
        Storage::disk('local')->assertExists($leave->attachment_path);

        $this->get(route('dashboard'))->assertSee('Family event')->assertSee('Pending')->assertSee('Leave Balance');
        $this->get(route('leave-requests.attachment', $leave))->assertOk();

        $this->post(route('my.leave.store'), ['leave_type_id' => $casual->id, 'from_date' => '2026-09-28', 'to_date' => '2026-09-28'])->assertSessionHasErrors('from_date');

        $other = $this->staff('Helper', ['sales.view']);
        $this->actingAs($other)->get(route('leave-requests.attachment', $leave))->assertNotFound();

        $this->actingAs($this->staffUser)->post(route('my.leave.cancel', $leave))->assertRedirect();
        $this->assertSame('cancelled', $leave->fresh()->status);

        $this->actingAs($this->admin)->get(route('leave-requests.index'))->assertSee('invitation.pdf')->assertSee('Self');
    }

    public function test_a_user_not_linked_to_an_employee_gets_no_self_service(): void
    {
        $other = $this->staff('Helper', ['sales.view']);

        $this->actingAs($other)->get(route('dashboard'))->assertOk()->assertDontSee('My Workspace');
        $this->post(route('my.clock'), ['direction' => 'in'])->assertForbidden();
    }

    public function test_hr_clocks_staff_in_and_out_from_the_daily_sheet(): void
    {
        $this->get(route('attendance.sheet'))->assertOk()->assertSee('Clock In');

        $this->post(route('attendance.clock', [$this->karim, 'in']))->assertRedirect();
        $this->assertSame('late', Attendance::where('employee_id', $this->karim->id)->value('status'));
        $this->get(route('attendance.sheet'))->assertSee('Clock Out');

        $this->get(route('employees.profile.edit', $this->karim))->assertOk()->assertSee('Login User');
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
