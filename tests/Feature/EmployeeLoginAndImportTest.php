<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Mail\SetPasswordMail;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Department;
use Modules\Employee\Models\Designation;
use Modules\Employee\Models\Employee;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeLoginAndImportTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $admin;

    private Role $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create(['name' => 'Login Test Shop', 'slug' => 'login-test-shop', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->shop, Features::keys());

        $this->cashier = Role::create(['name' => 'Cashier', 'guard_name' => 'web', 'shop_id' => $this->shop->id]);

        $this->admin = User::create([
            'name' => 'Shop Admin', 'email' => 'admin@loginshop.test', 'phone' => '01700000001',
            'password' => bcrypt('password123'), 'shop_id' => $this->shop->id, 'email_verified_at' => now(),
        ]);
        $this->admin->syncRoles([$adminRole]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function employee(array $overrides = []): array
    {
        return [
            'name' => 'Rahim Uddin', 'phone' => '01711000001', 'email' => 'rahim@example.com',
            'designation' => 'Sales Executive', 'salary' => 18000, 'status' => 'active', ...$overrides,
        ];
    }

    /**
     * @param  list<list<mixed>>  $rows
     */
    private function sheet(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['Employee Code', 'Name *', 'Phone *', 'Email', 'Designation *', 'Department', 'Monthly Salary *', 'Joining Date', 'Address', 'Status'],
            ...$rows,
        ]);

        $path = tempnam(sys_get_temp_dir(), 'employees').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'employees.xlsx', null, null, true);
    }

    public function test_an_employee_with_an_email_gets_a_login_and_sets_their_own_password(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post(route('employees.store'), $this->employee([
            'create_user' => '1', 'user_role' => $this->cashier->id,
        ]))->assertRedirect(route('employees.index'))->assertSessionHasNoErrors();

        $employee = Employee::withoutGlobalScopes()->where('phone', '01711000001')->firstOrFail();
        $user = User::where('email', 'rahim@example.com')->firstOrFail();

        $this->assertSame($user->id, $employee->user_id);
        $this->assertSame($this->shop->id, $user->shop_id);
        $this->assertSame('01711000001', $user->phone);
        $this->assertNull($user->email_verified_at);
        setPermissionsTeamId($this->shop->id);
        $this->assertTrue($user->hasRole('Cashier'));
        $this->assertFalse($user->isShopOwner($this->shop->id));

        $setupUrl = null;
        Mail::assertSent(SetPasswordMail::class, function (SetPasswordMail $mail) use (&$setupUrl) {
            $setupUrl = $mail->setupUrl;

            return $mail->hasTo('rahim@example.com');
        });

        // The emailed link lets them choose a password and sign in.
        auth()->logout();
        $this->get($setupUrl)->assertOk()->assertSee('rahim@example.com');
        parse_str((string) parse_url($setupUrl, PHP_URL_QUERY), $query);
        $token = basename((string) parse_url($setupUrl, PHP_URL_PATH));

        $this->post(route('password.setup.store'), ['token' => 'wrong', 'email' => $query['email'], 'password' => 'my-secret-1', 'password_confirmation' => 'my-secret-1'])
            ->assertSessionHasErrors('email');
        $this->post(route('password.setup.store'), ['token' => $token, 'email' => $query['email'], 'password' => 'my-secret-1', 'password_confirmation' => 'my-secret-1'])
            ->assertRedirect(route('login'));

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->post(route('login.store'), ['email' => 'rahim@example.com', 'password' => 'my-secret-1']);
        $this->assertAuthenticatedAs($user);

        // The link works once.
        auth()->logout();
        $this->post(route('password.setup.store'), ['token' => $token, 'email' => $query['email'], 'password' => 'another-1', 'password_confirmation' => 'another-1'])
            ->assertSessionHasErrors('email');
    }

    public function test_without_an_email_the_admin_sets_the_password_and_no_email_is_sent(): void
    {
        Mail::fake();
        $details = $this->employee(['email' => null, 'create_user' => '1', 'user_role' => $this->cashier->id]);

        $this->actingAs($this->admin)->post(route('employees.store'), $details)->assertSessionHasErrors('user_password');
        $this->assertSame(0, Employee::withoutGlobalScopes()->count());

        $this->post(route('employees.store'), [...$details, 'user_password' => 'secret-123'])->assertSessionHasNoErrors();

        $user = User::where('phone', '01711000001')->firstOrFail();
        $this->assertSame('password', $user->verifySecret('secret-123'));
        $this->assertSame($user->id, Employee::withoutGlobalScopes()->firstOrFail()->user_id);
        Mail::assertNothingSent();
    }

    public function test_a_login_needs_a_role_a_free_phone_and_permission_to_create_users(): void
    {
        Mail::fake();
        $details = $this->employee(['create_user' => '1']);

        $this->actingAs($this->admin)->post(route('employees.store'), $details)->assertSessionHasErrors('user_role');
        $this->post(route('employees.store'), [...$details, 'user_role' => 999999])->assertSessionHasErrors('user_role');

        // The admin's own phone already has a user.
        $this->post(route('employees.store'), [...$details, 'phone' => '01700000001', 'user_role' => $this->cashier->id])->assertSessionHasErrors('create_user');

        // HR staff who can't create users can't hand out logins.
        $hrRole = Role::create(['name' => 'HR', 'guard_name' => 'web', 'shop_id' => $this->shop->id]);
        $hrRole->syncPermissions(['employees.view', 'employees.create']);
        $hr = User::create(['name' => 'HR', 'phone' => '01700000002', 'password' => bcrypt('password123'), 'shop_id' => $this->shop->id]);
        $hr->syncRoles([$hrRole]);

        $this->actingAs($hr)->post(route('employees.store'), [...$details, 'user_role' => $this->cashier->id])->assertSessionHasErrors('create_user');
        $this->assertSame(0, Employee::withoutGlobalScopes()->count());
        $this->get(route('employees.index'))->assertOk()->assertDontSee('id="create_employee_create_user"', false)->assertDontSee('id="import_employee_create_user"', false);
        $this->actingAs($this->admin)->get(route('employees.index'))->assertOk()->assertSee('id="create_employee_create_user"', false)->assertSee('id="import_employee_create_user"', false);
        $this->get(route('employees.create'))->assertOk()->assertSee('id="employee_create_user"', false);
    }

    public function test_a_login_can_be_created_while_editing_an_employee_without_one(): void
    {
        Mail::fake();
        $this->actingAs($this->admin)->post(route('employees.store'), $this->employee())->assertSessionHasNoErrors();
        $employee = Employee::withoutGlobalScopes()->firstOrFail();
        $this->assertNull($employee->user_id);

        $update = $this->employee(['employee_code' => $employee->employee_code, 'create_user' => '1', 'user_role' => $this->cashier->id]);
        $this->put(route('employees.update', $employee), $update)->assertSessionHasNoErrors();

        $user = User::where('email', 'rahim@example.com')->firstOrFail();
        $this->assertSame($user->id, $employee->fresh()->user_id);
        Mail::assertSent(SetPasswordMail::class, 1);

        // The edit form lists the new login as the linked account.
        $this->getJson(route('employees.edit', $employee))->assertOk()
            ->assertJsonPath('employee.user_id', $user->id)
            ->assertJsonFragment(['id' => $user->id, 'name' => 'Rahim Uddin', 'email' => 'rahim@example.com']);

        // Not twice.
        $this->put(route('employees.update', $employee), $update)->assertSessionHasErrors('create_user');
    }

    public function test_designation_and_department_are_chosen_from_the_lists_or_added_as_new(): void
    {
        $this->actingAs($this->admin)->post(route('employees.store'), $this->employee([
            'designation_id' => '__new', 'designation' => 'Manager', 'department_id' => '__new', 'department' => 'Accounts',
        ]))->assertSessionHasNoErrors();

        $manager = Designation::withoutGlobalScopes()->where('name', 'Manager')->firstOrFail();
        $accounts = Department::withoutGlobalScopes()->where('name', 'Accounts')->firstOrFail();
        $this->get(route('employees.index'))->assertOk()
            ->assertSee('<option value="'.$manager->id.'"', false)->assertSee('<option value="'.$accounts->id.'"', false);

        $this->post(route('employees.store'), $this->employee([
            'phone' => '01711000002', 'email' => null, 'designation' => null, 'designation_id' => $manager->id, 'department_id' => $accounts->id,
        ]))->assertSessionHasNoErrors();

        $second = Employee::withoutGlobalScopes()->where('phone', '01711000002')->firstOrFail();
        $this->assertSame(['Manager', 'Accounts'], [$second->designation, $second->department]);

        $this->get(route('employees.edit', $second))->assertOk()->assertSee('<option value="'.$manager->id.'" selected', false);

        // The department is cleared with "None"; a designation is always needed.
        $update = $this->employee(['phone' => '01711000002', 'email' => null, 'employee_code' => $second->employee_code, 'designation' => null]);
        $this->put(route('employees.update', $second), [...$update, 'designation_id' => $manager->id, 'department_id' => ''])->assertSessionHasNoErrors();
        $this->assertNull($second->fresh()->department_id);
        $this->assertNull($second->fresh()->department);
        $this->put(route('employees.update', $second), [...$update, 'designation_id' => ''])->assertSessionHasErrors('designation');

        // Another company's designation can't be used.
        $other = Company::create(['name' => 'Other Group', 'slug' => 'other-group', 'type' => Company::TYPE_COMPANY, 'status' => 'active']);
        $foreign = Designation::withoutGlobalScopes()->create(['company_id' => $other->id, 'name' => 'Foreign']);
        $this->post(route('employees.store'), $this->employee(['phone' => '01711000003', 'email' => null, 'designation' => null, 'designation_id' => $foreign->id]))
            ->assertSessionHasErrors('designation_id');
    }

    public function test_the_import_format_downloads_with_the_expected_columns(): void
    {
        $response = $this->actingAs($this->admin)->get(route('employees.import.template'))->assertOk()
            ->assertDownload('employee-import-format.xlsx');

        $path = tempnam(sys_get_temp_dir(), 'format').'.xlsx';
        file_put_contents($path, $response->streamedContent());
        $spreadsheet = IOFactory::load($path);

        $this->assertSame(['Employees', 'Instructions'], $spreadsheet->getSheetNames());
        $this->assertSame(
            ['Employee Code', 'Name *', 'Phone *', 'Email', 'Designation *', 'Department', 'Monthly Salary *', 'Joining Date', 'Address', 'Status'],
            $spreadsheet->getSheet(0)->toArray()[0],
        );
    }

    public function test_employees_are_imported_from_excel_with_logins_for_those_with_an_email(): void
    {
        Mail::fake();

        $file = $this->sheet([
            [null, 'Karim Mia', 1711000011, 'Karim@Example.com', 'Sales Executive', 'Sales', '18,000', '15/01/2026', 'Mirpur', null],
            ['EMP-0900', 'Salma Begum', '01711000012', null, 'Cashier', null, 15000, '2026-02-01', null, 'inactive'],
            [null, null, null, null, null, null, null, null, null, null],
            [null, 'Jamal Hossain', '01700000001', 'jamal@example.com', 'Cashier', 'Sales', 16000, null, null, 'active'],
        ]);

        $this->actingAs($this->admin)->post(route('employees.import.store'), [
            'file' => $file, 'create_user' => '1', 'user_role' => $this->cashier->id,
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('import_errors');

        $karim = Employee::withoutGlobalScopes()->where('phone', '01711000011')->firstOrFail();
        $this->assertSame('karim@example.com', $karim->email);
        $this->assertSame('18000.00', $karim->salary);
        $this->assertSame('2026-01-15', $karim->joining_date->toDateString());
        $this->assertSame('active', $karim->status);
        $this->assertSame($this->shop->id, $karim->shop_id);
        $this->assertNotNull($karim->employee_code);
        $this->assertNotNull($karim->designation_id);
        $this->assertSame('Sales', $karim->departmentRecord->name);

        $salma = Employee::withoutGlobalScopes()->where('employee_code', 'EMP-0900')->firstOrFail();
        $this->assertSame('inactive', $salma->status);

        // Karim is invited; Salma has no email; Jamal's phone is already a user's.
        $this->assertSame(User::where('email', 'karim@example.com')->value('id'), $karim->user_id);
        $this->assertNull($salma->user_id);
        $this->assertNull(Employee::withoutGlobalScopes()->where('phone', '01700000001')->firstOrFail()->user_id);
        Mail::assertQueued(SetPasswordMail::class, 1);
        Mail::assertQueued(SetPasswordMail::class, fn (SetPasswordMail $mail) => $mail->hasTo('karim@example.com'));

        $report = session('import_report');
        $this->assertSame([3, 1], [$report['created'], $report['logins']]);
        $this->assertSame(['Salma Begum', 'Jamal Hossain'], array_column($report['without_login'], 'name'));
        $this->get(route('employees.index'))->assertOk();
    }

    public function test_a_file_with_a_bad_row_imports_nothing_and_lists_the_mistakes(): void
    {
        Mail::fake();
        $this->actingAs($this->admin)->post(route('employees.store'), $this->employee(['phone' => '01711000021', 'email' => null]));

        $this->post(route('employees.import.store'), ['file' => $this->sheet([
            [null, 'Good Row', '01711000022', null, 'Cashier', null, 15000, null, null, null],
            [null, 'Existing Phone', '01711000021', null, 'Cashier', null, 15000, null, null, null],
            [null, 'Same Phone In File', '01711000022', null, 'Cashier', null, 15000, null, null, null],
            [null, null, '01711000023', 'not-an-email', null, null, 'abc', 'someday', null, 'gone'],
        ]), 'create_user' => '1', 'user_role' => $this->cashier->id])->assertRedirect();

        $errors = session('import_errors');
        $this->assertCount(3, $errors);
        $this->assertStringContainsString('Row) 3', $errors[0]);
        $this->assertStringContainsString('same as row 2', $errors[1]);
        $this->assertStringContainsString('Row) 5', $errors[2]);
        $this->assertSame(1, Employee::withoutGlobalScopes()->count());
        Mail::assertNothingQueued();

        // Not the format at all.
        $this->post(route('employees.import.store'), ['file' => UploadedFile::fake()->createWithContent('staff.csv', "Full Name,Mobile\nA,B\n")])->assertRedirect();
        $this->assertCount(1, session('import_errors'));
        $this->post(route('employees.import.store'), ['file' => UploadedFile::fake()->create('staff.pdf', 10)])->assertSessionHasErrors('file');
        $this->post(route('employees.import.store'), ['file' => $this->sheet([]), 'create_user' => '1'])->assertSessionHasErrors('user_role');
    }

    public function test_in_the_company_workspace_the_login_is_a_company_employee_with_a_company_role(): void
    {
        Mail::fake();
        $company = Company::create(['name' => 'Karim Group', 'slug' => 'karim-group', 'type' => Company::TYPE_COMPANY, 'status' => 'active']);
        $branch = Shop::create(['company_id' => $company->id, 'name' => 'Karim Dhaka', 'slug' => 'karim-dhaka', 'status' => 'active']);
        $this->subscribeShopToFeatures($branch, Features::keys());
        $owner = User::factory()->create();
        $company->users()->attach($owner->id, ['role' => Company::ROLE_OWNER, 'is_owner' => true]);
        $hrOfficer = $company->roles()->create(['name' => 'HR Officer', 'permissions' => ['employees.view']]);

        $this->actingAs($owner)->post(route('employees.store'), $this->employee(['create_user' => '1', 'user_role' => $this->cashier->id]))
            ->assertSessionHasErrors('user_role');
        $this->post(route('employees.store'), $this->employee(['create_user' => '1', 'user_role' => $hrOfficer->id]))->assertSessionHasNoErrors();

        $user = User::where('email', 'rahim@example.com')->firstOrFail();
        $this->assertNull($user->shop_id);
        $this->assertTrue($user->isCompanyLevelUser());
        $this->assertSame($hrOfficer->id, (int) $company->users()->where('users.id', $user->id)->firstOrFail()->pivot->company_role_id);
        $this->assertSame($user->id, Employee::withoutGlobalScopes()->where('company_id', $company->id)->firstOrFail()->user_id);
        Mail::assertSent(SetPasswordMail::class, 1);
    }
}
