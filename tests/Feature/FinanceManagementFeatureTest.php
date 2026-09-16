<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\FinanceManagement\Models\Asset;
use Modules\FinanceManagement\Models\Debt;
use Modules\FinanceManagement\Models\Lend;
use Modules\FinanceManagement\Models\SecurityMoney;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Account $account;

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

        $this->account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash',
            'type' => 'cash',
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    // --- Assets: simple CRUD, no ledger effect ---

    public function test_can_create_and_list_asset(): void
    {
        $response = $this->actingAs($this->user)->post(route('assets.store'), [
            'name' => 'Shop Refrigerator',
            'amount' => 45000,
            'note' => 'Bought second-hand',
        ]);

        $response->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', [
            'shop_id' => $this->shop->id,
            'name' => 'Shop Refrigerator',
            'amount' => 45000,
        ]);

        // No cash movement for a static asset register.
        $this->assertEquals(10000, (float) $this->account->fresh()->current_balance);

        // The list itself is rendered client-side via DataTables' own AJAX draw.
        $this->actingAs($this->user)->get(route('assets.index'))->assertOk();

        $drawResponse = $this->actingAs($this->user)->getJson(
            route('assets.index').'?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $drawResponse->assertOk();
        $drawResponse->assertJsonFragment(['name' => '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">Shop Refrigerator</div>']);
    }

    public function test_can_update_and_delete_asset(): void
    {
        $asset = Asset::create([
            'shop_id' => $this->shop->id,
            'name' => 'Old Furniture',
            'amount' => 5000,
        ]);

        $this->actingAs($this->user)->put(route('assets.update', $asset), [
            'name' => 'Updated Furniture',
            'amount' => 6000,
        ])->assertRedirect(route('assets.index'));

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Updated Furniture', 'amount' => 6000]);

        $this->actingAs($this->user)->delete(route('assets.destroy', $asset))
            ->assertRedirect(route('assets.index'));

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_can_create_and_update_asset_with_depreciation(): void
    {
        // Flat depreciation
        $response = $this->actingAs($this->user)->post(route('assets.store'), [
            'name' => 'Delivery Van',
            'amount' => 500000,
            'depreciation_type' => 'flat',
            'depreciation' => 50000,
            'note' => 'Year 1 depreciation',
        ]);

        $response->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', [
            'shop_id' => $this->shop->id,
            'name' => 'Delivery Van',
            'amount' => 500000,
            'depreciation_type' => 'flat',
            'depreciation' => 50000,
        ]);

        $asset = Asset::where('name', 'Delivery Van')->first();
        $this->assertEquals(50000, $asset->depreciation_amount);
        $this->assertEquals(450000, $asset->net_value);

        // Edit via AJAX returns depreciation_type, depreciation, and net_value
        $editResponse = $this->actingAs($this->user)->getJson(route('assets.edit', $asset));
        $editResponse->assertOk();
        $editResponse->assertJsonFragment([
            'id' => $asset->id,
            'name' => 'Delivery Van',
            'amount' => 500000.0,
            'depreciation_type' => 'flat',
            'depreciation' => 50000.0,
            'depreciation_amount' => 50000.0,
            'net_value' => 450000.0,
        ]);

        // Update to percentage depreciation (10%)
        $updateResponse = $this->actingAs($this->user)->put(route('assets.update', $asset), [
            'name' => 'Delivery Van',
            'amount' => 500000,
            'depreciation_type' => 'percentage',
            'depreciation' => 10,
        ]);

        $updateResponse->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'depreciation_type' => 'percentage',
            'depreciation' => 10,
        ]);
        $fresh = $asset->fresh();
        $this->assertEquals(50000, $fresh->depreciation_amount);
        $this->assertEquals(450000, $fresh->net_value);
    }

    public function test_can_create_asset_with_percentage_depreciation(): void
    {
        $response = $this->actingAs($this->user)->post(route('assets.store'), [
            'name' => 'Air Conditioner',
            'amount' => 80000,
            'depreciation_type' => 'percentage',
            'depreciation' => 20,
        ]);

        $response->assertRedirect(route('assets.index'));
        $asset = Asset::where('name', 'Air Conditioner')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('percentage', $asset->depreciation_type);
        $this->assertEquals(16000, $asset->depreciation_amount);
        $this->assertEquals(64000, $asset->net_value);
    }

    public function test_asset_depreciation_cannot_exceed_limits(): void
    {
        // Flat cannot exceed amount
        $flatResponse = $this->actingAs($this->user)->post(route('assets.store'), [
            'name' => 'Computer',
            'amount' => 30000,
            'depreciation_type' => 'flat',
            'depreciation' => 35000,
        ]);
        $flatResponse->assertSessionHasErrors('depreciation');
        $this->assertDatabaseMissing('assets', ['name' => 'Computer']);

        // Percentage cannot exceed 100%
        $percentResponse = $this->actingAs($this->user)->post(route('assets.store'), [
            'name' => 'Generator',
            'amount' => 30000,
            'depreciation_type' => 'percentage',
            'depreciation' => 120,
        ]);
        $percentResponse->assertSessionHasErrors('depreciation');
        $this->assertDatabaseMissing('assets', ['name' => 'Generator']);
    }

    public function test_asset_can_be_stored_and_updated_with_validity(): void
    {
        $response = $this->actingAs($this->user)->post(route('assets.store'), [
            'name' => 'Delivery Van',
            'amount' => 500000,
            'depreciation_type' => 'percentage',
            'depreciation' => 10,
            'validity' => 5,
            'validity_unit' => 'year',
        ]);

        $response->assertRedirect(route('assets.index'));
        $asset = Asset::where('name', 'Delivery Van')->first();
        $this->assertNotNull($asset);
        $this->assertEquals(5, (float) $asset->validity);
        $this->assertEquals('year', $asset->validity_unit);
        $this->assertStringContainsString('৫ বছর', $asset->validity_formatted);
        // Backward compatibility getters
        $this->assertEquals(5, (float) $asset->useful_life);
        $this->assertEquals('year', $asset->useful_life_unit);
        $this->assertStringContainsString('৫ বছর', $asset->useful_life_formatted);

        // Edit JSON returns validity & validity_unit
        $editResponse = $this->actingAs($this->user)->getJson(route('assets.edit', $asset));
        $editResponse->assertOk();
        $editResponse->assertJsonFragment([
            'validity' => 5.0,
            'validity_unit' => 'year',
        ]);

        $updateResponse = $this->actingAs($this->user)->put(route('assets.update', $asset), [
            'name' => 'Delivery Van (Updated)',
            'amount' => 500000,
            'depreciation_type' => 'percentage',
            'depreciation' => 10,
            'validity' => 60,
            'validity_unit' => 'month',
        ]);

        $updateResponse->assertRedirect(route('assets.index'));
        $asset->refresh();
        $this->assertEquals(60, (float) $asset->validity);
        $this->assertEquals('month', $asset->validity_unit);
        $this->assertStringContainsString('৬০ মাস', $asset->validity_formatted);
    }

    // --- Debts: create posts IN, marking paid additionally posts OUT ---

    public function test_creating_unpaid_debt_increases_cash_balance(): void
    {
        $this->actingAs($this->user)->post(route('debts.store'), [
            'account_id' => $this->account->id,
            'lender_name' => 'Karim Mia',
            'date' => now()->toDateString(),
            'amount' => 5000,
            'status' => 'unpaid',
        ])->assertRedirect(route('debts.index'));

        // Borrowing cash is an inflow, even though it's still an open liability.
        $this->assertEquals(15000, (float) $this->account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $this->account->id,
            'type' => 'in',
            'amount' => 5000,
            'source' => 'debt_received',
        ]);
    }

    public function test_marking_debt_as_paid_also_records_repayment_outflow(): void
    {
        $debt = Debt::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'lender_name' => 'Karim Mia',
            'date' => now()->toDateString(),
            'amount' => 5000,
            'status' => 'unpaid',
        ]);

        // After creation: +5000 (loan received) = 15000
        $this->assertEquals(15000, (float) $this->account->fresh()->current_balance);

        $this->actingAs($this->user)->put(route('debts.update', $debt), [
            'account_id' => $this->account->id,
            'lender_name' => 'Karim Mia',
            'date' => now()->toDateString(),
            'amount' => 5000,
            'status' => 'paid',
        ])->assertRedirect(route('debts.index'));

        // Now both the IN (received) and OUT (repaid) transactions exist, netting back to 10000.
        $this->assertEquals(10000, (float) $this->account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $this->account->id,
            'type' => 'out',
            'amount' => 5000,
            'source' => 'debt_repaid',
        ]);
    }

    public function test_debt_created_directly_as_paid_nets_to_zero_cash_effect(): void
    {
        Debt::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'lender_name' => 'Historical Lender',
            'date' => now()->toDateString(),
            'amount' => 3000,
            'status' => 'paid',
        ]);

        // Already-settled historical debt: IN + OUT cancel out.
        $this->assertEquals(10000, (float) $this->account->fresh()->current_balance);
    }

    // --- Lend: create posts OUT, marking received additionally posts IN ---

    public function test_creating_lend_decreases_cash_balance(): void
    {
        $this->actingAs($this->user)->post(route('lend.store'), [
            'account_id' => $this->account->id,
            'borrower_name' => 'Rahim Uddin',
            'date' => now()->toDateString(),
            'amount' => 2000,
            'status' => 'due',
        ])->assertRedirect(route('lend.index'));

        $this->assertEquals(8000, (float) $this->account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $this->account->id,
            'type' => 'out',
            'amount' => 2000,
            'source' => 'lend_given',
        ]);
    }

    public function test_marking_lend_as_received_also_records_repayment_inflow(): void
    {
        $lend = Lend::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'borrower_name' => 'Rahim Uddin',
            'date' => now()->toDateString(),
            'amount' => 2000,
            'status' => 'due',
        ]);

        $this->assertEquals(8000, (float) $this->account->fresh()->current_balance);

        $this->actingAs($this->user)->put(route('lend.update', $lend), [
            'account_id' => $this->account->id,
            'borrower_name' => 'Rahim Uddin',
            'date' => now()->toDateString(),
            'amount' => 2000,
            'status' => 'received',
        ])->assertRedirect(route('lend.index'));

        $this->assertEquals(10000, (float) $this->account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $this->account->id,
            'type' => 'in',
            'amount' => 2000,
            'source' => 'lend_repaid',
        ]);
    }

    // --- Security Money: single-event-per-record (paid XOR received) ---

    public function test_security_money_paid_decreases_cash_balance(): void
    {
        $this->actingAs($this->user)->post(route('security-money.store'), [
            'account_id' => $this->account->id,
            'receiver_name' => 'Landlord',
            'date' => now()->toDateString(),
            'amount' => 4000,
            'status' => 'paid',
        ])->assertRedirect(route('security-money.index'));

        $this->assertEquals(6000, (float) $this->account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $this->account->id,
            'type' => 'out',
            'amount' => 4000,
            'source' => 'security_money_paid',
        ]);
    }

    public function test_security_money_received_increases_cash_balance(): void
    {
        $this->actingAs($this->user)->post(route('security-money.store'), [
            'account_id' => $this->account->id,
            'receiver_name' => 'Tenant',
            'date' => now()->toDateString(),
            'amount' => 2500,
            'status' => 'received',
        ])->assertRedirect(route('security-money.index'));

        $this->assertEquals(12500, (float) $this->account->fresh()->current_balance);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $this->account->id,
            'type' => 'in',
            'amount' => 2500,
            'source' => 'security_money_received',
        ]);
    }

    public function test_flipping_security_money_status_replaces_the_transaction_not_adds_to_it(): void
    {
        $securityMoney = SecurityMoney::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'receiver_name' => 'Landlord',
            'date' => now()->toDateString(),
            'amount' => 4000,
            'status' => 'paid',
        ]);

        $this->assertEquals(6000, (float) $this->account->fresh()->current_balance);

        $this->actingAs($this->user)->put(route('security-money.update', $securityMoney), [
            'account_id' => $this->account->id,
            'receiver_name' => 'Landlord',
            'date' => now()->toDateString(),
            'amount' => 4000,
            'status' => 'received',
        ])->assertRedirect(route('security-money.index'));

        // The old OUT was replaced by a new IN (single-event model), not stacked.
        $this->assertEquals(14000, (float) $this->account->fresh()->current_balance);
    }

    // --- Deleting removes the ledger effect ---

    public function test_deleting_debt_restores_account_balance(): void
    {
        $debt = Debt::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'lender_name' => 'Karim Mia',
            'date' => now()->toDateString(),
            'amount' => 5000,
            'status' => 'unpaid',
        ]);

        $this->assertEquals(15000, (float) $this->account->fresh()->current_balance);

        $this->actingAs($this->user)->delete(route('debts.destroy', $debt))
            ->assertRedirect(route('debts.index'));

        $this->assertSoftDeleted('debts', ['id' => $debt->id]);
        $this->assertEquals(10000, (float) $this->account->fresh()->current_balance);
    }

    // --- Permission & feature gating ---

    public function test_user_without_permission_is_forbidden(): void
    {
        $limitedRole = Role::create(['name' => 'NoFinanceManagement', 'guard_name' => 'web']);
        $limitedUser = User::create([
            'name' => 'Limited User',
            'email' => 'limited@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $limitedUser->syncRoles([$limitedRole]);

        $this->actingAs($limitedUser)->get(route('assets.index'))->assertForbidden();
        $this->actingAs($limitedUser)->get(route('debts.index'))->assertForbidden();
        $this->actingAs($limitedUser)->get(route('lend.index'))->assertForbidden();
        $this->actingAs($limitedUser)->get(route('security-money.index'))->assertForbidden();
    }

    public function test_shop_without_feature_is_forbidden_even_with_permission(): void
    {
        $shopWithoutFeature = Shop::create([
            'name' => 'No Finance Management Shop',
            'slug' => 'no-fm-shop',
            'status' => 'active',
        ]);
        $this->subscribeShopToFeatures($shopWithoutFeature, ['sales', 'purchase']);

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@no-fm.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'shop_id' => $shopWithoutFeature->id,
        ]);
        $adminRole = Role::where('name', 'Admin')->where('guard_name', 'web')->first();
        $user->syncRoles([$adminRole]);

        $this->actingAs($user)->get(route('assets.index'))->assertForbidden();
    }
}
