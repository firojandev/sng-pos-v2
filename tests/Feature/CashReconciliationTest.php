<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\CashSession;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CashReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $admin;

    protected Account $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create(['name' => 'Drawer Shop', 'slug' => 'drawer-shop', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->shop, Features::keys());

        $this->admin = User::create([
            'name' => 'Drawer Admin',
            'email' => 'admin@drawer.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->admin->syncRoles([$adminRole]);

        $this->cashAccount = Account::withoutGlobalScopes()->create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash',
            'type' => 'cash',
            'opening_balance' => 5000,
            'current_balance' => 5000,
            'status' => 'active',
        ]);
    }

    public function test_index_lists_cash_accounts_with_their_expected_opening_cash(): void
    {
        $response = $this->actingAs($this->admin)->get(route('cash-reconciliation.index'));

        $response->assertOk();
        $response->assertSee('Main Cash');
        $response->assertSee('৳5,000.00');
        $response->assertSee(route('cash-reconciliation.create', $this->cashAccount));
    }

    public function test_opening_without_a_count_carries_the_expected_balance_forward(): void
    {
        $this->actingAs($this->admin)
            ->post(route('cash-reconciliation.store', $this->cashAccount), ['note' => 'Morning'])
            ->assertRedirect();

        $session = CashSession::sole();
        $this->assertTrue($session->isOpen());
        $this->assertSame('5000.00', $session->expected_opening_balance);
        $this->assertNull($session->counted_opening_balance);
        $this->assertSame('5000.00', $session->opening_balance);
        $this->assertSame('0.00', $session->opening_difference);
        $this->assertSame($this->admin->id, $session->opened_by);
    }

    public function test_opening_with_a_count_uses_the_counted_cash_and_records_the_difference(): void
    {
        $this->actingAs($this->admin)->post(route('cash-reconciliation.store', $this->cashAccount), [
            'count_opening' => '1',
            'counts' => ['1000' => 4, '500' => 1, '100' => 4],
            'other_amount' => '50.50',
        ])->assertRedirect();

        $session = CashSession::sole();
        $this->assertSame('4950.50', $session->counted_opening_balance);
        $this->assertSame('4950.50', $session->opening_balance);
        $this->assertSame('-49.50', $session->opening_difference);
        $this->assertSame(
            [['1000.00', 4, '4000.00'], ['500.00', 1, '500.00'], ['100.00', 4, '400.00'], ['0.00', 1, '50.50']],
            $session->counts()->where('stage', 'opening')->get()->map(fn ($count) => [$count->denomination, $count->quantity, $count->subtotal])->all()
        );
    }

    public function test_a_second_session_cannot_open_while_one_is_open(): void
    {
        $this->actingAs($this->admin)->post(route('cash-reconciliation.store', $this->cashAccount))->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('cash-reconciliation.create', $this->cashAccount))
            ->assertRedirect(route('cash-reconciliation.show', CashSession::sole()));

        $this->actingAs($this->admin)
            ->post(route('cash-reconciliation.store', $this->cashAccount))
            ->assertSessionHasErrors('session');

        $this->assertSame(1, CashSession::count());
    }

    public function test_expected_cash_follows_cash_movements_and_a_matching_count_closes_balanced(): void
    {
        $session = $this->openSession();
        $this->recordCash('in', 12000, 'sale');
        $this->recordCash('in', 2000, 'cash_in');
        $this->recordCash('out', 500, 'expense');
        $this->recordCash('out', 200, 'sale_return');

        $this->actingAs($this->admin)
            ->get(route('cash-reconciliation.show', $session))
            ->assertOk()
            ->assertSee('৳18,300.00');

        $this->actingAs($this->admin)->post(route('cash-reconciliation.close', $session), [
            'counts' => ['1000' => 18, '100' => 3],
        ])->assertRedirect(route('cash-reconciliation.show', $session));

        $session->refresh();
        $this->assertFalse($session->isOpen());
        $this->assertSame('18300.00', $session->expected_closing_balance);
        $this->assertSame('18300.00', $session->counted_closing_balance);
        $this->assertSame('0.00', $session->closing_difference);
        $this->assertSame(14000.0, (float) $session->movement_summary['cash_in']);
        $this->assertSame(700.0, (float) $session->movement_summary['cash_out']);
        $this->assertSame(0.0, (float) $session->movement_summary['other_changes']);
    }

    public function test_a_short_count_needs_a_reason_and_is_recorded_as_a_negative_difference(): void
    {
        $session = $this->openSession();
        $this->recordCash('in', 12300, 'sale');

        $this->actingAs($this->admin)
            ->post(route('cash-reconciliation.close', $session), ['counts' => ['1000' => 17]])
            ->assertSessionHasErrors('note');
        $this->assertTrue($session->fresh()->isOpen());

        $this->actingAs($this->admin)->post(route('cash-reconciliation.close', $session), [
            'counts' => ['1000' => 17],
            'note' => 'Change given twice',
        ])->assertRedirect();

        $session->refresh();
        $this->assertSame('17300.00', $session->expected_closing_balance);
        $this->assertSame('17000.00', $session->counted_closing_balance);
        $this->assertSame('-300.00', $session->closing_difference);
        $this->assertSame('Change given twice', $session->closing_note);
        $this->assertSame('short', CashSession::differenceStatus((float) $session->closing_difference)['key']);
    }

    public function test_a_closed_session_cannot_be_closed_again_and_keeps_its_figures(): void
    {
        $session = $this->openSession();
        $this->closeSession($session, ['1000' => 5]);

        $this->recordCash('in', 999, 'sale');

        $this->actingAs($this->admin)
            ->post(route('cash-reconciliation.close', $session), ['counts' => ['1000' => 1], 'note' => 'again'])
            ->assertSessionHasErrors('session');

        $this->assertSame('5000.00', $session->fresh()->expected_closing_balance);
    }

    public function test_next_session_opens_from_the_last_count_plus_cash_moved_in_between(): void
    {
        $session = $this->openSession();
        $this->recordCash('in', 12300, 'sale');
        $this->closeSession($session, ['1000' => 17], 'Short 300');

        $this->recordCash('in', 400, 'sale');

        $this->actingAs($this->admin)
            ->get(route('cash-reconciliation.create', $this->cashAccount))
            ->assertOk()
            ->assertSee('৳17,400.00');

        $this->actingAs($this->admin)->post(route('cash-reconciliation.store', $this->cashAccount))->assertRedirect();

        $nextSession = CashSession::latest('id')->first();
        $this->assertSame('17400.00', $nextSession->expected_opening_balance);
        $this->assertSame('17400.00', $nextSession->opening_balance);
    }

    public function test_editing_an_earlier_entry_only_counts_its_net_change_in_the_open_session(): void
    {
        $service = app(AccountTransactionService::class);
        $earlierSaleSource = Account::withoutGlobalScopes()->create([
            'shop_id' => $this->shop->id,
            'name' => 'Earlier entry source',
            'type' => 'bank',
            'status' => 'active',
        ]);
        $this->actingAs($this->admin);
        $service->recordTransaction($this->cashAccount->fresh(), 'in', 1000, 'sale', $earlierSaleSource);

        $session = $this->openSession();

        // An earlier sale is edited from ৳1,000 to ৳1,100: the old row is replaced by a new one.
        $service->deleteTransactionsFor($earlierSaleSource);
        $service->recordTransaction($this->cashAccount->fresh(), 'in', 1100, 'sale', $earlierSaleSource);

        $this->closeSession($session, ['1000' => 6, '100' => 1]);

        $session->refresh();
        $this->assertSame('6000.00', $session->opening_balance);
        $this->assertSame('6100.00', $session->expected_closing_balance);
        $this->assertSame('0.00', $session->closing_difference);
        $this->assertSame(1100.0, (float) $session->movement_summary['cash_in']);
        $this->assertSame(-1000.0, (float) $session->movement_summary['other_changes']);
    }

    public function test_bank_and_mobile_accounts_cannot_be_reconciled(): void
    {
        $bank = Account::withoutGlobalScopes()->create([
            'shop_id' => $this->shop->id,
            'name' => 'Bank',
            'type' => 'bank',
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)->get(route('cash-reconciliation.create', $bank))->assertNotFound();
        $this->actingAs($this->admin)->post(route('cash-reconciliation.store', $bank))->assertNotFound();
    }

    public function test_other_shops_sessions_are_not_visible(): void
    {
        $otherShop = Shop::create(['name' => 'Other Shop', 'slug' => 'other-shop', 'status' => 'active']);
        $otherAccount = Account::withoutGlobalScopes()->create([
            'shop_id' => $otherShop->id,
            'name' => 'Other Cash',
            'type' => 'cash',
            'status' => 'active',
        ]);
        $otherSession = CashSession::withoutGlobalScopes()->create([
            'shop_id' => $otherShop->id,
            'account_id' => $otherAccount->id,
            'status' => 'open',
            'opened_at' => now(),
            'system_balance_at_open' => 0,
            'expected_opening_balance' => 0,
            'opening_balance' => 0,
        ]);

        $this->actingAs($this->admin)->get(route('cash-reconciliation.show', $otherSession))->assertNotFound();
        $this->actingAs($this->admin)->get(route('cash-reconciliation.create', $otherAccount))->assertNotFound();
        $this->actingAs($this->admin)->get(route('cash-reconciliation.index'))->assertOk()->assertDontSee('Other Cash');
    }

    public function test_view_only_users_cannot_open_or_close_sessions(): void
    {
        setPermissionsTeamId($this->shop->id);
        $viewerRole = Role::create(['shop_id' => $this->shop->id, 'name' => 'Viewer', 'guard_name' => 'web']);
        $viewerRole->syncPermissions(['cash-reconciliation.view']);
        $viewer = User::create([
            'name' => 'Viewer',
            'email' => 'viewer@drawer.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $viewer->assignRole($viewerRole);
        setPermissionsTeamId(null);

        $session = $this->openSession();

        $this->actingAs($viewer)->get(route('cash-reconciliation.show', $session))->assertOk()->assertDontSee(route('cash-reconciliation.close', $session));
        $this->actingAs($viewer)->post(route('cash-reconciliation.close', $session), ['counts' => ['1000' => 5]])->assertForbidden();
        $this->actingAs($viewer)->get(route('cash-reconciliation.create', $this->cashAccount))->assertForbidden();
    }

    private function openSession(): CashSession
    {
        $this->actingAs($this->admin)->post(route('cash-reconciliation.store', $this->cashAccount))->assertRedirect();

        return CashSession::latest('id')->firstOrFail();
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function closeSession(CashSession $session, array $counts, ?string $note = null): void
    {
        $this->actingAs($this->admin)
            ->post(route('cash-reconciliation.close', $session), ['counts' => $counts, 'note' => $note])
            ->assertSessionHasNoErrors();
    }

    private function recordCash(string $type, float $amount, string $source): void
    {
        $this->actingAs($this->admin);
        app(AccountTransactionService::class)->recordTransaction($this->cashAccount->fresh(), $type, $amount, $source);
    }
}
