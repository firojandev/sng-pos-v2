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
    /**
     * Common payment methods mapping (Cash, Bank, MFS).
     *
     * @return array<string, array{bn: string, en: string}>
     */
    protected function paymentMethods(): array
    {
        return [
            'cash' => ['bn' => 'নগদ', 'en' => 'Cash'],
            'bank' => ['bn' => 'ব্যাংক', 'en' => 'Bank'],
            'mfs' => ['bn' => 'মোবাইল ব্যাংকিং (MFS)', 'en' => 'Mobile Banking (MFS)'],
        ];
    }

    /**
     * Resolve the appropriate cash account ID if payment method is cash.
     */
    protected function resolveCashAccountId(?int $accountId, ?string $paymentMethod): ?int
    {
        if ($paymentMethod === 'cash') {
            if ($accountId && $acc = Account::find($accountId)) {
                if ($acc->type === 'cash') {
                    return $acc->id;
                }
            }

            $cashAcc = Account::where('type', 'cash')->where('is_default', true)->first()
                ?? Account::where('type', 'cash')->first();

            return $cashAcc?->id ?? $accountId;
        }

        return $accountId;
    }

    public function index(ExpensesDataTable $dataTable): mixed
    {
        $expenseCategories = ExpenseCategory::parents()->with('subCategories')->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $subCategoriesByCategory = $expenseCategories->mapWithKeys(
            fn ($category) => [$category->id => $category->subCategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()]
        );

        $metrics = [
            'totalExpense' => (float) Expense::sum('amount'),
            'todayExpense' => (float) Expense::whereDate('expense_date', today())->sum('amount'),
            'thisMonthExpense' => (float) Expense::whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month)
                ->sum('amount'),
            'totalCount' => (int) Expense::count(),
        ];

        $paymentMethods = $this->paymentMethods();

        return $dataTable->render('finance::expenses.index', compact('expenseCategories', 'accounts', 'subCategoriesByCategory', 'metrics', 'paymentMethods'));
    }

    public function create(): View
    {
        $expenseCategories = ExpenseCategory::parents()->with('subCategories')->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $paymentMethods = $this->paymentMethods();

        return view('finance::expenses.create', [
            'expense' => new Expense,
            'expenseCategories' => $expenseCategories,
            'accounts' => $accounts,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['account_id'] = $this->resolveCashAccountId(
            isset($data['account_id']) ? (int) $data['account_id'] : null,
            $data['payment_method'] ?? 'cash'
        );

        $expense = Expense::create($data);

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
        $paymentMethods = $this->paymentMethods();

        return view('finance::expenses.edit', compact('expense', 'expenseCategories', 'accounts', 'paymentMethods'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['account_id'] = $this->resolveCashAccountId(
            isset($data['account_id']) ? (int) $data['account_id'] : null,
            $data['payment_method'] ?? 'cash'
        );

        $expense->update($data);

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
