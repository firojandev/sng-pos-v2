<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Services\OpeningBalanceService;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\AttendanceLog;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\Holiday;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Models\Shift;
use Modules\Employee\Services\AttendanceService;
use Modules\Employee\Services\DevicePunchService;
use Modules\Employee\Services\HrSetup;
use Modules\Employee\Services\LeaveService;
use Modules\Finance\Models\Account;
use Modules\Payroll\Models\EmployeeTaxDeclaration;
use Modules\Payroll\Models\PayrollSetting;
use Modules\Payroll\Models\StatutoryPayment;
use Modules\Payroll\Models\TaxYear;
use Modules\Payroll\Services\PayrollService;
use Modules\Payroll\Services\PayrollSetup;
use Modules\Payroll\Services\SalaryStructure;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayrollPoliciesTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Account $cash;

    private PayrollService $payroll;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-27 12:00:00');
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create(['name' => 'Karim Supershop', 'slug' => 'karim-supershop', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->shop, Features::keys());
        $admin = User::factory()->create(['shop_id' => $this->shop->id]);
        $admin->syncRoles(['Admin']);
        $this->actingAs($admin);

        app(HrSetup::class)->ensureFor($this->shop->company_id);
        app(PayrollSetup::class)->ensureFor($this->shop->company_id);
        $this->cash = Account::create(['shop_id' => $this->shop->id, 'name' => 'Bank', 'type' => 'bank', 'status' => 'active', 'is_default' => true, 'opening_balance' => 1000000, 'current_balance' => 1000000]);
        app(OpeningBalanceService::class)->post($this->shop->company, Carbon::parse('2026-07-01'));
        $this->payroll = app(PayrollService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_an_investment_rebate_lowers_the_tax_and_later_months_correct_themselves(): void
    {
        $rahim = $this->employee('Rahim', 150000);

        $july = $this->payroll->create($this->shop->id, Carbon::parse('2026-07-01'));
        $this->assertSame('14250.00', $july->payslips()->first()->tax, '171,000 a year over 12 months.');
        $this->payroll->approve($july);

        $year = TaxYear::where('name', '2026-27')->firstOrFail();
        $this->put(route('payroll.tax.declarations.update', [$year, $rahim]), ['declared_investment' => 350000, 'eligible_investment' => 300000])
            ->assertRedirect()->assertSessionHasNoErrors();

        $august = $this->payroll->create($this->shop->id, Carbon::parse('2026-08-01'));
        $this->assertSame('10214.00', $august->payslips()->first()->tax, 'The 44,400 rebate: (126,600 − 14,250 deducted) over 11 months.');
        $this->payroll->approve($august);

        $this->get(route('payroll.tax.index', ['year' => $year->id]))->assertOk()->assertSee('Rahim');

        $this->post(route('payroll.tax.close', $year))->assertRedirect();
        $declaration = EmployeeTaxDeclaration::where('employee_id', $rahim->id)->firstOrFail();
        $this->assertSame('24464.00', $declaration->tax_deducted);
        $this->assertSame('-24464.00', $declaration->year_end_adjustment, 'Only two months were paid, below the threshold: all of it is refundable.');
    }

    public function test_every_working_day_counts_and_a_half_day_is_half(): void
    {
        PayrollSetting::where('company_id', $this->shop->company_id)->update(['attendance_deductions' => true]);
        $mitu = $this->employee('Mitu', 30000, '2026-08-10');
        Holiday::create(['company_id' => $this->shop->company_id, 'date' => '2026-08-15', 'name' => 'National Mourning Day', 'type' => 'public']);

        foreach (['2026-08-10' => 'present', '2026-08-11' => 'present', '2026-08-12' => 'present', '2026-08-13' => 'late', '2026-08-16' => 'half_day'] as $date => $status) {
            Attendance::create(['company_id' => $mitu->company_id, 'employee_id' => $mitu->id, 'shop_id' => $mitu->shop_id, 'date' => $date, 'status' => $status]);
        }

        $unpaid = LeaveType::create(['company_id' => $this->shop->company_id, 'name' => 'Leave without pay', 'code' => 'LWP', 'is_paid' => false]);
        $casual = LeaveType::where('code', 'CL')->firstOrFail();
        LeaveRequest::create(['company_id' => $mitu->company_id, 'employee_id' => $mitu->id, 'leave_type_id' => $casual->id, 'from_date' => '2026-08-17', 'to_date' => '2026-08-18', 'days' => 2, 'status' => 'approved']);
        LeaveRequest::create(['company_id' => $mitu->company_id, 'employee_id' => $mitu->id, 'leave_type_id' => $unpaid->id, 'from_date' => '2026-08-19', 'to_date' => '2026-08-19', 'days' => 1, 'status' => 'approved']);

        $run = $this->payroll->create($this->shop->id, Carbon::parse('2026-08-01'));
        $payslip = $run->payslips()->first();

        $this->assertSame(22, $payslip->payable_days, 'From joining (10 Aug) to the month end.');
        $this->assertSame([4.5, 10.5, 2.0, 1.0, 1], [$payslip->present_days, $payslip->absent_days, $payslip->paid_leave_days, $payslip->unpaid_leave_days, $payslip->late_days]);
        $this->assertSame(6900.0, (float) $payslip->items()->where('code', 'ABSENT')->value('amount'), '(10.5 absent + 1 unpaid leave) × basic 18,000 ÷ 30.');
    }

    public function test_a_salary_change_inside_the_month_follows_the_company_policy(): void
    {
        $karim = $this->employee('Karim', 30000);
        app(SalaryStructure::class)->revise($karim, ['effective_from' => '2026-08-16', 'new_salary' => 36000, 'type' => 'increment']);

        $earnings = function (string $policy) {
            PayrollSetting::where('company_id', $this->shop->company_id)->update(['salary_change_policy' => $policy]);
            $run = $this->payroll->create($this->shop->id, Carbon::parse('2026-08-01'));
            $total = (float) $run->payslips()->first()->earnings_total;
            $this->payroll->delete($run);

            return $total;
        };

        $this->assertEqualsWithDelta(30000 * 15 / 31 + 36000 * 16 / 31, $earnings('prorated'), 0.05);
        $this->assertSame(30000.0, $earnings('next_month'));
        $this->assertSame(36000.0, $earnings('full_month'));
    }

    public function test_a_night_shift_is_one_shift_across_midnight(): void
    {
        $night = Shift::create(['company_id' => $this->shop->company_id, 'name' => 'Night', 'start_time' => '20:00', 'end_time' => '04:00', 'grace_minutes' => 10, 'weekend_days' => [5]]);
        $guard = $this->employee('Guard', 20000);
        $guard->update(['shift_id' => $night->id, 'device_user_id' => '101']);

        app(DevicePunchService::class)->ingest($this->shop->company_id, $this->shop->id, [
            ['101', Carbon::parse('2026-09-24 19:55:00')],
            ['101', Carbon::parse('2026-09-25 04:05:00')],
        ], 'import');

        $day = Attendance::where('employee_id', $guard->id)->whereDate('date', '2026-09-24')->firstOrFail();
        $this->assertSame('2026-09-24 19:55', $day->check_in->format('Y-m-d H:i'));
        $this->assertSame('2026-09-25 04:05', $day->check_out->format('Y-m-d H:i'));
        $this->assertSame('present', $day->status);
        $this->assertSame(5, $day->overtime_minutes);
        $this->assertFalse(Attendance::where('employee_id', $guard->id)->whereDate('date', '2026-09-25')->exists(), 'The 4:05 AM punch is not a day of its own.');
        $this->assertSame(['2026-09-24'], AttendanceLog::where('employee_id', $guard->id)->get()->map(fn ($log) => $log->shift_date->toDateString())->unique()->values()->all());

        $manual = app(AttendanceService::class)->recordManual($guard, Carbon::parse('2026-09-23'), '20:00', '04:00');
        $this->assertSame(480, $manual->worked_minutes, 'A check-out before the check-in is the next morning.');
    }

    public function test_provident_fund_and_income_tax_are_paid_over(): void
    {
        PayrollSetting::where('company_id', $this->shop->company_id)->update(['pf_enabled' => true]);
        $this->employee('Rahim', 150000);
        $this->payroll->approve($this->payroll->create($this->shop->id, Carbon::parse('2026-07-01')));

        $this->assertSame(9000.0, $this->balance('pf_payable'));
        $this->assertSame(9000.0, $this->balance('pf_employer_payable'));
        $tax = $this->balance('tds_payable');
        $this->assertGreaterThan(0, $tax);

        $this->post(route('payroll.statutory.store'), ['type' => 'pf', 'period_from' => '2026-07', 'period_to' => '2026-07', 'payee' => 'Staff PF Trust'])->assertRedirect()->assertSessionHasNoErrors();
        $pf = StatutoryPayment::where('type', 'pf')->firstOrFail();
        $this->assertSame(['9000.00', '9000.00', '18000.00', 'pending'], [$pf->employee_amount, $pf->employer_amount, $pf->amount, $pf->status]);

        $this->post(route('payroll.statutory.pay', $pf), ['account_id' => $this->cash->id, 'payment_date' => '2026-08-10', 'reference' => 'CHQ-1001'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(0.0, $this->balance('pf_payable'));
        $this->assertSame(0.0, $this->balance('pf_employer_payable'));

        $this->post(route('payroll.statutory.store'), ['type' => 'tax', 'period_from' => '2026-07', 'period_to' => '2026-07', 'payee' => '1-1141-0020-0111'])->assertRedirect();
        $taxPayment = StatutoryPayment::where('type', 'tax')->firstOrFail();
        $this->assertSame($tax, (float) $taxPayment->amount);
        $this->post(route('payroll.statutory.pay', $taxPayment), ['account_id' => $this->cash->id, 'payment_date' => '2026-08-12', 'reference' => 'Challan 55'])->assertRedirect();
        $this->assertSame(0.0, $this->balance('tds_payable'));
        $this->assertSame(1000000 - 18000 - $tax, (float) $this->cash->fresh()->current_balance);
        $this->assertBalanced();

        $this->get(route('payroll.statutory.index'))->assertOk()->assertSee('Challan 55');

        $this->delete(route('payroll.statutory.destroy', $taxPayment))->assertRedirect();
        $this->assertSame($tax, $this->balance('tds_payable'), 'Deleting the payment reverses it.');
        $this->assertSame(1000000 - 18000.0, (float) $this->cash->fresh()->current_balance);
        $this->assertBalanced();
    }

    public function test_earned_leave_carries_forward_up_to_its_limit_and_can_expire(): void
    {
        $worker = $this->employee('Worker', 20000, '2024-01-01');
        $earned = LeaveType::where('code', 'EL')->firstOrFail();
        $earned->update(['earn_one_day_per' => 1]);

        $rows = [];
        foreach ([['2024-01-01', 70], ['2025-01-01', 5]] as [$start, $days]) {
            for ($i = 0; $i < $days; $i++) {
                $rows[] = ['company_id' => $worker->company_id, 'employee_id' => $worker->id, 'shop_id' => $worker->shop_id, 'date' => Carbon::parse($start)->addDays($i)->toDateString(), 'status' => 'present', 'created_at' => now(), 'updated_at' => now()];
            }
        }
        Attendance::insert($rows);

        $leave = app(LeaveService::class);
        $balance = $leave->balance($worker, $earned->fresh(), 2026);
        $this->assertSame([60, 60, 0], [$balance['carried'], $balance['available'], $balance['expired']], '70 earned in 2024, capped at 60; 60 + 5 in 2025 capped at 60 again.');

        $earned->update(['max_carry_forward' => null]);
        $this->assertSame(75, $leave->balance($worker, $earned->fresh(), 2026)['available'], 'No limit: everything carries.');

        $earned->update(['max_carry_forward' => 60, 'carry_forward_expires' => true, 'carry_forward_expiry_months' => 3]);
        $early = $leave->balance($worker, $earned->fresh(), 2026, Carbon::parse('2026-02-01'));
        $late = $leave->balance($worker, $earned->fresh(), 2026, Carbon::parse('2026-09-01'));
        $this->assertSame([5, 5], [$early['carried'], $early['available']], "2025's carried days lapsed in April 2025; only 2025's own 5 carry.");
        $this->assertSame([5, 0], [$late['expired'], $late['available']], 'Unused by April, they lapse.');

        $this->put(route('hr-setup.leave-types.update', $earned), [
            'days_per_year' => null, 'earn_one_day_per' => 18, 'is_paid' => 1, 'is_active' => 1, 'carry_forward' => 1,
            'max_carry_forward' => 45, 'carry_forward_expires' => 0, 'is_encashable' => 0,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame([45, false], [$earned->fresh()->max_carry_forward, $earned->fresh()->is_encashable]);
    }

    private function employee(string $name, float $salary, string $joined = '2020-01-01'): Employee
    {
        return Employee::create(['name' => $name, 'phone' => '0171'.random_int(1000000, 9999999), 'designation' => 'Staff', 'salary' => $salary, 'status' => 'active', 'joining_date' => $joined, 'gender' => 'male']);
    }

    private function balance(string $key): float
    {
        $account = app(ChartOfAccounts::class)->account($this->shop->company_id, $key);
        $row = app(LedgerService::class)->totalsByAccount($this->shop->company_id)[$account->id] ?? null;

        return $row ? round($account->normalBalance((float) $row->debit, (float) $row->credit), 2) : 0.0;
    }

    private function assertBalanced(): void
    {
        $totals = app(LedgerService::class)->totalsByAccount($this->shop->company_id);

        $this->assertSame(round((float) $totals->sum('debit'), 2), round((float) $totals->sum('credit'), 2), 'The ledger balances.');
    }
}
