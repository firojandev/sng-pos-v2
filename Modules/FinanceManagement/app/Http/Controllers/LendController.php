<?php

namespace Modules\FinanceManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\Models\Account;
use Modules\FinanceManagement\DataTables\LendsDataTable;
use Modules\FinanceManagement\Http\Requests\StoreLendRequest;
use Modules\FinanceManagement\Http\Requests\UpdateLendRequest;
use Modules\FinanceManagement\Models\Lend;

class LendController extends Controller
{
    public function index(LendsDataTable $dataTable): mixed
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        $metrics = [
            'totalDue' => (float) Lend::where('status', 'due')->sum('amount'),
            'totalReceived' => (float) Lend::where('status', 'received')->sum('amount'),
            'totalCount' => (int) Lend::count(),
        ];

        return $dataTable->render('financemanagement::lend.index', compact('accounts', 'metrics'));
    }

    public function create(): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('financemanagement::lend.create', [
            'lend' => new Lend,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreLendRequest $request): RedirectResponse|JsonResponse
    {
        $lend = Lend::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ধারের তথ্য সফলভাবে যোগ করা হয়েছে',
                'lend' => $lend,
            ]);
        }

        return redirect()->route('lend.index')->with('status', 'ধারের তথ্য সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Lend $lend): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $lend->id,
                'account_id' => $lend->account_id,
                'borrower_name' => $lend->borrower_name,
                'amount' => (float) $lend->amount,
                'date' => optional($lend->date)->format('Y-m-d'),
                'status' => $lend->status,
                'note' => $lend->note,
                'update_url' => route('lend.update', $lend),
            ]);
        }

        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('financemanagement::lend.edit', compact('lend', 'accounts'));
    }

    public function update(UpdateLendRequest $request, Lend $lend): RedirectResponse|JsonResponse
    {
        $lend->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ধারের তথ্য হালনাগাদ করা হয়েছে',
                'lend' => $lend,
            ]);
        }

        return redirect()->route('lend.index')->with('status', 'ধারের তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Lend $lend): RedirectResponse|JsonResponse
    {
        $lend->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ধারের তথ্য মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('lend.index')->with('status', 'ধারের তথ্য মুছে ফেলা হয়েছে');
    }
}
