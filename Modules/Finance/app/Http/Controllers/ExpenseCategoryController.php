<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Finance\Http\Requests\StoreExpenseCategoryRequest;
use Modules\Finance\Http\Requests\UpdateExpenseCategoryRequest;
use Modules\Finance\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $expenseCategories = ExpenseCategory::withCount('expenses')->latest()->paginate(10);

        return view('finance::expense-categories.index', compact('expenseCategories'));
    }

    public function create(): View
    {
        return view('finance::expense-categories.create', ['expenseCategory' => new ExpenseCategory()]);
    }

    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        ExpenseCategory::create($request->validated());

        return redirect()->route('expense-categories.index')->with('status', 'ব্যয় ক্যাটাগরি সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        return view('finance::expense-categories.edit', compact('expenseCategory'));
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update($request->validated());

        return redirect()->route('expense-categories.index')->with('status', 'ব্যয় ক্যাটাগরি হালনাগাদ করা হয়েছে');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            return redirect()->route('expense-categories.index')->with('status', 'এই ক্যাটাগরিতে ব্যয় যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')->with('status', 'ব্যয় ক্যাটাগরি মুছে ফেলা হয়েছে');
    }
}
