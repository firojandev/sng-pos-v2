<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cashbox\DataTables\CashTransactionsDataTable;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CashboxDataTableTest extends TestCase
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
            'name' => 'Cashbox Test Shop',
            'slug' => 'cashbox-test-shop',
            'status' => 'active',
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }
        $this->subscribeShopToFeatures($this->shop, Features::keys());

        $this->user = User::create([
            'name' => 'Cashbox Admin',
            'email' => 'admin@cashbox.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);
    }

    public function test_cashbox_datatable_generates_html_builder(): void
    {
        $dataTable = new CashTransactionsDataTable;
        $html = $dataTable->html();

        $this->assertEquals('cashbox-data-table', $html->getTableAttribute('id'));
        $this->assertCount(6, $dataTable->getColumns());
    }

    public function test_cashbox_datatable_query_returns_query_builder(): void
    {
        $dataTable = new CashTransactionsDataTable;
        $query = $dataTable->query(new CashTransaction);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_cashbox_datatable_returns_json_data(): void
    {
        CashTransaction::create([
            'shop_id' => $this->shop->id,
            'type' => 'in',
            'source' => 'manual',
            'amount' => 500.00,
            'note' => 'Opening Cash',
            'occurred_at' => now(),
            'created_by' => $this->user->id,
        ]);

        CashTransaction::create([
            'shop_id' => $this->shop->id,
            'type' => 'out',
            'source' => 'manual',
            'amount' => 200.00,
            'note' => 'Stationery Expense',
            'occurred_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('cashbox.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
            'summary' => [
                'balance',
                'cash_in',
                'cash_out',
                'total_count',
            ],
        ]);

        $json = $response->json();
        $this->assertEquals(2, $json['recordsTotal']);
        $this->assertEquals(300.00, $json['summary']['balance']);
        $this->assertEquals(500.00, $json['summary']['cash_in']);
        $this->assertEquals(200.00, $json['summary']['cash_out']);
        $this->assertEquals(2, $json['summary']['total_count']);
    }

    public function test_cashbox_datatable_filtering_by_type(): void
    {
        CashTransaction::create([
            'shop_id' => $this->shop->id,
            'type' => 'in',
            'source' => 'sale',
            'amount' => 1000.00,
            'note' => 'Sale #101',
            'occurred_at' => now(),
            'created_by' => $this->user->id,
        ]);

        CashTransaction::create([
            'shop_id' => $this->shop->id,
            'type' => 'out',
            'source' => 'expense',
            'amount' => 300.00,
            'note' => 'Tea Bill',
            'occurred_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $responseIn = $this->actingAs($this->user)->getJson(route('cashbox.index', ['type' => 'cash_in']), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $responseIn->assertOk();
        $this->assertEquals(1, $responseIn->json('recordsFiltered'));
        $this->assertEquals(1000.00, $responseIn->json('summary.cash_in'));

        $responseOut = $this->actingAs($this->user)->getJson(route('cashbox.index', ['type' => 'cash_out']), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $responseOut->assertOk();
        $this->assertEquals(1, $responseOut->json('recordsFiltered'));
        $this->assertEquals(300.00, $responseOut->json('summary.cash_out'));
    }

    public function test_cashbox_datatable_filtering_by_date_range(): void
    {
        CashTransaction::create([
            'shop_id' => $this->shop->id,
            'type' => 'in',
            'source' => 'manual',
            'amount' => 450.00,
            'occurred_at' => now()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        CashTransaction::create([
            'shop_id' => $this->shop->id,
            'type' => 'in',
            'source' => 'manual',
            'amount' => 650.00,
            'occurred_at' => now()->subDays(15),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('cashbox.index', [
            'from' => now()->subDays(7)->toDateString(),
            'to' => now()->toDateString(),
        ]), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertEquals(450.00, $response->json('summary.cash_in'));
    }

    public function test_user_can_submit_cash_in(): void
    {
        $response = $this->actingAs($this->user)->post(route('cashbox.cash-in'), [
            'cash_form' => 'in',
            'amount' => 1500.50,
            'note' => 'Added capital',
            'occurred_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_transactions', [
            'shop_id' => $this->shop->id,
            'type' => 'in',
            'source' => 'manual',
            'amount' => 1500.50,
            'note' => 'Added capital',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_user_can_submit_cash_out(): void
    {
        $response = $this->actingAs($this->user)->post(route('cashbox.cash-out'), [
            'cash_form' => 'out',
            'amount' => 750.00,
            'note' => 'Emergency purchase',
            'occurred_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_transactions', [
            'shop_id' => $this->shop->id,
            'type' => 'out',
            'source' => 'manual',
            'amount' => 750.00,
            'note' => 'Emergency purchase',
            'created_by' => $this->user->id,
        ]);
    }
}
