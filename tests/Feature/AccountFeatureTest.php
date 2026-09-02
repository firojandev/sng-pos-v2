<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransfer;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'Test Mart',
            'slug' => 'test-mart',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);
    }

    public function test_can_list_accounts(): void
    {
        $cash = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash',
            'type' => 'cash',
            'opening_balance' => 1000,
            'current_balance' => 1000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('accounts.index'));

        $response->assertOk();
        $response->assertSee('Main Cash');
        $response->assertSee('1,000.00');
    }

    public function test_can_create_bank_account(): void
    {
        $response = $this->actingAs($this->user)->post(route('accounts.store'), [
            'name' => 'City Bank Current',
            'type' => 'bank',
            'bank_name' => 'City Bank',
            'account_number' => '1102993848',
            'branch_name' => 'Gulshan Branch',
            'opening_balance' => 50000,
            'is_default' => 0,
            'status' => 'active',
            'note' => 'Main operative account',
        ]);

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', [
            'shop_id' => $this->shop->id,
            'name' => 'City Bank Current',
            'type' => 'bank',
            'current_balance' => 50000,
        ]);

        $this->assertDatabaseHas('account_transactions', [
            'shop_id' => $this->shop->id,
            'type' => 'in',
            'amount' => 50000,
            'source' => 'opening_balance',
        ]);
    }

    public function test_setting_default_account_unsets_previous_default(): void
    {
        $cash1 = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash 1',
            'type' => 'cash',
            'is_default' => true,
            'status' => 'active',
        ]);

        $cash2 = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash 2',
            'type' => 'cash',
            'is_default' => false,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)->post(route('accounts.set-default', $cash2));

        $this->assertFalse($cash1->fresh()->is_default);
        $this->assertTrue($cash2->fresh()->is_default);
    }

    public function test_can_transfer_funds_between_accounts_and_records_ledger(): void
    {
        $cash = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Counter Cash',
            'type' => 'cash',
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $bank = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'DBBL Bank',
            'type' => 'bank',
            'opening_balance' => 20000,
            'current_balance' => 20000,
            'is_default' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->post(route('account-transfers.store'), [
            'from_account_id' => $cash->id,
            'to_account_id' => $bank->id,
            'amount' => 3000,
            'charge' => 10,
            'transfer_date' => now()->toDateString(),
            'note' => 'Deposit counter cash to bank',
        ]);

        $response->assertRedirect(route('account-transfers.index'));

        // Cash balance should be 10000 - 3000 - 10 = 6990
        $this->assertEquals(6990, (float) $cash->fresh()->current_balance);
        // Bank balance should be 20000 + 3000 = 23000
        $this->assertEquals(23000, (float) $bank->fresh()->current_balance);

        $this->assertDatabaseHas('account_transfers', [
            'shop_id' => $this->shop->id,
            'from_account_id' => $cash->id,
            'to_account_id' => $bank->id,
            'amount' => 3000,
            'charge' => 10,
        ]);

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $cash->id,
            'type' => 'out',
            'amount' => 3010,
            'source' => 'transfer_out',
        ]);

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $bank->id,
            'type' => 'in',
            'amount' => 3000,
            'source' => 'transfer_in',
        ]);
    }

    public function test_cannot_transfer_more_than_available_balance(): void
    {
        $cash = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Low Cash',
            'type' => 'cash',
            'opening_balance' => 500,
            'current_balance' => 500,
            'status' => 'active',
        ]);

        $bank = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Bank',
            'type' => 'bank',
            'opening_balance' => 0,
            'current_balance' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->post(route('account-transfers.store'), [
            'from_account_id' => $cash->id,
            'to_account_id' => $bank->id,
            'amount' => 1000,
            'transfer_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_expense_deducts_account_balance_and_records_transaction(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Expense Cash',
            'type' => 'cash',
            'opening_balance' => 5000,
            'current_balance' => 5000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $category = ExpenseCategory::create([
            'shop_id' => $this->shop->id,
            'name' => 'Utility',
        ]);

        $this->actingAs($this->user)->post(route('expense.store'), [
            'account_id' => $account->id,
            'expense_category_id' => $category->id,
            'title' => 'Electricity Bill',
            'amount' => 1200,
            'expense_date' => now()->toDateString(),
        ]);

        $this->assertEquals(3800, (float) $account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'type' => 'out',
            'amount' => 1200,
            'source' => 'expense',
        ]);
    }

    public function test_income_increases_account_balance_and_records_transaction(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'bKash Merchant',
            'type' => 'mfs',
            'mfs_provider' => 'bkash',
            'opening_balance' => 2000,
            'current_balance' => 2000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)->post(route('income.store'), [
            'account_id' => $account->id,
            'source' => 'Consulting fee',
            'amount' => 3500,
            'income_date' => now()->toDateString(),
        ]);

        $this->assertEquals(5500, (float) $account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'type' => 'in',
            'amount' => 3500,
            'source' => 'income',
        ]);
    }

    public function test_quick_sale_credits_account(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Account',
            'type' => 'cash',
            'opening_balance' => 1000,
            'current_balance' => 1000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->post(route('quick-sale.store'), [
            'account_id' => $account->id,
            'amount' => 1500,
            'profit' => 300,
            'customer_name' => 'Walk-in Customer',
            'payment_method' => 'নগদ টাকা',
            'sale_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('sales.index'));

        // Account balance should be 1000 + 1500 = 2500
        $this->assertEquals(2500, (float) $account->fresh()->current_balance);

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'type' => 'in',
            'amount' => 1500,
            'source' => 'sale',
        ]);
    }

    public function test_can_fetch_account_details_via_ajax_and_update(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Old Account Name',
            'type' => 'bank',
            'bank_name' => 'Old Bank',
            'account_number' => '123456',
            'opening_balance' => 5000,
            'current_balance' => 5000,
            'status' => 'active',
        ]);

        $ajaxResponse = $this->actingAs($this->user)->getJson(route('accounts.edit', $account));
        $ajaxResponse->assertOk();
        $ajaxResponse->assertJsonFragment([
            'name' => 'Old Account Name',
            'bank_name' => 'Old Bank',
            'account_number' => '123456',
            'type' => 'bank',
        ]);

        $updateResponse = $this->actingAs($this->user)->put(route('accounts.update', $account), [
            'name' => 'Updated Bank Account',
            'type' => 'bank',
            'bank_name' => 'Updated Bank',
            'account_number' => '654321',
            'branch_name' => 'Dhanmondi',
            'status' => 'active',
            'is_default' => 0,
        ]);

        $updateResponse->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Updated Bank Account',
            'bank_name' => 'Updated Bank',
            'account_number' => '654321',
            'branch_name' => 'Dhanmondi',
        ]);
    }

    public function test_can_delete_transfer_and_restore_balances(): void
    {
        $cash = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Account',
            'type' => 'cash',
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'status' => 'active',
        ]);

        $bank = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Bank Account',
            'type' => 'bank',
            'opening_balance' => 5000,
            'current_balance' => 5000,
            'status' => 'active',
        ]);

        // Perform transfer of 2000 with 50 charge
        $this->actingAs($this->user)->post(route('account-transfers.store'), [
            'from_account_id' => $cash->id,
            'to_account_id' => $bank->id,
            'amount' => 2000,
            'charge' => 50,
            'transfer_date' => now()->toDateString(),
        ]);

        $this->assertEquals(7950, (float) $cash->fresh()->current_balance);
        $this->assertEquals(7000, (float) $bank->fresh()->current_balance);

        $transfer = AccountTransfer::first();
        $this->assertNotNull($transfer);

        // Delete the transfer
        $deleteResponse = $this->actingAs($this->user)->delete(route('account-transfers.destroy', $transfer));
        $deleteResponse->assertRedirect(route('account-transfers.index'));

        // Balances must be restored
        $this->assertEquals(10000, (float) $cash->fresh()->current_balance);
        $this->assertEquals(5000, (float) $bank->fresh()->current_balance);

        // Transfer must be soft-deleted
        $this->assertSoftDeleted('account_transfers', ['id' => $transfer->id]);

        // Transactions must be removed
        $this->assertDatabaseMissing('account_transactions', [
            'sourceable_type' => $transfer->getMorphClass(),
            'sourceable_id' => $transfer->id,
        ]);
    }
}
