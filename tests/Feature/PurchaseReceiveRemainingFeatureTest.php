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

class PurchaseReceiveRemainingFeatureTest extends TestCase
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
            'name' => 'Receive Test Shop',
            'slug' => 'receive-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Stock Admin',
            'email' => 'admin@receive.test',
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
            'name' => 'Brother Enterprise',
            'phone' => '01711223344',
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'Electronics',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Earphone',
            'sku' => 'EP-01',
            'purchase_price' => 100,
            'sale_price' => 120,
            'status' => 'active',
        ]);
    }

    public function test_can_find_purchase_by_do_number_and_receive_remaining_quantity(): void
    {
        // 1. Create a purchase with ordered = 50, received = 40 (pending = 10)
        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BT-TEST-BATCH-01',
            'quantity' => 40.00,
        ]);

        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'PU-0032',
            'purchase_date' => '2026-09-04',
            'do_number' => 'PD-001',
            'do_date' => '2026-09-04',
            'subtotal' => 5000,
            'total' => 5000,
            'paid_amount' => 4000,
            'due_amount' => 1000,
            'payment_status' => 'partial',
        ]);

        $purchaseItem = $purchase->items()->create([
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'batch_no' => 'BT-TEST-BATCH-01',
            'quantity' => 50.00,
            'received_quantity' => 40.00,
            'purchase_price' => 100.00,
            'total' => 5000.00,
        ]);

        $purchase->receiptItems()->create([
            'shop_id' => $this->shop->id,
            'purchase_item_id' => $purchaseItem->id,
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'received_quantity' => 40.00,
            'do_number' => 'PD-001',
            'do_date' => '2026-09-04',
            'received_by' => $this->user->id,
        ]);

        $this->assertTrue($purchase->hasPendingItems());
        $this->assertEquals(10.00, $purchase->totalPendingQuantity());
        $this->assertEquals(10.00, $purchaseItem->pendingQuantity());

        // 2. Test find-by-do endpoint
        $findResponse = $this->actingAs($this->user)->getJson(route('purchase.find-by-do', ['do_number' => 'PD-001']));
        $findResponse->assertOk()
            ->assertJson([
                'success' => true,
                'purchase_id' => $purchase->id,
                'invoice_no' => 'PU-0032',
            ]);

        // 3. Test receive-modal endpoint
        $modalResponse = $this->actingAs($this->user)->get(route('purchase.receive.modal', $purchase));
        $modalResponse->assertOk()
            ->assertSee('ডিও দিয়ে বাকি পণ্য গ্রহণ')
            ->assertSee('PU-0032')
            ->assertSee('Earphone');

        // 4. Test submitting receive form with do_number and remaining quantity 10
        $receivePayload = [
            'do_number' => 'PD-002',
            'do_date' => '2026-09-04',
            'vehicle_number' => 'DHAKA-METRO-11',
            'delivery_person_name' => 'Karim',
            'items' => [
                [
                    'purchase_item_id' => $purchaseItem->id,
                    'received_qty' => 10,
                    'batch_no' => 'BT-TEST-BATCH-01',
                ],
            ],
        ];

        $receiveResponse = $this->actingAs($this->user)->postJson(route('purchase.receive.store', $purchase), $receivePayload);
        $receiveResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // 5. Verify batch stock increased by 10 (40 -> 50)
        $batch->refresh();
        $this->assertEquals(50.00, (float) $batch->quantity);

        // 6. Verify purchase item received_quantity is now 50
        $purchaseItem->refresh();
        $this->assertEquals(50.00, (float) $purchaseItem->received_quantity);
        $this->assertEquals(0.00, $purchaseItem->pendingQuantity());
        $this->assertTrue($purchaseItem->isFullyReceived());

        // 7. Verify purchase no longer has pending items
        $purchase->refresh();
        $this->assertFalse($purchase->hasPendingItems());
        $this->assertEquals(0.00, $purchase->totalPendingQuantity());
        $this->assertTrue($purchase->isFullyReceived());

        // 8. Verify purchase_receipt_items has the second receipt record with PD-002
        $this->assertDatabaseHas('purchase_receipt_items', [
            'purchase_id' => $purchase->id,
            'purchase_item_id' => $purchaseItem->id,
            'received_quantity' => 10.00,
            'do_number' => 'PD-002',
            'vehicle_number' => 'DHAKA-METRO-11',
            'delivery_person_name' => 'Karim',
            'received_by' => $this->user->id,
        ]);

        // 9. Verify stock movement recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'type' => 'purchase',
            'quantity_change' => 10.00,
            'quantity_before' => 40.00,
            'quantity_after' => 50.00,
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
        ]);

        // 10. Once fully received, finding by DO should indicate already fully received
        $secondFindResponse = $this->actingAs($this->user)->getJson(route('purchase.find-by-do', ['do_number' => 'PD-002']));
        $secondFindResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'fully_received' => true,
            ]);
    }

    public function test_cannot_receive_more_than_pending_quantity(): void
    {
        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BT-TEST-BATCH-02',
            'quantity' => 40.00,
        ]);

        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'PU-0033',
            'purchase_date' => '2026-09-04',
            'do_number' => 'PD-003',
            'subtotal' => 5000,
            'total' => 5000,
            'paid_amount' => 5000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $purchaseItem = $purchase->items()->create([
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'batch_no' => 'BT-TEST-BATCH-02',
            'quantity' => 50.00,
            'received_quantity' => 40.00,
            'purchase_price' => 100.00,
            'total' => 5000.00,
        ]);

        // Try to receive 15 when pending is 10
        $response = $this->actingAs($this->user)->postJson(route('purchase.receive.store', $purchase), [
            'do_number' => 'PD-004',
            'items' => [
                [
                    'purchase_item_id' => $purchaseItem->id,
                    'received_qty' => 15,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_do_number_is_required_to_receive_goods(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'PU-0034',
            'purchase_date' => '2026-09-04',
            'subtotal' => 5000,
            'total' => 5000,
            'paid_amount' => 5000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $purchaseItem = $purchase->items()->create([
            'product_id' => $this->product->id,
            'batch_no' => 'BT-TEST-03',
            'quantity' => 50.00,
            'received_quantity' => 40.00,
            'purchase_price' => 100.00,
            'total' => 5000.00,
        ]);

        // Missing do_number
        $response = $this->actingAs($this->user)->postJson(route('purchase.receive.store', $purchase), [
            'items' => [
                [
                    'purchase_item_id' => $purchaseItem->id,
                    'received_qty' => 10,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['do_number']);
    }

    public function test_can_view_product_receipt_history_modal(): void
    {
        $batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'BT-TEST-HIST-01',
            'quantity' => 40.00,
        ]);

        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'PU-0035',
            'purchase_date' => '2026-09-04',
            'subtotal' => 5000,
            'total' => 5000,
            'paid_amount' => 5000,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $purchaseItem = $purchase->items()->create([
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'batch_no' => 'BT-TEST-HIST-01',
            'quantity' => 50.00,
            'received_quantity' => 40.00,
            'purchase_price' => 100.00,
            'total' => 5000.00,
        ]);

        $purchase->receiptItems()->create([
            'shop_id' => $this->shop->id,
            'purchase_item_id' => $purchaseItem->id,
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'received_quantity' => 40.00,
            'do_number' => 'DO-HIST-999',
            'do_date' => '2026-09-04',
            'vehicle_number' => 'TRUCK-777',
            'delivery_person_name' => 'Driver Rafiq',
            'received_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase.receipt-history', $purchase));

        $response->assertOk()
            ->assertSee('পণ্য গ্রহণের ইতিহাস')
            ->assertSee('DO-HIST-999')
            ->assertSee('TRUCK-777')
            ->assertSee('Driver Rafiq')
            ->assertSee('Earphone');

        // Test action view renders receipt history button
        $actionsView = view('purchase::purchase.datatables-actions', ['purchase' => $purchase])->render();
        $this->assertStringContainsString('btn-receipt-history', $actionsView);
        $this->assertStringContainsString(route('purchase.receipt-history', $purchase), $actionsView);
    }
}
