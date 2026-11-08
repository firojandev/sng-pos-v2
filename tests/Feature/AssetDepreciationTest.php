<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Services\OpeningBalanceService;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\FinanceManagement\Models\Asset;
use Modules\FinanceManagement\Models\AssetDepreciation;
use Modules\FinanceManagement\Services\AssetDepreciationService;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssetDepreciationTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-27 12:00:00');
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

        Account::create(['shop_id' => $this->shop->id, 'name' => 'Cash', 'type' => 'cash', 'status' => 'active', 'is_default' => true, 'opening_balance' => 1000, 'current_balance' => 1000]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_straight_line_depreciation_is_scheduled_and_posted_month_by_month(): void
    {
        $this->post(route('assets.store'), [
            'name' => 'Freezer', 'amount' => 120000, 'purchase_date' => '2026-01-15', 'residual_value' => 12000,
            'depreciation_type' => 'straight_line', 'validity' => 3, 'validity_unit' => 'year',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $asset = Asset::firstOrFail();

        $schedule = app(AssetDepreciationService::class)->schedule($asset);
        $this->assertCount(36, $schedule);
        $this->assertSame(3000.0, $schedule[0]['amount'], '(120,000 − 12,000) ÷ 36 months.');
        $this->assertSame(12000.0, end($schedule)['book_value'], 'Ends at the residual value.');

        app(OpeningBalanceService::class)->post($this->shop->company, '2026-01-01');

        $this->post(route('assets.depreciate'))->assertRedirect();
        $this->assertSame(8, AssetDepreciation::count(), 'January to August.');
        $this->assertSame(24000.0, $this->balance('depreciation_expense'));
        $this->assertSame(-24000.0, $this->balance('accumulated_depreciation'), 'A contra-asset: a credit balance.');
        $this->assertSame(96000.0, $asset->fresh()->net_value);

        $this->post(route('assets.depreciate'))->assertRedirect();
        $this->assertSame(8, AssetDepreciation::count(), 'Months already recorded are not recorded again.');

        $this->get(route('assets.schedule', $asset))->assertOk()->assertSee('Straight-line');
        $this->get(route('assets.index'))->assertOk()->assertSee('Post Depreciation');
    }

    private function balance(string $key): float
    {
        $account = app(ChartOfAccounts::class)->account($this->shop->company_id, $key);
        $row = app(LedgerService::class)->totalsByAccount($this->shop->company_id)[$account->id] ?? null;

        return $row ? round($account->normalBalance((float) $row->debit, (float) $row->credit), 2) : 0.0;
    }
}
