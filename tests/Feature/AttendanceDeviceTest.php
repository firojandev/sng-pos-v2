<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\AttendanceDevice;
use Modules\Employee\Models\AttendanceDevicePunch;
use Modules\Employee\Models\AttendanceLog;
use Modules\Employee\Models\Employee;
use Modules\Employee\Services\HrSetup;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceDeviceTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $admin;

    private Employee $karim;

    private AttendanceDevice $device;

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
        $this->karim = Employee::create(['name' => 'Karim', 'phone' => '01711000001', 'designation' => 'Cashier', 'salary' => 15000, 'status' => 'active', 'device_user_id' => '101']);
        $this->device = AttendanceDevice::create(['name' => 'Front gate', 'serial_number' => 'CQZ7231260001', 'shop_id' => $this->shop->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_the_machine_handshake_gets_its_options_and_is_marked_seen(): void
    {
        auth()->logout();

        $this->get('/iclock/cdata?SN=CQZ7231260001&options=all&pushver=2.4.1')
            ->assertOk()
            ->assertSee('GET OPTION FROM: CQZ7231260001')
            ->assertSee('ATTLOGStamp=None');

        $this->assertNotNull($this->device->fresh()->last_seen_at);
        $this->get('/iclock/getrequest?SN=CQZ7231260001')->assertOk()->assertSeeText('OK');
    }

    public function test_punches_pushed_by_the_machine_become_attendance(): void
    {
        auth()->logout();

        $this->call('POST', '/iclock/cdata?SN=CQZ7231260001&table=ATTLOG&Stamp=9999', [], [], [], ['CONTENT_TYPE' => 'text/plain'],
            "101\t2026-09-23 09:20:11\t0\t1\t0\t0\n101\t2026-09-23 18:40:03\t1\t1\t0\t0\n555\t2026-09-23 09:01:00\t0\t1\t0\t0\n"
        )->assertOk()->assertSeeText('OK: 3');

        $day = Attendance::withoutGlobalScopes()->where('employee_id', $this->karim->id)->whereDate('date', '2026-09-23')->firstOrFail();
        $this->assertSame('late', $day->status);
        $this->assertSame(10, $day->late_minutes);
        $this->assertSame(40, $day->overtime_minutes);
        $this->assertSame(2, AttendanceLog::withoutGlobalScopes()->where('source', 'device')->count());
        $this->assertSame(1, AttendanceDevicePunch::withoutGlobalScopes()->whereNull('employee_id')->count(), 'User 555 is kept unmatched.');
    }

    public function test_repeated_uploads_are_not_counted_twice_and_unknown_or_inactive_machines_are_ignored(): void
    {
        auth()->logout();
        $body = "101\t2026-09-23 09:00:00\t0\t1\t0\t0\n";

        $this->call('POST', '/iclock/cdata?SN=CQZ7231260001&table=ATTLOG', [], [], [], [], $body)->assertOk();
        $this->call('POST', '/iclock/cdata?SN=CQZ7231260001&table=ATTLOG', [], [], [], [], $body)->assertOk();
        $this->assertSame(1, AttendanceLog::withoutGlobalScopes()->count());

        $this->call('POST', '/iclock/cdata?SN=UNKNOWN&table=ATTLOG', [], [], [], [], "101\t2026-09-22 09:00:00\n")->assertOk()->assertSeeText('OK');
        $this->device->update(['is_active' => false]);
        $this->call('POST', '/iclock/cdata?SN=CQZ7231260001&table=ATTLOG', [], [], [], [], "101\t2026-09-21 09:00:00\n")->assertOk();

        $this->assertSame(1, AttendanceDevicePunch::withoutGlobalScopes()->count());
    }

    public function test_unmatched_punches_are_matched_once_the_employee_gets_the_device_id(): void
    {
        $rahim = Employee::create(['name' => 'Rahim', 'phone' => '01711000002', 'designation' => 'Helper', 'salary' => 10000, 'status' => 'active']);
        $this->call('POST', '/iclock/cdata?SN=CQZ7231260001&table=ATTLOG', [], [], [], [], "202\t2026-09-23 09:00:00\n202\t2026-09-23 18:00:00\n");

        $this->get(route('attendance.devices.index'))->assertOk()->assertSee('202');

        $rahim->update(['device_user_id' => '202']);
        $this->post(route('attendance.devices.match'))->assertRedirect();

        $this->assertSame('present', Attendance::where('employee_id', $rahim->id)->whereDate('date', '2026-09-23')->value('status'));
        $this->assertSame(0, AttendanceDevicePunch::whereNull('employee_id')->count());
    }

    public function test_punches_can_be_imported_from_a_csv_or_machine_file(): void
    {
        $csv = UploadedFile::fake()->createWithContent('attendance.csv', "User ID,Date,Time\n101,22/09/2026,09:02\n101,22/09/2026,18:05\n");

        $this->post(route('attendance.devices.import'), ['file' => $csv, 'shop_id' => $this->shop->id])
            ->assertRedirect()
            ->assertSessionHas('status');

        $day = Attendance::where('employee_id', $this->karim->id)->whereDate('date', '2026-09-22')->firstOrFail();
        $this->assertSame('present', $day->status);
        $this->assertSame(5, $day->overtime_minutes);
        $this->assertSame(2, AttendanceLog::where('source', 'import')->count());

        $attlog = UploadedFile::fake()->createWithContent('1_attlog.dat', "  101\t2026-09-21 09:30:00\t0\t1\t0\t0\n");
        $this->post(route('attendance.devices.import'), ['file' => $attlog, 'shop_id' => $this->shop->id])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('late', Attendance::where('employee_id', $this->karim->id)->whereDate('date', '2026-09-21')->value('status'));

        $empty = UploadedFile::fake()->createWithContent('empty.csv', "nothing,here\n");
        $this->post(route('attendance.devices.import'), ['file' => $empty, 'shop_id' => $this->shop->id])->assertSessionHasErrors('file');
    }

    public function test_machines_are_registered_toggled_and_removed(): void
    {
        $this->post(route('attendance.devices.store'), ['name' => 'Back door', 'serial_number' => 'CQZ7231260002', 'shop_id' => $this->shop->id])->assertRedirect();
        $this->post(route('attendance.devices.store'), ['name' => 'Copy', 'serial_number' => 'CQZ7231260002', 'shop_id' => $this->shop->id])->assertSessionHasErrors('serial_number');

        $backDoor = AttendanceDevice::where('serial_number', 'CQZ7231260002')->firstOrFail();
        $this->post(route('attendance.devices.toggle', $backDoor))->assertRedirect();
        $this->assertFalse($backDoor->fresh()->is_active);

        $this->delete(route('attendance.devices.destroy', $backDoor))->assertRedirect();
        $this->assertSame(1, AttendanceDevice::count());
    }

    public function test_staff_without_attendance_edit_permission_cannot_manage_machines(): void
    {
        $staff = User::factory()->create(['shop_id' => $this->shop->id]);
        $staff->shops()->updateExistingPivot($this->shop->id, ['role' => 'Cashier', 'is_owner' => false]);
        setPermissionsTeamId($this->shop->id);
        $cashier = Role::create(['shop_id' => $this->shop->id, 'name' => 'Cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions(['attendance.view']);
        $staff->assignRole($cashier);
        setPermissionsTeamId(null);

        $this->actingAs($staff)->get(route('attendance.devices.index'))->assertForbidden();
    }
}
