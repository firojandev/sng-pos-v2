<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShopProduct;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogueScreensTest extends TestCase
{
    use RefreshDatabase;

    private Shop $dhaka;

    private Shop $ctg;

    private User $ctgAdmin;

    private Category $grocery;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $company = Company::factory()->create();
        $this->dhaka = Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->dhaka, Features::keys());

        $this->ctgAdmin = User::factory()->create(['shop_id' => $this->ctg->id]);
        $this->ctgAdmin->syncRoles(['Admin']);

        $this->grocery = Category::create(['shop_id' => $this->ctg->id, 'name' => 'Grocery', 'type' => 'product']);
    }

    public function test_the_catalogue_offers_products_of_the_categories_the_shop_sells(): void
    {
        $rice = $this->unlistedProduct('Miniket Rice', $this->grocery);
        $water = $this->unlistedProduct('Mineral Water', $this->grocery);
        $cosmetics = Category::create(['shop_id' => $this->ctg->id, 'name' => 'Cosmetics', 'type' => 'product']);
        $this->unlistedProduct('Face Wash', $cosmetics);

        $this->actingAs($this->ctgAdmin)
            ->put(route('catalogue.categories.update'), ['category_ids' => [$this->grocery->id]])
            ->assertRedirect(route('catalogue.index'));

        $this->assertSame([$this->grocery->id], $this->ctg->categories()->pluck('categories.id')->all());
        $this->actingAs($this->ctgAdmin)->get(route('catalogue.index'))
            ->assertOk()
            ->assertSee($rice->name)
            ->assertSee($water->name)
            ->assertDontSee('Face Wash');
    }

    public function test_a_shop_adds_a_single_product_or_a_whole_category(): void
    {
        $rice = $this->unlistedProduct('Miniket Rice', $this->grocery);
        $lentils = $this->unlistedProduct('Red Lentils', $this->grocery);
        $this->ctg->categories()->attach($this->grocery->id);

        $this->actingAs($this->ctgAdmin)->post(route('catalogue.products.list', $rice))->assertRedirect();
        $this->assertTrue(ShopProduct::where('shop_id', $this->ctg->id)->where('product_id', $rice->id)->exists());
        $this->assertFalse(ShopProduct::where('shop_id', $this->ctg->id)->where('product_id', $lentils->id)->exists());

        $this->actingAs($this->ctgAdmin)->post(route('catalogue.categories.list', $this->grocery))->assertRedirect();
        $this->assertTrue(ShopProduct::where('shop_id', $this->ctg->id)->where('product_id', $lentils->id)->exists());
    }

    public function test_a_shop_sets_and_clears_its_own_price(): void
    {
        $rice = $this->unlistedProduct('Miniket Rice', $this->grocery, 70);
        $rice->listInShop($this->ctg->id);

        $this->actingAs($this->ctgAdmin)->get(route('products.shop-price.edit', $rice))->assertOk();
        $this->actingAs($this->ctgAdmin)
            ->put(route('products.shop-price.update', $rice), ['sale_price' => 75, 'alert_qty' => 10])
            ->assertRedirect(route('products.index'));

        $this->assertSame('75.00', Product::findOrFail($rice->id)->sale_price);

        $this->actingAs($this->ctgAdmin)->put(route('products.shop-price.update', $rice), ['sale_price' => '']);
        $this->assertSame('70.00', Product::findOrFail($rice->id)->sale_price);
    }

    public function test_duplicate_check_finds_same_barcode_and_similar_names_in_the_shop(): void
    {
        $rice = $this->productAt($this->ctg, 'Miniket Rice 5kg', $this->grocery);
        $rice->update(['has_barcode' => true, 'barcode' => '8901234567890']);
        $water = $this->productAt($this->ctg, 'Mineral Water', $this->grocery);
        $water->update(['has_barcode' => true, 'barcode' => '4444']);
        $dhakaGrocery = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Grocery', 'type' => 'product']);
        $this->productAt($this->dhaka, 'Miniket Rice 10kg', $dhakaGrocery);

        $byName = $this->actingAs($this->ctgAdmin)->getJson(route('catalogue.duplicates', ['name' => 'miniket']))->assertOk()->json('matches');
        $this->assertSame(['Miniket Rice 5kg'], array_column($byName, 'name'), "Another shop's products aren't matched.");
        $this->assertTrue($byName[0]['listed']);

        $byBarcode = $this->actingAs($this->ctgAdmin)->getJson(route('catalogue.duplicates', ['barcode' => '4444']))->json('matches');
        $this->assertSame(['Mineral Water'], array_column($byBarcode, 'name'));
    }

    public function test_listing_a_product_adds_its_category_to_the_shop(): void
    {
        $rice = $this->unlistedProduct('Miniket Rice', $this->grocery);

        $rice->listInShop($this->ctg->id);

        $this->assertTrue($this->ctg->categories()->whereKey($this->grocery->id)->exists());
    }

    /**
     * A product of Ctg's catalogue that Ctg doesn't sell (list) yet.
     */
    private function unlistedProduct(string $name, Category $category, float $salePrice = 50): Product
    {
        $product = $this->productAt($this->ctg, $name, $category, $salePrice);
        ShopProduct::where('product_id', $product->id)->delete();
        DB::table('shop_category')->where('shop_id', $this->ctg->id)->delete();

        return $product;
    }

    /**
     * A shop's own product (only that shop sees it).
     */
    private function productAt(Shop $shop, string $name, Category $category, float $salePrice = 50): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'name' => $name,
            'category_id' => $category->id,
            'sale_price' => $salePrice,
            'purchase_price' => $salePrice * 0.8,
            'status' => 'active',
        ]);
    }

    private function superAdmin(): User
    {
        setPermissionsTeamId(null);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['shop_id' => null]);
        $user->assignRole('Super Admin');

        return $user;
    }
}
