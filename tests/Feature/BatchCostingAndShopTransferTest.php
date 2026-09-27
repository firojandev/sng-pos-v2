<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockTransfer;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BatchCostingAndShopTransferTest extends TestCase
{
    use RefreshDatabase;

    private Shop $dhaka;

    private Shop $ctg;

    private Warehouse $dhakaStore;

    private Warehouse $ctgStore;

    private User $dhakaAdmin;

    private User $ctgAdmin;

    private Product $rice;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $company = Company::factory()->create();
        $this->dhaka = Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->dhaka, Features::keys());

        $this->dhakaStore = $this->warehouseOf($this->dhaka);
        $this->ctgStore = $this->warehouseOf($this->ctg);
        $this->dhakaAdmin = $this->adminOf($this->dhaka);
        $this->ctgAdmin = $this->adminOf($this->ctg);

        $category = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Grocery', 'type' => 'product']);
        $this->rice = Product::create(['shop_id' => $this->dhaka->id, 'name' => 'Miniket Rice', 'category_id' => $category->id, 'purchase_price' => 60, 'sale_price' => 80, 'status' => 'active']);
    }

    public function test_adding_stock_at_a_different_cost_blends_the_batch_cost(): void
    {
        $batch = $this->batchAtDhaka('B-1', 10, 50);

        $batch->absorbCost(30, 70);

        $this->assertSame('65.0000', $batch->unit_cost);
    }

    public function test_a_batch_entered_without_a_cost_is_valued_at_the_product_purchase_price(): void
    {
        $batch = Batch::create(['shop_id' => $this->dhaka->id, 'warehouse_id' => $this->dhakaStore->id, 'product_id' => $this->rice->id, 'batch_no' => 'B-1', 'quantity' => 5]);

        $this->assertSame(60.0, (float) $batch->unit_cost);
    }

    public function test_sale_profit_uses_the_cost_of_the_batches_the_stock_came_from(): void
    {
        $this->batchAtDhaka('OLD', 4, 50);
        $this->batchAtDhaka('NEW', 10, 70);

        $this->actingAs($this->dhakaAdmin)->post(route('sales.store'), [
            'warehouse_id' => $this->dhakaStore->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $this->rice->id, 'quantity' => 6, 'unit_price' => 80, 'discount' => 0]],
            'payments' => [['method' => 'cash', 'amount' => 480]],
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::withoutGlobalScopes()->latest('id')->firstOrFail();

        // 4 units at 50 + 2 units at 70 = 340 cost; 480 - 340 = 140 profit
        $this->assertSame([200.0, 140.0], $sale->items()->orderBy('id')->pluck('cost_total')->map(fn ($cost) => (float) $cost)->all());
        $this->assertSame(140.0, (float) $sale->profit);
    }

    public function test_stock_moves_to_another_shop_at_its_cost_and_the_receiving_shop_confirms_it(): void
    {
        $source = $this->batchAtDhaka('B-1', 20, 55);

        $this->actingAs($this->dhakaAdmin)->postJson(route('stock-transfers.store'), [
            'from_warehouse_id' => $this->dhakaStore->id,
            'to_warehouse_id' => $this->ctgStore->id,
            'items' => [['product_id' => $this->rice->id, 'batch_id' => $source->id, 'quantity' => 8]],
        ])->assertOk();

        $transfer = StockTransfer::withoutGlobalScopes()->latest('id')->firstOrFail();
        $this->assertSame($this->ctg->id, $transfer->to_shop_id);
        $this->assertTrue($transfer->isBetweenShops());

        $this->actingAs($this->ctgAdmin)->postJson(route('stock-transfers.dispatch', $transfer))->assertForbidden();
        $this->actingAs($this->dhakaAdmin)->postJson(route('stock-transfers.approve', $transfer))->assertOk();
        $this->actingAs($this->dhakaAdmin)->postJson(route('stock-transfers.dispatch', $transfer))->assertOk();
        $this->assertSame(12.0, (float) $source->fresh()->quantity);

        $this->actingAs($this->dhakaAdmin)->postJson(route('stock-transfers.receive', $transfer))->assertForbidden();
        $this->actingAs($this->ctgAdmin)->postJson(route('stock-transfers.receive', $transfer))->assertOk();

        $received = Batch::withoutGlobalScopes()->where('warehouse_id', $this->ctgStore->id)->firstOrFail();
        $this->assertSame($this->ctg->id, $received->shop_id);
        $this->assertSame(8.0, (float) $received->quantity);
        $this->assertSame(55.0, (float) $received->unit_cost);

        $this->actingAs($this->ctgAdmin);
        $this->assertTrue(Product::listedInShop()->whereKey($this->rice->id)->exists());
        $this->assertTrue(StockTransfer::whereKey($transfer->id)->exists(), 'The receiving shop sees the transfer.');
    }

    public function test_transfers_only_use_the_shops_own_stock_and_the_companys_warehouses(): void
    {
        $source = $this->batchAtDhaka('B-1', 20, 55);
        $otherShop = Shop::create(['name' => 'Other Company Store', 'slug' => 'other-store', 'status' => 'active']);
        $foreignWarehouse = $this->warehouseOf($otherShop);
        $ctgBatch = Batch::create(['shop_id' => $this->ctg->id, 'warehouse_id' => $this->ctgStore->id, 'product_id' => $this->rice->id, 'batch_no' => 'C-1', 'quantity' => 5]);

        $this->actingAs($this->dhakaAdmin)->postJson(route('stock-transfers.store'), [
            'from_warehouse_id' => $this->dhakaStore->id,
            'to_warehouse_id' => $foreignWarehouse->id,
            'items' => [['product_id' => $this->rice->id, 'batch_id' => $source->id, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('to_warehouse_id');

        $this->actingAs($this->dhakaAdmin)->postJson(route('stock-transfers.store'), [
            'from_warehouse_id' => $this->dhakaStore->id,
            'to_warehouse_id' => $this->ctgStore->id,
            'items' => [['product_id' => $this->rice->id, 'batch_id' => $ctgBatch->id, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.batch_id');
    }

    private function batchAtDhaka(string $batchNo, float $quantity, float $unitCost): Batch
    {
        return Batch::create([
            'shop_id' => $this->dhaka->id,
            'warehouse_id' => $this->dhakaStore->id,
            'product_id' => $this->rice->id,
            'batch_no' => $batchNo,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
        ]);
    }

    private function warehouseOf(Shop $shop): Warehouse
    {
        $branch = Branch::create(['shop_id' => $shop->id, 'name' => $shop->name.' Branch', 'status' => 'active']);

        return Warehouse::create(['shop_id' => $shop->id, 'branch_id' => $branch->id, 'name' => $shop->name.' Store', 'status' => 'active', 'is_default' => true]);
    }

    private function adminOf(Shop $shop): User
    {
        $user = User::factory()->create(['shop_id' => $shop->id]);
        $user->syncRoles(['Admin']);

        return $user;
    }
}
