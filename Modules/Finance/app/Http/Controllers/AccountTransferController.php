<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Finance\DataTables\AccountTransfersDataTable;
use Modules\Finance\Http\Requests\StoreAccountTransferRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransfer;
use Modules\Finance\Services\AccountTransactionService;

class AccountTransferController extends Controller
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function index(AccountTransfersDataTable $dataTable): mixed
    {
        $totalTransferAmount = (float) AccountTransfer::sum('amount');
        $totalChargeAmount = (float) AccountTransfer::sum('charge');
        $thisMonthTransferAmount = (float) AccountTransfer::whereYear('transfer_date', now()->year)
            ->whereMonth('transfer_date', now()->month)
            ->sum('amount');
        $totalTransferCount = (int) AccountTransfer::count();

        $accounts = Account::active()->orderBy('name')->get();

        return $dataTable->render('finance::transfers.index', [
            'accounts' => $accounts,
            'transfer' => new AccountTransfer,
            'totalTransferAmount' => $totalTransferAmount,
            'totalChargeAmount' => $totalChargeAmount,
            'thisMonthTransferAmount' => $thisMonthTransferAmount,
            'totalTransferCount' => $totalTransferCount,
        ]);
    }

    public function create(): View
    {
        $accounts = Account::active()->orderBy('name')->get();

        return view('finance::transfers.create', [
            'transfer' => new AccountTransfer,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreAccountTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $fromAccount = Account::findOrFail($data['from_account_id']);
        $toAccount = Account::findOrFail($data['to_account_id']);
        $amount = (float) $data['amount'];
        $charge = (float) ($data['charge'] ?? 0);

        if ((float) $fromAccount->current_balance < ($amount + $charge)) {
            throw ValidationException::withMessages([
                'amount' => "উৎস অ্যাকাউন্ট '{$fromAccount->name}' এ পর্যাপ্ত ব্যালেন্স নেই (বর্তমান ব্যালেন্স: ৳".number_format($fromAccount->current_balance, 2).')',
            ]);
        }

        $this->transactionService->transfer(
            fromAccount: $fromAccount,
            toAccount: $toAccount,
            amount: $amount,
            charge: $charge,
            transferDate: $data['transfer_date'],
            note: $data['note'] ?? null,
            userId: Auth::id()
        );

        return redirect()->route('account-transfers.index')->with('status', 'ফান্ড ট্রান্সফার সফলভাবে সম্পন্ন হয়েছে');
    }

    public function destroy(AccountTransfer $accountTransfer, Request $request): RedirectResponse|JsonResponse
    {
        $this->transactionService->deleteTransactionsFor($accountTransfer);
        $accountTransfer->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ফান্ড ট্রান্সফার রেকর্ড বাতিল করা হয়েছে',
            ]);
        }

        return redirect()->route('account-transfers.index')->with('status', 'ফান্ড ট্রান্সফার রেকর্ড বাতিল করা হয়েছে');
    }
}
