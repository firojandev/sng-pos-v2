<?php

namespace Modules\FinanceManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\Models\Account;
use Modules\FinanceManagement\DataTables\DebtsDataTable;
use Modules\FinanceManagement\Http\Requests\StoreDebtRequest;
use Modules\FinanceManagement\Http\Requests\UpdateDebtRequest;
use Modules\FinanceManagement\Models\Debt;

class DebtController extends Controller
{
    public function index(DebtsDataTable $dataTable): mixed
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        $metrics = [
            'totalUnpaid' => (float) Debt::where('status', 'unpaid')->sum('amount'),
            'totalPaid' => (float) Debt::where('status', 'paid')->sum('amount'),
            'totalCount' => (int) Debt::count(),
        ];

        return $dataTable->render('financemanagement::debts.index', compact('accounts', 'metrics'));
    }

    public function create(): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('financemanagement::debts.create', [
            'debt' => new Debt,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreDebtRequest $request): RedirectResponse|JsonResponse
    {
        $debt = Debt::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'দেনার তথ্য সফলভাবে যোগ করা হয়েছে',
                'debt' => $debt,
            ]);
        }

        return redirect()->route('debts.index')->with('status', 'দেনার তথ্য সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Debt $debt): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $debt->id,
                'account_id' => $debt->account_id,
                'lender_name' => $debt->lender_name,
                'amount' => (float) $debt->amount,
                'date' => optional($debt->date)->format('Y-m-d'),
                'status' => $debt->status,
                'note' => $debt->note,
                'update_url' => route('debts.update', $debt),
            ]);
        }

        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('financemanagement::debts.edit', compact('debt', 'accounts'));
    }

    public function update(UpdateDebtRequest $request, Debt $debt): RedirectResponse|JsonResponse
    {
        $debt->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'দেনার তথ্য হালনাগাদ করা হয়েছে',
                'debt' => $debt,
            ]);
        }

        return redirect()->route('debts.index')->with('status', 'দেনার তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Debt $debt): RedirectResponse|JsonResponse
    {
        $debt->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'দেনার তথ্য মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('debts.index')->with('status', 'দেনার তথ্য মুছে ফেলা হয়েছে');
    }
}
