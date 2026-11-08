<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Http\Requests\StoreSaleRequest;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyWideCustomersAndSuppliersTest extends TestCase
{
    use RefreshDatabase;

    private Shop $dhaka;

    private Shop $ctg;

    private User $dhakaAdmin;

    private User $ctgAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::where('guard_name', 'web')->get());

        $company = Company::factory()->create(['name' => 'Karim Holdings']);
        $this->dhaka = Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->dhaka, Features::keys());

        $this->dhakaAdmin = $this->adminOf($this->dhaka);
        $this->ctgAdmin = $this->adminOf($this->ctg);
    }

    public function test_customers_and_suppliers_are_shared_by_the_companys_shops_only(): void
    {
        Customer::create(['shop_id' => $this->dhaka->id, 'name' => 'Rahim', 'status' => 'active']);
        Supplier::create(['shop_id' => $this->dhaka->id, 'name' => 'Pran Foods', 'status' => 'active']);
        $otherShop = Shop::create(['name' => 'Other Company Store', 'slug' => 'other-store', 'status' => 'active']);

        $this->actingAs($this->ctgAdmin);
        $this->assertSame(['Rahim'], Customer::pluck('name')->all());
        $this->assertSame(['Pran Foods'], Supplier::pluck('name')->all());

        $this->actingAs(User::factory()->create(['shop_id' => $otherShop->id]));
        $this->assertSame(0, Customer::count());
        $this->assertSame(0, Supplier::count());
    }

    public function test_a_customer_records_the_shop_and_company_it_was_created_at(): void
    {
        $this->actingAs($this->ctgAdmin);

        $customer = Customer::create(['name' => 'Walk-in Regular', 'status' => 'active']);

        $this->assertSame($this->ctg->id, $customer->shop_id);
        $this->assertSame($this->ctg->company_id, $customer->company_id);
    }

    public function test_sales_only_accept_customers_of_the_current_company(): void
    {
        $ownCustomer = Customer::create(['shop_id' => $this->dhaka->id, 'name' => 'Rahim', 'status' => 'active']);
        $foreignShop = Shop::create(['name' => 'Other Company Store', 'slug' => 'other-store', 'status' => 'active']);
        $foreignCustomer = Customer::create(['shop_id' => $foreignShop->id, 'name' => 'Stranger', 'status' => 'active']);

        $this->actingAs($this->ctgAdmin);
        $customerRules = ['customer_id' => (new StoreSaleRequest)->rules()['customer_id']];

        $this->assertTrue(Validator::make(['customer_id' => $ownCustomer->id], $customerRules)->passes());
        $this->assertTrue(Validator::make(['customer_id' => $foreignCustomer->id], $customerRules)->fails());
    }

    public function test_a_customers_due_from_one_shop_can_be_collected_at_another(): void
    {
        $customer = Customer::create(['shop_id' => $this->dhaka->id, 'name' => 'Rahim', 'opening_due' => 100, 'status' => 'active']);
        $sale = $this->saleAt($this->dhaka, $customer, 1000);
        $ctgCash = $this->cashAccountOf($this->ctg);

        $this->actingAs($this->ctgAdmin)->get(route('due-ledger.customer.details', $customer))
            ->assertOk()
            ->assertSee('INV-DHAKA-1')
            ->assertSee('Dhaka Outlet');

        $this->actingAs($this->ctgAdmin)->postJson(route('due-ledger.customer.payment.store', $customer), [
            'payment_date' => now()->toDateString(),
            'account_id' => $ctgCash->id,
            'payment_method' => 'cash',
            'total_amount' => 600,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame('0.00', $customer->fresh()->opening_due);
        $this->assertSame('500.00', $sale->fresh()->due_amount);
        $this->assertSame(600.0, (float) $ctgCash->fresh()->current_balance);

        $cashRows = CashTransaction::withoutGlobalScope('shop')->where('sourceable_type', Sale::class)->where('sourceable_id', $sale->id)->get();
        $this->assertCount(1, $cashRows);
        $this->assertSame($this->ctg->id, $cashRows->first()->shop_id);
        $this->assertSame(500.0, (float) $cashRows->first()->amount);
    }

    public function test_cashbox_splits_a_sale_paid_at_its_own_shop_and_at_another_shop(): void
    {
        $customer = Customer::create(['shop_id' => $this->dhaka->id, 'name' => 'Rahim', 'status' => 'active']);
        $sale = $this->saleAt($this->dhaka, $customer, 1000);
        $sale->update(['paid_amount' => 300, 'due_amount' => 700, 'payment_status' => 'partial']);

        $this->actingAs($this->ctgAdmin)->postJson(route('due-ledger.customer.payment.store', $customer), [
            'payment_date' => now()->toDateString(),
            'account_id' => $this->cashAccountOf($this->ctg)->id,
            'payment_method' => 'cash',
            'total_amount' => 200,
        ])->assertOk();

        $amountsByShop = CashTransaction::withoutGlobalScope('shop')
            ->where('sourceable_type', Sale::class)
            ->where('sourceable_id', $sale->id)
            ->pluck('amount', 'shop_id')
            ->map(fn ($amount) => (float) $amount)
            ->all();

        $this->assertSame([$this->dhaka->id => 300.0, $this->ctg->id => 200.0], $amountsByShop);
    }

    public function test_a_supplier_due_from_one_shop_can_be_paid_from_another(): void
    {
        $supplier = Supplier::create(['shop_id' => $this->dhaka->id, 'name' => 'Pran Foods', 'status' => 'active']);
        $purchase = $this->purchaseAt($this->dhaka, $supplier, 800);
        $ctgCash = $this->cashAccountOf($this->ctg, 1000);

        $this->actingAs($this->ctgAdmin)->postJson(route('due-ledger.supplier.payment.store', $supplier), [
            'payment_date' => now()->toDateString(),
            'account_id' => $ctgCash->id,
            'payment_method' => 'cash',
            'total_amount' => 800,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame('0.00', $purchase->fresh()->due_amount);
        $this->assertSame(200.0, (float) $ctgCash->fresh()->current_balance);
        $this->assertSame(
            [$this->ctg->id],
            CashTransaction::withoutGlobalScope('shop')->where('sourceable_type', Purchase::class)->where('sourceable_id', $purchase->id)->pluck('shop_id')->all(),
        );
    }

    public function test_due_ledger_totals_are_company_wide(): void
    {
        $dhakaCustomer = Customer::create(['shop_id' => $this->dhaka->id, 'name' => 'Rahim', 'opening_due' => 100, 'status' => 'active']);
        Customer::create(['shop_id' => $this->ctg->id, 'name' => 'Karim', 'opening_due' => 50, 'status' => 'active']);
        $this->saleAt($this->dhaka, $dhakaCustomer, 1000);

        $this->actingAs($this->ctgAdmin)->get(route('due-ledger.sales'))
            ->assertOk()
            ->assertSee('1,150.00');
    }

    public function test_a_shops_own_figures_count_opening_dues_of_customers_created_there(): void
    {
        Customer::create(['shop_id' => $this->dhaka->id, 'name' => 'Rahim', 'opening_due' => 100, 'status' => 'active']);
        Customer::create(['shop_id' => $this->ctg->id, 'name' => 'Karim', 'opening_due' => 50, 'status' => 'active']);

        $this->actingAs($this->ctgAdmin);

        $this->assertSame(150.0, (float) Customer::sum('opening_due'));
        $this->assertSame(50.0, (float) Customer::createdAtShop($this->ctg->id)->sum('opening_due'));
    }

    private function adminOf(Shop $shop): User
    {
        $user = User::factory()->create(['shop_id' => $shop->id]);
        $user->syncRoles(['Admin']);

        return $user;
    }

    private function cashAccountOf(Shop $shop, float $balance = 0): Account
    {
        return Account::create([
            'shop_id' => $shop->id,
            'name' => $shop->name.' Cash',
            'type' => 'cash',
            'opening_balance' => $balance,
            'current_balance' => $balance,
            'status' => 'active',
            'is_default' => true,
        ]);
    }

    private function saleAt(Shop $shop, Customer $customer, float $total): Sale
    {
        [$branch, $warehouse] = $this->branchAndWarehouseOf($shop);

        return Sale::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'INV-DHAKA-1',
            'sale_date' => now()->subDay()->toDateString(),
            'subtotal' => $total,
            'total' => $total,
            'paid_amount' => 0,
            'due_amount' => $total,
            'payment_status' => 'due',
        ]);
    }

    private function purchaseAt(Shop $shop, Supplier $supplier, float $total): Purchase
    {
        [, $warehouse] = $this->branchAndWarehouseOf($shop);

        return Purchase::create([
            'shop_id' => $shop->id,
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'invoice_no' => 'PUR-DHAKA-1',
            'purchase_date' => now()->subDay()->toDateString(),
            'subtotal' => $total,
            'total' => $total,
            'paid_amount' => 0,
            'due_amount' => $total,
            'payment_status' => 'due',
        ]);
    }

    /**
     * @return array{0: Branch, 1: Warehouse}
     */
    private function branchAndWarehouseOf(Shop $shop): array
    {
        $branch = Branch::create(['shop_id' => $shop->id, 'name' => $shop->name.' Branch', 'status' => 'active']);
        $warehouse = Warehouse::create(['shop_id' => $shop->id, 'branch_id' => $branch->id, 'name' => $shop->name.' Store', 'status' => 'active']);

        return [$branch, $warehouse];
    }
}
