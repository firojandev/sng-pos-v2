<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Department;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\Holiday;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Models\Shift;
use Modules\Employee\Services\AttendanceService;
use Modules\Employee\Services\HrSetup;
use Modules\Employee\Services\LeaveService;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrAttendanceAndLeaveTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $admin;

    private Employee $karim;

    private AttendanceService $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-24 20:00:00'); // a Thursday
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
        $this->karim = Employee::create(['name' => 'Karim', 'phone' => '01711000001', 'designation' => 'Cashier', 'department' => 'Sales', 'salary' => 15000, 'status' => 'active', 'gender' => 'male', 'joining_date' => '2026-01-01']);
        $this->attendance = app(AttendanceService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_new_employee_belongs_to_the_company_with_a_code_and_linked_department(): void
    {
        $this->assertSame($this->shop->company_id, $this->karim->company_id);
        $this->assertSame($this->shop->id, $this->karim->shop_id);
        $this->assertSame('EMP-0001', $this->karim->employee_code);
        $this->assertSame('Sales', $this->karim->departmentRecord->name);
        $this->assertSame('Cashier', $this->karim->designationRecord->name);
    }

    public function test_the_full_profile_is_edited_and_only_accepts_the_companys_records(): void
    {
        $finance = Department::create(['name' => 'Finance']);
        $foreignShop = Shop::create(['name' => 'Other Store', 'slug' => 'other-store', 'status' => 'active']);
        $foreignDepartment = Department::withoutGlobalScopes()->create(['company_id' => $foreignShop->company_id, 'name' => 'Theirs']);
        $profile = fn (array $changes) => array_merge([
            'employee_code' => 'EMP-0001', 'name' => 'Karim', 'phone' => '01711000001', 'shop_id' => $this->shop->id,
            'employment_type' => 'permanent', 'status' => 'active', 'salary' => 15000,
        ], $changes);

        $this->get(route('employees.profile.edit', $this->karim))->assertOk()->assertSee('Casual Leave');

        $this->put(route('employees.profile.update', $this->karim), $profile(['department_id' => $foreignDepartment->id]))->assertSessionHasErrors('department_id');
        $this->put(route('employees.profile.update', $this->karim), $profile(['status' => 'resigned']))->assertSessionHasErrors('separation_date');

        $this->put(route('employees.profile.update', $this->karim), $profile(['department_id' => $finance->id, 'nid' => '1990123456789', 'blood_group' => 'B+']))
            ->assertRedirect(route('employees.profile.edit', $this->karim));
        $this->assertSame('Finance', $this->karim->fresh()->department);
        $this->assertSame('1990123456789', $this->karim->fresh()->nid);
    }

    public function test_hr_setup_manages_departments_shifts_holidays_and_leave_types(): void
    {
        $this->get(route('hr-setup.index'))->assertOk()->assertSee('Earned Leave')->assertSee('09:00');

        $this->post(route('hr-setup.named.store', 'designations'), ['name' => 'Store Keeper'])->assertRedirect();
        $this->post(route('hr-setup.shifts.store'), ['name' => 'Evening', 'start_time' => '14:00', 'end_time' => '22:00', 'grace_minutes' => 5, 'weekend_days' => [5]])->assertRedirect();
        $this->post(route('hr-setup.holidays.store'), ['date' => '2026-03-30', 'to_date' => '2026-04-01', 'name' => 'Eid-ul-Fitr', 'type' => 'festival'])->assertRedirect();
        $casual = LeaveType::where('code', 'CL')->firstOrFail();
        $this->put(route('hr-setup.leave-types.update', $casual), ['days_per_year' => 12, 'is_paid' => 1, 'is_active' => 1])->assertRedirect();

        $this->assertDatabaseHas('designations', ['name' => 'Store Keeper']);
        $this->assertSame(2, Shift::count());
        $this->assertSame(3, Holiday::count());
        $this->assertSame(12, $casual->fresh()->days_per_year);
    }

    public function test_a_day_is_worked_out_from_the_punches_and_the_shift(): void
    {
        $cases = [
            ['2026-09-22', '09:25', '19:00', 'late', 15, 60, 575],
            ['2026-09-23', '09:05', '18:00', 'present', 0, 0, 535],
            ['2026-09-24', '09:00', '12:00', 'half_day', 0, 0, 180],
        ];

        foreach ($cases as [$date, $in, $out, $status, $late, $overtime, $worked]) {
            $day = $this->attendance->recordManual($this->karim, Carbon::parse($date), $in, $out);

            $this->assertSame($status, $day->status, $date);
            $this->assertSame($late, $day->late_minutes, $date);
            $this->assertSame($overtime, $day->overtime_minutes, $date);
            $this->assertSame($worked, $day->worked_minutes, $date);
        }
    }

    public function test_days_without_punches_are_absent_weekend_holiday_or_leave_and_work_on_a_holiday_is_overtime(): void
    {
        Holiday::create(['date' => '2026-09-21', 'name' => 'Public holiday', 'type' => 'public']);

        $this->assertSame('absent', $this->attendance->process($this->karim, Carbon::parse('2026-09-22'))->status);
        $this->assertSame('weekend', $this->attendance->process($this->karim, Carbon::parse('2026-09-18'))->status);
        $this->assertSame('holiday', $this->attendance->process($this->karim, Carbon::parse('2026-09-21'))->status);

        $workedOnHoliday = $this->attendance->recordManual($this->karim, Carbon::parse('2026-09-21'), '10:00', '14:00');
        $this->assertSame('present', $workedOnHoliday->status);
        $this->assertSame(240, $workedOnHoliday->overtime_minutes);
    }

    public function test_the_daily_sheet_saves_times_and_statuses_and_fills_the_rest(): void
    {
        $rahim = Employee::create(['name' => 'Rahim', 'phone' => '01711000002', 'designation' => 'Helper', 'salary' => 10000, 'status' => 'active']);
        $hasan = Employee::create(['name' => 'Hasan', 'phone' => '01711000003', 'designation' => 'Helper', 'salary' => 10000, 'status' => 'active']);

        $this->get(route('attendance.sheet', ['date' => '2026-09-23']))->assertOk()->assertSee('Rahim');

        $this->post(route('attendance.save'), [
            'date' => '2026-09-23',
            'rows' => [
                ['employee_id' => $this->karim->id, 'check_in' => '09:00', 'check_out' => '18:00'],
                ['employee_id' => $rahim->id, 'status' => 'leave', 'note' => 'Family event'],
                ['employee_id' => $hasan->id],
            ],
        ])->assertRedirect(route('attendance.sheet', ['date' => '2026-09-23']));

        $this->post(route('attendance.process'), ['date' => '2026-09-23'])->assertRedirect();

        $statusOf = fn (Employee $employee) => Attendance::where('employee_id', $employee->id)->whereDate('date', '2026-09-23')->value('status');
        $this->assertSame('present', $statusOf($this->karim));
        $this->assertSame('leave', $statusOf($rahim), 'A status set by hand is kept.');
        $this->assertSame('absent', $statusOf($hasan));

        $this->get(route('attendance.monthly', ['month' => '2026-09']))->assertOk()->assertSee('Hasan');
    }

    public function test_leave_counts_working_days_and_is_limited_by_the_balance(): void
    {
        Holiday::create(['date' => '2026-10-01', 'name' => 'Holiday', 'type' => 'public']);
        $leave = app(LeaveService::class);
        $casual = LeaveType::where('code', 'CL')->firstOrFail();

        // 28 Sep – 3 Oct: Friday 2 Oct is the weekend and 1 Oct a holiday
        $this->assertSame(4, $leave->countDays($this->karim, Carbon::parse('2026-09-28'), Carbon::parse('2026-10-03')));
        $this->assertSame(['entitled' => 10, 'used' => 0, 'available' => 10, 'carried' => 0, 'expired' => 0], $leave->balance($this->karim, $casual));

        $tooLong = LeaveRequest::create(['employee_id' => $this->karim->id, 'leave_type_id' => $casual->id, 'from_date' => '2026-11-01', 'to_date' => '2026-11-20', 'days' => 11, 'status' => 'pending']);
        $this->expectException(ValidationException::class);
        $leave->approve($tooLong);
    }

    public function test_approving_and_cancelling_leave_updates_attendance(): void
    {
        $sick = LeaveType::where('code', 'SL')->firstOrFail();

        $this->post(route('leave-requests.store'), ['employee_id' => $this->karim->id, 'leave_type_id' => $sick->id, 'from_date' => '2026-09-21', 'to_date' => '2026-09-22', 'reason' => 'Fever'])
            ->assertRedirect(route('leave-requests.index'));
        $request = LeaveRequest::firstOrFail();
        $this->assertSame(2, $request->days);

        $this->post(route('leave-requests.approve', $request))->assertRedirect();
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertSame('leave', Attendance::where('employee_id', $this->karim->id)->whereDate('date', '2026-09-22')->value('status'));
        $this->assertSame(12, app(LeaveService::class)->balance($this->karim, $sick)['available']);

        $this->post(route('leave-requests.cancel', $request))->assertRedirect();
        $this->assertSame('absent', Attendance::where('employee_id', $this->karim->id)->whereDate('date', '2026-09-22')->value('status'));
        $this->get(route('leave-requests.index'))->assertOk()->assertSee('Fever');
    }

    public function test_earned_leave_accrues_with_days_worked_and_maternity_leave_is_for_women(): void
    {
        $leave = app(LeaveService::class);
        $earned = LeaveType::where('code', 'EL')->firstOrFail();
        $maternity = LeaveType::where('code', 'ML')->firstOrFail();

        foreach (range(1, 37) as $offset) {
            Attendance::create(['employee_id' => $this->karim->id, 'date' => Carbon::parse('2026-06-01')->addDays($offset)->toDateString(), 'status' => 'present']);
        }

        $this->assertSame(2, $leave->balance($this->karim, $earned)['entitled'], '37 days worked earn 2 days.');
        $this->assertSame(0, $leave->balance($this->karim, $maternity)['entitled']);

        $this->karim->update(['gender' => 'female']);
        $this->assertSame(112, $leave->balance($this->karim->fresh(), $maternity)['entitled']);
    }

    public function test_staff_without_hr_permissions_are_kept_out(): void
    {
        $staff = User::factory()->create(['shop_id' => $this->shop->id]);
        $staff->shops()->updateExistingPivot($this->shop->id, ['role' => 'Cashier', 'is_owner' => false]);
        setPermissionsTeamId($this->shop->id);
        $cashier = Role::create(['shop_id' => $this->shop->id, 'name' => 'Cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions(['sales.view']);
        $staff->assignRole($cashier);
        setPermissionsTeamId(null);

        $this->actingAs($staff)->get(route('attendance.sheet'))->assertForbidden();
        $this->actingAs($staff)->get(route('leave-requests.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('hr-setup.index'))->assertForbidden();
    }
}
