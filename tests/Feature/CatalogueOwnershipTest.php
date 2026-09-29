<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Modules\Company\Models\Company;
use Modules\Core\Support\TenantRules;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\Unit;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogueOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private Shop $dhaka;

    private Shop $ctg;

    private Shop $otherCompanyShop;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::factory()->create();
        $this->dhaka = Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $this->ctg = Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);
        $this->otherCompanyShop = Shop::create(['name' => 'Other Company Store', 'slug' => 'other-store', 'status' => 'active']);
    }

    public function test_each_shop_keeps_its_own_catalogue(): void
    {
        $this->actingAs($this->userAt($this->dhaka));
        $category = Category::create(['name' => 'Grocery', 'type' => 'product']);
        $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc']);
        $brand = Brand::create(['name' => 'Pran']);
        $product = Product::create(['name' => 'Pran Mango Juice', 'category_id' => $category->id, 'brand_id' => $brand->id]);

        $this->assertSame($this->dhaka->company_id, $product->company_id);
        $this->assertSame($this->dhaka->id, $product->shop_id);

        // Not even the company's other shop sees it.
        foreach ([$this->ctg, $this->otherCompanyShop] as $shop) {
            $this->actingAs($this->userAt($shop));
            $this->assertSame(0, Product::count());
            $this->assertSame(0, Category::count());
            $this->assertSame(0, Unit::count());
            $this->assertSame(0, Brand::count());
        }
    }

    public function test_shared_catalogue_records_are_visible_to_every_company(): void
    {
        $this->actingAs($this->superAdmin());
        $category = Category::create(['name' => 'Beverages', 'type' => 'product']);
        $product = Product::create(['name' => 'Mineral Water 500ml', 'category_id' => $category->id]);

        $this->assertTrue($product->isShared());

        foreach ([$this->dhaka, $this->otherCompanyShop] as $shop) {
            $this->actingAs($this->userAt($shop));
            $this->assertSame(['Mineral Water 500ml'], Product::pluck('name')->all());
        }
    }

    public function test_stock_stays_with_the_shop_that_holds_it(): void
    {
        $category = Category::create(['name' => 'Grocery', 'type' => 'product']);
        $product = Product::create(['name' => 'Rice 5kg', 'category_id' => $category->id]);
        Batch::create(['shop_id' => $this->dhaka->id, 'product_id' => $product->id, 'batch_no' => 'B-1', 'quantity' => 20]);

        $this->actingAs($this->userAt($this->ctg));

        $this->assertTrue(Product::whereKey($product->id)->exists());
        $this->assertSame(0.0, (float) Batch::where('product_id', $product->id)->sum('quantity'));
    }

    public function test_catalogue_validation_accepts_own_and_shared_records_only(): void
    {
        $ownCategory = Category::create(['shop_id' => $this->ctg->id, 'name' => 'Grocery', 'type' => 'product']);
        $sisterShopsCategory = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Toys', 'type' => 'product']);
        $foreignCategory = Category::create(['shop_id' => $this->otherCompanyShop->id, 'name' => 'Grocery', 'type' => 'product']);
        $sharedCategory = Category::create(['name' => 'Beverages', 'type' => 'product']);

        $this->actingAs($this->userAt($this->ctg));
        $rules = ['category_id' => [TenantRules::catalogExists('categories')]];

        $this->assertTrue(Validator::make(['category_id' => $ownCategory->id], $rules)->passes());
        $this->assertTrue(Validator::make(['category_id' => $sharedCategory->id], $rules)->passes());
        $this->assertTrue(Validator::make(['category_id' => $sisterShopsCategory->id], $rules)->fails());
        $this->assertTrue(Validator::make(['category_id' => $foreignCategory->id], $rules)->fails());
    }

    public function test_expense_categories_belong_to_the_company(): void
    {
        $this->actingAs($this->userAt($this->dhaka));
        $expenseCategory = ExpenseCategory::create(['name' => 'Rent', 'type' => 'expense']);

        $this->assertSame($this->dhaka->company_id, $expenseCategory->company_id);

        $this->actingAs($this->userAt($this->otherCompanyShop));
        $this->assertFalse(ExpenseCategory::whereKey($expenseCategory->id)->exists());
    }

    public function test_existing_records_move_to_their_shop_unless_another_shop_uses_them(): void
    {
        $grocery = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Grocery', 'type' => 'product']);
        $toys = Category::create(['shop_id' => $this->dhaka->id, 'name' => 'Toys', 'type' => 'product']);
        $usedByBoth = Product::create(['shop_id' => $this->dhaka->id, 'name' => 'Miniket Rice', 'category_id' => $grocery->id]);
        $usedByBoth->listInShop($this->ctg->id);
        $dhakasOnly = Product::create(['shop_id' => $this->dhaka->id, 'name' => 'Toy Car', 'category_id' => $toys->id]);
        $singleShopBrand = Brand::create(['company_id' => $this->otherCompanyShop->company_id, 'name' => 'Old Brand']);

        (require base_path('Modules/Product/database/migrations/2026_09_29_085536_make_catalogue_per_shop.php'))->up();

        // Used by two shops: kept at company level, so Ctg still has it.
        $this->assertNull($usedByBoth->fresh()->shop_id);
        $this->assertNull($grocery->fresh()->shop_id);
        $this->actingAs($this->userAt($this->ctg));
        $this->assertTrue(Product::whereKey($usedByBoth->id)->exists());
        $this->assertFalse(Product::whereKey($dhakasOnly->id)->exists());

        $this->assertSame($this->dhaka->id, $dhakasOnly->fresh()->shop_id);
        $this->assertSame($this->otherCompanyShop->id, $singleShopBrand->fresh()->shop_id);
    }

    public function test_product_management_is_only_inside_a_shop(): void
    {
        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin)->get(route('dashboard'))->assertOk()->assertDontSee('Catalogue Review');

        foreach (['products.index', 'products.create', 'categories.index', 'brands.index', 'catalogue.index', 'stock.index'] as $route) {
            $this->actingAs($superAdmin)->get(route($route))->assertForbidden();
        }
        $this->assertFalse(app('router')->has('catalogue-review.index'));
        $this->assertFalse(app('router')->has('products.suggest'));
    }

    public function test_shared_records_a_single_shop_uses_become_its_own(): void
    {
        $category = Category::create(['name' => 'Beverages', 'type' => 'product']);
        $water = Product::create(['name' => 'Mineral Water', 'category_id' => $category->id]);
        $water->listInShop($this->dhaka->id);
        $juice = Product::create(['name' => 'Mango Juice', 'category_id' => $category->id]);
        $juice->listInShop($this->dhaka->id);
        $juice->listInShop($this->otherCompanyShop->id);

        (require base_path('Modules/Product/database/migrations/2026_09_29_150753_remove_shared_catalogue.php'))->up();

        $this->assertSame($this->dhaka->id, $water->fresh()->shop_id);
        $this->assertSame($this->dhaka->company_id, $water->fresh()->company_id);
        $this->assertNull($juice->fresh()->company_id, 'Used by two shops: left shared, read only.');
        $this->assertNull($category->fresh()->company_id);

        $this->actingAs($this->userAt($this->otherCompanyShop));
        $this->assertFalse(Product::whereKey($water->id)->exists());
        $this->assertTrue(Product::whereKey($juice->id)->exists());
    }

    private function userAt(Shop $shop): User
    {
        return User::factory()->create(['shop_id' => $shop->id]);
    }

    private function superAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['shop_id' => null]);
        $user->assignRole('Super Admin');

        return $user;
    }
}
