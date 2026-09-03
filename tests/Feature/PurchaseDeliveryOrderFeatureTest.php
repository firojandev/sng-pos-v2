<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchaseDeliveryOrder;
use Modules\Purchase\Models\PurchaseDeliveryReceipt;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseDeliveryOrderFeatureTest extends TestCase
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
            'name' => 'Test Mart',
            'slug' => 'test-mart',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Central Warehouse',
            'status' => 'active',
        ]);

        $this->supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Acme Supplies',
            'phone' => '01711111111',
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'Groceries',
            'type' => 'product',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Cooking Oil 5L',
            'sku' => 'OIL-5L',
            'barcode' => '894123456789',
            'purchase_price' => 500.00,
            'sale_price' => 600.00,
            'status' => 'active',
        ]);
    }

    public function test_user_can_view_delivery_orders_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-delivery-orders.index'));
        $response->assertOk();
        $response->assertSee('ডেলিভারি অর্ডার তালিকা');
    }

    public function test_user_can_create_purchase_delivery_order(): void
    {
        $response = $this->actingAs($this->user)->post(route('purchase-delivery-orders.store'), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(3)->toDateString(),
            'discount' => 50,
            'delivery_charge' => 100,
            'note' => 'Urgent replenishment',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'purchase_price' => 500,
                ],
            ],
        ]);

        $this->assertDatabaseHas('purchase_delivery_orders', [
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'subtotal' => 5000.00,
            'discount' => 50.00,
            'delivery_charge' => 100.00,
            'total_amount' => 5050.00,
        ]);

        $this->assertDatabaseHas('purchase_delivery_order_items', [
            'product_id' => $this->product->id,
            'ordered_quantity' => 10.00,
            'received_quantity' => 0.00,
            'purchase_price' => 500.00,
        ]);

        // Stock should NOT be updated at order placement
        $this->assertEquals(0, Batch::where('product_id', $this->product->id)->count());
        $this->assertEquals(0, StockMovement::where('product_id', $this->product->id)->count());
    }

    public function test_user_can_receive_partial_and_full_delivery_and_update_stock_and_ledger(): void
    {
        $order = PurchaseDeliveryOrder::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_no' => 'PDO-TEST-001',
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'subtotal' => 5000.00,
            'total_amount' => 5000.00,
            'due_amount' => 5000.00,
            'created_by' => $this->user->id,
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $this->product->id,
            'ordered_quantity' => 10.00,
            'received_quantity' => 0.00,
            'purchase_price' => 500.00,
            'subtotal' => 5000.00,
        ]);

        // 1. Partial Delivery: Receive 4 out of 10 items
        $response1 = $this->actingAs($this->user)->post(route('purchase-delivery-orders.store-receive', $order), [
            'delivery_date' => now()->toDateString(),
            'challan_no' => 'CH-PARTIAL-1',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'received_quantity' => 4,
                    'batch_no' => 'BT-PARTIAL-1',
                ],
            ],
        ]);

        $response1->assertRedirect(route('purchase-delivery-orders.show', $order));

        $order->refresh();
        $orderItem->refresh();

        $this->assertEquals('partial_received', $order->status);
        $this->assertEquals(4.0, (float) $orderItem->received_quantity);
        $this->assertEquals(6.0, $orderItem->pendingQuantity());

        // Batch created and incremented
        $batch = Batch::where('product_id', $this->product->id)
            ->where('batch_no', 'BT-PARTIAL-1')
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertNotNull($batch);
        $this->assertEquals(4.0, (float) $batch->quantity);

        // Stock movement logged
        $movement = StockMovement::where('product_id', $this->product->id)
            ->where('type', 'purchase')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(4.0, (float) $movement->quantity_change);

        // Purchase Delivery Receipt created
        $receipt1 = PurchaseDeliveryReceipt::where('purchase_delivery_order_id', $order->id)->first();
        $this->assertNotNull($receipt1);
        $this->assertEquals(2000.00, (float) $receipt1->total_amount);

        // Purchase Ledger synced record created
        $purchase1 = Purchase::where('invoice_no', 'CH-PARTIAL-1')->first();
        $this->assertNotNull($purchase1);
        $this->assertEquals(2000.00, (float) $purchase1->total);
        $this->assertEquals($purchase1->id, $receipt1->purchase_id);

        // 2. Final Delivery: Receive the remaining 6 items
        $response2 = $this->actingAs($this->user)->post(route('purchase-delivery-orders.store-receive', $order), [
            'delivery_date' => now()->toDateString(),
            'challan_no' => 'CH-FINAL-2',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'received_quantity' => 6,
                    'batch_no' => 'BT-FINAL-2',
                ],
            ],
        ]);

        $response2->assertRedirect(route('purchase-delivery-orders.show', $order));

        $order->refresh();
        $orderItem->refresh();

        $this->assertEquals('received', $order->status);
        $this->assertEquals(10.0, (float) $orderItem->received_quantity);
        $this->assertEquals(0.0, $orderItem->pendingQuantity());
        $this->assertTrue($orderItem->isFulfilled());

        // Second batch created
        $batch2 = Batch::where('product_id', $this->product->id)
            ->where('batch_no', 'BT-FINAL-2')
            ->first();
        $this->assertNotNull($batch2);
        $this->assertEquals(6.0, (float) $batch2->quantity);

        // Second Purchase record created for Purchase Ledger
        $purchase2 = Purchase::where('invoice_no', 'CH-FINAL-2')->first();
        $this->assertNotNull($purchase2);
        $this->assertEquals(3000.00, (float) $purchase2->total);
    }

    public function test_user_can_cancel_pending_delivery_order(): void
    {
        $order = PurchaseDeliveryOrder::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_no' => 'PDO-CANCEL-001',
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'subtotal' => 1000.00,
            'total_amount' => 1000.00,
            'due_amount' => 1000.00,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('purchase-delivery-orders.cancel', $order));
        $response->assertRedirect(route('purchase-delivery-orders.show', $order));

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_cannot_receive_more_than_pending_quantity(): void
    {
        $order = PurchaseDeliveryOrder::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_no' => 'PDO-EXCEED-001',
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'subtotal' => 2500.00,
            'total_amount' => 2500.00,
            'due_amount' => 2500.00,
            'created_by' => $this->user->id,
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $this->product->id,
            'ordered_quantity' => 5.00,
            'received_quantity' => 0.00,
            'purchase_price' => 500.00,
            'subtotal' => 2500.00,
        ]);

        $response = $this->actingAs($this->user)->post(route('purchase-delivery-orders.store-receive', $order), [
            'delivery_date' => now()->toDateString(),
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'received_quantity' => 10, // Exceeds 5!
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Batch::where('product_id', $this->product->id)->count());
    }

    public function test_user_can_view_order_print_and_receipt_print(): void
    {
        $order = PurchaseDeliveryOrder::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_no' => 'PDO-PRINT-001',
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'subtotal' => 500.00,
            'total_amount' => 500.00,
            'due_amount' => 500.00,
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'ordered_quantity' => 1.00,
            'received_quantity' => 0.00,
            'purchase_price' => 500.00,
            'subtotal' => 500.00,
        ]);

        $printOrderResponse = $this->actingAs($this->user)->get(route('purchase-delivery-orders.print', $order));
        $printOrderResponse->assertOk();
        $printOrderResponse->assertSee('পারচেজ ডেলিভারি অর্ডার (PDO)');

        // Create a receipt
        $receipt = PurchaseDeliveryReceipt::create([
            'shop_id' => $this->shop->id,
            'purchase_delivery_order_id' => $order->id,
            'receipt_no' => 'PDR-PRINT-001',
            'warehouse_id' => $this->warehouse->id,
            'delivery_date' => now()->toDateString(),
            'total_amount' => 500.00,
        ]);

        $printReceiptResponse = $this->actingAs($this->user)->get(route('purchase-delivery-receipts.print', $receipt));
        $printReceiptResponse->assertOk();
        $printReceiptResponse->assertSee('পণ্য গ্রহণ চালান (Goods Received Note)');
    }

    public function test_purchase_ledger_can_search_by_invoice_no(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_no' => 'PU-PDO-1-1',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 1000.00,
            'total' => 1000.00,
            'paid_amount' => 0.00,
            'due_amount' => 1000.00,
            'payment_status' => 'due',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('purchase.ledger', ['q' => 'PU-PDO-1-1']), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['recordsFiltered' => 1]);
        $this->assertStringContainsString('PU-PDO-1-1', $response->json('data.0.invoice_no'));
        $this->assertStringContainsString($this->supplier->name, $response->json('data.0.supplier'));
    }
}
