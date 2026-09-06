<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Expense;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Shop;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_all_stat_cards(): void
    {
        $shop = Shop::create([
            'name' => 'টেস্ট শপ',
            'slug' => 'test-shop',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);

        $category = Category::create([
            'shop_id' => $shop->id,
            'name' => 'General',
            'status' => 'active',
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'টেস্ট পণ্য',
            'sku' => 'SKU-001',
            'purchase_price' => 100,
            'selling_price' => 150,
            'status' => 'active',
        ]);

        Batch::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'batch_no' => 'BATCH-001',
            'quantity' => 20,
        ]);

        Sale::create([
            'shop_id' => $shop->id,
            'sale_date' => now()->toDateString(),
            'invoice_no' => 'INV-001',
            'subtotal' => 300,
            'total' => 300,
            'paid_amount' => 300,
            'due_amount' => 0,
            'profit' => 100,
        ]);

        Expense::create([
            'shop_id' => $shop->id,
            'amount' => 40,
            'expense_date' => now()->toDateString(),
            'title' => 'টেস্ট খরচ',
        ]);

        Account::create([
            'shop_id' => $shop->id,
            'name' => 'প্রধান ক্যাশ',
            'type' => 'cash',
            'current_balance' => 5000,
            'status' => 'active',
        ]);

        Account::create([
            'shop_id' => $shop->id,
            'name' => 'সিটি ব্যাংক',
            'type' => 'bank',
            'current_balance' => 15000,
            'status' => 'active',
        ]);

        Account::create([
            'shop_id' => $shop->id,
            'name' => 'বিকাশ মার্চেন্ট',
            'type' => 'mfs',
            'current_balance' => 8000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);

        $response->assertViewHasAll([
            'saleTotal',
            'purchaseTotal',
            'expenseTotal',
            'productProfit',
            'totalProfit',
            'totalStockQty',
            'totalStockValue',
            'totalReceivable',
            'totalPayable',
            'totalCash',
            'totalBank',
            'totalMfs',
            'totalAccountBalance',
            'balance',
        ]);

        $response->assertSee('পণ্য লাভ');
        $response->assertSee('Product Profit');
        $response->assertSee('মোট লাভ');
        $response->assertSee('Total Profit');
        $response->assertSee('মোট মজুদ মূল্য');
        $response->assertSee('Stock Valuation');
        $response->assertSee('ক্যাশ ব্যালেন্স');
        $response->assertSee('Cash Balance');
        $response->assertSee('ব্যাংক ব্যালেন্স');
        $response->assertSee('Bank Balance');
        $response->assertSee('মোবাইল ব্যাংকিং (MFS)');
        $response->assertSee('MFS Balance');
    }
}
