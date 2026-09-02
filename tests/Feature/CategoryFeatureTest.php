<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Finance\Models\ExpenseSubCategory;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\SubCategory;
use Modules\Product\Models\Unit;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

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
            'name' => 'Test Mart',
            'slug' => 'test-mart',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);
    }

    public function test_can_create_product_category_and_sub_category(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Electronics',
            'description' => 'Electronic items',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'shop_id' => $this->shop->id,
            'name' => 'Electronics',
            'type' => 'product',
            'parent_id' => null,
        ]);

        $category = Category::where('name', 'Electronics')->first();

        $subResponse = $this->actingAs($this->user)->post(route('sub-categories.store'), [
            'category_id' => $category->id,
            'name' => 'Smartphones',
            'description' => 'Mobile phones',
        ]);

        $subResponse->assertRedirect(route('sub-categories.index'));
        $this->assertDatabaseHas('categories', [
            'shop_id' => $this->shop->id,
            'name' => 'Smartphones',
            'type' => 'product',
            'parent_id' => $category->id,
        ]);

        $subCategory = SubCategory::where('name', 'Smartphones')->first();
        $this->assertEquals($category->id, $subCategory->category->id);
        $this->assertCount(1, $category->fresh()->subCategories);
    }

    public function test_can_create_expense_category_and_sub_category(): void
    {
        $response = $this->actingAs($this->user)->post(route('expense-categories.store'), [
            'name' => 'Operations',
            'description' => 'Operational expenses',
        ]);

        $response->assertRedirect(route('expense-categories.index'));
        $this->assertDatabaseHas('categories', [
            'shop_id' => $this->shop->id,
            'name' => 'Operations',
            'type' => 'expense',
            'parent_id' => null,
        ]);

        $parentExpenseCat = ExpenseCategory::where('name', 'Operations')->first();

        $subResponse = $this->actingAs($this->user)->post(route('expense-sub-categories.store'), [
            'category_id' => $parentExpenseCat->id,
            'name' => 'Office Rent',
            'description' => 'Monthly rental',
        ]);

        $subResponse->assertRedirect(route('expense-sub-categories.index'));
        $this->assertDatabaseHas('categories', [
            'shop_id' => $this->shop->id,
            'name' => 'Office Rent',
            'type' => 'expense',
            'parent_id' => $parentExpenseCat->id,
        ]);

        $this->assertCount(1, $parentExpenseCat->fresh()->subCategories);
    }

    public function test_can_create_product_with_category_and_sub_category(): void
    {
        $unit = Unit::create(['shop_id' => $this->shop->id, 'name' => 'Pcs', 'short_code' => 'pcs']);
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Groceries']);
        $subCategory = SubCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Rice']);

        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'name' => 'Basmati Rice 5kg',
            'sku' => 'RICE-001',
            'purchase_price' => 450,
            'sale_price' => 550,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'alert_qty' => 5,
            'status' => 'active',
            'units' => [
                [
                    'unit_id' => $unit->id,
                    'is_base' => true,
                    'conversion_factor' => 1,
                    'is_smaller_unit' => false,
                ],
            ],
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'shop_id' => $this->shop->id,
            'name' => 'Basmati Rice 5kg',
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
        ]);

        $product = Product::where('sku', 'RICE-001')->first();
        $this->assertEquals('Groceries', $product->category->name);
        $this->assertEquals('Rice', $product->subCategory->name);
    }

    public function test_can_create_expense_with_category_and_sub_category(): void
    {
        $account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cash Account',
            'type' => 'cash',
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $category = ExpenseCategory::create(['shop_id' => $this->shop->id, 'name' => 'Utilities']);
        $subCategory = ExpenseCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Electricity Bill']);

        $response = $this->actingAs($this->user)->post(route('expense.store'), [
            'account_id' => $account->id,
            'expense_category_id' => $category->id,
            'expense_sub_category_id' => $subCategory->id,
            'title' => 'Shop Electricity July',
            'amount' => 1500,
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('expense.index'));
        $this->assertDatabaseHas('expenses', [
            'shop_id' => $this->shop->id,
            'expense_category_id' => $category->id,
            'expense_sub_category_id' => $subCategory->id,
            'amount' => 1500,
        ]);

        $expense = Expense::where('title', 'Shop Electricity July')->first();
        $this->assertEquals('Utilities', $expense->category->name);
        $this->assertEquals('Electricity Bill', $expense->subCategory->name);
    }

    public function test_cannot_delete_category_with_sub_categories(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Fashion']);
        SubCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Mens Wear']);

        $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category));
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_list_accurately_counts_sub_categories_and_products(): void
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Tech']);
        SubCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Laptops']);
        SubCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Tablets']);

        $categoryWithCount = Category::parents()->withCount(['subCategories', 'products'])->where('id', $category->id)->first();

        $this->assertEquals(2, $categoryWithCount->sub_categories_count);
    }

    public function test_expense_category_list_accurately_counts_sub_categories_and_expenses(): void
    {
        $category = ExpenseCategory::create(['shop_id' => $this->shop->id, 'name' => 'Office']);
        ExpenseSubCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Supplies']);
        ExpenseSubCategory::create(['shop_id' => $this->shop->id, 'parent_id' => $category->id, 'name' => 'Snacks']);

        $categoryWithCount = ExpenseCategory::parents()->withCount(['subCategories', 'expenses'])->where('id', $category->id)->first();

        $this->assertEquals(2, $categoryWithCount->sub_categories_count);
    }
}
