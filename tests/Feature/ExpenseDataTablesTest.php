<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\DataTables\ExpensesDataTable;
use Modules\Finance\DataTables\ExpenseSubCategoriesDataTable;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Finance\Models\ExpenseSubCategory;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseDataTablesTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Account $account;

    protected ExpenseCategory $category;

    protected ExpenseSubCategory $subCategory;

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
            'name' => 'Finance Test Shop',
            'slug' => 'finance-test-shop',
            'status' => 'active',
            'enabled_features' => Features::keys(),
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }

        $this->user = User::create([
            'name' => 'Finance Admin',
            'email' => 'admin@finance.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $this->account = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Cash',
            'type' => 'cash',
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->category = ExpenseCategory::create([
            'shop_id' => $this->shop->id,
            'name' => 'Office Expense',
            'description' => 'Office related costs',
        ]);

        $this->subCategory = ExpenseSubCategory::create([
            'shop_id' => $this->shop->id,
            'parent_id' => $this->category->id,
            'name' => 'Stationery',
            'description' => 'Pens, papers, books',
        ]);
    }

    public function test_expenses_datatable_generates_html_builder(): void
    {
        $dataTable = new ExpensesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('expenses-data-table', $html->getTableAttribute('id'));
        $this->assertCount(6, $dataTable->getColumns());
    }

    public function test_expenses_datatable_query_returns_query_builder(): void
    {
        $dataTable = new ExpensesDataTable;
        $query = $dataTable->query(new Expense);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_expenses_index_page_loads_with_modals_and_datatable(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense.index'));

        $response->assertOk();
        $response->assertSee('expenses-data-table');
        $response->assertSee('createExpenseModal');
        $response->assertSee('editExpenseModal');
        $response->assertSee('নতুন ব্যয়');
    }

    public function test_expenses_datatable_ajax_returns_json(): void
    {
        Expense::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'expense_sub_category_id' => $this->subCategory->id,
            'title' => 'Office Notebooks',
            'amount' => 450.00,
            'expense_date' => now()->toDateString(),
            'note' => 'For accounting dept',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('expense.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('Office Notebooks', $response->json('data.0.title'));
        $this->assertStringContainsString('Office Expense', $response->json('data.0.category'));
        $this->assertStringContainsString('Stationery', $response->json('data.0.category'));
        $this->assertStringContainsString('450.00', $response->json('data.0.amount'));
    }

    public function test_expense_can_be_created_via_post_and_ajax(): void
    {
        // Standard POST
        $response = $this->actingAs($this->user)->post(route('expense.store'), [
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'expense_sub_category_id' => $this->subCategory->id,
            'title' => 'Internet Bill',
            'amount' => 1200,
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('expense.index'));
        $this->assertDatabaseHas('expenses', [
            'shop_id' => $this->shop->id,
            'title' => 'Internet Bill',
            'amount' => 1200,
        ]);

        // AJAX POST
        $ajaxResponse = $this->actingAs($this->user)->postJson(route('expense.store'), [
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'expense_sub_category_id' => $this->subCategory->id,
            'title' => 'Electricity Bill',
            'amount' => 3500,
            'expense_date' => now()->toDateString(),
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertDatabaseHas('expenses', [
            'shop_id' => $this->shop->id,
            'title' => 'Electricity Bill',
            'amount' => 3500,
        ]);
    }

    public function test_expense_edit_endpoint_returns_json_for_modal(): void
    {
        $expense = Expense::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'expense_sub_category_id' => $this->subCategory->id,
            'title' => 'Printer Ink',
            'amount' => 800,
            'expense_date' => now()->toDateString(),
            'note' => 'Color ink cartridge',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('expense.edit', $expense));

        $response->assertOk();
        $response->assertJson([
            'id' => $expense->id,
            'title' => 'Printer Ink',
            'amount' => 800,
            'expense_category_id' => $this->category->id,
            'expense_sub_category_id' => $this->subCategory->id,
            'account_id' => $this->account->id,
            'note' => 'Color ink cartridge',
            'update_url' => route('expense.update', $expense),
        ]);
    }

    public function test_expense_can_be_updated_via_put_and_ajax(): void
    {
        $expense = Expense::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'title' => 'Old Title',
            'amount' => 500,
            'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->putJson(route('expense.update', $expense), [
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'expense_sub_category_id' => $this->subCategory->id,
            'title' => 'Updated Title',
            'amount' => 650,
            'expense_date' => now()->toDateString(),
            'note' => 'Updated note',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'title' => 'Updated Title',
            'amount' => 650,
        ]);
    }

    public function test_expense_can_be_deleted_via_delete_and_ajax(): void
    {
        $expense = Expense::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'title' => 'To Delete',
            'amount' => 300,
            'expense_date' => now()->toDateString(),
        ]);

        // Standard DELETE
        $response = $this->actingAs($this->user)->delete(route('expense.destroy', $expense));
        $response->assertRedirect(route('expense.index'));
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);

        $expense2 = Expense::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'title' => 'To Delete via AJAX',
            'amount' => 200,
            'expense_date' => now()->toDateString(),
        ]);

        // AJAX DELETE
        $ajaxResponse = $this->actingAs($this->user)->deleteJson(route('expense.destroy', $expense2));
        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertSoftDeleted('expenses', ['id' => $expense2->id]);
    }

    public function test_expense_sub_categories_datatable_generates_html_builder(): void
    {
        $dataTable = new ExpenseSubCategoriesDataTable;
        $html = $dataTable->html();

        $this->assertEquals('expense-sub-categories-data-table', $html->getTableAttribute('id'));
        $this->assertCount(5, $dataTable->getColumns());
    }

    public function test_expense_sub_categories_datatable_query_returns_query_builder(): void
    {
        $dataTable = new ExpenseSubCategoriesDataTable;
        $query = $dataTable->query(new ExpenseSubCategory);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_expense_sub_categories_index_page_loads_with_modals_and_datatable(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense-sub-categories.index'));

        $response->assertOk();
        $response->assertSee('expense-sub-categories-data-table');
        $response->assertSee('createExpenseSubCategoryModal');
        $response->assertSee('editExpenseSubCategoryModal');
        $response->assertSee('নতুন সাব-ক্যাটাগরি');
    }

    public function test_expense_sub_categories_datatable_ajax_returns_json(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('expense-sub-categories.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('Stationery', $response->json('data.0.name'));
        $this->assertStringContainsString('Office Expense', $response->json('data.0.parent_category'));
    }

    public function test_expense_sub_category_can_be_created_via_post_and_ajax(): void
    {
        // Standard POST
        $response = $this->actingAs($this->user)->post(route('expense-sub-categories.store'), [
            'parent_id' => $this->category->id,
            'name' => 'Cleaning Items',
            'description' => 'Mop, soap, sanitizer',
        ]);

        $response->assertRedirect(route('expense-sub-categories.index'));
        $this->assertDatabaseHas('categories', [
            'shop_id' => $this->shop->id,
            'parent_id' => $this->category->id,
            'name' => 'Cleaning Items',
            'type' => 'expense',
        ]);

        // AJAX POST
        $ajaxResponse = $this->actingAs($this->user)->postJson(route('expense-sub-categories.store'), [
            'parent_id' => $this->category->id,
            'name' => 'Snacks',
            'description' => 'Tea, coffee, biscuits',
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertDatabaseHas('categories', [
            'shop_id' => $this->shop->id,
            'parent_id' => $this->category->id,
            'name' => 'Snacks',
            'type' => 'expense',
        ]);
    }

    public function test_expense_sub_category_edit_endpoint_returns_json_for_modal(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('expense-sub-categories.edit', $this->subCategory));

        $response->assertOk();
        $response->assertJson([
            'id' => $this->subCategory->id,
            'parent_id' => $this->category->id,
            'name' => 'Stationery',
            'description' => 'Pens, papers, books',
            'update_url' => route('expense-sub-categories.update', $this->subCategory),
        ]);
    }

    public function test_expense_sub_category_can_be_updated_via_put_and_ajax(): void
    {
        $response = $this->actingAs($this->user)->putJson(route('expense-sub-categories.update', $this->subCategory), [
            'parent_id' => $this->category->id,
            'name' => 'Updated Stationery',
            'description' => 'All office stationary',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('categories', [
            'id' => $this->subCategory->id,
            'name' => 'Updated Stationery',
            'description' => 'All office stationary',
        ]);
    }

    public function test_expense_sub_category_cannot_be_deleted_when_it_has_expenses(): void
    {
        Expense::create([
            'shop_id' => $this->shop->id,
            'account_id' => $this->account->id,
            'expense_category_id' => $this->category->id,
            'expense_sub_category_id' => $this->subCategory->id,
            'title' => 'Paper ream',
            'amount' => 500,
            'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->delete(route('expense-sub-categories.destroy', $this->subCategory));
        $response->assertRedirect(route('expense-sub-categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $this->subCategory->id]);

        $ajaxResponse = $this->actingAs($this->user)->deleteJson(route('expense-sub-categories.destroy', $this->subCategory));
        $ajaxResponse->assertStatus(422);
        $this->assertDatabaseHas('categories', ['id' => $this->subCategory->id]);
    }

    public function test_expense_sub_category_can_be_deleted_when_no_expenses(): void
    {
        $emptySub = ExpenseSubCategory::create([
            'shop_id' => $this->shop->id,
            'parent_id' => $this->category->id,
            'name' => 'Empty SubCategory',
        ]);

        $response = $this->actingAs($this->user)->delete(route('expense-sub-categories.destroy', $emptySub));
        $response->assertRedirect(route('expense-sub-categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $emptySub->id]);
    }
}
