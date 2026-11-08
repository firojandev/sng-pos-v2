<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\OpeningBalanceService;
use Modules\Company\Models\Company;
use Modules\Core\Services\DatabaseBackupService;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\LoyaltyProgram;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Payroll\Services\PayrollService;
use Modules\Payroll\Services\PayrollSetup;
use Modules\Shop\Models\Shop;
use Tests\TestCase;

/**
 * The test database runs inside a transaction, where SQLite ignores the
 * restore's "foreign keys off", so rows are removed in key order here and
 * shared rows aren't tied to a shop row the restore replaces.
 */
class ShopBackupCoverageTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseBackupService $backups;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-27 12:00:00');
        $this->backups = app(DatabaseBackupService::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->backups->getBackups() as $backup) {
            $this->backups->deleteBackup($backup['filename']);
        }
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_single_shop_company_is_restored_with_its_ledger_loyalty_hr_and_payroll(): void
    {
        $shop = Shop::create(['name' => 'Karim Store', 'slug' => 'karim-store', 'status' => 'active']);
        $companyId = $shop->company_id;
        Account::withoutGlobalScopes()->create(['shop_id' => $shop->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active', 'opening_balance' => 5000, 'current_balance' => 5000]);
        Customer::withoutGlobalScopes()->create(['company_id' => $companyId, 'shop_id' => $shop->id, 'name' => 'Rahim', 'phone' => '01711000000', 'status' => 'active']);
        LoyaltyProgram::withoutGlobalScopes()->create(['company_id' => $companyId, 'spend_amount' => 100, 'points' => 1, 'point_value' => 1]);
        app(OpeningBalanceService::class)->post($shop->company, '2026-07-01');
        app(PayrollSetup::class)->ensureFor($companyId);
        Employee::withoutGlobalScopes()->create(['company_id' => $companyId, 'shop_id' => $shop->id, 'name' => 'Karim', 'phone' => '01711000001', 'designation' => 'Cashier', 'salary' => 20000, 'status' => 'active', 'joining_date' => '2024-01-01']);
        app(PayrollService::class)->create($shop->id, Carbon::parse('2026-08-01'));

        $tables = ['payslip_items', 'payslips', 'payroll_runs', 'journal_lines', 'journal_entries', 'accounts', 'ledger_accounts', 'loyalty_programs', 'customers', 'employees', 'salary_components', 'tax_years'];
        $before = $this->counts($tables);
        $this->assertNotContains(0, $before);

        $backup = $this->backups->createBackup('coverage');
        DB::table('ledger_accounts')->update(['parent_id' => null]);
        foreach ($tables as $table) {
            DB::table($table)->delete();
        }

        $this->backups->restoreShops($backup['filename'], [$shop->id]);

        $this->assertSame($before, $this->counts($tables));
    }

    public function test_restoring_one_shop_of_a_group_leaves_the_other_shop_and_shared_data_alone(): void
    {
        $first = Shop::create(['name' => 'Branch One', 'slug' => 'branch-one', 'status' => 'active']);
        $second = Shop::create(['name' => 'Branch Two', 'slug' => 'branch-two', 'status' => 'active', 'company_id' => $first->company_id]);
        $this->assertSame(2, Company::find($first->company_id)->shops()->count());

        $firstCash = Account::withoutGlobalScopes()->create(['shop_id' => $first->id, 'name' => 'Cash One', 'type' => 'cash', 'status' => 'active']);
        $secondCash = Account::withoutGlobalScopes()->create(['shop_id' => $second->id, 'name' => 'Cash Two', 'type' => 'cash', 'status' => 'active']);
        $customer = Customer::withoutGlobalScopes()->create(['company_id' => $first->company_id, 'shop_id' => null, 'name' => 'Shared', 'phone' => '01711000009', 'status' => 'active']);

        $backup = $this->backups->createBackup('group');

        $firstCash->update(['name' => 'Changed One']);
        $secondCash->update(['name' => 'Changed Two']);
        $customer->update(['name' => 'Changed Customer']);

        $this->backups->restoreShops($backup['filename'], [$first->id]);

        $this->assertSame('Cash One', $firstCash->fresh()->name);
        $this->assertSame('Changed Two', $secondCash->fresh()->name, 'The sister shop is untouched.');
        $this->assertSame('Changed Customer', $customer->fresh()->name, 'Company-wide customers are shared, not restored per shop.');
    }

    /**
     * @param  list<string>  $tables
     * @return array<string, int>
     */
    private function counts(array $tables): array
    {
        return collect($tables)->mapWithKeys(fn (string $table) => [$table => DB::table($table)->count()])->all();
    }
}
