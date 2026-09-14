<?php

namespace Modules\FinanceManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\Models\Account;
use Modules\FinanceManagement\DataTables\SecurityMoneysDataTable;
use Modules\FinanceManagement\Http\Requests\StoreSecurityMoneyRequest;
use Modules\FinanceManagement\Http\Requests\UpdateSecurityMoneyRequest;
use Modules\FinanceManagement\Models\SecurityMoney;

class SecurityMoneyController extends Controller
{
    public function index(SecurityMoneysDataTable $dataTable): mixed
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        $metrics = [
            'totalPaid' => (float) SecurityMoney::where('status', 'paid')->sum('amount'),
            'totalReceived' => (float) SecurityMoney::where('status', 'received')->sum('amount'),
            'totalCount' => (int) SecurityMoney::count(),
        ];

        return $dataTable->render('financemanagement::security-money.index', compact('accounts', 'metrics'));
    }

    public function create(): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('financemanagement::security-money.create', [
            'securityMoney' => new SecurityMoney,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreSecurityMoneyRequest $request): RedirectResponse|JsonResponse
    {
        $securityMoney = SecurityMoney::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'জামানতের তথ্য সফলভাবে যোগ করা হয়েছে',
                'securityMoney' => $securityMoney,
            ]);
        }

        return redirect()->route('security-money.index')->with('status', 'জামানতের তথ্য সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, SecurityMoney $securityMoney): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $securityMoney->id,
                'account_id' => $securityMoney->account_id,
                'receiver_name' => $securityMoney->receiver_name,
                'amount' => (float) $securityMoney->amount,
                'date' => optional($securityMoney->date)->format('Y-m-d'),
                'status' => $securityMoney->status,
                'note' => $securityMoney->note,
                'update_url' => route('security-money.update', $securityMoney),
            ]);
        }

        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('financemanagement::security-money.edit', compact('securityMoney', 'accounts'));
    }

    public function update(UpdateSecurityMoneyRequest $request, SecurityMoney $securityMoney): RedirectResponse|JsonResponse
    {
        $securityMoney->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'জামানতের তথ্য হালনাগাদ করা হয়েছে',
                'securityMoney' => $securityMoney,
            ]);
        }

        return redirect()->route('security-money.index')->with('status', 'জামানতের তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, SecurityMoney $securityMoney): RedirectResponse|JsonResponse
    {
        $securityMoney->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'জামানতের তথ্য মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('security-money.index')->with('status', 'জামানতের তথ্য মুছে ফেলা হয়েছে');
    }
}
