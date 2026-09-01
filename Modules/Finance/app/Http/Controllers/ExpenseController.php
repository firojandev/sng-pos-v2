<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Finance\Http\Requests\StoreExpenseRequest;
use Modules\Finance\Http\Requests\UpdateExpenseRequest;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $expenses = Expense::with('category')->latest('expense_date')->paginate(10);

        return view('finance::expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('finance::expenses.create', ['expense' => new Expense, 'expenseCategories' => $expenseCategories]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        Expense::create($request->validated());

        return redirect()->route('expense.index')->with('status', 'ব্যয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Expense $expense): View
    {
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('finance::expenses.edit', compact('expense', 'expenseCategories'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()->route('expense.index')->with('status', 'ব্যয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expense.index')->with('status', 'ব্যয় মুছে ফেলা হয়েছে');
    }
}
