<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\DataTables\AccountTransfersDataTable;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransfer;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountTransfersDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Account $cashAccount;

    protected Account $bankAccount;

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
            'name' => 'Transfer Test Shop',
            'slug' => 'transfer-test-shop',
            'status' => 'active',
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        } else {
            $this->subscribeShopToFeatures($this->shop, Features::keys());
        }

        $this->user = User::create([
            'name' => 'Finance Admin',
            'email' => 'finance@test.com',
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $this->cashAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash Box',
            'type' => 'cash',
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->bankAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Prime Bank Main',
            'type' => 'bank',
            'bank_name' => 'Prime Bank',
            'account_number' => '9988776655',
            'opening_balance' => 20000,
            'current_balance' => 20000,
            'is_default' => false,
            'status' => 'active',
        ]);
    }

    public function test_account_transfers_datatable_generates_html_and_query(): void
    {
        $dataTable = new AccountTransfersDataTable;
        $html = $dataTable->html();

        $this->assertEquals('account-transfers-data-table', $html->getTableAttribute('id'));
        $this->assertInstanceOf(Builder::class, $dataTable->query(new AccountTransfer));
    }

    public function test_account_transfers_index_view_renders_stat_cards_and_datatable(): void
    {
        app(AccountTransactionService::class)->transfer(
            fromAccount: $this->cashAccount,
            toAccount: $this->bankAccount,
            amount: 5000,
            charge: 25,
            transferDate: now()->toDateString(),
            note: 'Daily cash deposit',
            userId: $this->user->id
        );

        $response = $this->actingAs($this->user)->get(route('account-transfers.index'));

        $response->assertOk();
        $response->assertSee('ফান্ড ট্রান্সফার');
        $response->assertSee('id="account-transfers-data-table"', false);
        $response->assertSee('id="filter-from-account"', false);
        $response->assertSee('id="filter-to-account"', false);
        $response->assertSee('id="filter-date-from"', false);
        $response->assertSee('id="filter-date-to"', false);
        $response->assertSee('5,000.00');
        $response->assertSee('25.00');
    }

    public function test_account_transfers_datatable_ajax_returns_records(): void
    {
        $transfer = app(AccountTransactionService::class)->transfer(
            fromAccount: $this->cashAccount,
            toAccount: $this->bankAccount,
            amount: 7500,
            charge: 30,
            transferDate: now()->toDateString(),
            note: 'Midday deposit',
            userId: $this->user->id
        );

        $response = $this->actingAs($this->user)->getJson(route('account-transfers.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $row = $data[0];

        $this->assertStringContainsString($transfer->transfer_no, $row['transfer_no']);
        $this->assertStringContainsString('Main Cash Box', $row['from_account']);
        $this->assertStringContainsString('Prime Bank Main', $row['to_account']);
        $this->assertStringContainsString('7,500.00', $row['amount']);
        $this->assertStringContainsString('30.00', $row['charge']);
        $this->assertStringContainsString('Midday deposit', $row['details']);
        $this->assertStringContainsString('delete-form', $row['action']);
    }

    public function test_account_transfers_datatable_filters_by_account_and_date(): void
    {
        $service = app(AccountTransactionService::class);

        $transfer1 = $service->transfer(
            fromAccount: $this->cashAccount,
            toAccount: $this->bankAccount,
            amount: 1000,
            charge: 10,
            transferDate: '2026-09-01',
            note: 'Transfer September 1',
            userId: $this->user->id
        );

        $transfer2 = $service->transfer(
            fromAccount: $this->bankAccount,
            toAccount: $this->cashAccount,
            amount: 2000,
            charge: 0,
            transferDate: '2026-09-05',
            note: 'Transfer September 5',
            userId: $this->user->id
        );

        // Filter by from_account_id = cashAccount
        $response = $this->actingAs($this->user)->getJson(
            route('account-transfers.index', ['from_account_id' => $this->cashAccount->id]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString($transfer1->transfer_no, $data[0]['transfer_no']);

        // Filter by date range
        $responseDate = $this->actingAs($this->user)->getJson(
            route('account-transfers.index', ['date_from' => '2026-09-04', 'date_to' => '2026-09-06']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $responseDate->assertOk();
        $dataDate = $responseDate->json('data');
        $this->assertCount(1, $dataDate);
        $this->assertStringContainsString($transfer2->transfer_no, $dataDate[0]['transfer_no']);
    }

    public function test_account_transfers_destroy_reverts_balance_and_deletes(): void
    {
        $transfer = app(AccountTransactionService::class)->transfer(
            fromAccount: $this->cashAccount,
            toAccount: $this->bankAccount,
            amount: 3000,
            charge: 15,
            transferDate: now()->toDateString(),
            note: 'Test revert',
            userId: $this->user->id
        );

        $this->assertEquals(46985, (float) $this->cashAccount->fresh()->current_balance);
        $this->assertEquals(23000, (float) $this->bankAccount->fresh()->current_balance);

        $response = $this->actingAs($this->user)->delete(route('account-transfers.destroy', $transfer));

        $response->assertRedirect(route('account-transfers.index'));
        $this->assertSoftDeleted('account_transfers', ['id' => $transfer->id]);

        $this->assertEquals(50000, (float) $this->cashAccount->fresh()->current_balance);
        $this->assertEquals(20000, (float) $this->bankAccount->fresh()->current_balance);
    }
}
