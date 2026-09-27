<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\LedgerAccount;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountingScreensTest extends TestCase
{
    use RefreshDatabase;

    private Shop $dhaka;

    private Shop $ctg;

    private User $admin;

    private ChartOfAccounts $chart;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $company = Company::factory()->create();
        $this->dhaka = Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->dhaka, Features::keys());

        $this->admin = User::factory()->create(['shop_id' => $this->dhaka->id]);
        $this->admin->syncRoles(['Admin']);
        $this->actingAs($this->admin);
        $this->chart = app(ChartOfAccounts::class);
    }

    public function test_the_opening_balance_is_previewed_and_posted_once(): void
    {
        Account::withoutGlobalScopes()->create(['shop_id' => $this->dhaka->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active', 'current_balance' => 8000]);

        $this->get(route('accounting-setup.index'))->assertOk()->assertSee('8,000.00')->assertSee('Opening Balance Equity');

        $response = $this->post(route('accounting-setup.opening-balance'), ['opening_date' => now()->toDateString()]);
        $entry = JournalEntry::firstOrFail();
        $response->assertRedirect(route('journal-entries.show', $entry));
        $this->assertSame('OPENING-BALANCE', $entry->reference);

        $this->post(route('accounting-setup.opening-balance'), ['opening_date' => now()->toDateString()])->assertSessionHasErrors('opening');
        $this->get(route('accounting-setup.index'))->assertOk()->assertSee('ledger is running');
    }

    public function test_the_chart_can_be_extended_but_standard_accounts_keep_their_code(): void
    {
        $this->get(route('ledger-accounts.index'))->assertOk()->assertSee('Accounts Receivable')->assertSee('Cost of Goods Sold');
        $expenses = $this->chart->account($this->dhaka->company_id, 'expenses');

        $this->post(route('ledger-accounts.store'), ['parent_id' => $expenses->id, 'code' => '5800', 'name' => 'Shop Rent'])
            ->assertRedirect(route('ledger-accounts.index'));
        $this->assertDatabaseHas('ledger_accounts', ['code' => '5800', 'name' => 'Shop Rent', 'type' => 'expense', 'company_id' => $this->dhaka->company_id]);
        $this->post(route('ledger-accounts.store'), ['parent_id' => $expenses->id, 'code' => '5800', 'name' => 'Duplicate'])->assertSessionHasErrors('code');

        $sales = $this->chart->account($this->dhaka->company_id, 'sales');
        $this->put(route('ledger-accounts.update', $sales), ['code' => '9999', 'name' => 'Retail Sales'])->assertRedirect();
        $this->assertSame('4100', $sales->fresh()->code);
        $this->assertSame('Retail Sales', $sales->fresh()->name);
    }

    public function test_a_manual_entry_is_posted_only_when_balanced_and_can_be_reversed(): void
    {
        $capital = $this->chart->account($this->dhaka->company_id, 'owners_capital');
        $cash = $this->chart->forMoneyAccount(Account::withoutGlobalScopes()->create(['shop_id' => $this->dhaka->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active']));

        $this->get(route('journal-entries.create'))->assertOk();

        $this->post(route('journal-entries.store'), [
            'entry_date' => now()->toDateString(),
            'narration' => 'Owner brings cash',
            'lines' => [
                ['ledger_account_id' => $cash->id, 'debit' => 5000],
                ['ledger_account_id' => $capital->id, 'credit' => 4000],
            ],
        ])->assertSessionHasErrors('lines');

        $group = $this->chart->account($this->dhaka->company_id, 'assets');
        $this->post(route('journal-entries.store'), [
            'entry_date' => now()->toDateString(),
            'narration' => 'To a group',
            'lines' => [['ledger_account_id' => $group->id, 'debit' => 5000], ['ledger_account_id' => $capital->id, 'credit' => 5000]],
        ])->assertSessionHasErrors('lines.0.ledger_account_id');

        $this->post(route('journal-entries.store'), [
            'entry_date' => now()->toDateString(),
            'narration' => 'Owner brings cash',
            'lines' => [['ledger_account_id' => $cash->id, 'debit' => 5000], ['ledger_account_id' => $capital->id, 'credit' => 5000]],
        ])->assertRedirect();

        $entry = JournalEntry::firstOrFail();
        $this->assertSame($this->dhaka->id, $entry->lines->first()->shop_id);
        $this->get(route('journal-entries.index'))->assertOk()->assertSee($entry->number);
        $this->get(route('journal-entries.show', $entry))->assertOk()->assertSee('5,000.00');

        $this->post(route('journal-entries.reverse', $entry))->assertRedirect();
        $this->assertTrue($entry->fresh()->isReversed());
        $this->assertSame(2, JournalEntry::count());
    }

    public function test_reports_read_the_ledger_for_the_company_or_one_shop(): void
    {
        $companyId = $this->dhaka->company_id;
        $post = fn (Shop $shop, string $debitKey, string $creditKey, float $amount) => app(LedgerService::class)->post($companyId, now(), [
            ['account' => $this->chart->account($companyId, $debitKey), 'debit' => $amount],
            ['account' => $this->chart->account($companyId, $creditKey), 'credit' => $amount],
        ], 'Test', $shop->id);

        $post($this->dhaka, 'accounts_receivable', 'sales', 1000);
        $post($this->dhaka, 'cogs', 'inventory', 600);
        $post($this->ctg, 'accounts_receivable', 'sales', 500);
        $post($this->ctg, 'inventory', 'owners_capital', 2000);

        $this->get(route('accounting-reports.trial-balance'))->assertOk()->assertSee('3,500.00');
        $this->get(route('accounting-reports.profit-loss'))->assertOk()->assertSee('Net Profit')->assertSee('900.00');
        $this->get(route('accounting-reports.profit-loss', ['shop_id' => $this->ctg->id]))->assertOk()->assertSee('500.00')->assertDontSee('600.00');
        $this->get(route('accounting-reports.balance-sheet'))->assertOk()->assertDontSee('Assets do not equal');

        $receivable = $this->chart->account($companyId, 'accounts_receivable');
        $this->get(route('accounting-reports.general-ledger', ['account_id' => $receivable->id]))
            ->assertOk()
            ->assertSeeInOrder(['1,000.00', '500.00', '1,500.00']);
    }

    public function test_a_closed_fiscal_year_refuses_new_entries(): void
    {
        $this->chart->ensureFor($this->dhaka->company_id);
        $year = FiscalYear::firstOrFail();

        $this->post(route('accounting-setup.fiscal-years.toggle', $year))->assertRedirect();
        $this->assertTrue($year->fresh()->is_closed);

        $capital = $this->chart->account($this->dhaka->company_id, 'owners_capital');
        $receivable = $this->chart->account($this->dhaka->company_id, 'accounts_receivable');
        $this->post(route('journal-entries.store'), [
            'entry_date' => now()->toDateString(),
            'narration' => 'Late entry',
            'lines' => [['ledger_account_id' => $receivable->id, 'debit' => 100], ['ledger_account_id' => $capital->id, 'credit' => 100]],
        ])->assertSessionHasErrors('entry_date');

        $this->post(route('accounting-setup.fiscal-years.store'))->assertRedirect();
        $this->assertSame(2, FiscalYear::count());
    }

    public function test_staff_without_accounting_permission_are_kept_out(): void
    {
        $staff = User::factory()->create(['shop_id' => $this->dhaka->id]);
        $staff->shops()->updateExistingPivot($this->dhaka->id, ['role' => 'Cashier', 'is_owner' => false]);
        setPermissionsTeamId($this->dhaka->id);
        $cashier = Role::create(['shop_id' => $this->dhaka->id, 'name' => 'Cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions(['sales.view']);
        $staff->assignRole($cashier);
        setPermissionsTeamId(null);

        $this->actingAs($staff)->get(route('ledger-accounts.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('journal-entries.create'))->assertForbidden();
        $this->assertSame(0, LedgerAccount::withoutGlobalScopes()->where('code', '5800')->count());
    }
}
