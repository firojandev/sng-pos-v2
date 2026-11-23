<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Category;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTopSellingProductsTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $owner;

    protected Category $category;

    private int $invoiceCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->shop = $this->createShop('top-shop');

        $this->owner = User::create([
            'name' => 'Shop Owner',
            'email' => 'owner@top-shop.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        setPermissionsTeamId($this->shop->id);
        $this->owner->assignRole(Role::firstOrCreate([
            'shop_id' => $this->shop->id,
            'name' => 'Owner',
            'guard_name' => 'web',
        ]));
        setPermissionsTeamId(null);

        $this->category = Category::create([
            'shop_id' => $this->shop->id,
            'name' => 'General',
            'type' => 'product',
        ]);
    }

    public function test_dashboard_ranks_the_ten_products_with_the_highest_sales_amount(): void
    {
        foreach (range(1, 12) as $rank) {
            $product = $this->createProduct("Product {$rank}");
            $this->createSale($this->shop, $product, 1000 - $rank * 10, now()->toDateString());
        }

        $repeatProduct = $this->createProduct('Repeat Product');
        $this->createSale($this->shop, $repeatProduct, 600, now()->toDateString());
        $this->createSale($this->shop, $repeatProduct, 600, now()->toDateString());

        $response = $this->actingAs($this->owner)->get(route('dashboard', ['range' => 'today']));

        $response->assertOk();
        $response->assertSee('সর্বাধিক বিক্রিত ১০টি পণ্য');

        $topSellingProducts = $response->viewData('topSellingProducts');
        $this->assertCount(10, $topSellingProducts);
        $this->assertSame(['name' => 'Repeat Product', 'quantity' => 2.0, 'unit' => null, 'amount' => 1200.0], $topSellingProducts[0]);
        $this->assertSame(['name' => 'Product 1', 'quantity' => 1.0, 'unit' => null, 'amount' => 990.0], $topSellingProducts[1]);
        $this->assertSame('Product 9', $topSellingProducts[9]['name']);
    }

    public function test_top_selling_products_exclude_other_shops_deleted_sales_and_sales_outside_the_range(): void
    {
        $visibleProduct = $this->createProduct('Visible Product');
        $this->createSale($this->shop, $visibleProduct, 500, now()->toDateString());

        $oldProduct = $this->createProduct('Old Product');
        $this->createSale($this->shop, $oldProduct, 9000, now()->subYears(2)->toDateString());

        $deletedProduct = $this->createProduct('Deleted Sale Product');
        $this->createSale($this->shop, $deletedProduct, 9000, now()->toDateString())->delete();

        $otherShop = $this->createShop('other-shop');
        $this->createSale($otherShop, $this->createProduct('Other Shop Product', $otherShop), 9000, now()->toDateString());

        $response = $this->actingAs($this->owner)->get(route('dashboard', ['range' => 'month']));

        $response->assertOk();
        $this->assertSame(['Visible Product'], array_column($response->viewData('topSellingProducts'), 'name'));
    }

    public function test_dashboard_yesterday_range_shows_only_yesterdays_sales(): void
    {
        $this->createSale($this->shop, $this->createProduct('Yesterday Product'), 300, now()->subDay()->toDateString());
        $this->createSale($this->shop, $this->createProduct('Today Product'), 900, now()->toDateString());

        $response = $this->actingAs($this->owner)->get(route('dashboard', ['range' => 'yesterday']));

        $response->assertOk();
        $response->assertViewHas('range', 'yesterday');
        $response->assertViewHas('saleTotal', 300.0);
        $this->assertSame(['Yesterday Product'], array_column($response->viewData('topSellingProducts'), 'name'));
    }

    public function test_top_selling_products_panel_is_hidden_without_the_sales_stat_permission(): void
    {
        setPermissionsTeamId($this->shop->id);
        $role = Role::create(['shop_id' => $this->shop->id, 'name' => 'Viewer', 'guard_name' => 'web']);
        $role->syncPermissions(['dashboard.view', 'dashboard.stat-cash']);
        $viewer = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@top-shop.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $viewer->assignRole($role);
        setPermissionsTeamId(null);

        $this->createSale($this->shop, $this->createProduct('Hidden Product'), 500, now()->toDateString());

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('সর্বাধিক বিক্রিত ১০টি পণ্য');
        $this->assertSame([], $response->viewData('topSellingProducts'));
    }

    public function test_top_selling_products_panel_shows_empty_state_when_there_are_no_sales(): void
    {
        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('এই সময়ে কোনো বিক্রি নেই');
        $response->assertDontSee('topSellingProductsChart');
    }

    private function createShop(string $slug): Shop
    {
        $shop = Shop::create(['name' => $slug, 'slug' => $slug, 'status' => 'active']);

        $plan = Plan::where('slug', 'standard')->first();
        if ($plan) {
            $shop->subscribe($plan);
        }

        return $shop;
    }

    private function createProduct(string $name, ?Shop $shop = null): Product
    {
        return Product::create([
            'shop_id' => ($shop ?? $this->shop)->id,
            'category_id' => $this->category->id,
            'name' => $name,
            'sale_price' => 100,
            'purchase_price' => 50,
            'status' => 'active',
        ]);
    }

    private function createSale(Shop $shop, Product $product, float $amount, string $saleDate): Sale
    {
        $this->invoiceCounter++;

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'invoice_no' => 'INV-'.str_pad((string) $this->invoiceCounter, 4, '0', STR_PAD_LEFT),
            'sale_date' => $saleDate,
            'subtotal' => $amount,
            'discount' => 0,
            'tax' => 0,
            'total' => $amount,
            'paid_amount' => $amount,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $amount,
            'discount' => 0,
            'total' => $amount,
        ]);

        return $sale;
    }
}
