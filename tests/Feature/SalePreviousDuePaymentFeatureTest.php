<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SalePayment;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalePreviousDuePaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected Product $product;

    protected Account $cashAccount;

    protected Account $bankAccount;

    protected Batch $batch;

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
            'name' => 'Sale Test Shop',
            'slug' => 'sale-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Sale Admin',
            'email' => 'admin@saletest.test',
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

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Rahim Customer',
            'phone' => '01711999777',
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

        $this->batch = Batch::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_no' => 'B-01',
            'quantity' => 100,
        ]);
    }

    public function test_create_sale_can_pay_current_bill_and_customer_opening_due(): void
    {
        // Sale: 2 qty at 150 = 300.00 total.
        // Customer opening due is 500.00. Total payable is 800.00.
        // User pays 600.00 (300 for current sale, 300 for opening due).
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 150.00,
                    'discount' => 0,
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

        $response->assertRedirect(route('sales.index'));

        $newSale = Sale::latest('id')->first();
        $this->assertNotNull($newSale);
        $this->assertEquals(300.00, (float) $newSale->total);
        $this->assertEquals(300.00, (float) $newSale->paid_amount);
        $this->assertEquals(0.00, (float) $newSale->due_amount);
        $this->assertEquals('paid', $newSale->payment_status);

        // Check payment on the new sale
        $this->assertEquals(1, $newSale->payments()->count());
        $this->assertEquals(300.00, (float) $newSale->payments()->sum('amount'));

        // Customer opening due should be reduced by 300 (500 - 300 = 200)
        $this->customer->refresh();
        $this->assertEquals(200.00, (float) $this->customer->opening_due);

        // Due ledger check: remaining total due should be 200.00
        $dueLedgerResponse = $this->actingAs($this->user)->getJson(route('due-ledger.sales'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $dueLedgerResponse->assertOk();
        $this->assertEquals('200.00', $dueLedgerResponse->json('totalDue'));

        // Cash account balance increased by 600 (20000 + 600 = 20600)
        $this->cashAccount->refresh();
        $this->assertEquals(20600.00, (float) $this->cashAccount->current_balance);
    }

    public function test_create_sale_can_pay_current_bill_opening_due_and_earlier_sales_fifo(): void
    {
        // Customer opening due = 200.00
        $this->customer->update(['opening_due' => 200.00]);

        // Sale 1: due 300.00
        $s1 = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-HIST-01',
            'sale_date' => now()->subDays(5)->toDateString(),
            'subtotal' => 500.00,
            'total' => 500.00,
            'paid_amount' => 200.00,
            'due_amount' => 300.00,
            'payment_status' => 'partial',
        ]);

        // Sale 2: due 400.00
        $s2 = Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'invoice_no' => 'SL-HIST-02',
            'sale_date' => now()->subDays(3)->toDateString(),
            'subtotal' => 400.00,
            'total' => 400.00,
            'paid_amount' => 0.00,
            'due_amount' => 400.00,
            'payment_status' => 'due',
        ]);

        // Total previous due = 200 (opening) + 300 (s1) + 400 (s2) = 900.00.
        // New sale: 2 qty * 150 = 300.00.
        // Total payable = 300 + 900 = 1200.00.
        // User pays 900.00 (Cash: 500, Bank: 400).
        // Allocation:
        // - 300 pays new sale (from Cash: 300). Remaining Cash = 200, Remaining Bank = 400.
        // - Excess 600 for previous due:
        //   - 200 pays opening_due completely -> opening_due = 0. (from Cash: 200, Bank: 0).
        //   - 300 pays s1 completely -> s1 paid = 500, due = 0, status = 'paid'. (from Bank: 300).
        //   - 100 pays s2 partially -> s2 paid = 100, due = 300, status = 'partial'. (from Bank: 100).
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 150.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 500.00,
                ],
                [
                    'account_id' => $this->bankAccount->id,
                    'method' => 'bank',
                    'amount' => 400.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        $newSale = Sale::whereNotIn('id', [$s1->id, $s2->id])->latest('id')->first();
        $this->assertNotNull($newSale);
        $this->assertEquals(300.00, (float) $newSale->total);
        $this->assertEquals(300.00, (float) $newSale->paid_amount);
        $this->assertEquals(0.00, (float) $newSale->due_amount);
        $this->assertEquals('paid', $newSale->payment_status);

        $this->customer->refresh();
        $s1->refresh();
        $s2->refresh();

        $this->assertEquals(0.00, (float) $this->customer->opening_due);
        $this->assertEquals(500.00, (float) $s1->paid_amount);
        $this->assertEquals(0.00, (float) $s1->due_amount);
        $this->assertEquals('paid', $s1->payment_status);

        $this->assertEquals(100.00, (float) $s2->paid_amount);
        $this->assertEquals(300.00, (float) $s2->due_amount);
        $this->assertEquals('partial', $s2->payment_status);

        // Remaining customer total due should be 300.00
        $dueLedgerResponse = $this->actingAs($this->user)->getJson(route('due-ledger.sales'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $dueLedgerResponse->assertOk();
        $this->assertEquals('300.00', $dueLedgerResponse->json('totalDue'));

        // Accounts: Cash added 500, Bank added 400
        $this->cashAccount->refresh();
        $this->bankAccount->refresh();
        $this->assertEquals(20500.00, (float) $this->cashAccount->current_balance);
        $this->assertEquals(20400.00, (float) $this->bankAccount->current_balance);

        // Verify account transactions occurred_at dates are today's sale date, not historical dates
        $s1Payment = $s1->payments()->latest('id')->first();
        $this->assertNotNull($s1Payment);
        $this->assertEquals(now()->toDateString(), $s1Payment->payment_date->toDateString());

        $tx = AccountTransaction::where('sourceable_type', SalePayment::class)
            ->where('sourceable_id', $s1Payment->id)
            ->first();
        $this->assertNotNull($tx);
        $this->assertEquals(now()->toDateString(), $tx->occurred_at->toDateString());
        $this->assertStringContainsString('বিক্রয় ইনভয়েস: '.$newSale->invoice_no.' থেকে সমন্বয়কৃত', $tx->note);

        // Deleting $newSale reverts the excess payments on opening due and previous sales
        $deleteResponse = $this->actingAs($this->user)->delete(route('sales.destroy', $newSale));
        $deleteResponse->assertRedirect(route('sales.index'));

        $this->customer->refresh();
        $s1->refresh();
        $s2->refresh();

        $this->assertEquals(200.00, (float) $this->customer->opening_due);
        $this->assertEquals(200.00, (float) $s1->paid_amount);
        $this->assertEquals(300.00, (float) $s1->due_amount);
        $this->assertEquals('partial', $s1->payment_status);

        $this->assertEquals(0.00, (float) $s2->paid_amount);
        $this->assertEquals(400.00, (float) $s2->due_amount);
        $this->assertEquals('due', $s2->payment_status);
    }

    public function test_create_sale_paying_only_current_bill_does_not_touch_previous_due(): void
    {
        $this->customer->update(['opening_due' => 400.00]);

        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 150.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 300.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        $newSale = Sale::latest('id')->first();
        $this->assertEquals(300.00, (float) $newSale->total);
        $this->assertEquals(300.00, (float) $newSale->paid_amount);
        $this->assertEquals(0.00, (float) $newSale->due_amount);
        $this->assertEquals('paid', $newSale->payment_status);

        $this->customer->refresh();
        $this->assertEquals(400.00, (float) $this->customer->opening_due);
    }

    public function test_create_sale_cannot_pay_more_than_total_bill_plus_previous_due(): void
    {
        $this->customer->update(['opening_due' => 200.00]);

        // Sale total = 300.00, previous due = 200.00. Max payable = 500.00.
        // Trying to pay 700.00 should fail validation.
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 150.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'method' => 'cash',
                    'amount' => 700.00,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['payments']);
    }
}
