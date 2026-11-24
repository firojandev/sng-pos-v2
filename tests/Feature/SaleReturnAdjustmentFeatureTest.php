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
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleReturnAdjustmentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected Product $product;

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
            'name' => 'Return Test Shop',
            'slug' => 'return-test-shop',
            'status' => 'active',
        ]);
        $this->subscribeShopToFeatures($this->shop, Features::keys());

        $this->user = User::create([
            'name' => 'Return Admin',
            'email' => 'admin@return.test',
            'email_verified_at' => now(),
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

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Test Customer',
            'phone' => '01711000000',
            'status' => 'active',
        ]);

        $category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'General',
            'status' => 'active',
        ]);

        // Product with purchase price 500 and sale price 550
        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'TP-01',
            'purchase_price' => 500.00,
            'sale_price' => 550.00,
            'status' => 'active',
        ]);

        // Stock in batch with unit_cost 500
        $this->batch = Batch::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_no' => 'BATCH-001',
            'quantity' => 10,
            'unit_cost' => 500.00,
        ]);
    }

    protected function createTestSale(
        float $qty = 1,
        float $salePrice = 550.00,
        float $paidAmount = 550.00,
        float $discount = 0.0,
        float $invoiceDiscount = 0.0,
        float $adjustment = 0.0
    ): Sale {
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'discount' => $invoiceDiscount,
            'adjustment' => $adjustment,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $qty,
                    'unit_price' => $salePrice,
                    'discount' => $discount,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => $paidAmount,
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();

        return Sale::latest('id')->first();
    }

    public function test_full_return_adjusts_sale_total_and_profit_to_zero(): void
    {
        // 1. Sale: 1 qty @ 550 tk (cost 500, profit 50)
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 550.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 550.00,
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(550.00, (float) $sale->total);
        $this->assertEquals(50.00, (float) $sale->profit);
        $this->assertEquals(550.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);

        $this->batch->refresh();
        $this->assertEquals(9.0, (float) $this->batch->quantity);

        $saleItem = $sale->items->first();

        // 2. Return the 1 product
        $returnResponse = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertSessionHasNoErrors();

        // Stock restored
        $this->batch->refresh();
        $this->assertEquals(10.0, (float) $this->batch->quantity);

        // Sale total and profit must be adjusted to 0
        $sale->refresh();
        $this->assertEquals(0.00, (float) $sale->total, 'Sale total should be 0 after returning all items');
        $this->assertEquals(0.00, (float) $sale->profit, 'Sale profit should be 0 after returning all items');
        $this->assertEquals(0.00, (float) $sale->subtotal, 'Sale subtotal should be 0 after returning all items');
        $this->assertEquals(0.00, (float) $sale->paid_amount, 'Sale paid_amount should be 0 after refunding');
        $this->assertEquals(0.00, (float) $sale->due_amount, 'Sale due_amount should be 0');

        // Verify reports show 0 total sale and 0 profit
        $reportResponse = $this->actingAs($this->user)->get(route('reports.sales', ['range' => 'today']));
        $reportResponse->assertOk();
        $totals = $reportResponse->viewData('totals');
        $this->assertEquals(0.00, $totals['total']);
        $this->assertEquals(0.00, $totals['profit']);

        $plResponse = $this->actingAs($this->user)->get(route('reports.profit-loss', ['range' => 'today']));
        $plResponse->assertOk();
        $this->assertEquals(0.00, (float) $plResponse->viewData('sales'));
        $this->assertEquals(0.00, (float) $plResponse->viewData('profitFromSales'));

        $pplResponse = $this->actingAs($this->user)->get(route('reports.product-profit-loss', ['range' => 'today']));
        $pplResponse->assertOk();
        $pplTotals = $pplResponse->viewData('totals');
        $this->assertEquals(0.00, $pplTotals['qty']);
        $this->assertEquals(0.00, $pplTotals['sale_revenue']);
        $this->assertEquals(0.00, $pplTotals['profit']);
    }

    public function test_due_sale_return_adjusts_sale_total_profit_and_due_to_zero(): void
    {
        // 1. Sale on credit: 1 qty @ 550 tk, paid 0, due 550
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 550.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [],
        ]);
        $response->assertSessionHasNoErrors();

        $sale = Sale::latest('id')->first();
        $this->assertEquals(550.00, (float) $sale->total);
        $this->assertEquals(50.00, (float) $sale->profit);
        $this->assertEquals(0.00, (float) $sale->paid_amount);
        $this->assertEquals(550.00, (float) $sale->due_amount);

        $saleItem = $sale->items->first();

        // 2. Return the 1 product
        $returnResponse = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertSessionHasNoErrors();

        $sale->refresh();
        $this->assertEquals(0.00, (float) $sale->total, 'Sale total should be 0');
        $this->assertEquals(0.00, (float) $sale->profit, 'Sale profit should be 0');
        $this->assertEquals(0.00, (float) $sale->paid_amount, 'Sale paid_amount should be 0');
        $this->assertEquals(0.00, (float) $sale->due_amount, 'Sale due_amount should be 0');
    }

    public function test_partial_return_adjusts_sale_total_and_profit_proportionally(): void
    {
        // 1. Sale: 2 qty @ 550 tk = 1100 tk (cost 1000, profit 100)
        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 550.00,
                    'discount' => 0,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 1100.00,
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();

        $sale = Sale::latest('id')->first();
        $this->assertEquals(1100.00, (float) $sale->total);
        $this->assertEquals(100.00, (float) $sale->profit);

        $saleItem = $sale->items->first();

        // 2. Return 1 of the 2 products
        $returnResponse = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertSessionHasNoErrors();

        // Sale total and profit must be adjusted: 1100 - 550 = 550, profit 100 - 50 = 50
        $sale->refresh();
        $this->assertEquals(550.00, (float) $sale->total, 'Sale total should be 550 after returning 1 of 2 items');
        $this->assertEquals(50.00, (float) $sale->profit, 'Sale profit should be 50 after returning 1 of 2 items');
        $this->assertEquals(550.00, (float) $sale->subtotal, 'Sale subtotal should be 550');
        $this->assertEquals(550.00, (float) $sale->paid_amount, 'Sale paid_amount should be 550');
        $this->assertEquals(0.00, (float) $sale->due_amount, 'Sale due_amount should be 0');
    }

    public function test_it_renders_sale_returns_index_page_successfully(): void
    {
        $sale = $this->createTestSale(qty: 1, salePrice: 550.00, paidAmount: 550.00);
        $saleItem = $sale->items->first();

        // Process a return
        $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'note' => 'Customer changed mind',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)->get(route('sale-returns.index'));

        $response->assertOk();
        $response->assertViewIs('sales::sale-returns.index');
        $response->assertSee('বিক্রয় ফেরত তালিকা');
        $response->assertSee('#'.$sale->invoice_no);
        $response->assertSee('Test Customer');
        $response->assertSee('550.00');
    }

    public function test_it_renders_sale_returns_create_page_successfully(): void
    {
        $sale = $this->createTestSale(qty: 2, salePrice: 550.00, paidAmount: 1100.00);

        $response = $this->actingAs($this->user)->get(route('sale-returns.create', $sale));

        $response->assertOk();
        $response->assertViewIs('sales::sale-returns.create');
        $response->assertSee('বিক্রয় ফেরত &mdash; ইনভয়েস #'.$sale->invoice_no, false);
        $response->assertSee('Test Product');
        $response->assertSee('Test Customer');
        $response->assertSee('ফেরতের সারসংক্ষেপ');
        $response->assertSee('ফেরত সম্পন্ন করুন');
    }

    public function test_sale_return_with_item_discount_tracks_discount_and_adjusts_profit_and_totals(): void
    {
        // 1. Sale: 1 qty @ 550 tk, discount 30 tk => customer pays 520 tk, profit is 20 tk (520 - 500)
        $sale = $this->createTestSale(qty: 1, salePrice: 550.00, paidAmount: 520.00, discount: 30.00);

        $this->assertEquals(520.00, (float) $sale->total);
        $this->assertEquals(520.00, (float) $sale->subtotal);
        $this->assertEquals(30.00, (float) $sale->product_discount);
        $this->assertEquals(20.00, (float) $sale->profit);
        $this->assertEquals(520.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);

        $saleItem = $sale->items->first();
        $this->assertEquals(550.00, (float) $saleItem->unit_price);
        $this->assertEquals(30.00, (float) $saleItem->discount);
        $this->assertEquals(520.00, (float) $saleItem->total);

        // 2. Return the item
        $returnResponse = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertSessionHasNoErrors();

        // 3. Verify SaleReturn record tracks gross, discount, and net total
        $saleReturn = $sale->returns()->latest('id')->first();
        $this->assertNotNull($saleReturn);
        $this->assertEquals(550.00, (float) $saleReturn->subtotal, 'Gross return subtotal should be 550');
        $this->assertEquals(30.00, (float) $saleReturn->product_discount, 'Return product discount tracked should be 30');
        $this->assertEquals(0.00, (float) $saleReturn->discount, 'Return invoice discount should be 0');
        $this->assertEquals(30.00, (float) $saleReturn->total_discount, 'Total return discount tracked should be 30');
        $this->assertEquals(520.00, (float) $saleReturn->total, 'Net return total should be 520');
        $this->assertEquals(520.00, (float) $saleReturn->refund_amount, 'Refund to customer should be 520 (not 550)');

        // 4. Verify SaleReturnItem tracks discount and net total
        $returnItem = $saleReturn->items()->first();
        $this->assertNotNull($returnItem);
        $this->assertEquals(1.0, (float) $returnItem->quantity);
        $this->assertEquals(550.00, (float) $returnItem->unit_price);
        $this->assertEquals(30.00, (float) $returnItem->discount, 'Line return discount should be 30');
        $this->assertEquals(520.00, (float) $returnItem->total, 'Line return net total should be 520');
        $this->assertEquals(500.00, (float) $returnItem->cost_total, 'Line return cost should be 500');

        // 5. Verify Sale totals, profit, and discount are adjusted to 0
        $sale->refresh();
        $this->assertEquals(0.00, (float) $sale->total);
        $this->assertEquals(0.00, (float) $sale->subtotal);
        $this->assertEquals(0.00, (float) $sale->product_discount);
        $this->assertEquals(0.00, (float) $sale->profit);
        $this->assertEquals(0.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);

        // 6. Verify view displays discount on create and index pages
        $createResponse = $this->actingAs($this->user)->get(route('sale-returns.create', $sale));
        $createResponse->assertOk();

        $indexResponse = $this->actingAs($this->user)->get(route('sale-returns.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('520.00');
    }

    public function test_partial_sale_return_with_item_discount_prorates_discount_correctly(): void
    {
        // 1. Sale: 2 qty @ 550 tk, discount 30 tk (15 tk per piece) => customer pays 1070 tk, profit is 70 tk (1070 - 1000)
        $sale = $this->createTestSale(qty: 2, salePrice: 550.00, paidAmount: 1070.00, discount: 30.00);

        $this->assertEquals(1070.00, (float) $sale->total);
        $this->assertEquals(1070.00, (float) $sale->subtotal);
        $this->assertEquals(30.00, (float) $sale->product_discount);
        $this->assertEquals(70.00, (float) $sale->profit);

        $saleItem = $sale->items->first();

        // 2. Return 1 of the 2 products
        $returnResponse = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertSessionHasNoErrors();

        // 3. Verify SaleReturn record tracks gross 550, prorated discount 15, and net total 535
        $saleReturn = $sale->returns()->latest('id')->first();
        $this->assertNotNull($saleReturn);
        $this->assertEquals(550.00, (float) $saleReturn->subtotal);
        $this->assertEquals(15.00, (float) $saleReturn->product_discount, 'Prorated product discount should be 15');
        $this->assertEquals(0.00, (float) $saleReturn->discount, 'Invoice discount should be 0');
        $this->assertEquals(15.00, (float) $saleReturn->total_discount, 'Total prorated discount should be 15');
        $this->assertEquals(535.00, (float) $saleReturn->total, 'Net return total should be 535');
        $this->assertEquals(535.00, (float) $saleReturn->refund_amount, 'Refund should be 535');

        // 4. Verify remaining sale totals
        $sale->refresh();
        $this->assertEquals(535.00, (float) $sale->total, 'Remaining sale total should be 535');
        $this->assertEquals(535.00, (float) $sale->subtotal, 'Remaining sale subtotal should be 535');
        $this->assertEquals(15.00, (float) $sale->product_discount, 'Remaining discount should be 15');
        $this->assertEquals(35.00, (float) $sale->profit, 'Remaining profit should be 35');
        $this->assertEquals(535.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);
    }

    public function test_sale_return_with_product_discount_invoice_discount_and_adjustment_full_return(): void
    {
        // 1. Sale: 1 qty @ 550 tk, line discount 30 tk => line subtotal 520 tk
        //    Invoice discount: 20 tk
        //    Adjustment: -5 tk
        //    Total = 520 - 20 + (-5) = 495 tk
        //    Cost: 500 tk. Profit: (520 - 500) - 20 = 0 tk
        //    Customer pays 495 tk in cash.
        $sale = $this->createTestSale(
            qty: 1,
            salePrice: 550.00,
            paidAmount: 495.00,
            discount: 30.00,
            invoiceDiscount: 20.00,
            adjustment: -5.00
        );

        $this->assertEquals(495.00, (float) $sale->total);
        $this->assertEquals(520.00, (float) $sale->subtotal);
        $this->assertEquals(30.00, (float) $sale->product_discount);
        $this->assertEquals(20.00, (float) $sale->discount);
        $this->assertEquals(-5.00, (float) $sale->adjustment);
        $this->assertEquals(0.00, (float) $sale->profit);
        $this->assertEquals(495.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);

        $saleItem = $sale->items->first();

        // 2. Return the item in full
        $returnResponse = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertSessionHasNoErrors();

        // 3. Verify SaleReturn record tracks gross, product discount, invoice discount, adjustment and net total
        $saleReturn = $sale->returns()->latest('id')->first();
        $this->assertNotNull($saleReturn);
        $this->assertEquals(550.00, (float) $saleReturn->subtotal, 'Gross return subtotal should be 550');
        $this->assertEquals(30.00, (float) $saleReturn->product_discount, 'Return product discount should be 30');
        $this->assertEquals(20.00, (float) $saleReturn->discount, 'Return invoice discount should be 20');
        $this->assertEquals(50.00, (float) $saleReturn->total_discount, 'Total discount reversed should be 50');
        $this->assertEquals(-5.00, (float) $saleReturn->adjustment, 'Return adjustment should be -5');
        $this->assertEquals(495.00, (float) $saleReturn->total, 'Net return total should be 495');
        $this->assertEquals(495.00, (float) $saleReturn->refund_amount, 'Refund to customer should be 495');

        // 4. Verify SaleReturnItem tracks line discount
        $returnItem = $saleReturn->items()->first();
        $this->assertNotNull($returnItem);
        $this->assertEquals(1.0, (float) $returnItem->quantity);
        $this->assertEquals(550.00, (float) $returnItem->unit_price);
        $this->assertEquals(30.00, (float) $returnItem->discount);
        $this->assertEquals(520.00, (float) $returnItem->total);

        // 5. Verify all 8 fields on Sale are safely zeroed out
        $sale->refresh();
        $this->assertEquals(0.00, (float) $sale->total);
        $this->assertEquals(0.00, (float) $sale->subtotal);
        $this->assertEquals(0.00, (float) $sale->product_discount);
        $this->assertEquals(0.00, (float) $sale->discount);
        $this->assertEquals(0.00, (float) $sale->adjustment);
        $this->assertEquals(0.00, (float) $sale->profit);
        $this->assertEquals(0.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);
    }

    public function test_sale_return_with_product_discount_invoice_discount_and_adjustment_partial_return(): void
    {
        // 1. Sale: 2 qty @ 550 tk = 1100 tk, line discount 40 tk (20/unit) => line subtotal 1060 tk
        //    Invoice discount: 60 tk
        //    Adjustment: 10 tk
        //    Total = 1060 - 60 + 10 = 1010 tk
        //    Customer pays 1010 tk
        $sale = $this->createTestSale(
            qty: 2,
            salePrice: 550.00,
            paidAmount: 1010.00,
            discount: 40.00,
            invoiceDiscount: 60.00,
            adjustment: 10.00
        );

        $this->assertEquals(1010.00, (float) $sale->total);
        $this->assertEquals(1060.00, (float) $sale->subtotal);
        $this->assertEquals(40.00, (float) $sale->product_discount);
        $this->assertEquals(60.00, (float) $sale->discount);
        $this->assertEquals(10.00, (float) $sale->adjustment);
        $this->assertEquals(1010.00, (float) $sale->paid_amount);

        $saleItem = $sale->items->first();

        // 2. Return 1 of the 2 products (half the sale)
        //    Returned line: 1 qty @ 550, discount 20 => net 530
        //    Subtotal ratio: 530 / 1060 = 0.5
        //    Prorated invoice discount: 60 * 0.5 = 30
        //    Prorated adjustment: 10 * 0.5 = 5
        //    Net return total = 530 - 30 + 5 = 505
        $returnResponse = $this->actingAs($this->user)->post(route('sale-returns.store', $sale), [
            'return_date' => now()->toDateString(),
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertSessionHasNoErrors();

        // 3. Verify SaleReturn record
        $saleReturn = $sale->returns()->latest('id')->first();
        $this->assertNotNull($saleReturn);
        $this->assertEquals(550.00, (float) $saleReturn->subtotal);
        $this->assertEquals(20.00, (float) $saleReturn->product_discount, 'Prorated product discount should be 20');
        $this->assertEquals(30.00, (float) $saleReturn->discount, 'Prorated invoice discount should be 30');
        $this->assertEquals(50.00, (float) $saleReturn->total_discount, 'Total discount reversed should be 50');
        $this->assertEquals(5.00, (float) $saleReturn->adjustment, 'Prorated adjustment should be 5');
        $this->assertEquals(505.00, (float) $saleReturn->total, 'Net return total should be 505');
        $this->assertEquals(505.00, (float) $saleReturn->refund_amount, 'Refund should be 505');

        // 4. Verify remaining sale totals are mathematically consistent
        $sale->refresh();
        $this->assertEquals(530.00, (float) $sale->subtotal, 'Remaining subtotal should be 530');
        $this->assertEquals(20.00, (float) $sale->product_discount, 'Remaining product discount should be 20');
        $this->assertEquals(30.00, (float) $sale->discount, 'Remaining invoice discount should be 30');
        $this->assertEquals(5.00, (float) $sale->adjustment, 'Remaining adjustment should be 5');
        $this->assertEquals(505.00, (float) $sale->total, 'Remaining total should be 505 (530 - 30 + 5)');
        $this->assertEquals(505.00, (float) $sale->paid_amount, 'Remaining paid should be 505');
        $this->assertEquals(0.00, (float) $sale->due_amount);
    }
}
