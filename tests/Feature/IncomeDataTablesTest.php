<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\DataTables\IncomesDataTable;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Income;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncomeDataTablesTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Account $account;

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
            'name' => 'Income Test Shop',
            'slug' => 'income-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Income Admin',
            'email' => 'admin@income.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $this->account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash',
            'type' => 'cash',
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    public function test_incomes_datatable_generates_html_builder(): void
    {
        $dataTable = new IncomesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('incomes-data-table', $html->getTableAttribute('id'));
        $this->assertCount(7, $dataTable->getColumns());
    }

    public function test_incomes_datatable_query_returns_query_builder(): void
    {
        $dataTable = new IncomesDataTable;
        $query = $dataTable->query(new Income);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_incomes_index_page_loads_with_modals_and_datatable(): void
    {
        $response = $this->actingAs($this->user)->get(route('income.index'));

        $response->assertOk();
        $response->assertSee('incomes-data-table');
        $response->assertSee('createIncomeModal');
        $response->assertSee('editIncomeModal');
        $response->assertSee('নতুন আয়');
    }

    public function test_incomes_datatable_ajax_returns_json(): void
    {
        Income::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'source' => 'Delivery Charge',
            'amount' => 500.00,
            'income_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'note' => 'Delivery fee from customer',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('income.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('#INC-', $response->json('data.0.voucher_no'));
        $this->assertStringContainsString('Delivery Charge', $response->json('data.0.source'));
        $this->assertStringContainsString('500.00', $response->json('data.0.amount'));
        $this->assertStringContainsString('Main Cash', $response->json('data.0.account'));
    }

    public function test_income_can_be_created_via_post_and_ajax(): void
    {
        // Standard POST
        $response = $this->actingAs($this->user)->post(route('income.store'), [
            'account_id' => $this->account->id,
            'source' => 'Consulting Service',
            'amount' => 2500,
            'income_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('income.index'));
        $this->assertDatabaseHas('incomes', [
            'shop_id' => $this->shop->id,
            'source' => 'Consulting Service',
            'amount' => 2500,
        ]);

        // AJAX POST
        $ajaxResponse = $this->actingAs($this->user)->postJson(route('income.store'), [
            'account_id' => $this->account->id,
            'source' => 'Scrap Sale',
            'amount' => 800,
            'income_date' => now()->toDateString(),
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson(['success' => true]);
        // AJAX POST with cash and null account_id
        $cashResponse = $this->actingAs($this->user)->postJson(route('income.store'), [
            'payment_method' => 'cash',
            'source' => 'Direct Cash Payment',
            'amount' => 1200,
            'income_date' => now()->toDateString(),
        ]);

        $cashResponse->assertOk();
        $cashResponse->assertJson(['success' => true]);
        $this->assertDatabaseHas('incomes', [
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id, // auto-resolved to cash account
            'payment_method' => 'cash',
            'source' => 'Direct Cash Payment',
            'amount' => 1200,
        ]);
    }

    public function test_income_edit_endpoint_returns_json_for_modal(): void
    {
        $income = Income::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'source' => 'Gift Commission',
            'amount' => 1000,
            'income_date' => now()->toDateString(),
            'note' => 'Supplier promo reward',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('income.edit', $income));

        $response->assertOk();
        $response->assertJson([
            'id' => $income->id,
            'source' => 'Gift Commission',
            'amount' => 1000,
            'account_id' => $this->account->id,
            'note' => 'Supplier promo reward',
            'update_url' => route('income.update', $income),
        ]);
    }

    public function test_income_can_be_updated_via_put_and_ajax(): void
    {
        $income = Income::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'source' => 'Old Source',
            'amount' => 300,
            'income_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->putJson(route('income.update', $income), [
            'account_id' => $this->account->id,
            'source' => 'Updated Source',
            'amount' => 450,
            'income_date' => now()->toDateString(),
            'note' => 'Updated income note',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'source' => 'Updated Source',
            'amount' => 450,
        ]);
    }

    public function test_income_can_be_deleted_via_delete_and_ajax(): void
    {
        $income = Income::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'source' => 'To Delete',
            'amount' => 300,
            'income_date' => now()->toDateString(),
        ]);

        // Standard DELETE
        $response = $this->actingAs($this->user)->delete(route('income.destroy', $income));
        $response->assertRedirect(route('income.index'));
        $this->assertSoftDeleted('incomes', ['id' => $income->id]);

        $income2 = Income::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'source' => 'To Delete via AJAX',
            'amount' => 200,
            'income_date' => now()->toDateString(),
        ]);

        // AJAX DELETE
        $ajaxResponse = $this->actingAs($this->user)->deleteJson(route('income.destroy', $income2));
        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertSoftDeleted('incomes', ['id' => $income2->id]);
    }

    public function test_incomes_datatable_filters_by_payment_method_and_date_range(): void
    {
        Income::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'source' => 'Cash Income Yesterday',
            'amount' => 400.00,
            'income_date' => now()->subDay()->toDateString(),
            'payment_method' => 'cash',
        ]);

        Income::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'source' => 'bKash Income Today',
            'amount' => 600.00,
            'income_date' => now()->toDateString(),
            'payment_method' => 'bkash',
        ]);

        // Filter by payment method
        $responseMethod = $this->actingAs($this->user)
            ->getJson(route('income.index', ['payment_method' => 'bkash']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $responseMethod->assertOk();
        $this->assertEquals(1, $responseMethod->json('recordsFiltered'));
        $this->assertStringContainsString('bKash Income Today', $responseMethod->json('data.0.source'));

        // Filter by date range
        $responseDate = $this->actingAs($this->user)
            ->getJson(route('income.index', [
                'date_from' => now()->subDay()->toDateString(),
                'date_to' => now()->subDay()->toDateString(),
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $responseDate->assertOk();
        $this->assertEquals(1, $responseDate->json('recordsFiltered'));
        $this->assertStringContainsString('Cash Income Yesterday', $responseDate->json('data.0.source'));
    }
}
