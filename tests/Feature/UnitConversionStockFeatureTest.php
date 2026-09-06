<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Product\Models\Unit;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchaseItem;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnitConversionStockFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Customer $customer;

    protected Product $product;

    protected Unit $baseUnit;

    protected Unit $boxUnit;

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
            'name' => 'Unit Test Shop',
            'slug' => 'unit-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Unit Admin',
            'email' => 'admin@unit.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'code' => 'BR-01',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-01',
            'status' => 'active',
        ]);

        $this->supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Acme Supplier',
            'phone' => '01711223344',
            'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'John Customer',
            'phone' => '01811223344',
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'Beverages',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Juice Bottle',
            'sku' => 'JB-01',
            'purchase_price' => 40.00,
            'sale_price' => 50.00,
            'status' => 'active',
        ]);

        // Base unit: Piece (factor 1)
        $this->baseUnit = Unit::create([
            'shop_id' => $this->shop->id,
            'name' => 'Piece',
            'short_code' => 'pcs',
            'status' => 'active',
        ]);

        // Secondary unit: Box (1 Box = 10 Pieces)
        $this->boxUnit = Unit::create([
            'shop_id' => $this->shop->id,
            'name' => 'Box',
            'short_code' => 'bx',
            'status' => 'active',
        ]);

        $this->product->units()->attach([
            $this->baseUnit->id => ['is_base' => true, 'conversion_factor' => 1.0, 'is_smaller_unit' => false],
            $this->boxUnit->id => ['is_base' => false, 'conversion_factor' => 10.0, 'is_smaller_unit' => false],
        ]);
    }

    public function test_purchase_with_unit_conversion_creates_stock_in_base_units_and_updates_base_prices(): void
    {
        $response = $this->actingAs($this->user)->post(route('purchase.store'), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5, // 5 Boxes
                    'unit_id' => $this->boxUnit->id,
                    'purchase_price' => 400.00, // 400 per Box (so 40 per Piece)
                    'sale_price' => 600.00, // 600 per Box (so 60 per Piece)
                    'received_qty' => 5,
                    'batch_no' => 'BT-BOX-01',
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 2000.00],
            ],
        ]);

        $response->assertRedirect(route('purchase.index'));

        // 1. Check purchase items stored entered unit details
        $purchaseItem = PurchaseItem::first();
        $this->assertNotNull($purchaseItem);
        $this->assertEquals($this->boxUnit->id, $purchaseItem->unit_id);
        $this->assertEquals(5.00, (float) $purchaseItem->quantity);
        $this->assertEquals(5.00, (float) $purchaseItem->received_quantity);
        $this->assertEquals(400.00, (float) $purchaseItem->purchase_price);
        $this->assertEquals(2000.00, (float) $purchaseItem->total);
        $this->assertEquals(50.00, $purchaseItem->baseQuantity());
        $this->assertEquals(50.00, $purchaseItem->baseReceivedQuantity());

        // 2. Check batch stock is in base units: 5 boxes * 10 = 50 Pieces
        $batch = Batch::where('batch_no', 'BT-BOX-01')->first();
        $this->assertNotNull($batch);
        $this->assertEquals(50.00, (float) $batch->quantity);

        // 3. Check product prices updated to base unit prices
        $this->product->refresh();
        $this->assertEquals(40.00, (float) $this->product->purchase_price); // 400 / 10
        $this->assertEquals(60.00, (float) $this->product->sale_price); // 600 / 10

        // 4. Check StockMovement recorded base quantity change
        $movement = StockMovement::where('reference_type', Purchase::class)->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(50.00, (float) $movement->quantity_change);
    }

    public function test_purchase_partial_receive_and_remaining_receive_converts_to_base_stock(): void
    {
        // 1. Purchase 5 Boxes, only receive 2 Boxes initially
        $this->actingAs($this->user)->post(route('purchase.store'), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_id' => $this->boxUnit->id,
                    'purchase_price' => 400.00,
                    'sale_price' => 600.00,
                    'received_qty' => 2, // 2 Boxes received initially (= 20 pieces)
                    'batch_no' => 'BT-PARTIAL-01',
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 2000.00],
            ],
        ]);

        $purchase = Purchase::latest('id')->first();
        $purchaseItem = $purchase->items->first();

        // 2 Boxes received = 20 Pieces in batch
        $batch = Batch::where('batch_no', 'BT-PARTIAL-01')->first();
        $this->assertEquals(20.00, (float) $batch->quantity);
        $this->assertEquals(3.00, $purchaseItem->pendingQuantity()); // 3 Boxes pending

        // 2. Receive remaining 3 Boxes
        $response = $this->actingAs($this->user)->post(route('purchase.receive.store', $purchase), [
            'do_number' => 'DO-REMAINING-01',
            'do_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_item_id' => $purchaseItem->id,
                    'received_qty' => 3, // 3 Boxes (= 30 pieces)
                    'batch_no' => 'BT-PARTIAL-01',
                ],
            ],
        ]);

        $response->assertRedirect(route('purchase.ledger'));

        // Batch stock should now be 20 + 30 = 50 Pieces
        $batch->refresh();
        $this->assertEquals(50.00, (float) $batch->quantity);

        $purchaseItem->refresh();
        $this->assertEquals(5.00, (float) $purchaseItem->received_quantity);
        $this->assertEquals(0.00, $purchaseItem->pendingQuantity());
    }

    public function test_purchase_delete_rolls_back_stock_in_base_units(): void
    {
        $this->actingAs($this->user)->post(route('purchase.store'), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 4, // 4 Boxes = 40 Pieces
                    'unit_id' => $this->boxUnit->id,
                    'purchase_price' => 400.00,
                    'sale_price' => 600.00,
                    'received_qty' => 4,
                    'batch_no' => 'BT-DEL-01',
                ],
            ],
        ]);

        $purchase = Purchase::latest('id')->first();
        $batch = Batch::where('batch_no', 'BT-DEL-01')->first();
        $this->assertEquals(40.00, (float) $batch->quantity);

        // Delete purchase
        $this->actingAs($this->user)->delete(route('purchase.destroy', $purchase));

        // Batch stock should be reduced by 40 base units to 0
        $batch->refresh();
        $this->assertEquals(0.00, (float) $batch->quantity);

        $reversal = StockMovement::where('type', 'purchase_reversal')->latest('id')->first();
        $this->assertNotNull($reversal);
        $this->assertEquals(-40.00, (float) $reversal->quantity_change);
    }

    public function test_purchase_return_deducts_stock_in_base_units(): void
    {
        $this->actingAs($this->user)->post(route('purchase.store'), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5, // 5 Boxes = 50 Pieces
                    'unit_id' => $this->boxUnit->id,
                    'purchase_price' => 400.00,
                    'sale_price' => 600.00,
                    'received_qty' => 5,
                    'batch_no' => 'BT-RET-01',
                ],
            ],
        ]);

        $purchase = Purchase::latest('id')->first();
        $batch = Batch::where('batch_no', 'BT-RET-01')->first();
        $this->assertEquals(50.00, (float) $batch->quantity);

        // Return 1 Box (= 10 Pieces)
        $response = $this->actingAs($this->user)->post(route('purchase-returns.store', $purchase), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_item_id' => $purchase->items->first()->id,
                    'quantity' => 1, // 1 Box returned
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('purchase.ledger'));

        // Stock in batch should be 50 - 10 = 40 Pieces (not 49)
        $batch->refresh();
        $this->assertEquals(40.00, (float) $batch->quantity);

        $returnMovement = StockMovement::where('type', 'purchase_return')->latest('id')->first();
        $this->assertNotNull($returnMovement);
        $this->assertEquals(-10.00, (float) $returnMovement->quantity_change);
    }

    public function test_sale_with_unit_conversion_deducts_stock_in_base_units_and_records_unit_values(): void
    {
        // Setup initial batch with 50 Pieces
        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BT-SALE-01',
            'quantity' => 50.00,
        ]);

        // Sell 2 Boxes @ ৳500/Box
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2, // 2 Boxes
                    'unit_id' => $this->boxUnit->id,
                    'unit_price' => 500.00, // 500 per Box
                    'discount' => 0,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1000.00],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        // 1. Batch stock reduced by 2 * 10 = 20 Pieces: 50 - 20 = 30 Pieces
        $batch->refresh();
        $this->assertEquals(30.00, (float) $batch->quantity);

        // 2. Sale item records entered unit and unit price, not base values
        $saleItem = SaleItem::first();
        $this->assertNotNull($saleItem);
        $this->assertEquals($this->boxUnit->id, $saleItem->unit_id);
        $this->assertEquals(2.00, (float) $saleItem->quantity);
        $this->assertEquals(500.00, (float) $saleItem->unit_price);
        $this->assertEquals(1000.00, (float) $saleItem->total);
        $this->assertEquals(20.00, $saleItem->baseQuantity());

        // 3. Profit: Revenue = 1000, Cost = 20 Pieces * 40/pc = 800 -> Profit = 200
        $sale = Sale::first();
        $this->assertEquals(200.00, (float) $sale->profit);

        // 4. StockMovement logged -20 Pieces
        $movement = StockMovement::where('reference_type', Sale::class)->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-20.00, (float) $movement->quantity_change);
    }

    public function test_sale_update_does_not_compound_quantity(): void
    {
        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BT-SALE-COMP-01',
            'quantity' => 50.00,
        ]);

        // Sell 2 Boxes (= 20 pieces deducted, 30 remaining)
        $this->actingAs($this->user)->post(route('sales.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_id' => $this->boxUnit->id,
                    'unit_price' => 500.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1000.00],
            ],
        ]);

        $sale = Sale::latest('id')->first();
        $batch->refresh();
        $this->assertEquals(30.00, (float) $batch->quantity);

        // Update the sale with the same values (simulating edit and save)
        $this->actingAs($this->user)->put(route('sales.update', $sale), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_id' => $this->boxUnit->id,
                    'unit_price' => 500.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1000.00],
            ],
        ]);

        // Batch stock MUST still be 30.00 (NOT compounded or over-deducted)
        $batch->refresh();
        $this->assertEquals(30.00, (float) $batch->quantity);
    }

    public function test_sale_delete_reverts_stock_in_base_units(): void
    {
        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BT-SALE-DEL-01',
            'quantity' => 50.00,
        ]);

        $this->actingAs($this->user)->post(route('sales.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_id' => $this->boxUnit->id,
                    'unit_price' => 500.00,
                    'discount' => 0,
                ],
            ],
        ]);

        $sale = Sale::latest('id')->first();
        $batch->refresh();
        $this->assertEquals(30.00, (float) $batch->quantity);

        // Delete sale
        $this->actingAs($this->user)->delete(route('sales.destroy', $sale));

        // Batch stock should be restored to 50 Pieces
        $batch->refresh();
        $this->assertEquals(50.00, (float) $batch->quantity);

        $movement = StockMovement::where('type', 'sale_reversal')->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(20.00, (float) $movement->quantity_change);
    }

    public function test_sale_return_restores_stock_in_base_units(): void
    {
        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BT-SALE-RET-01',
            'quantity' => 50.00,
        ]);

        $this->actingAs($this->user)->post(route('sales.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2, // 2 Boxes = 20 pieces
                    'unit_id' => $this->boxUnit->id,
                    'unit_price' => 500.00,
                    'discount' => 0,
                ],
            ],
        ]);

        $sale = Sale::latest('id')->first();
        $saleItem = $sale->items->first();
        $batch->refresh();
        $this->assertEquals(30.00, (float) $batch->quantity);

        // Return 1 Box (= 10 Pieces)
        $response = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1, // 1 Box returned
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('sales.ledger'));

        // Stock in batch should be 30 + 10 = 40 Pieces (not 31)
        $batch->refresh();
        $this->assertEquals(40.00, (float) $batch->quantity);

        $movement = StockMovement::where('type', 'sale_return')->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10.00, (float) $movement->quantity_change);
    }

    public function test_smaller_unit_conversion_purchase_and_sale_and_stock(): void
    {
        // Drum is base unit (1 Drum)
        $drumUnit = Unit::create([
            'shop_id' => $this->shop->id,
            'name' => 'Drum',
            'short_code' => 'drm',
            'status' => 'active',
        ]);

        // Litre is smaller unit (200 Litres = 1 Drum)
        $litreUnit = Unit::create([
            'shop_id' => $this->shop->id,
            'name' => 'Litre',
            'short_code' => 'ltr',
            'status' => 'active',
        ]);

        $oil = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $this->product->category_id,
            'name' => 'Cooking Oil',
            'sku' => 'OIL-01',
            'purchase_price' => 2000.00,
            'sale_price' => 2500.00,
            'status' => 'active',
        ]);

        $oil->units()->attach([
            $drumUnit->id => ['is_base' => true, 'conversion_factor' => 1.0, 'is_smaller_unit' => false],
            $litreUnit->id => ['is_base' => false, 'conversion_factor' => 200.0, 'is_smaller_unit' => true],
        ]);

        // 1. Purchase 400 Litres @ ৳10/Litre (total 4000)
        $this->actingAs($this->user)->post(route('purchase.store'), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $oil->id,
                    'quantity' => 400,
                    'unit_id' => $litreUnit->id,
                    'purchase_price' => 10.00, // 10 per Litre -> 2000 per Drum
                    'sale_price' => 15.00,
                    'received_qty' => 400,
                    'batch_no' => 'BT-OIL-01',
                ],
            ],
        ]);

        // 400 Litres / 200 = 2 Drums in batch stock
        $batch = Batch::where('batch_no', 'BT-OIL-01')->first();
        $this->assertNotNull($batch);
        $this->assertEquals(2.00, (float) $batch->quantity);

        // Product purchase price updated to base unit price (2000/Drum)
        $oil->refresh();
        $this->assertEquals(2000.00, (float) $oil->purchase_price);

        // 2. Sell 100 Litres @ ৳15/Litre (0.5 Drums)
        $this->actingAs($this->user)->post(route('sales.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $oil->id,
                    'quantity' => 100, // 100 Litres = 0.5 Drum
                    'unit_id' => $litreUnit->id,
                    'unit_price' => 15.00, // 1500 total
                    'discount' => 0,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1500.00],
            ],
        ]);

        // Batch stock should be 2.0 - 0.5 = 1.5 Drums
        $batch->refresh();
        $this->assertEquals(1.50, (float) $batch->quantity);

        // Sale item records 100 Litres @ 15
        $sale = Sale::latest('id')->first();
        $saleItem = $sale->items->first();
        $this->assertEquals(100.00, (float) $saleItem->quantity);
        $this->assertEquals(15.00, (float) $saleItem->unit_price);
        $this->assertEquals(1500.00, (float) $saleItem->total);

        // Profit: 1500 - (0.5 Drum * 2000) = 500
        $this->assertEquals(500.00, (float) $sale->profit);
    }
}
