<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\Category;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\FinanceManagement\Models\Asset;
use Modules\FinanceManagement\Models\Debt;
use Modules\FinanceManagement\Models\Lend;
use Modules\FinanceManagement\Models\SecurityMoney;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportFinancialPositionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'Test Mart',
            'slug' => 'test-mart',
            'status' => 'active',
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        } else {
            $this->subscribeShopToFeatures($this->shop, Features::keys());
        }

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);
    }

    /**
     * Seed a consistent set of fixtures across every asset/liability source
     * so both reports' figures can be checked against known totals.
     */
    private function seedFixtures(): void
    {
        Account::create([
            'shop_id' => $this->shop->id, 'name' => 'Main Cash', 'type' => 'cash',
            'current_balance' => 10000, 'is_default' => true, 'status' => 'active',
        ]);
        Account::create([
            'shop_id' => $this->shop->id, 'name' => 'Bank Account', 'type' => 'bank',
            'current_balance' => 5000, 'status' => 'active',
        ]);
        Account::create([
            'shop_id' => $this->shop->id, 'name' => 'bKash', 'type' => 'mfs',
            'current_balance' => 2000, 'status' => 'active',
        ]);

        Asset::create(['shop_id' => $this->shop->id, 'name' => 'Fixtures', 'amount' => 10000]);

        SecurityMoney::create([
            'shop_id' => $this->shop->id, 'receiver_name' => 'Landlord',
            'date' => now()->toDateString(), 'amount' => 2000, 'status' => 'paid',
        ]);
        SecurityMoney::create([
            'shop_id' => $this->shop->id, 'receiver_name' => 'Tenant',
            'date' => now()->toDateString(), 'amount' => 500, 'status' => 'received',
        ]);

        Lend::create([
            'shop_id' => $this->shop->id, 'borrower_name' => 'Karim',
            'date' => now()->toDateString(), 'amount' => 1500, 'status' => 'due',
        ]);
        Lend::create([
            'shop_id' => $this->shop->id, 'borrower_name' => 'Rahim',
            'date' => now()->toDateString(), 'amount' => 999, 'status' => 'received',
        ]);

        Debt::create([
            'shop_id' => $this->shop->id, 'lender_name' => 'Bank Loan',
            'date' => now()->toDateString(), 'amount' => 4000, 'status' => 'unpaid',
        ]);
        Debt::create([
            'shop_id' => $this->shop->id, 'lender_name' => 'Old Loan',
            'date' => now()->toDateString(), 'amount' => 999, 'status' => 'paid',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id, 'type' => 'product', 'name' => 'General',
        ]);
        $product = Product::create([
            'shop_id' => $this->shop->id, 'name' => 'Test Product', 'category_id' => $category->id,
            'purchase_price' => 50, 'sale_price' => 80,
        ]);
        Batch::create([
            'shop_id' => $this->shop->id, 'product_id' => $product->id,
            'batch_no' => 'B1', 'quantity' => 20,
        ]);

        $customer = Customer::create([
            'shop_id' => $this->shop->id, 'name' => 'John Customer', 'opening_due' => 300,
        ]);
        Sale::create([
            'shop_id' => $this->shop->id, 'customer_id' => $customer->id,
            'sale_date' => now()->toDateString(), 'total' => 700, 'due_amount' => 700,
        ]);

        $supplier = Supplier::create([
            'shop_id' => $this->shop->id, 'name' => 'Acme Supplier', 'opening_due' => 200,
        ]);
        Purchase::create([
            'shop_id' => $this->shop->id, 'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(), 'total' => 800, 'due_amount' => 800,
        ]);
    }

    public function test_financial_position_computes_all_six_figures_correctly(): void
    {
        $this->seedFixtures();

        $response = $this->actingAs($this->user)->get(route('reports.financial-position'));
        $response->assertOk();

        // Fixed Assets (10000) + Security Money paid only (2000) — the received one (500) excluded.
        $response->assertViewHas('totalAssetsWithSecurity', fn ($v) => abs($v - 12000) < 0.01);
        // 20 qty * 50 purchase price.
        $response->assertViewHas('stockValue', fn ($v) => abs($v - 1000) < 0.01);
        // 300 opening due + 700 sale due.
        $response->assertViewHas('receivable', fn ($v) => abs($v - 1000) < 0.01);
        // 200 opening due + 800 purchase due.
        $response->assertViewHas('payable', fn ($v) => abs($v - 1000) < 0.01);
        // Only the 'due' lend (1500) counts, not the 'received' one (999).
        $response->assertViewHas('lendOutstanding', fn ($v) => abs($v - 1500) < 0.01);
        // 10000 + 5000 + 2000 opening balances, plus the net ledger effect the
        // Debt/Lend/SecurityMoney observers post to the default cash account
        // (no account_id given on any fixture): -2000 +500 -1500 +4000 = +1000.
        $response->assertViewHas('cashAndBank', fn ($v) => abs($v - 18000) < 0.01);
        // (12000 + 1000 + 1000 + 1500 + 18000) - 1000 payable.
        $response->assertViewHas('netPosition', fn ($v) => abs($v - 32500) < 0.01);
    }

    public function test_balance_sheet_assets_equal_liabilities_plus_equity(): void
    {
        $this->seedFixtures();

        $response = $this->actingAs($this->user)->get(route('reports.balance-sheet'));
        $response->assertOk();

        // Cash&Bank (18000, see financial-position test) + receivable (1000) + lend
        // receivable (1500) + security paid (2000) + stock (1000) + fixed assets (10000).
        $response->assertViewHas('totalAssets', fn ($v) => abs($v - 33500) < 0.01);
        // Supplier due (1000) + unpaid debt only (4000, not the paid 999) + security money received (500).
        $response->assertViewHas('totalLiabilities', fn ($v) => abs($v - 5500) < 0.01);
        $response->assertViewHas('equity', fn ($v) => abs($v - 28000) < 0.01);

        // The balancing identity must always hold, since equity is a plug figure.
        $totalAssets = $response->viewData('totalAssets');
        $totalLiabilities = $response->viewData('totalLiabilities');
        $equity = $response->viewData('equity');
        $this->assertEqualsWithDelta($totalAssets, $totalLiabilities + $equity, 0.01);
    }

    public function test_as_of_past_date_reconstructs_history_and_excludes_later_activity(): void
    {
        $pastDate = now()->subDays(10)->startOfDay();

        $account = Account::create([
            'shop_id' => $this->shop->id, 'name' => 'Main Cash', 'type' => 'cash',
            'current_balance' => 5000, 'is_default' => true, 'status' => 'active',
        ]);

        // Opening balance of 5000, dated at the very start of $pastDate.
        AccountTransaction::withoutGlobalScopes()->create([
            'shop_id' => $this->shop->id, 'account_id' => $account->id,
            'type' => 'in', 'amount' => 5000, 'balance_after' => 5000,
            'source' => 'opening_balance', 'occurred_at' => $pastDate,
        ]);

        // Recorded chronologically (not backdated after later activity already
        // exists) so the ledger's running balance_after stays historically
        // consistent — the "as of" reconstruction can only trust a ledger
        // that was built in the order things actually happened.
        // Lend dated $pastDate: posts an OUT while current_balance is still
        // 5000, correctly landing later in that same day (5000 -> 4000).
        Lend::create([
            'shop_id' => $this->shop->id, 'account_id' => $account->id, 'borrower_name' => 'Old Lend',
            'date' => $pastDate->toDateString(), 'amount' => 1000, 'status' => 'due',
        ]);

        // A same-day-as-today deposit brings the balance to 7000 — must not leak into the past view.
        AccountTransaction::withoutGlobalScopes()->create([
            'shop_id' => $this->shop->id, 'account_id' => $account->id,
            'type' => 'in', 'amount' => 3000, 'balance_after' => 7000,
            'source' => 'manual_adjustment', 'occurred_at' => now(),
        ]);
        $account->update(['current_balance' => 7000]);

        // Lend dated today: posts an OUT against today's 7000 balance -> 5000.
        Lend::create([
            'shop_id' => $this->shop->id, 'account_id' => $account->id, 'borrower_name' => 'New Lend',
            'date' => now()->toDateString(), 'amount' => 2000, 'status' => 'due',
        ]);

        $category = Category::create(['shop_id' => $this->shop->id, 'type' => 'product', 'name' => 'General']);
        $product = Product::create([
            'shop_id' => $this->shop->id, 'name' => 'Test Product', 'category_id' => $category->id,
            'purchase_price' => 50, 'sale_price' => 80,
        ]);
        $batch = Batch::create([
            'shop_id' => $this->shop->id, 'product_id' => $product->id,
            'batch_no' => 'B1', 'quantity' => 20,
        ]);
        // Stock was 5 units as of $pastDate; a later +15 purchase brought it to today's 20.
        StockMovement::create([
            'shop_id' => $this->shop->id, 'product_id' => $product->id, 'batch_id' => $batch->id,
            'type' => 'purchase', 'quantity_change' => 15, 'quantity_before' => 5, 'quantity_after' => 20,
            'created_at' => now(),
        ]);

        $pastResponse = $this->actingAs($this->user)
            ->get(route('reports.financial-position', ['as_of' => $pastDate->toDateString()]));
        $pastResponse->assertOk();
        // Latest transaction on $pastDate is the Old Lend's posting: 5000 - 1000 = 4000.
        $pastResponse->assertViewHas('cashAndBank', fn ($v) => abs($v - 4000) < 0.01);
        $pastResponse->assertViewHas('stockValue', fn ($v) => abs($v - 250) < 0.01);
        // Only the Old Lend (dated $pastDate) counts; New Lend (dated today) is excluded.
        $pastResponse->assertViewHas('lendOutstanding', fn ($v) => abs($v - 1000) < 0.01);

        $todayResponse = $this->actingAs($this->user)->get(route('reports.financial-position'));
        $todayResponse->assertOk();
        // Final live balance: 5000 - 1000 (Old Lend) + 3000 (deposit) - 2000 (New Lend) = 5000.
        $todayResponse->assertViewHas('cashAndBank', fn ($v) => abs($v - 5000) < 0.01);
        $todayResponse->assertViewHas('stockValue', fn ($v) => abs($v - 1000) < 0.01);
        // Both lends count for the default/today view.
        $todayResponse->assertViewHas('lendOutstanding', fn ($v) => abs($v - 3000) < 0.01);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $limitedRole = Role::create(['name' => 'NoReports', 'guard_name' => 'web']);
        $limitedUser = User::create([
            'name' => 'Limited User',
            'email' => 'limited@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $limitedUser->syncRoles([$limitedRole]);

        $this->actingAs($limitedUser)->get(route('reports.financial-position'))->assertForbidden();
        $this->actingAs($limitedUser)->get(route('reports.balance-sheet'))->assertForbidden();
    }

    public function test_shop_without_feature_is_forbidden_even_with_permission(): void
    {
        $shopWithoutFeature = Shop::create([
            'name' => 'No Reports Shop',
            'slug' => 'no-reports-shop',
            'status' => 'active',
        ]);
        $this->subscribeShopToFeatures($shopWithoutFeature, ['sales', 'purchase']);

        $adminRole = Role::where('name', 'Admin')->where('guard_name', 'web')->first();
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@no-reports.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $shopWithoutFeature->id,
        ]);
        $user->syncRoles([$adminRole]);

        $this->actingAs($user)->get(route('reports.financial-position'))->assertForbidden();
        $this->actingAs($user)->get(route('reports.balance-sheet'))->assertForbidden();
    }
}
