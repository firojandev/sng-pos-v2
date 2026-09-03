<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\DataTables\IncomesDataTable;
use Modules\Finance\Http\Requests\StoreIncomeRequest;
use Modules\Finance\Http\Requests\UpdateIncomeRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Income;

class IncomeController extends Controller
{
    public function index(IncomesDataTable $dataTable): mixed
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return $dataTable->render('finance::income.index', compact('accounts'));
    }

    public function create(): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('finance::income.create', [
            'income' => new Income,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreIncomeRequest $request): RedirectResponse|JsonResponse
    {
        $income = Income::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'আয় সফলভাবে যোগ করা হয়েছে',
                'income' => $income,
            ]);
        }

        return redirect()->route('income.index')->with('status', 'আয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Income $income): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $income->id,
                'source' => $income->source,
                'amount' => (float) $income->amount,
                'income_date' => optional($income->income_date)->format('Y-m-d'),
                'account_id' => $income->account_id,
                'payment_method' => $income->payment_method,
                'note' => $income->note,
                'update_url' => route('income.update', $income),
            ]);
        }

        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('finance::income.edit', compact('income', 'accounts'));
    }

    public function update(UpdateIncomeRequest $request, Income $income): RedirectResponse|JsonResponse
    {
        $income->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'আয় হালনাগাদ করা হয়েছে',
                'income' => $income,
            ]);
        }

        return redirect()->route('income.index')->with('status', 'আয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Income $income): RedirectResponse|JsonResponse
    {
        $income->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'আয় মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('income.index')->with('status', 'আয় মুছে ফেলা হয়েছে');
    }
}
