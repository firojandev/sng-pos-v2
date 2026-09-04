<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchasePayment;
use Modules\Purchase\Models\PurchaseReturn;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseDeleteAndRollbackFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Product $product;

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
            'name' => 'Rollback Shop',
            'slug' => 'rollback-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Rollback Admin',
            'email' => 'admin@rollback.test',
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
            'name' => 'Key Supplier',
            'phone' => '01711998877',
            'opening_due' => 0,
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'Groceries',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Basmati Rice',
            'sku' => 'BR-01',
            'purchase_price' => 100,
            'sale_price' => 130,
            'status' => 'active',
        ]);

        $this->account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Primary Bank Account',
            'type' => 'bank',
            'opening_balance' => 5000,
            'current_balance' => 5000,
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    public function test_purchase_cannot_be_deleted_if_quantity_was_used_in_sale(): void
    {
        // 1. Create a purchase with 10 units and 400 paid
        $purchasePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-LOCK-01',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'received_qty' => 10,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-LOCK-01',
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->account->id,
                    'method' => 'bank',
                    'amount' => 400,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('purchase.store'), $purchasePayload);

        $purchase = Purchase::where('invoice_no', 'PU-LOCK-01')->firstOrFail();
        $batch = Batch::where('batch_no', 'BT-LOCK-01')->firstOrFail();
        $this->assertEquals(10.0, (float) $batch->quantity);

        // 2. Perform a sale consuming 2 units from this batch
        $salePayload = [
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 130,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 260,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('sales.store'), $salePayload);

        $batch->refresh();
        $this->assertEquals(8.0, (float) $batch->quantity);

        // 3. Verify Purchase::hasUsedQuantity() is true
        $this->assertTrue($purchase->hasUsedQuantity());
        $this->assertFalse($purchase->canBeDeleted());
        $this->assertNotNull($purchase->cannotBeDeletedReason());

        // 4. Attempt to delete purchase via AJAX -> should fail with 422
        $response = $this->actingAs($this->user)
            ->deleteJson(route('purchase.destroy', $purchase));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('বিক্রয়', $response->json('message'));

        // Verify purchase was NOT deleted
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'deleted_at' => null,
        ]);
    }

    public function test_purchase_cannot_be_deleted_if_purchase_return_exists(): void
    {
        $purchasePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-RETURN-01',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'received_qty' => 10,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-RET-01',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('purchase.store'), $purchasePayload);
        $purchase = Purchase::where('invoice_no', 'PU-RETURN-01')->firstOrFail();

        // Create a return against this purchase
        PurchaseReturn::create([
            'shop_id' => $this->shop->id,
            'purchase_id' => $purchase->id,
            'return_no' => 'RT-TEST-01',
            'return_date' => now()->toDateString(),
            'subtotal' => 200,
            'refund_amount' => 0,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($purchase->hasUsedQuantity());
        $this->assertFalse($purchase->canBeDeleted());

        $response = $this->actingAs($this->user)
            ->deleteJson(route('purchase.destroy', $purchase));

        $response->assertStatus(422);
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'deleted_at' => null,
        ]);
    }

    public function test_purchase_delete_rolls_back_stock_payment_account_balance_cash_and_supplier_due(): void
    {
        $initialBalance = (float) $this->account->current_balance; // 5000

        // 1. Create a purchase of 10 units @ 100 = 1000, 400 paid from Bank Account, 600 due
        $purchasePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-ROLLBACK-ALL',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'received_qty' => 10,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-ROLLBACK-01',
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->account->id,
                    'method' => 'bank',
                    'amount' => 400,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('purchase.store'), $purchasePayload);

        $purchase = Purchase::where('invoice_no', 'PU-ROLLBACK-ALL')->firstOrFail();
        $batch = Batch::where('batch_no', 'BT-ROLLBACK-01')->firstOrFail();

        // Check state after purchase:
        // Stock: 10
        $this->assertEquals(10.0, (float) $batch->quantity);
        // Bank Account balance deducted: 5000 - 400 = 4600
        $this->account->refresh();
        $this->assertEquals(4600.0, (float) $this->account->current_balance);
        // AccountTransaction created:
        $payment = $purchase->payments->first();
        $this->assertNotNull($payment);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $this->account->id,
            'sourceable_type' => PurchasePayment::class,
            'sourceable_id' => $payment->id,
            'type' => 'out',
            'amount' => 400.0,
        ]);
        // CashTransaction created:
        $this->assertDatabaseHas('cash_transactions', [
            'sourceable_type' => Purchase::class,
            'sourceable_id' => $purchase->id,
            'type' => 'out',
            'amount' => 400.0,
        ]);
        // Supplier total due: 600
        $this->assertEquals('600.00', $this->supplier->totalDue());

        // 2. Can be deleted since 0 quantity was used
        $this->assertFalse($purchase->hasUsedQuantity());
        $this->assertTrue($purchase->canBeDeleted());

        // 3. Delete the purchase via AJAX
        $response = $this->actingAs($this->user)
            ->deleteJson(route('purchase.destroy', $purchase));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // 4. VERIFY COMPLETE ROLLBACK:
        // a) Stock rolled back: Batch quantity becomes 0
        $batch->refresh();
        $this->assertEquals(0.0, (float) $batch->quantity);

        // b) StockMovement 'purchase_reversal' logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'type' => 'purchase_reversal',
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
            'quantity_change' => -10.0,
        ]);

        // c) Bank Account balance restored to original 5000
        $this->account->refresh();
        $this->assertEquals($initialBalance, (float) $this->account->current_balance);

        // d) AccountTransaction deleted
        $this->assertDatabaseMissing('account_transactions', [
            'sourceable_type' => PurchasePayment::class,
            'sourceable_id' => $payment->id,
        ]);

        // e) Cashbox CashTransaction deleted (soft deleted and excluded from cashbox query)
        $this->assertSoftDeleted('cash_transactions', [
            'sourceable_type' => Purchase::class,
            'sourceable_id' => $purchase->id,
        ]);
        $this->assertNull(CashTransaction::where('sourceable_type', Purchase::class)->where('sourceable_id', $purchase->id)->first());

        // f) Supplier due rolled back to 0
        $this->assertEquals('0.00', $this->supplier->totalDue());

        // g) Purchase is soft-deleted
        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);

        // h) Purchase payments are soft-deleted
        $this->assertSoftDeleted('purchase_payments', ['id' => $payment->id]);

        // i) Purchase items are soft-deleted
        $this->assertSoftDeleted('purchase_items', ['purchase_id' => $purchase->id]);

        // j) Receipt items are deleted
        $this->assertDatabaseMissing('purchase_receipt_items', [
            'purchase_id' => $purchase->id,
        ]);
    }

    public function test_purchase_delete_redirects_with_error_on_standard_http_request_if_quantity_used(): void
    {
        $purchasePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-STD-LOCK',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'received_qty' => 5,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-STD-LOCK',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('purchase.store'), $purchasePayload);
        $purchase = Purchase::where('invoice_no', 'PU-STD-LOCK')->firstOrFail();
        $batch = Batch::where('batch_no', 'BT-STD-LOCK')->firstOrFail();

        // Reduce batch directly (e.g. consumed/used)
        $batch->decrement('quantity', 1);

        $response = $this->actingAs($this->user)
            ->delete(route('purchase.destroy', $purchase));

        $response->assertRedirect(route('purchase.ledger'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'deleted_at' => null,
        ]);
    }

    public function test_purchase_delete_blocked_when_stock_movement_indicates_decrease(): void
    {
        $purchasePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-ADJ-LOCK',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'received_qty' => 10,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-ADJ-LOCK',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('purchase.store'), $purchasePayload);
        $purchase = Purchase::where('invoice_no', 'PU-ADJ-LOCK')->firstOrFail();
        $batch = Batch::where('batch_no', 'BT-ADJ-LOCK')->firstOrFail();

        // Simulate a stock movement decrease (e.g. damaged stock adjustment)
        $batch->decrement('quantity', 2);
        StockMovement::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'type' => 'adjustment_decrease',
            'quantity_change' => -2,
            'quantity_before' => 10,
            'quantity_after' => 8,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($purchase->hasUsedQuantity());
        $this->assertFalse($purchase->canBeDeleted());

        $response = $this->actingAs($this->user)
            ->deleteJson(route('purchase.destroy', $purchase));

        $response->assertStatus(422);
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'deleted_at' => null,
        ]);
    }

    public function test_purchase_cannot_be_edited_if_quantity_was_used(): void
    {
        $purchasePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-EDIT-LOCK',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'received_qty' => 10,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-EDIT-LOCK',
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->account->id,
                    'method' => 'bank',
                    'amount' => 400,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('purchase.store'), $purchasePayload);
        $purchase = Purchase::where('invoice_no', 'PU-EDIT-LOCK')->firstOrFail();
        $batch = Batch::where('batch_no', 'BT-EDIT-LOCK')->firstOrFail();

        // 1. Consume 1 unit via sale
        $salePayload = [
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 130,
                ],
            ],
        ];
        $this->actingAs($this->user)->post(route('sales.store'), $salePayload);

        $this->assertTrue($purchase->hasUsedQuantity());
        $this->assertFalse($purchase->canBeEdited());

        // 2. GET edit should redirect with error
        $editResponse = $this->actingAs($this->user)->get(route('purchase.edit', $purchase));
        $editResponse->assertRedirect(route('purchase.ledger'));
        $editResponse->assertSessionHas('error');

        // 3. PUT update should fail with 422 JSON
        $updateResponse = $this->actingAs($this->user)->putJson(route('purchase.update', $purchase), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                ],
            ],
        ]);
        $updateResponse->assertStatus(422);
    }

    public function test_purchase_edit_syncs_all_data_like_delete_and_reapply(): void
    {
        $secondAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Secondary Account',
            'type' => 'bank',
            'opening_balance' => 2000,
            'current_balance' => 2000,
            'status' => 'active',
        ]);

        // 1. Create Purchase: 10 units @ 100 = 1000, paid 400 from primary account (balance 5000 -> 4600)
        $purchasePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-EDIT-SYNC',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'received_qty' => 10,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-SYNC-OLD',
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->account->id,
                    'method' => 'bank',
                    'amount' => 400,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('purchase.store'), $purchasePayload);
        $purchase = Purchase::where('invoice_no', 'PU-EDIT-SYNC')->firstOrFail();
        $oldBatch = Batch::where('batch_no', 'BT-SYNC-OLD')->firstOrFail();

        $this->assertEquals(10.0, (float) $oldBatch->quantity);
        $this->account->refresh();
        $this->assertEquals(4600.0, (float) $this->account->current_balance);
        $this->assertEquals('600.00', $this->supplier->totalDue());

        // 2. Edit Purchase: Change to 6 units @ 100 = 600, paid 200 from secondAccount
        $updatePayload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'invoice_no' => 'PU-EDIT-SYNC',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 6,
                    'received_qty' => 6,
                    'purchase_price' => 100,
                    'sale_price' => 130,
                    'batch_no' => 'BT-SYNC-NEW',
                ],
            ],
            'payments' => [
                [
                    'account_id' => $secondAccount->id,
                    'method' => 'bank',
                    'amount' => 200,
                ],
            ],
        ];

        $updateResponse = $this->actingAs($this->user)->put(route('purchase.update', $purchase), $updatePayload);
        $updateResponse->assertRedirect(route('purchase.index'));

        // 3. Verify ALL data synchronized:
        // a) Old batch stock rolled back: 10 -> 0
        $oldBatch->refresh();
        $this->assertEquals(0.0, (float) $oldBatch->quantity);

        // b) New batch stock applied: 6
        $newBatch = Batch::where('batch_no', 'BT-SYNC-NEW')->firstOrFail();
        $this->assertEquals(6.0, (float) $newBatch->quantity);

        // c) Old account refunded: 4600 -> 5000
        $this->account->refresh();
        $this->assertEquals(5000.0, (float) $this->account->current_balance);

        // d) New account deducted: 2000 - 200 = 1800
        $secondAccount->refresh();
        $this->assertEquals(1800.0, (float) $secondAccount->current_balance);

        // e) Purchase total, paid, due updated
        $purchase->refresh();
        $this->assertEquals(600.0, (float) $purchase->total);
        $this->assertEquals(200.0, (float) $purchase->paid_amount);
        $this->assertEquals(400.0, (float) $purchase->due_amount);

        // f) Supplier due updated: 600 -> 400
        $this->assertEquals('400.00', $this->supplier->totalDue());

        // g) Cashbox CashTransaction updated to 200
        $cashTx = CashTransaction::where('sourceable_type', Purchase::class)
            ->where('sourceable_id', $purchase->id)
            ->first();
        $this->assertNotNull($cashTx);
        $this->assertEquals(200.0, (float) $cashTx->amount);
    }
}
