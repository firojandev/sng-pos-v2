<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Models\LedgerAccount;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Services\OpeningBalanceService;
use Modules\Company\Models\Company;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Tests\TestCase;

class LedgerFoundationTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Shop $dhaka;

    private Shop $ctg;

    private ChartOfAccounts $chart;

    private LedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-09-27 10:00:00');
        $this->company = Company::factory()->create(['fiscal_year_start_month' => 7]);
        $this->dhaka = Shop::create(['company_id' => $this->company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $this->company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);
        $this->chart = app(ChartOfAccounts::class);
        $this->ledger = app(LedgerService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_every_company_gets_the_standard_chart_and_its_current_fiscal_year(): void
    {
        $this->chart->ensureFor($this->company->id);
        $this->chart->ensureFor($this->company->id);

        $this->assertSame(37, LedgerAccount::withoutGlobalScopes()->where('company_id', $this->company->id)->count());
        $sales = $this->chart->account($this->company->id, 'sales');
        $this->assertSame('4100', $sales->code);
        $this->assertSame('income', $sales->parent->system_key);

        $year = FiscalYear::withoutGlobalScopes()->where('company_id', $this->company->id)->firstOrFail();
        $this->assertSame('2026-07-01', $year->starts_on->toDateString());
        $this->assertSame('2027-06-30', $year->ends_on->toDateString());
        $this->assertSame('2026-27', $year->name);
    }

    public function test_balanced_entries_are_posted_with_sequential_numbers(): void
    {
        $first = $this->capitalInjection(5000);
        $second = $this->capitalInjection(3000);

        $this->assertSame('JV-00001', $first->number);
        $this->assertSame('JV-00002', $second->number);
        $this->assertSame(5000.0, $first->total());
    }

    public function test_entries_that_break_the_rules_are_refused(): void
    {
        $cash = $this->cashLedger($this->dhaka);
        $capital = $this->chart->account($this->company->id, 'owners_capital');
        $group = $this->chart->account($this->company->id, 'assets');
        $otherCompany = Company::factory()->create();
        $foreign = $this->chart->account($otherCompany->id, 'owners_capital');

        $cases = [
            'unbalanced' => [['account' => $cash, 'debit' => 100], ['account' => $capital, 'credit' => 90]],
            'single line' => [['account' => $cash, 'debit' => 100]],
            'group account' => [['account' => $group, 'debit' => 100], ['account' => $capital, 'credit' => 100]],
            'another company' => [['account' => $cash, 'debit' => 100], ['account' => $foreign, 'credit' => 100]],
            'both sides' => [['account' => $cash, 'debit' => 100, 'credit' => 100], ['account' => $capital, 'credit' => 100]],
        ];

        foreach ($cases as $case => $lines) {
            try {
                $this->ledger->post($this->company->id, now(), $lines);
                $this->fail("A {$case} entry should be refused.");
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }

        FiscalYear::withoutGlobalScopes()->where('company_id', $this->company->id)->update(['is_closed' => true]);
        $this->expectException(ValidationException::class);
        $this->capitalInjection(100);
    }

    public function test_reversing_posts_the_mirror_entry_once(): void
    {
        $entry = $this->capitalInjection(5000);

        $reversal = $this->ledger->reverse($entry);

        $this->assertNotNull($entry->fresh()->reversed_at);
        $this->assertSame($entry->id, $reversal->reversal_of_id);
        $totals = $this->ledger->totalsByAccount($this->company->id);
        foreach ($totals as $row) {
            $this->assertSame((float) $row->debit, (float) $row->credit);
        }

        $this->expectException(ValidationException::class);
        $this->ledger->reverse($entry->fresh());
    }

    public function test_totals_can_be_read_for_one_shop(): void
    {
        $this->capitalInjection(5000, $this->dhaka);
        $this->capitalInjection(2000, $this->ctg);

        $capital = $this->chart->account($this->company->id, 'owners_capital');

        $this->assertSame(7000.0, (float) $this->ledger->totalsByAccount($this->company->id)[$capital->id]->credit);
        $this->assertSame(2000.0, (float) $this->ledger->totalsByAccount($this->company->id, shopId: $this->ctg->id)[$capital->id]->credit);
    }

    public function test_each_money_account_gets_its_own_ledger_account(): void
    {
        $bank = Account::withoutGlobalScopes()->create(['shop_id' => $this->ctg->id, 'name' => 'City Bank', 'type' => 'bank', 'status' => 'active']);

        $ledger = $this->chart->forMoneyAccount($bank);

        $this->assertSame('City Bank — Ctg Outlet', $ledger->name);
        $this->assertSame('cash_and_bank', $ledger->parent->system_key);
        $this->assertSame($ledger->id, $bank->fresh()->ledger_account_id);
        $this->assertTrue($ledger->is($this->chart->forMoneyAccount($bank->fresh())));
    }

    public function test_the_opening_balance_starts_the_ledger_from_the_current_position_per_shop(): void
    {
        Account::withoutGlobalScopes()->create(['shop_id' => $this->dhaka->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active', 'current_balance' => 10000]);
        $customer = Customer::create(['shop_id' => $this->dhaka->id, 'name' => 'Rahim', 'opening_due' => 300, 'status' => 'active']);
        Supplier::create(['shop_id' => $this->ctg->id, 'name' => 'Pran', 'opening_due' => 1500, 'status' => 'active']);
        $branch = Branch::create(['shop_id' => $this->dhaka->id, 'name' => 'Main', 'status' => 'active']);
        $warehouse = Warehouse::create(['shop_id' => $this->dhaka->id, 'branch_id' => $branch->id, 'name' => 'Store', 'status' => 'active']);
        $category = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Grocery', 'type' => 'product']);
        $product = Product::create(['shop_id' => $this->dhaka->id, 'name' => 'Rice', 'category_id' => $category->id]);
        Batch::create(['shop_id' => $this->dhaka->id, 'warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'batch_no' => 'B-1', 'quantity' => 20, 'unit_cost' => 50]);
        Sale::create(['shop_id' => $this->dhaka->id, 'warehouse_id' => $warehouse->id, 'customer_id' => $customer->id, 'invoice_no' => 'INV-1', 'sale_date' => now()->toDateString(), 'subtotal' => 700, 'total' => 700, 'paid_amount' => 0, 'due_amount' => 700, 'payment_status' => 'due']);

        $opening = app(OpeningBalanceService::class);
        $entry = $opening->post($this->company, now());

        $totals = $this->ledger->totalsByAccount($this->company->id);
        $balanceOf = fn (string $key, ?int $shopId = null) => (function () use ($key, $shopId) {
            $account = $this->chart->account($this->company->id, $key);
            $row = $this->ledger->totalsByAccount($this->company->id, shopId: $shopId)[$account->id] ?? null;

            return $row ? $account->normalBalance((float) $row->debit, (float) $row->credit) : 0.0;
        })();

        $this->assertSame(1000.0, $balanceOf('accounts_receivable'));
        $this->assertSame(1000.0, $balanceOf('inventory'));
        $this->assertSame(1500.0, $balanceOf('accounts_payable'));
        $this->assertSame(12000.0, $balanceOf('opening_balance_equity', $this->dhaka->id));
        $this->assertSame(-1500.0, $balanceOf('opening_balance_equity', $this->ctg->id));
        $this->assertSame((float) $totals->sum('debit'), (float) $totals->sum('credit'));
        $this->assertTrue($entry->lines->contains(fn ($line) => $line->party_id === $customer->id && (float) $line->debit === 1000.0));
        $this->assertTrue($opening->isPosted($this->company->id));

        $this->expectException(ValidationException::class);
        $opening->post($this->company, now());
    }

    private function capitalInjection(float $amount, ?Shop $shop = null)
    {
        $shop ??= $this->dhaka;

        return $this->ledger->post($this->company->id, now(), [
            ['account' => $this->cashLedger($shop), 'debit' => $amount],
            ['account' => $this->chart->account($this->company->id, 'owners_capital'), 'credit' => $amount],
        ], 'Capital injection', $shop->id);
    }

    private function cashLedger(Shop $shop): LedgerAccount
    {
        $cash = Account::withoutGlobalScopes()->firstOrCreate(
            ['shop_id' => $shop->id, 'type' => 'cash'],
            ['name' => 'Cash', 'status' => 'active'],
        );

        return $this->chart->forMoneyAccount($cash);
    }
}
