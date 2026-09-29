<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Services\OpeningBalanceService;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Designation;
use Modules\Employee\Models\Employee;
use Modules\Employee\Services\HrSetup;
use Modules\Finance\Models\Account;
use Modules\Payroll\Models\EmployeeLoan;
use Modules\Payroll\Models\FinalSettlement;
use Modules\Payroll\Models\PayrollPayment;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\PayrollSetting;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\SalaryRevision;
use Modules\Payroll\Models\TaxYear;
use Modules\Payroll\Services\IncomeTax;
use Modules\Payroll\Services\LoanService;
use Modules\Payroll\Services\PayrollService;
use Modules\Payroll\Services\PayrollSetup;
use Modules\Payroll\Services\SalaryStructure;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Account $cash;

    private Employee $karim;

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
        $this->cash = Account::create(['shop_id' => $this->shop->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active', 'is_default' => true, 'opening_balance' => 200000, 'current_balance' => 200000]);
        app(OpeningBalanceService::class)->post($this->shop->company, Carbon::parse('2026-08-01'));

        $this->karim = Employee::create(['name' => 'Karim', 'phone' => '01711000001', 'designation' => 'Cashier', 'salary' => 30000, 'status' => 'active', 'joining_date' => '2024-01-01', 'gender' => 'male']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_monthly_payroll_pays_attendance_overtime_and_recovers_an_advance(): void
    {
        PayrollSetting::where('company_id', $this->shop->company_id)->update(['attendance_deductions' => true]);
        $this->attendanceForAugust($this->karim, absent: ['2026-08-03'], late: ['2026-08-04', '2026-08-05'], overtime: ['2026-08-10' => 60, '2026-08-11' => 60]);

        $this->post(route('payroll.loans.store'), [
            'employee_id' => $this->karim->id, 'type' => 'advance', 'amount' => 5000, 'installment' => 2000,
            'issued_on' => '2026-08-05', 'deduct_from' => '2026-08', 'account_id' => $this->cash->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('payroll.runs.store'), ['type' => 'salary', 'month' => '2026-08'])->assertRedirect();
        $run = PayrollRun::firstOrFail();
        $payslip = Payslip::where('employee_id', $this->karim->id)->firstOrFail();

        $this->assertSame([26.0, 1.0, 2, 120], [$payslip->present_days, $payslip->absent_days, $payslip->late_days, $payslip->overtime_minutes]);
        $this->assertSame(['BASIC' => 18000.0, 'HRA' => 9000.0, 'MED' => 1500.0, 'CONV' => 1500.0, 'OT' => 346.15], $this->itemAmounts($payslip, 'earning'));
        $this->assertSame(['ABSENT' => 600.0, 'LOAN' => 2000.0], $this->itemAmounts($payslip, 'deduction'), 'Below the tax-free threshold, no tax.');
        $this->assertSame('27746.15', $payslip->net_pay);

        $this->put(route('payroll.payslips.adjust', $payslip), ['other_deduction' => 100, 'adjustment_note' => 'Broken glass'])->assertRedirect();
        $this->assertSame('27646.15', $payslip->fresh()->net_pay);

        $this->post(route('payroll.runs.approve', $run))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertBalanced();
        $this->assertSame(29746.15, $this->balance('salary_expense'));
        $this->assertSame(27646.15, $this->balance('salary_payable'));
        $this->assertSame(3000.0, $this->balance('employee_advances'), 'Advance of 5,000 less one installment.');
        $this->assertSame(100.0, $this->balance('other_income'));
        $this->assertSame(3000.0, EmployeeLoan::firstOrFail()->balance());

        $this->post(route('payroll.runs.pay-all', $run), ['account_id' => $this->cash->id, 'paid_on' => '2026-09-01'])->assertRedirect();
        $this->assertSame(0.0, $this->balance('salary_payable'));
        $this->assertSame(200000 - 5000 - 27646.15, (float) $this->cash->fresh()->current_balance);
        $this->assertSame('paid', $run->fresh()->paymentStatus());
        $this->assertBalanced();

        $this->post(route('payroll.runs.reopen', $run))->assertSessionHasErrors('status');

        $this->delete(route('payroll.payments.destroy', PayrollPayment::firstOrFail()))->assertRedirect();
        $this->post(route('payroll.runs.reopen', $run))->assertSessionHasNoErrors();
        $this->assertSame(0.0, $this->balance('salary_expense'), 'Reopening reverses the payroll entry.');
        $this->assertSame(5000.0, EmployeeLoan::firstOrFail()->balance(), 'The installment is given back.');
        $this->assertSame(200000.0 - 5000, (float) $this->cash->fresh()->current_balance);
        $this->assertBalanced();
    }

    public function test_a_new_joiner_is_paid_for_their_days_with_provident_fund_and_income_tax(): void
    {
        PayrollSetting::where('company_id', $this->shop->company_id)->update(['pf_enabled' => true]);
        $nasrin = Employee::create(['name' => 'Nasrin', 'phone' => '01711000002', 'designation' => 'Manager', 'salary' => 120000, 'status' => 'active', 'joining_date' => '2026-08-16', 'gender' => 'female']);

        $run = app(PayrollService::class)->create($this->shop->id, Carbon::parse('2026-08-01'));
        $payslip = Payslip::where('employee_id', $nasrin->id)->firstOrFail();

        $this->assertSame(16, $payslip->payable_days);
        $this->assertSame('61935.48', $payslip->earnings_total);
        $this->assertSame(['PF' => 3716.13, 'TAX' => 6151.0], $this->itemAmounts($payslip, 'deduction'), 'Tax on 11 months to June plus two bonuses, spread over 11 months.');
        $this->assertSame('52068.35', $payslip->net_pay);

        app(PayrollService::class)->approve($run);
        $this->assertBalanced();
        $this->assertSame(3716.13 + 1800.0, $this->balance('pf_expense'), "Nasrin's and Karim's employer PF.");
        $this->assertSame(3716.13 + 1800.0, $this->balance('pf_payable'), 'The employees\' share.');
        $this->assertSame(3716.13 + 1800.0, $this->balance('pf_employer_payable'), 'The employer\'s share.');
        $this->assertSame(6151.0, $this->balance('tds_payable'));
    }

    public function test_income_tax_follows_the_slabs_exemption_and_thresholds(): void
    {
        $tax = app(IncomeTax::class);
        $year2025 = TaxYear::where('name', '2025-26')->firstOrFail();

        $this->assertSame(20003.0, $tax->annualTax($year2025, 900000));
        $this->assertSame(0.0, $tax->annualTax($year2025, 500000), 'Under the threshold after the exemption.');
        $this->assertSame(5000.0, $tax->annualTax($year2025, 540000), 'Just over the threshold pays the minimum tax.');
        $this->assertSame(3000.0, $tax->annualTax($year2025, 540000, 'general', 'elsewhere'), 'The minimum tax depends on the location.');
        $this->assertSame(15003.0, $tax->annualTax($year2025, 900000, 'female_senior'));

        // 2026-27: the slabs from the Finance Act, and an investment rebate.
        $year2026 = TaxYear::where('name', '2026-27')->firstOrFail();
        $assessment = $tax->assess($year2026, 2100000, 'general', 'dhaka_chattogram', 200000);
        $this->assertSame(1600000.0, $assessment['taxable_income'], 'A third, capped at 500,000, is exempt.');
        $this->assertSame(30000.0 + 60000.0 + 100000.0 + 6250.0, $assessment['slab_tax']);
        $this->assertSame(30000.0, $assessment['rebate'], 'The lowest of 3% of 1,600,000, 15% of 200,000 and 10 lakh.');
        $this->assertSame(166250.0, $assessment['annual_tax']);
        $this->assertSame('2027-28', $year2026->assessment_year);
        $this->assertSame('2026-27', PayrollSetup::incomeYearName(Carbon::parse('2026-07-01')));
    }

    public function test_the_festival_bonus_is_one_basic_for_staff_with_a_year_of_service(): void
    {
        Employee::create(['name' => 'Nasrin', 'phone' => '01711000002', 'designation' => 'Helper', 'salary' => 20000, 'status' => 'active', 'joining_date' => '2026-06-01']);

        $this->post(route('payroll.runs.store'), ['type' => 'bonus', 'month' => '2026-09', 'title' => 'Eid-ul-Adha'])->assertRedirect();
        $run = PayrollRun::where('type', 'bonus')->firstOrFail();

        $this->assertSame(1, $run->employees_count);
        $this->assertSame('18000.00', $run->net_total);

        $this->post(route('payroll.runs.store'), ['type' => 'salary', 'month' => '2026-09'])->assertRedirect();
        $this->post(route('payroll.runs.store'), ['type' => 'salary', 'month' => '2026-09'])->assertSessionHasErrors('month');
    }

    public function test_salary_changes_apply_from_their_effective_date(): void
    {
        $this->post(route('payroll.salary.revisions.store', $this->karim), ['type' => 'increment', 'effective_from' => '2026-09-01', 'new_salary' => 33000])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('33000.00', $this->karim->fresh()->salary);

        $service = app(PayrollService::class);
        $august = $service->create($this->shop->id, Carbon::parse('2026-08-01'));
        $september = $service->create($this->shop->id, Carbon::parse('2026-09-01'));

        $this->assertSame('30000.00', $august->payslips()->first()->salary);
        $this->assertSame('33000.00', $september->payslips()->first()->salary);

        $this->karim->fresh()->update(['salary' => 35000]);
        $this->assertSame(2, SalaryRevision::count(), 'A salary edited on the employee form is kept in the history.');
        $this->assertSame(['33000.00', '35000.00'], [SalaryRevision::latest('id')->first()->previous_salary, SalaryRevision::latest('id')->first()->new_salary]);
    }

    public function test_a_final_settlement_pays_the_benefit_and_recovers_what_is_owed(): void
    {
        app(LoanService::class)->issue($this->karim, $this->cash, [
            'type' => 'loan', 'amount' => 4000, 'installment' => 1000, 'issued_on' => '2026-09-01', 'deduct_from' => '2026-10-01',
        ]);

        $this->post(route('payroll.settlements.store'), ['employee_id' => $this->karim->id, 'separation_type' => 'termination', 'separation_date' => '2026-09-30'])->assertRedirect();
        $settlement = FinalSettlement::firstOrFail();
        $items = $settlement->items->pluck('amount', 'code')->map(fn ($amount) => (float) $amount)->all();

        $this->assertSame(36000.0, $items['BENEFIT'], '30 days of basic (600) for each of 2 years.');
        $this->assertSame(4000.0, $items['LOAN']);

        $notice = $settlement->items->firstWhere('code', 'NOTICE_PAY');
        $this->put(route('payroll.settlements.update', $settlement), ['amounts' => [$notice->id => 5000]])->assertRedirect();
        $settlement->refresh();
        $expectedNet = 36000 + $items['LEAVE'] + 5000 - 4000;
        $this->assertSame($expectedNet, (float) $settlement->net_pay);

        $this->post(route('payroll.settlements.finalize', $settlement))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('terminated', $this->karim->fresh()->status);
        $this->assertSame('closed', EmployeeLoan::firstOrFail()->status);
        $this->assertSame(0.0, $this->balance('employee_advances'));
        $this->assertSame($expectedNet, $this->balance('salary_payable'));
        $this->assertBalanced();

        $this->post(route('payroll.settlements.pay', $settlement), ['account_id' => $this->cash->id, 'amount' => $expectedNet, 'paid_on' => '2026-09-30'])->assertRedirect();
        $this->assertSame(0.0, $this->balance('salary_payable'));
        $this->assertSame(0.0, $settlement->fresh()->due());
        $this->assertBalanced();
    }

    public function test_the_payroll_screens_render_and_need_permission(): void
    {
        $service = app(PayrollService::class);
        $run = $service->create($this->shop->id, Carbon::parse('2026-08-01'));
        $payslip = $run->payslips()->first();

        foreach ([
            route('payroll.runs.index'), route('payroll.runs.show', $run), route('payroll.runs.print', $run), route('payroll.payslips.show', $payslip),
            route('payroll.loans.index'), route('payroll.settlements.index'), route('payroll-setup.index'), route('payroll.tax.index'), route('payroll.salary.show', $this->karim),
            route('employees.profile.edit', $this->karim),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $service->approve($run);
        $this->get(route('payroll.runs.show', $run))->assertOk()->assertSee('Pay Everyone');

        $staff = User::factory()->create(['shop_id' => $this->shop->id]);
        $staff->shops()->updateExistingPivot($this->shop->id, ['role' => 'Cashier', 'is_owner' => false]);
        setPermissionsTeamId($this->shop->id);
        $cashier = Role::create(['shop_id' => $this->shop->id, 'name' => 'Cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions(['employees.view']);
        $staff->assignRole($cashier);
        setPermissionsTeamId(null);

        $this->actingAs($staff)->get(route('payroll.runs.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('payroll.payslips.show', $payslip))->assertForbidden();
    }

    public function test_setup_saves_rules_components_and_tax_years(): void
    {
        $this->put(route('payroll-setup.settings.update'), [
            'overtime_enabled' => 1, 'overtime_multiplier' => 2, 'overtime_hours_base' => 208, 'attendance_deductions' => 1, 'absence_deduction_basis' => 'gross',
            'late_days_per_deduction' => 3, 'pf_enabled' => 1, 'pf_employee_percent' => 8, 'pf_employer_percent' => 8, 'pf_eligible_after_months' => 12,
            'pf_employer_vesting_years' => 5, 'tax_enabled' => 0, 'festival_bonus_percent' => 50, 'festival_bonus_min_months' => 6,
            'salary_change_policy' => 'full_month', 'default_tax_location' => 'elsewhere', 'festival_bonuses_per_year' => 2,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(8.0, PayrollSetting::firstOrFail()->pf_employee_percent);

        $this->post(route('payroll-setup.components.store'), ['name' => 'Mobile', 'code' => 'MOBILE', 'type' => 'earning', 'calculation' => 'fixed', 'value' => 500, 'is_taxable' => 1, 'is_active' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->get(route('payroll.salary.show', $this->karim))->assertOk()->assertSee('Mobile');

        $this->post(route('payroll-setup.tax-years.store'), [
            'name' => '2027-28', 'assessment_year' => '2028-29', 'starts_on' => '2027-07-01', 'ends_on' => '2028-06-30',
            'thresholds' => ['general' => 400000, 'female_senior' => 450000, 'disabled' => 525000, 'freedom_fighter' => 550000, 'july_fighter' => 550000],
            'exemption_percent' => 33.33, 'exemption_cap' => 500000, 'minimum_taxes' => ['dhaka_chattogram' => 5000, 'other_city' => 4000, 'elsewhere' => 3000],
            'rebate_income_percent' => 3, 'rebate_investment_percent' => 15, 'rebate_cap' => 1000000,
            'slab_widths' => [300000, 400000, ''], 'slab_rates' => [10, 20, 30],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame([[300000, 10], [400000, 20], [null, 30]], TaxYear::where('name', '2027-28')->firstOrFail()->slabs);
    }

    public function test_an_employee_with_payroll_records_cannot_be_deleted(): void
    {
        app(PayrollService::class)->create($this->shop->id, Carbon::parse('2026-08-01'));

        $this->delete(route('employees.destroy', $this->karim))->assertSessionHasErrors('employee');
        $this->assertNotNull($this->karim->fresh());
    }

    public function test_a_payment_cannot_exceed_what_is_due(): void
    {
        $service = app(PayrollService::class);
        $run = $service->create($this->shop->id, Carbon::parse('2026-08-01'));
        $payslip = $run->payslips()->first();

        try {
            $service->pay($payslip, $this->cash, 100, now());
            $this->fail('A draft payroll cannot be paid.');
        } catch (ValidationException) {
        }

        $service->approve($run);
        $this->expectException(ValidationException::class);
        $service->pay($payslip->fresh(), $this->cash, (float) $payslip->net_pay + 1, now());
    }

    /**
     * Every day of August 2026: Fridays off, the rest present unless listed.
     *
     * @param  list<string>  $absent
     * @param  list<string>  $late
     * @param  array<string, int>  $overtime
     */
    public function test_an_employee_sees_their_salary_payslips_and_payments(): void
    {
        $user = User::factory()->create(['shop_id' => $this->shop->id]);
        $this->karim->update(['user_id' => $user->id]);
        $rahim = Employee::create(['name' => 'Rahim', 'phone' => '01711000002', 'designation' => 'Helper', 'salary' => 20000, 'status' => 'active', 'joining_date' => '2024-01-01']);

        $service = app(PayrollService::class);
        $run = $service->create($this->shop->id, Carbon::parse('2026-08-01'));
        $payslip = Payslip::where('employee_id', $this->karim->id)->firstOrFail();
        $rahimsPayslip = Payslip::where('employee_id', $rahim->id)->firstOrFail();

        // A draft run isn't theirs to see yet.
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('href="'.route('my.salary.index').'"', false);
        $this->get(route('my.salary.index'))->assertOk()->assertSee('30,000.00')->assertSee('No payslips yet');
        $this->get(route('my.salary.payslip', $payslip->id))->assertNotFound();

        $service->approve($run);
        $service->pay($payslip->fresh(), $this->cash, 10000, Carbon::parse('2026-09-01'));

        $this->get(route('my.salary.index'))->assertOk()
            ->assertSee('August 2026')
            ->assertSee('Partly paid')
            ->assertSee('10,000.00')
            ->assertSee('01 Sep, 2026')
            ->assertSee('href="'.route('my.salary.payslip', $payslip->id).'"', false);
        $this->get(route('my.salary.payslip', $payslip->id))->assertOk()->assertSee('Karim')->assertDontSee('Rahim');

        // Their employment history: joining to the current position.
        $senior = Designation::withoutGlobalScopes()->create(['company_id' => $this->shop->company_id, 'name' => 'Senior Cashier']);
        $this->actingAs(User::where('shop_id', $this->shop->id)->whereKeyNot($user->id)->firstOrFail());
        app(SalaryStructure::class)->revise($this->karim->fresh(), ['effective_from' => '2026-09-01', 'new_salary' => 35000, 'type' => 'promotion', 'designation_id' => $senior->id]);
        app(SalaryStructure::class)->revise($this->karim->fresh(), ['effective_from' => '2027-01-01', 'new_salary' => 38000, 'type' => 'increment']);

        $history = app(SalaryStructure::class)->employmentHistory($this->karim->fresh());
        $this->assertSame(
            [['01/01/2024', 'Cashier', 30000.0, 'joining', false], ['01/09/2026', 'Senior Cashier', 35000.0, 'promotion', true], ['01/01/2027', 'Senior Cashier', 38000.0, 'increment', false]],
            array_map(fn (array $row) => [$row['date']->format('d/m/Y'), $row['designation'], $row['salary'], $row['type'], $row['is_current']], $history),
        );
        $this->actingAs($user->fresh())->get(route('my.salary.index'))->assertOk()
            ->assertSee('Employment History')->assertSeeInOrder(['01/01/2024', 'Cashier', '30,000/=', '01/09/2026', 'Senior Cashier', '35,000/=', 'Upcoming']);

        // Only their own.
        $this->get(route('my.salary.payslip', $rahimsPayslip->id))->assertNotFound();
        $this->actingAs(User::factory()->create(['shop_id' => $this->shop->id]))->get(route('my.salary.index'))->assertForbidden();
    }

    private function attendanceForAugust(Employee $employee, array $absent = [], array $late = [], array $overtime = []): void
    {
        for ($day = Carbon::parse('2026-08-01'); $day->month === 8; $day->addDay()) {
            $date = $day->toDateString();

            Attendance::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'shop_id' => $employee->shop_id,
                'date' => $date,
                'status' => match (true) {
                    $day->isFriday() => 'weekend',
                    in_array($date, $absent, true) => 'absent',
                    in_array($date, $late, true) => 'late',
                    default => 'present',
                },
                'overtime_minutes' => $overtime[$date] ?? 0,
            ]);
        }
    }

    /**
     * @return array<string, float>
     */
    private function itemAmounts(Payslip $payslip, string $type): array
    {
        return $payslip->items()->where('type', $type)->get()->mapWithKeys(fn ($item) => [$item->code => (float) $item->amount])->all();
    }

    private function balance(string $key): float
    {
        $chart = app(ChartOfAccounts::class);
        $account = $chart->account($this->shop->company_id, $key);
        $row = app(LedgerService::class)->totalsByAccount($this->shop->company_id)[$account->id] ?? null;

        return $row ? round($account->normalBalance((float) $row->debit, (float) $row->credit), 2) : 0.0;
    }

    private function assertBalanced(): void
    {
        $totals = app(LedgerService::class)->totalsByAccount($this->shop->company_id);

        $this->assertSame(round((float) $totals->sum('debit'), 2), round((float) $totals->sum('credit'), 2), 'The ledger balances.');
    }
}
