<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Finance\Http\Requests\StoreExpenseSubCategoryRequest;
use Modules\Finance\Http\Requests\UpdateExpenseSubCategoryRequest;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Finance\Models\ExpenseSubCategory;

class ExpenseSubCategoryController extends Controller
{
    public function index(): View
    {
        $subCategories = ExpenseSubCategory::with('category')->withCount('expenses')->latest()->paginate(10);
        $categories = ExpenseCategory::parents()->orderBy('name')->get();

        return view('finance::expense-sub-categories.index', compact('subCategories', 'categories'));
    }

    public function create(): View
    {
        $categories = ExpenseCategory::parents()->orderBy('name')->get();

        return view('finance::expense-sub-categories.create', [
            'subCategory' => new ExpenseSubCategory,
            'categories' => $categories,
        ]);
    }

    public function store(StoreExpenseSubCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['category_id']) && ! isset($data['parent_id'])) {
            $data['parent_id'] = $data['category_id'];
        }

        ExpenseSubCategory::create($data);

        return redirect()->route('expense-sub-categories.index')->with('status', 'ব্যয় সাব-ক্যাটাগরি সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(ExpenseSubCategory $expenseSubCategory): View
    {
        $categories = ExpenseCategory::parents()->orderBy('name')->get();

        return view('finance::expense-sub-categories.edit', [
            'subCategory' => $expenseSubCategory,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateExpenseSubCategoryRequest $request, ExpenseSubCategory $expenseSubCategory): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['category_id']) && ! isset($data['parent_id'])) {
            $data['parent_id'] = $data['category_id'];
        }

        $expenseSubCategory->update($data);

        return redirect()->route('expense-sub-categories.index')->with('status', 'ব্যয় সাব-ক্যাটাগরি হালনাগাদ করা হয়েছে');
    }

    public function destroy(ExpenseSubCategory $expenseSubCategory): RedirectResponse
    {
        if ($expenseSubCategory->expenses()->exists()) {
            return redirect()->route('expense-sub-categories.index')->with('status', 'এই সাব-ক্যাটাগরিতে ব্যয় যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $expenseSubCategory->delete();

        return redirect()->route('expense-sub-categories.index')->with('status', 'ব্যয় সাব-ক্যাটাগরি মুছে ফেলা হয়েছে');
    }
}
