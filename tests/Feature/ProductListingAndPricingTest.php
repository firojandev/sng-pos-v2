<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Category;
use Modules\Product\Models\CompanyProduct;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShopProduct;
use Modules\Product\Services\ProductPricing;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductListingAndPricingTest extends TestCase
{
    use RefreshDatabase;

    private Shop $dhaka;

    private Shop $ctg;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::factory()->create();
        $this->dhaka = Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);
        $this->category = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Grocery', 'type' => 'product']);
    }

    public function test_a_product_is_listed_in_the_shop_that_creates_it(): void
    {
        $this->actingAs($this->userAt($this->dhaka));
        $product = Product::create(['name' => 'Rice 5kg', 'category_id' => $this->category->id]);

        $this->assertTrue(Product::listedInShop()->whereKey($product->id)->exists());

        $this->actingAs($this->userAt($this->ctg));
        $this->assertTrue(Product::whereKey($product->id)->exists(), 'The company can see the product.');
        $this->assertFalse(Product::listedInShop()->whereKey($product->id)->exists(), 'Ctg does not sell it yet.');

        $product->listInShop($this->ctg->id);

        $this->assertTrue(Product::listedInShop()->whereKey($product->id)->exists());
    }

    public function test_values_resolve_from_shop_then_company_then_base(): void
    {
        $product = $this->productWithBasePrice(100);
        CompanyProduct::create(['company_id' => $this->dhaka->company_id, 'product_id' => $product->id, 'sale_price' => 110, 'is_vat' => true]);
        ShopProduct::where('shop_id', $this->dhaka->id)->where('product_id', $product->id)->update(['sale_price' => 120]);

        $this->actingAs($this->userAt($this->dhaka));
        $inDhaka = Product::findOrFail($product->id);
        $this->assertSame('120.00', $inDhaka->sale_price);
        $this->assertTrue($inDhaka->is_vat);
        $this->assertSame('120.00', $inDhaka->toArray()['sale_price']);
        $this->assertArrayNotHasKey('current_shop_listing', $inDhaka->toArray());

        $this->actingAs($this->userAt($this->ctg));
        $this->assertSame('110.00', Product::findOrFail($product->id)->sale_price);

        $this->assertSame('100.00', number_format((float) $inDhaka->baseValue('sale_price'), 2, '.', ''));
    }

    public function test_company_values_of_an_own_product_are_stored_on_the_product(): void
    {
        $product = $this->productWithBasePrice(100);
        $this->actingAs($this->userAt($this->ctg));

        app(ProductPricing::class)->setCompanyValues($product->id, ['purchase_price' => 70]);

        $this->assertSame(70.0, (float) Product::withoutGlobalScopes()->findOrFail($product->id)->baseValue('purchase_price'));
        $this->assertSame(0, CompanyProduct::count());
    }

    public function test_company_values_of_a_shared_product_never_change_the_shared_product(): void
    {
        $shared = Product::create(['name' => 'Mineral Water 500ml', 'category_id' => $this->category->id, 'purchase_price' => 15, 'sale_price' => 20]);
        $this->assertTrue($shared->isShared());
        $this->actingAs($this->userAt($this->ctg));

        app(ProductPricing::class)->setCompanyValues($shared->id, ['purchase_price' => 14, 'sale_price' => 25]);

        $this->assertSame(15.0, (float) Product::withoutGlobalScopes()->findOrFail($shared->id)->baseValue('purchase_price'));
        $this->assertDatabaseHas('company_products', ['company_id' => $this->ctg->company_id, 'product_id' => $shared->id, 'sale_price' => 25]);
        $this->assertSame('25.00', Product::findOrFail($shared->id)->sale_price);
    }

    public function test_barcodes_are_unique_across_companies_and_shared_products_keep_theirs(): void
    {
        $otherShop = Shop::create(['name' => 'Other Company Store', 'slug' => 'other-store', 'status' => 'active']);
        $otherCategory = Category::create(['shop_id' => $otherShop->id, 'name' => 'Grocery', 'type' => 'product']);
        Product::create(['shop_id' => $otherShop->id, 'name' => 'Their Juice', 'category_id' => $otherCategory->id, 'has_barcode' => true, 'barcode' => '8901234567890']);
        $own = $this->productWithBasePrice(100);
        $shared = Product::create(['name' => 'Shared Soap', 'category_id' => $this->category->id, 'has_barcode' => true, 'barcode' => '111']);
        $this->actingAs($this->userAt($this->dhaka));
        $pricing = app(ProductPricing::class);

        $pricing->assignBarcode($own->id, '8901234567890');
        $pricing->assignBarcode($shared->id, '222');
        $pricing->assignBarcode($own->id, '555');

        $this->assertSame('555', Product::withoutGlobalScopes()->findOrFail($own->id)->barcode);
        $this->assertSame('111', Product::withoutGlobalScopes()->findOrFail($shared->id)->barcode);
    }

    public function test_a_shop_cannot_edit_or_delete_a_shared_product(): void
    {
        (new SubscriptionifySeeder)->run();
        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());
        $this->subscribeShopToFeatures($this->dhaka, Features::keys());
        $admin = $this->userAt($this->dhaka);
        $admin->syncRoles(['Admin']);
        $shared = Product::create(['name' => 'Mineral Water 500ml', 'category_id' => $this->category->id]);
        $own = $this->productWithBasePrice(100);

        $this->actingAs($admin)->get(route('products.edit', $shared))->assertForbidden();
        $this->actingAs($admin)->delete(route('products.destroy', $shared))->assertForbidden();
        $this->actingAs($admin)->get(route('products.edit', $own))->assertOk();
    }

    private function productWithBasePrice(float $salePrice): Product
    {
        return Product::create([
            'shop_id' => $this->dhaka->id,
            'name' => 'Rice 5kg',
            'category_id' => $this->category->id,
            'purchase_price' => $salePrice * 0.8,
            'sale_price' => $salePrice,
        ]);
    }

    private function userAt(Shop $shop): User
    {
        return User::factory()->create(['shop_id' => $shop->id]);
    }
}
