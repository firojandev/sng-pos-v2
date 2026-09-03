<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Finance\Http\Requests\StoreAccountRequest;
use Modules\Finance\Http\Requests\UpdateAccountRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Finance\Services\AccountTransactionService;

class AccountController extends Controller
{
    public function __construct(
        protected AccountTransactionService $transactionService
    ) {}

    public function index(Request $request): View
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $query = Account::query();

        if ($type && in_array($type, ['cash', 'bank', 'mfs'], true)) {
            $query->where('type', $type);
        }

        if ($status && in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhere('mfs_provider', 'like', "%{$search}%");
            });
        }

        $accounts = (clone $query)->orderByDesc('is_default')->orderBy('name')->paginate(15)->withQueryString();

        // Summary KPI calculations
        $totalBalance = (float) Account::where('status', 'active')->sum('current_balance');
        $totalCash = (float) Account::where('status', 'active')->where('type', 'cash')->sum('current_balance');
        $totalBank = (float) Account::where('status', 'active')->where('type', 'bank')->sum('current_balance');
        $totalMfs = (float) Account::where('status', 'active')->where('type', 'mfs')->sum('current_balance');

        $account = new Account;
        $typeLabels = Account::typeLabels();
        $mfsTypeLabels = Account::mfsTypeLabels();
        $mfsProviders = Account::mfsProviders();

        return view('finance::accounts.index', compact(
            'accounts',
            'account',
            'typeLabels',
            'mfsTypeLabels',
            'mfsProviders',
            'totalBalance',
            'totalCash',
            'totalBank',
            'totalMfs',
            'type',
            'status',
            'search'
        ));
    }

    public function create(): View
    {
        return view('finance::accounts.create', [
            'account' => new Account,
            'typeLabels' => Account::typeLabels(),
            'mfsTypeLabels' => Account::mfsTypeLabels(),
            'mfsProviders' => Account::mfsProviders(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);
        $openingBalance = (float) ($data['opening_balance'] ?? 0);

        $account = DB::transaction(function () use ($data, $isDefault, $openingBalance) {
            $data['current_balance'] = $openingBalance;
            $data['is_default'] = $isDefault;

            $account = Account::create($data);

            if ($openingBalance > 0) {
                AccountTransaction::create([
                    'shop_id' => $account->shop_id,
                    'account_id' => $account->id,
                    'type' => 'in',
                    'amount' => $openingBalance,
                    'balance_after' => $openingBalance,
                    'source' => 'opening_balance',
                    'note' => 'প্রারম্ভিক ব্যালেন্স (Opening Balance)',
                    'occurred_at' => now(),
                    'created_by' => Auth::id(),
                ]);
            }

            if ($isDefault) {
                $this->transactionService->setDefaultAccount($account);
            }

            return $account;
        });

        return redirect()->route('accounts.index')->with('status', 'অ্যাকাউন্ট সফলভাবে তৈরি করা হয়েছে');
    }

    public function edit(Request $request, Account $account): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'branch_name' => $account->branch_name,
                'mfs_provider' => $account->mfs_provider,
                'mfs_type' => $account->mfs_type,
                'current_balance' => $account->current_balance,
                'current_balance_formatted' => '৳ '.number_format($account->current_balance, 2),
                'status' => $account->status,
                'note' => $account->note,
                'is_default' => (bool) $account->is_default,
                'update_url' => route('accounts.update', $account),
            ]);
        }

        return view('finance::accounts.edit', [
            'account' => $account,
            'typeLabels' => Account::typeLabels(),
            'mfsTypeLabels' => Account::mfsTypeLabels(),
            'mfsProviders' => Account::mfsProviders(),
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $data = $request->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);

        DB::transaction(function () use ($account, $data, $isDefault) {
            $account->update($data);

            if ($isDefault) {
                $this->transactionService->setDefaultAccount($account);
            }
        });

        return redirect()->route('accounts.index')->with('status', 'অ্যাকাউন্ট তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->is_default) {
            return redirect()->route('accounts.index')->withErrors(['error' => 'ডিফল্ট অ্যাকাউন্ট মুছে ফেলা যাবে না। অন্য কোনো অ্যাকাউন্টকে ডিফল্ট হিসেবে সেট করে এটি মুছুন।']);
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('status', 'অ্যাকাউন্ট মুছে ফেলা হয়েছে');
    }

    public function setDefault(Account $account): RedirectResponse
    {
        $this->transactionService->setDefaultAccount($account);

        return redirect()->route('accounts.index')->with('status', "'{$account->name}' অ্যাকাউন্টটিকে ডিফল্ট অ্যাকাউন্ট হিসেবে নির্ধারণ করা হয়েছে");
    }

    public function ledger(Request $request, Account $account): View
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $type = $request->query('type');
        $source = $request->query('source');

        $query = $account->transactions()
            ->whereDate('occurred_at', '>=', $from)
            ->whereDate('occurred_at', '<=', $to);

        if ($type && in_array($type, ['in', 'out'], true)) {
            $query->where('type', $type);
        }

        if ($source && array_key_exists($source, AccountTransaction::sourceLabels())) {
            $query->where('source', $source);
        }

        $totalIn = (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (clone $query)->where('type', 'out')->sum('amount');

        $transactions = $query->paginate(20)->withQueryString();

        // Calculate daily Cash In, Cash Out, and Net Change for the chart
        $chartQuery = $account->transactions()
            ->reorder()
            ->whereDate('occurred_at', '>=', $from)
            ->whereDate('occurred_at', '<=', $to);

        if ($source && array_key_exists($source, AccountTransaction::sourceLabels())) {
            $chartQuery->where('source', $source);
        }

        $dailyRecords = $chartQuery
            ->selectRaw('DATE(occurred_at) as date, type, SUM(amount) as total')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $groupedByDate = [];
        foreach ($dailyRecords as $record) {
            $groupedByDate[$record->date][$record->type] = (float) $record->total;
        }

        $startDate = Carbon::parse($from);
        $endDate = Carbon::parse($to);
        $diffDays = $startDate->diffInDays($endDate);

        $chartLabels = [];
        $chartCashIn = [];
        $chartCashOut = [];
        $chartNetChange = [];

        if ($diffDays <= 62) {
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $d = $cursor->toDateString();
                $chartLabels[] = $cursor->format('d M');
                $in = $groupedByDate[$d]['in'] ?? 0.0;
                $out = $groupedByDate[$d]['out'] ?? 0.0;
                $chartCashIn[] = round($in, 2);
                $chartCashOut[] = round($out, 2);
                $chartNetChange[] = round($in - $out, 2);
                $cursor->addDay();
            }
        } else {
            $dates = array_keys($groupedByDate);
            sort($dates);
            if (empty($dates)) {
                $chartLabels = [$startDate->format('d M'), $endDate->format('d M')];
                $chartCashIn = [0, 0];
                $chartCashOut = [0, 0];
                $chartNetChange = [0, 0];
            } else {
                foreach ($dates as $d) {
                    $chartLabels[] = Carbon::parse($d)->format('d M Y');
                    $in = $groupedByDate[$d]['in'] ?? 0.0;
                    $out = $groupedByDate[$d]['out'] ?? 0.0;
                    $chartCashIn[] = round($in, 2);
                    $chartCashOut[] = round($out, 2);
                    $chartNetChange[] = round($in - $out, 2);
                }
            }
        }

        $chartData = [
            'labels' => $chartLabels,
            'cash_in' => $chartCashIn,
            'cash_out' => $chartCashOut,
            'net_change' => $chartNetChange,
        ];

        return view('finance::accounts.ledger', [
            'account' => $account,
            'transactions' => $transactions,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'chartData' => $chartData,
            'chartTotalIn' => array_sum($chartCashIn),
            'chartTotalOut' => array_sum($chartCashOut),
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'source' => $source,
            'sourceLabels' => AccountTransaction::sourceLabels(),
        ]);
    }
}
