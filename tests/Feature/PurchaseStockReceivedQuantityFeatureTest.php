<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Purchase\Models\Purchase;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseStockReceivedQuantityFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Product $product;

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
            'name' => 'Stock Test Shop',
            'slug' => 'stock-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Stock Admin',
            'email' => 'admin@stock.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Stock Branch',
            'code' => 'BR-01',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Stock Warehouse',
            'code' => 'WH-01',
            'status' => 'active',
        ]);

        $this->supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Direct Supplier',
            'phone' => '01711223344',
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'Supplies',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Rice Bag 50kg',
            'sku' => 'RB-50',
            'purchase_price' => 100,
            'sale_price' => 120,
            'status' => 'active',
        ]);
    }

    public function test_purchase_updates_stock_based_on_received_quantity_not_ordered_quantity(): void
    {
        $payload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => '2026-09-04',
            'invoice_no' => 'INV-DO-101',
            'do_number' => 'DO-9988',
            'do_date' => '2026-09-04',
            'vehicle_number' => 'DHAKA-METRO-11-2233',
            'delivery_person_name' => 'Rahim Uddin',
            'transportation_cost' => 50,
            'adjustment_cost' => 10,
            'discount' => 5,
            'delivery_charge' => 15,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'received_qty' => 6,
                    'purchase_price' => 100,
                    'sale_price' => 120,
                    'batch_no' => 'BT-TEST-01',
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 500,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase.store'), $payload);

        $response->assertRedirect(route('purchase.index'));

        // Check purchase record
        $purchase = Purchase::where('invoice_no', 'INV-DO-101')->first();
        $this->assertNotNull($purchase);
        $this->assertEquals('DO-9988', $purchase->do_number);
        $this->assertEquals('2026-09-04', $purchase->do_date->format('Y-m-d'));
        $this->assertEquals('DHAKA-METRO-11-2233', $purchase->vehicle_number);
        $this->assertEquals('Rahim Uddin', $purchase->delivery_person_name);
        $this->assertEquals(50.00, (float) $purchase->transportation_cost);
        $this->assertEquals(10.00, (float) $purchase->adjustment_cost);

        // Subtotal = 10 * 100 = 1000
        // Total = 1000 - 5 (discount) + 15 (delivery) + 50 (transport) + 10 (adjustment) = 1070
        $this->assertEquals(1000.00, (float) $purchase->subtotal);
        $this->assertEquals(1070.00, (float) $purchase->total);

        // Check purchase items
        $this->assertCount(1, $purchase->items);
        $item = $purchase->items->first();
        $this->assertEquals(10.00, (float) $item->quantity);
        $this->assertEquals(6.00, (float) $item->received_quantity);

        // Check purchase receipt items table
        $this->assertDatabaseHas('purchase_receipt_items', [
            'shop_id' => $this->shop->id,
            'purchase_id' => $purchase->id,
            'purchase_item_id' => $item->id,
            'product_id' => $this->product->id,
            'received_quantity' => 6.00,
            'received_by' => $this->user->id,
        ]);

        // Check batch quantity increased by 6 (NOT 10)
        $batch = Batch::where('product_id', $this->product->id)
            ->where('batch_no', 'BT-TEST-01')
            ->first();
        $this->assertNotNull($batch);
        $this->assertEquals(6.00, (float) $batch->quantity);

        // Test reverting / deleting purchase reverts stock by 6 (NOT 10)
        $this->actingAs($this->user)->delete(route('purchase.destroy', $purchase));

        $batch->refresh();
        $this->assertEquals(0.00, (float) $batch->quantity);
        $this->assertDatabaseMissing('purchase_receipt_items', [
            'purchase_id' => $purchase->id,
        ]);
    }
}
