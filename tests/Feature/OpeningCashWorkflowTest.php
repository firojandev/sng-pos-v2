<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OpeningCashWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Account $cash;

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
        $admin = User::factory()->create(['shop_id' => $this->shop->id]);
        $admin->syncRoles(['Admin']);
        $this->actingAs($admin);

        $this->cash = Account::create(['shop_id' => $this->shop->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active', 'is_default' => true, 'opening_balance' => 0, 'current_balance' => -500]);
    }

    public function test_the_counted_cash_is_recorded_as_a_transaction_and_opens_the_ledger(): void
    {
        $this->get(route('accounting-setup.index'))->assertOk()->assertSee('Opening Cash');

        $this->post(route('accounting-setup.money-opening'), ['counted' => [$this->cash->id => 2000], 'as_of' => now()->toDateString()])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('2000.00', $this->cash->fresh()->current_balance, 'Brought to the counted amount by a transaction, not overwritten.');
        $this->assertSame(['in', '2500.00', 'opening_balance'], array_values(AccountTransaction::latest('id')->firstOrFail()->only(['type', 'amount', 'source'])));

        $this->post(route('accounting-setup.opening-balance'), ['opening_date' => now()->toDateString()])->assertRedirect();
        $this->assertSame(2000.0, $this->moneyBalance());
        $this->assertSame(2000.0, $this->balance('opening_balance_equity'));
    }

    public function test_after_go_live_an_adjustment_posts_cash_against_opening_equity(): void
    {
        $this->post(route('accounting-setup.money-opening'), ['counted' => [$this->cash->id => 100], 'as_of' => now()->toDateString()]);
        $this->post(route('accounting-setup.opening-balance'), ['opening_date' => now()->subDay()->toDateString()])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('accounting-setup.money-opening'), ['counted' => [$this->cash->id => 3000], 'as_of' => now()->toDateString()])->assertRedirect();

        $this->assertSame(3000.0, $this->moneyBalance());
        $this->assertSame(3000.0, $this->balance('opening_balance_equity'));
    }

    private function moneyBalance(): float
    {
        $ledgerAccount = app(ChartOfAccounts::class)->forMoneyAccount($this->cash->fresh());
        $row = app(LedgerService::class)->totalsByAccount($this->shop->company_id)[$ledgerAccount->id] ?? null;

        return $row ? $ledgerAccount->normalBalance((float) $row->debit, (float) $row->credit) : 0.0;
    }

    private function balance(string $key): float
    {
        $account = app(ChartOfAccounts::class)->account($this->shop->company_id, $key);
        $row = app(LedgerService::class)->totalsByAccount($this->shop->company_id)[$account->id] ?? null;

        return $row ? $account->normalBalance((float) $row->debit, (float) $row->credit) : 0.0;
    }
}
