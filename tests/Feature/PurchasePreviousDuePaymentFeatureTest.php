<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
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

class PurchasePreviousDuePaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Product $product;

    protected Account $cashAccount;

    protected Account $bankAccount;

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
            'name' => 'Purchase Test Shop',
            'slug' => 'purchase-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Purchase Admin',
            'email' => 'admin@purchasetest.test',
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

        $this->cashAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Account',
            'type' => 'cash',
            'opening_balance' => 20000.00,
            'current_balance' => 20000.00,
            'status' => 'active',
            'is_default' => true,
        ]);

        $this->bankAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Bank Account',
            'type' => 'bank',
            'opening_balance' => 20000.00,
            'current_balance' => 20000.00,
            'status' => 'active',
            'is_default' => false,
        ]);

        $this->supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'ABC Supplier',
            'phone' => '01711999888',
            'address' => 'Dhaka, Bangladesh',
            'opening_due' => 500.00,
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'General',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'TP-01',
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
            'status' => 'active',
        ]);
    }

    public function test_create_purchase_can_pay_current_bill_and_supplier_opening_due(): void
    {
        // Purchase of 10 qty at 100 = 1000.00 total.
        // Supplier opening due is 500.00. Total payable is 1500.00.
        // User pays 1300.00 (1000 for current purchase, 300 for opening due).
        $response = $this->actingAs($this->user)->post(route('purchase.store'), [
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'purchase_price' => 100.00,
                    'sale_price' => 150.00,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 1300.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchase.index'));

        $newPurchase = Purchase::latest('id')->first();
        $this->assertNotNull($newPurchase);
        $this->assertEquals(1000.00, (float) $newPurchase->total);
        $this->assertEquals(1000.00, (float) $newPurchase->paid_amount);
        $this->assertEquals(0.00, (float) $newPurchase->due_amount);
        $this->assertEquals('paid', $newPurchase->payment_status);

        // Check payments on the new purchase
        $this->assertEquals(1, $newPurchase->payments()->count());
        $this->assertEquals(1000.00, (float) $newPurchase->payments()->sum('amount'));

        // Supplier opening due should be reduced by 300 (500 - 300 = 200)
        $this->supplier->refresh();
        $this->assertEquals(200.00, (float) $this->supplier->opening_due);

        // Total due in due ledger is opening_due (200) + purchase due (0) = 200.00
        $dueLedgerResponse = $this->actingAs($this->user)->getJson(route('due-ledger.purchase'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $dueLedgerResponse->assertOk();
        $this->assertEquals('200.00', $dueLedgerResponse->json('totalDue'));

        // Cash account deducted by 1300 (20000 - 1300 = 18700)
        $this->cashAccount->refresh();
        $this->assertEquals(18700.00, (float) $this->cashAccount->current_balance);
    }

    public function test_create_purchase_can_pay_current_bill_opening_due_and_earlier_purchases_fifo(): void
    {
        // Supplier opening due = 300.00
        $this->supplier->update(['opening_due' => 300.00]);

        // Purchase 1: due 400.00
        $p1 = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'PU-HIST-01',
            'purchase_date' => now()->subDays(5)->toDateString(),
            'subtotal' => 1000.00,
            'total' => 1000.00,
            'paid_amount' => 600.00,
            'due_amount' => 400.00,
            'payment_status' => 'partial',
        ]);

        // Purchase 2: due 800.00
        $p2 = Purchase::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'invoice_no' => 'PU-HIST-02',
            'purchase_date' => now()->subDays(3)->toDateString(),
            'subtotal' => 800.00,
            'total' => 800.00,
            'paid_amount' => 0.00,
            'due_amount' => 800.00,
            'payment_status' => 'due',
        ]);

        // Total previous due = 300 (opening) + 400 (p1) + 800 (p2) = 1500.00.
        // New purchase: 5 qty * 200 = 1000.00.
        // Grand total payable = 1000 + 1500 = 2500.00.
        // User pays 2000.00 (Cash: 1200, Bank: 800).
        // Allocation:
        // - 1000 pays new purchase (from Cash: 1000). Remaining Cash = 200, Remaining Bank = 800.
        // - Excess 1000 for previous due:
        //   - 300 pays opening_due completely -> opening_due = 0. (from Cash: 200, Bank: 100).
        //   - 400 pays p1 completely -> p1 paid = 1000, due = 0, status = 'paid'. (from Bank: 400).
        //   - 300 pays p2 partially -> p2 paid = 300, due = 500, status = 'partial'. (from Bank: 300).
        $response = $this->actingAs($this->user)->post(route('purchase.store'), [
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'purchase_price' => 200.00,
                    'sale_price' => 250.00,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 1200.00,
                ],
                [
                    'account_id' => $this->bankAccount->id,
                    'method' => 'bank',
                    'amount' => 800.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchase.index'));

        $newPurchase = Purchase::whereNotIn('id', [$p1->id, $p2->id])->latest('id')->first();
        $this->assertNotNull($newPurchase);
        $this->assertEquals(1000.00, (float) $newPurchase->total);
        $this->assertEquals(1000.00, (float) $newPurchase->paid_amount);
        $this->assertEquals(0.00, (float) $newPurchase->due_amount);
        $this->assertEquals('paid', $newPurchase->payment_status);

        $this->supplier->refresh();
        $p1->refresh();
        $p2->refresh();

        $this->assertEquals(0.00, (float) $this->supplier->opening_due);
        $this->assertEquals(1000.00, (float) $p1->paid_amount);
        $this->assertEquals(0.00, (float) $p1->due_amount);
        $this->assertEquals('paid', $p1->payment_status);

        $this->assertEquals(300.00, (float) $p2->paid_amount);
        $this->assertEquals(500.00, (float) $p2->due_amount);
        $this->assertEquals('partial', $p2->payment_status);

        // Due ledger check: remaining total due should be 500.00
        $dueLedgerResponse = $this->actingAs($this->user)->getJson(route('due-ledger.purchase'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $dueLedgerResponse->assertOk();
        $this->assertEquals('500.00', $dueLedgerResponse->json('totalDue'));

        // Account balances: Cash deducted 1200, Bank deducted 800
        $this->cashAccount->refresh();
        $this->bankAccount->refresh();
        $this->assertEquals(18800.00, (float) $this->cashAccount->current_balance);
        $this->assertEquals(19200.00, (float) $this->bankAccount->current_balance);
    }

    public function test_create_purchase_paying_only_current_bill_does_not_touch_previous_due(): void
    {
        $this->supplier->update(['opening_due' => 400.00]);

        $response = $this->actingAs($this->user)->post(route('purchase.store'), [
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'purchase_price' => 100.00,
                    'sale_price' => 150.00,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 1000.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchase.index'));

        $newPurchase = Purchase::latest('id')->first();
        $this->assertEquals(1000.00, (float) $newPurchase->total);
        $this->assertEquals(1000.00, (float) $newPurchase->paid_amount);
        $this->assertEquals(0.00, (float) $newPurchase->due_amount);

        $this->supplier->refresh();
        $this->assertEquals(400.00, (float) $this->supplier->opening_due);
    }

    public function test_create_purchase_partial_payment_does_not_touch_previous_due(): void
    {
        $this->supplier->update(['opening_due' => 400.00]);

        $response = $this->actingAs($this->user)->post(route('purchase.store'), [
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'purchase_price' => 100.00,
                    'sale_price' => 150.00,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 600.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchase.index'));

        $newPurchase = Purchase::latest('id')->first();
        $this->assertEquals(1000.00, (float) $newPurchase->total);
        $this->assertEquals(600.00, (float) $newPurchase->paid_amount);
        $this->assertEquals(400.00, (float) $newPurchase->due_amount);
        $this->assertEquals('partial', $newPurchase->payment_status);

        $this->supplier->refresh();
        $this->assertEquals(400.00, (float) $this->supplier->opening_due);
    }

    public function test_create_purchase_cannot_pay_more_than_total_bill_plus_previous_due(): void
    {
        $this->supplier->update(['opening_due' => 200.00]);

        // Purchase total = 1000.00, previous due = 200.00. Max payable = 1200.00.
        // Trying to pay 1500.00 should fail validation.
        $response = $this->actingAs($this->user)->post(route('purchase.store'), [
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'purchase_price' => 100.00,
                    'sale_price' => 150.00,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 1500.00,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['payments']);
    }
}
