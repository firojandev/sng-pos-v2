<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Finance\Http\Requests\StoreAccountTransferRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransfer;
use Modules\Finance\Services\AccountTransactionService;

class AccountTransferController extends Controller
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function index(Request $request): View
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $search = trim((string) $request->query('q', ''));

        $query = AccountTransfer::with(['fromAccount', 'toAccount', 'creator'])
            ->whereDate('transfer_date', '>=', $from)
            ->whereDate('transfer_date', '<=', $to);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transfer_no', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('fromAccount', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('toAccount', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        $transfers = $query->latest('transfer_date')->latest('id')->paginate(15)->withQueryString();
        $totalTransferAmount = (clone $query)->sum('amount');
        $totalChargeAmount = (clone $query)->sum('charge');

        $accounts = Account::active()->orderBy('name')->get();

        return view('finance::transfers.index', [
            'transfers' => $transfers,
            'accounts' => $accounts,
            'transfer' => new AccountTransfer,
            'totalTransferAmount' => $totalTransferAmount,
            'totalChargeAmount' => $totalChargeAmount,
            'from' => $from,
            'to' => $to,
            'search' => $search,
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

        $redirectRoute = $request->input('redirect_to', 'account-transfers.index');
        $targetUrl = ($redirectRoute === 'accounts.index') ? route('accounts.index') : route('account-transfers.index');

        return redirect()->to($targetUrl)->with('status', 'ফান্ড ট্রান্সফার সফলভাবে সম্পন্ন হয়েছে');
    }

    public function destroy(AccountTransfer $accountTransfer): RedirectResponse
    {
        $this->transactionService->deleteTransactionsFor($accountTransfer);
        $accountTransfer->delete();

        return redirect()->route('account-transfers.index')->with('status', 'ফান্ড ট্রান্সফার রেকর্ড বাতিল করা হয়েছে');
    }
}
