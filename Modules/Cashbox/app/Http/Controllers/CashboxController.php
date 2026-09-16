<?php

namespace Modules\Cashbox\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Cashbox\DataTables\CashTransactionsDataTable;
use Modules\Cashbox\Http\Requests\StoreCashTransactionRequest;
use Modules\Cashbox\Models\CashTransaction;

class CashboxController extends Controller
{
    public function index(CashTransactionsDataTable $dataTable, Request $request): JsonResponse|View
    {
        $type = $request->query('type', 'all');
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $creator = $request->query('creator');

        $query = CashTransaction::query();
        if ($from) {
            $query->whereDate('occurred_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('occurred_at', '<=', $to);
        }

        match ($type) {
            'cash_in' => $query->where('type', 'in'),
            'cash_out' => $query->where('type', 'out'),
            'sale' => $query->where('source', 'sale'),
            'purchase' => $query->where('source', 'purchase'),
            'income' => $query->where('source', 'income'),
            'expense' => $query->where('source', 'expense'),
            'sale_return' => $query->where('source', 'sale_return'),
            'purchase_return' => $query->where('source', 'purchase_return'),
            default => null,
        };

        if ($creator) {
            $query->where('created_by', $creator);
        }

        $summary = (clone $query)->selectRaw(
            "SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) as cash_in, ".
            "SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as cash_out, ".
            'COUNT(*) as total_count'
        )->first();

        $balance = CashTransaction::selectRaw("SUM(CASE WHEN type = 'in' THEN amount ELSE -amount END) as balance")->value('balance') ?? 0;

        $shopId = Auth::user()?->shop_id;
        $creators = $shopId ? User::where('shop_id', $shopId)->orderBy('name')->get() : collect();

        return $dataTable->render('cashbox::cashbox.index', [
            'balance' => (float) $balance,
            'summary' => $summary,
            'creators' => $creators,
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'creator' => $creator,
        ]);
    }

    public function cashIn(StoreCashTransactionRequest $request): RedirectResponse
    {
        CashTransaction::create([
            'type' => 'in',
            'source' => 'manual',
            'amount' => $request->validated('amount'),
            'note' => $request->validated('note'),
            'occurred_at' => $request->validated('occurred_at') ?? now(),
            'created_by' => Auth::id(),
        ]);

        return back()->with('status', 'ক্যাশ ইন সফলভাবে যোগ করা হয়েছে');
    }

    public function cashOut(StoreCashTransactionRequest $request): RedirectResponse
    {
        CashTransaction::create([
            'type' => 'out',
            'source' => 'manual',
            'amount' => $request->validated('amount'),
            'note' => $request->validated('note'),
            'occurred_at' => $request->validated('occurred_at') ?? now(),
            'created_by' => Auth::id(),
        ]);

        return back()->with('status', 'ক্যাশ আউট সফলভাবে যোগ করা হয়েছে');
    }
}
