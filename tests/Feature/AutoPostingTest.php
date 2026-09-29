<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Services\OpeningBalanceService;
use Modules\Accounting\Services\Reconciliation;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\LoyaltyProgram;
use Modules\Customer\Services\LoyaltyService;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockAdjustment;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutoPostingTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Warehouse $warehouse;

    private Account $cash;

    private Product $rice;

    private Customer $customer;

    private ChartOfAccounts $chart;

    private LedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create(['name' => 'Karim Store', 'slug' => 'karim-store', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->shop, Features::keys());
        $branch = Branch::create(['shop_id' => $this->shop->id, 'name' => 'Main', 'status' => 'active']);
        $this->warehouse = Warehouse::create(['shop_id' => $this->shop->id, 'branch_id' => $branch->id, 'name' => 'Store', 'status' => 'active', 'is_default' => true]);

        $admin = User::factory()->create(['shop_id' => $this->shop->id]);
        $admin->syncRoles(['Admin']);
        $this->actingAs($admin);

        $this->cash = Account::create(['shop_id' => $this->shop->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active', 'is_default' => true, 'opening_balance' => 10000, 'current_balance' => 10000]);
        $category = Category::create(['name' => 'Grocery', 'type' => 'product']);
        $this->rice = Product::create(['name' => 'Rice', 'category_id' => $category->id, 'purchase_price' => 60, 'sale_price' => 100, 'status' => 'active']);
        Batch::create(['shop_id' => $this->shop->id, 'warehouse_id' => $this->warehouse->id, 'product_id' => $this->rice->id, 'batch_no' => 'B-1', 'quantity' => 50, 'unit_cost' => 60]);
        $this->customer = Customer::create(['name' => 'Rahim', 'phone' => '01711000000', 'status' => 'active']);

        $this->chart = app(ChartOfAccounts::class);
        $this->ledger = app(LedgerService::class);
        app(OpeningBalanceService::class)->post($this->shop->company, now()->subDay());
    }

    public function test_a_sale_posts_revenue_cost_and_its_payment(): void
    {
        $this->sellRice(quantity: 5, paid: 300);

        $this->assertBalanced();
        $this->assertSame(500.0, $this->balance('sales'));
        $this->assertSame(300.0, $this->balance('cogs'));
        $this->assertSame(3000.0 - 300.0, $this->balance('inventory'));
        $this->assertSame(200.0, $this->balance('accounts_receivable'));
        $this->assertSame(10300.0, $this->moneyBalance());
    }

    public function test_editing_a_sale_replaces_its_entry_and_an_unchanged_save_does_nothing(): void
    {
        $sale = $this->sellRice(quantity: 5, paid: 500);
        $entries = JournalEntry::count();

        $sale->touch();
        $this->assertSame($entries, JournalEntry::count(), 'Nothing changed, nothing is posted.');

        $sale->update(['total' => 450, 'discount' => 50]);
        $this->assertSame($entries + 2, JournalEntry::count(), 'The old entry is reversed and a new one posted.');
        $this->assertSame(50.0, $this->balance('discounts_allowed'));
        $this->assertBalanced();

        $sale->delete();
        $this->assertSame(0.0, $this->balance('sales'));
        $this->assertBalanced();
    }

    public function test_a_purchase_posts_stock_and_the_supplier_payable_then_the_payment(): void
    {
        $supplier = Supplier::create(['name' => 'Pran', 'status' => 'active']);

        $this->post(route('purchase.store'), [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $this->rice->id, 'quantity' => 10, 'received_qty' => 10, 'purchase_price' => 70, 'sale_price' => 100, 'batch_no' => 'B-2']],
            'payments' => [['account_id' => $this->cash->id, 'method' => 'cash', 'amount' => 400]],
        ])->assertRedirect();

        $this->assertBalanced();
        $this->assertSame(3000.0 + 700.0, $this->balance('inventory'));
        $this->assertSame(300.0, $this->balance('accounts_payable'));
        $this->assertSame(9600.0, $this->moneyBalance());
    }

    public function test_expenses_income_and_owner_cash_post_against_the_money_account(): void
    {
        $service = app(AccountTransactionService::class);
        $service->recordTransaction(account: $this->cash, type: 'out', amount: 250, source: 'expense', note: 'Electricity');
        $service->recordTransaction(account: $this->cash, type: 'in', amount: 100, source: 'income', note: 'Scrap sale');
        $service->recordTransaction(account: $this->cash, type: 'in', amount: 5000, source: 'cash_in', note: 'Owner brings cash');

        $this->assertBalanced();
        $this->assertSame(250.0, $this->balance('general_expenses'));
        $this->assertSame(100.0, $this->balance('other_income'));
        $this->assertSame(5000.0, $this->balance('owners_capital'));
    }

    public function test_money_moved_between_accounts_posts_the_transfer_and_its_fee(): void
    {
        $bank = Account::create(['shop_id' => $this->shop->id, 'name' => 'City Bank', 'type' => 'bank', 'status' => 'active']);

        app(AccountTransactionService::class)->transfer($this->cash, $bank, 1000, 20, now()->toDateString());

        $this->assertBalanced();
        $this->assertSame(1000.0, $this->moneyBalance($bank));
        $this->assertSame(20.0, $this->balance('general_expenses'));
        $this->assertSame(10000.0 - 1020.0, $this->moneyBalance());
    }

    public function test_stock_adjustments_and_loyalty_points_post(): void
    {
        $batch = Batch::firstOrFail();
        StockAdjustment::create(['shop_id' => $this->shop->id, 'product_id' => $this->rice->id, 'batch_id' => $batch->id, 'type' => 'decrease', 'quantity' => 2, 'quantity_before' => 50, 'quantity_after' => 48]);
        $this->assertSame(120.0, $this->balance('stock_adjustment_loss'));

        LoyaltyProgram::create(['company_id' => $this->shop->company_id, 'is_enabled' => true, 'spend_amount' => 100, 'points_per_spend' => 1, 'point_value' => 2]);
        app(LoyaltyService::class)->enroll($this->customer);
        $this->sellRice(quantity: 5, paid: 500);

        // 500 spent earns 5 points worth 2 each
        $this->assertSame(10.0, $this->balance('loyalty_expense'));
        $this->assertSame(10.0, $this->balance('loyalty_liability'));
        $this->assertBalanced();
    }

    public function test_the_ledger_reconciles_with_the_apps_own_figures(): void
    {
        $this->sellRice(quantity: 5, paid: 300);

        $rows = collect(app(Reconciliation::class)->compare($this->shop->company_id));

        $this->assertTrue($rows->every(fn (array $row) => abs($row['difference']) < 0.01), $rows->toJson());
        $this->get(route('accounting-setup.index'))->assertOk()->assertSee('Accounts Receivable');
    }

    public function test_records_before_the_go_live_date_are_already_in_the_opening_balance(): void
    {
        $entries = JournalEntry::count();

        Sale::create(['shop_id' => $this->shop->id, 'warehouse_id' => $this->warehouse->id, 'invoice_no' => 'OLD-1', 'sale_date' => now()->subDays(10)->toDateString(), 'subtotal' => 100, 'total' => 100, 'paid_amount' => 0, 'due_amount' => 100, 'payment_status' => 'due']);

        $this->assertSame($entries, JournalEntry::count());
    }

    private function sellRice(int $quantity, float $paid): Sale
    {
        $this->post(route('sales.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $this->rice->id, 'quantity' => $quantity, 'unit_price' => 100, 'discount' => 0]],
            'payments' => [['account_id' => $this->cash->id, 'method' => 'cash', 'amount' => $paid]],
        ])->assertRedirect(route('sales.index'));

        return Sale::latest('id')->firstOrFail();
    }

    private function balance(string $key): float
    {
        $account = $this->chart->account($this->shop->company_id, $key);
        $row = $this->ledger->totalsByAccount($this->shop->company_id)[$account->id] ?? null;

        return $row ? $account->normalBalance((float) $row->debit, (float) $row->credit) : 0.0;
    }

    private function moneyBalance(?Account $account = null): float
    {
        $ledgerAccount = $this->chart->forMoneyAccount(($account ?? $this->cash)->fresh());
        $row = $this->ledger->totalsByAccount($this->shop->company_id)[$ledgerAccount->id] ?? null;

        return $row ? $ledgerAccount->normalBalance((float) $row->debit, (float) $row->credit) : 0.0;
    }

    private function assertBalanced(): void
    {
        $totals = $this->ledger->totalsByAccount($this->shop->company_id);

        $this->assertSame(round((float) $totals->sum('debit'), 2), round((float) $totals->sum('credit'), 2), 'The ledger balances.');
    }
}
