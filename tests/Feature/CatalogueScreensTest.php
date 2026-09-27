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
use Modules\Product\Models\Unit;
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

        $this->grocery = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Grocery', 'type' => 'product']);
    }

    public function test_the_catalogue_offers_products_of_the_categories_the_shop_sells(): void
    {
        $rice = $this->productAtDhaka('Miniket Rice', $this->grocery);
        $water = Product::create(['name' => 'Mineral Water', 'category_id' => $this->grocery->id]);
        $cosmetics = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Cosmetics', 'type' => 'product']);
        $this->productAtDhaka('Face Wash', $cosmetics);

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
        $rice = $this->productAtDhaka('Miniket Rice', $this->grocery);
        $lentils = $this->productAtDhaka('Red Lentils', $this->grocery);
        $this->ctg->categories()->attach($this->grocery->id);

        $this->actingAs($this->ctgAdmin)->post(route('catalogue.products.list', $rice))->assertRedirect();
        $this->assertTrue(ShopProduct::where('shop_id', $this->ctg->id)->where('product_id', $rice->id)->exists());
        $this->assertFalse(ShopProduct::where('shop_id', $this->ctg->id)->where('product_id', $lentils->id)->exists());

        $this->actingAs($this->ctgAdmin)->post(route('catalogue.categories.list', $this->grocery))->assertRedirect();
        $this->assertTrue(ShopProduct::where('shop_id', $this->ctg->id)->where('product_id', $lentils->id)->exists());
    }

    public function test_a_shop_sets_and_clears_its_own_price(): void
    {
        $rice = $this->productAtDhaka('Miniket Rice', $this->grocery, 70);
        $rice->listInShop($this->ctg->id);

        $this->actingAs($this->ctgAdmin)->get(route('products.shop-price.edit', $rice))->assertOk();
        $this->actingAs($this->ctgAdmin)
            ->put(route('products.shop-price.update', $rice), ['sale_price' => 75, 'alert_qty' => 10])
            ->assertRedirect(route('products.index'));

        $this->assertSame('75.00', Product::findOrFail($rice->id)->sale_price);
        $this->actingAs(User::factory()->create(['shop_id' => $this->dhaka->id]));
        $this->assertSame('70.00', Product::findOrFail($rice->id)->sale_price);

        $this->actingAs($this->ctgAdmin)->put(route('products.shop-price.update', $rice), ['sale_price' => '']);
        $this->assertSame('70.00', Product::findOrFail($rice->id)->sale_price);
    }

    public function test_a_suggested_product_is_shared_on_approval_and_its_company_keeps_its_price(): void
    {
        $unit = Unit::create(['shop_id' => $this->dhaka->id, 'name' => 'Kg', 'short_code' => 'kg']);
        $rice = $this->productAtDhaka('Miniket Rice', $this->grocery, 70);
        $rice->units()->attach($unit->id, ['is_base' => true, 'conversion_factor' => 1]);

        $this->actingAs($this->ctgAdmin)->post(route('products.suggest', $rice))->assertRedirect();
        $this->assertNotNull($rice->fresh()->suggested_at);

        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin)->get(route('catalogue-review.index'))->assertOk()->assertSee('Miniket Rice');
        $this->actingAs($superAdmin)->post(route('catalogue-review.approve', $rice))->assertRedirect();

        $shared = Product::withoutGlobalScopes()->findOrFail($rice->id);
        $this->assertNull($shared->company_id);
        $this->assertNull($shared->suggested_at);
        $this->assertNull(Category::withoutGlobalScopes()->findOrFail($this->grocery->id)->company_id);
        $this->assertNull(Unit::withoutGlobalScopes()->findOrFail($unit->id)->company_id);
        $this->assertDatabaseHas('company_products', ['company_id' => $this->dhaka->company_id, 'product_id' => $rice->id, 'sale_price' => 70]);

        $otherShop = Shop::create(['name' => 'Other Company Store', 'slug' => 'other-store', 'status' => 'active']);
        $this->actingAs(User::factory()->create(['shop_id' => $otherShop->id]));
        $this->assertTrue(Product::whereKey($rice->id)->exists());
    }

    public function test_a_rejected_suggestion_stays_private(): void
    {
        $rice = $this->productAtDhaka('Miniket Rice', $this->grocery);
        $rice->update(['suggested_at' => now()]);

        $this->actingAs($this->superAdmin())->post(route('catalogue-review.reject', $rice))->assertRedirect();

        $this->assertNull($rice->fresh()->suggested_at);
        $this->assertSame($this->dhaka->company_id, $rice->fresh()->company_id);
        $this->assertSame(0, CompanyProduct::count());
    }

    public function test_a_shared_product_cannot_be_suggested(): void
    {
        $water = Product::create(['name' => 'Mineral Water', 'category_id' => $this->grocery->id]);

        $this->actingAs($this->ctgAdmin)->post(route('products.suggest', $water))->assertStatus(422);
    }

    public function test_duplicate_check_finds_same_barcode_and_similar_names(): void
    {
        $rice = $this->productAtDhaka('Miniket Rice 5kg', $this->grocery);
        $rice->update(['has_barcode' => true, 'barcode' => '8901234567890']);
        $rice->listInShop($this->ctg->id);
        Product::create(['name' => 'Mineral Water', 'category_id' => $this->grocery->id, 'has_barcode' => true, 'barcode' => '4444']);

        $byName = $this->actingAs($this->ctgAdmin)->getJson(route('catalogue.duplicates', ['name' => 'miniket']))->assertOk()->json('matches');
        $this->assertSame(['Miniket Rice 5kg'], array_column($byName, 'name'));
        $this->assertTrue($byName[0]['listed']);

        $byBarcode = $this->actingAs($this->ctgAdmin)->getJson(route('catalogue.duplicates', ['barcode' => '4444']))->json('matches');
        $this->assertSame(['Mineral Water'], array_column($byBarcode, 'name'));
        $this->assertTrue($byBarcode[0]['shared']);
        $this->assertFalse($byBarcode[0]['listed']);
    }

    public function test_super_admin_assigns_shared_categories_when_creating_a_shop(): void
    {
        $beverages = Category::create(['name' => 'Beverages', 'type' => 'product']);

        $this->actingAs($this->superAdmin())->post(route('shops.store'), [
            'company_mode' => 'new',
            'new_company_name' => 'Test Company 170',
            'name' => 'Fresh Mart',
            'slug' => 'fresh-mart',
            'status' => 'active',
            'admin_name' => 'Fresh Owner',
            'admin_phone' => '01888999001',
            'admin_password' => 'Secret12345!',
            'admin_password_confirmation' => 'Secret12345!',
            'category_ids' => [$beverages->id],
        ])->assertRedirect(route('shops.index'));

        $shop = Shop::where('slug', 'fresh-mart')->firstOrFail();
        $this->assertSame([$beverages->id], $shop->categories()->pluck('categories.id')->all());
    }

    public function test_listing_a_product_adds_its_category_to_the_shop(): void
    {
        $rice = $this->productAtDhaka('Miniket Rice', $this->grocery);

        $rice->listInShop($this->ctg->id);

        $this->assertTrue($this->ctg->categories()->whereKey($this->grocery->id)->exists());
    }

    private function productAtDhaka(string $name, Category $category, float $salePrice = 50): Product
    {
        return Product::create([
            'shop_id' => $this->dhaka->id,
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
