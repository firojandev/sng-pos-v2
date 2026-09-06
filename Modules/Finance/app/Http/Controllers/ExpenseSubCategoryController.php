<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\DataTables\ExpenseSubCategoriesDataTable;
use Modules\Finance\Http\Requests\StoreExpenseSubCategoryRequest;
use Modules\Finance\Http\Requests\UpdateExpenseSubCategoryRequest;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Finance\Models\ExpenseSubCategory;

class ExpenseSubCategoryController extends Controller
{
    public function index(ExpenseSubCategoriesDataTable $dataTable): mixed
    {
        $categories = ExpenseCategory::parents()->orderBy('name')->get();

        return $dataTable->render('finance::expense-sub-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $categories = ExpenseCategory::parents()->orderBy('name')->get();

        return view('finance::expense-sub-categories.create', [
            'subCategory' => new ExpenseSubCategory,
            'categories' => $categories,
        ]);
    }

    public function store(StoreExpenseSubCategoryRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        if (isset($data['category_id']) && ! isset($data['parent_id'])) {
            $data['parent_id'] = $data['category_id'];
        }

        $subCategory = ExpenseSubCategory::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ব্যয় সাব-ক্যাটাগরি সফলভাবে যোগ করা হয়েছে',
                'subCategory' => $subCategory,
            ]);
        }

        return redirect()->route('expense-sub-categories.index')->with('status', 'ব্যয় সাব-ক্যাটাগরি সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, ExpenseSubCategory $expenseSubCategory): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $expenseSubCategory->id,
                'parent_id' => $expenseSubCategory->parent_id,
                'name' => $expenseSubCategory->name,
                'description' => $expenseSubCategory->description,
                'update_url' => route('expense-sub-categories.update', $expenseSubCategory),
            ]);
        }

        $categories = ExpenseCategory::parents()->orderBy('name')->get();

        return view('finance::expense-sub-categories.edit', [
            'subCategory' => $expenseSubCategory,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateExpenseSubCategoryRequest $request, ExpenseSubCategory $expenseSubCategory): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        if (isset($data['category_id']) && ! isset($data['parent_id'])) {
            $data['parent_id'] = $data['category_id'];
        }

        $expenseSubCategory->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ব্যয় সাব-ক্যাটাগরি হালনাগাদ করা হয়েছে',
                'subCategory' => $expenseSubCategory,
            ]);
        }

        return redirect()->route('expense-sub-categories.index')->with('status', 'ব্যয় সাব-ক্যাটাগরি হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, ExpenseSubCategory $expenseSubCategory): RedirectResponse|JsonResponse
    {
        if ($expenseSubCategory->expenses()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই সাব-ক্যাটাগরিতে ব্যয় যুক্ত আছে, মুছে ফেলা যাবে না',
                ], 422);
            }

            return redirect()->route('expense-sub-categories.index')->with('status', 'এই সাব-ক্যাটাগরিতে ব্যয় যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $expenseSubCategory->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ব্যয় সাব-ক্যাটাগরি মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('expense-sub-categories.index')->with('status', 'ব্যয় সাব-ক্যাটাগরি মুছে ফেলা হয়েছে');
    }
}
