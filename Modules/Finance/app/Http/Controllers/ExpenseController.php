<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\DataTables\ExpensesDataTable;
use Modules\Finance\Http\Requests\StoreExpenseRequest;
use Modules\Finance\Http\Requests\UpdateExpenseRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;

class ExpenseController extends Controller
{
    public function index(ExpensesDataTable $dataTable): mixed
    {
        $expenseCategories = ExpenseCategory::parents()->with('subCategories')->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $subCategoriesByCategory = $expenseCategories->mapWithKeys(
            fn ($category) => [$category->id => $category->subCategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()]
        );

        return $dataTable->render('finance::expenses.index', compact('expenseCategories', 'accounts', 'subCategoriesByCategory'));
    }

    public function create(): View
    {
        $expenseCategories = ExpenseCategory::parents()->with('subCategories')->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('finance::expenses.create', [
            'expense' => new Expense,
            'expenseCategories' => $expenseCategories,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse|JsonResponse
    {
        $expense = Expense::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ব্যয় সফলভাবে যোগ করা হয়েছে',
                'expense' => $expense,
            ]);
        }

        return redirect()->route('expense.index')->with('status', 'ব্যয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Expense $expense): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $expense->id,
                'title' => $expense->title,
                'amount' => (float) $expense->amount,
                'expense_date' => optional($expense->expense_date)->format('Y-m-d'),
                'expense_category_id' => $expense->expense_category_id,
                'expense_sub_category_id' => $expense->expense_sub_category_id,
                'account_id' => $expense->account_id,
                'payment_method' => $expense->payment_method,
                'note' => $expense->note,
                'update_url' => route('expense.update', $expense),
            ]);
        }

        $expenseCategories = ExpenseCategory::parents()->with('subCategories')->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('finance::expenses.edit', compact('expense', 'expenseCategories', 'accounts'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse|JsonResponse
    {
        $expense->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ব্যয় হালনাগাদ করা হয়েছে',
                'expense' => $expense,
            ]);
        }

        return redirect()->route('expense.index')->with('status', 'ব্যয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse|JsonResponse
    {
        $expense->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ব্যয় মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('expense.index')->with('status', 'ব্যয় মুছে ফেলা হয়েছে');
    }
}
