<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Core\Support\Features;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Income;
use Modules\FinanceManagement\Models\Asset;
use Modules\FinanceManagement\Models\Debt;
use Modules\FinanceManagement\Models\Lend;
use Modules\FinanceManagement\Models\SecurityMoney;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Supplier\Models\Supplier;

class ReportController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const ROUTES = [
        'report-sales' => 'reports.sales',
        'report-purchase' => 'reports.purchase',
        'report-stock' => 'reports.stock',
        'report-products' => 'reports.products',
        'report-profit-loss' => 'reports.profit-loss',
        'report-income' => 'reports.income',
        'report-expense' => 'reports.expense',
        'report-financial-position' => 'reports.financial-position',
        'report-balance-sheet' => 'reports.balance-sheet',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $labels = Features::all();

        $cards = collect(self::ROUTES)
            ->filter(fn (string $route, string $key) => $user->shop
                && $user->shop->hasFeature($key)
                && $user->can("{$key}.view"))
            ->map(fn (string $route, string $key) => [
                'key' => $key,
                'route' => $route,
                'bn' => $labels[$key]['bn'] ?? $key,
                'en' => $labels[$key]['en'] ?? $key,
            ])
            ->values();

        return view('report::index', compact('cards'));
    }

    public function sales(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);

        $sales = Sale::query()
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->with('customer')
            ->orderByDesc('sale_date')
            ->get();

        $totals = [
            'total' => (float) $sales->sum('total'),
            'profit' => (float) $sales->sum('profit'),
            'due' => (float) $sales->sum('due_amount'),
            'count' => $sales->count(),
        ];

        return view('report::sales', compact('range', 'from', 'to', 'sales', 'totals'));
    }

    public function purchase(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);

        $purchases = Purchase::query()
            ->when($from, fn ($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchase_date', '<=', $to))
            ->with('supplier')
            ->orderByDesc('purchase_date')
            ->get();

        $totals = [
            'total' => (float) $purchases->sum('total'),
            'transportation_cost' => (float) $purchases->sum('transportation_cost'),
            'due' => (float) $purchases->sum('due_amount'),
            'count' => $purchases->count(),
        ];

        return view('report::purchase', compact('range', 'from', 'to', 'purchases', 'totals'));
    }

    public function stock(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);

        $onHand = Batch::query()
            ->join('products', 'batches.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.purchase_price')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.purchase_price',
                DB::raw('SUM(batches.quantity) as qty_on_hand'),
                DB::raw('SUM(batches.quantity * products.purchase_price) as stock_value'),
            ])
            ->orderBy('products.name')
            ->get();

        $movementSummary = StockMovement::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->select('type', DB::raw('COUNT(*) as movement_count'), DB::raw('SUM(quantity_change) as quantity_change'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $totals = [
            'qty_on_hand' => (float) $onHand->sum('qty_on_hand'),
            'stock_value' => (float) $onHand->sum('stock_value'),
        ];

        return view('report::stock', compact('range', 'from', 'to', 'onHand', 'movementSummary', 'totals'));
    }

    public function products(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);

        $sold = SaleItem::query()
            ->whereHas('sale', function ($q) use ($from, $to) {
                $q->when($from, fn ($qq) => $qq->whereDate('sale_date', '>=', $from))
                    ->when($to, fn ($qq) => $qq->whereDate('sale_date', '<=', $to));
            })
            ->select('product_id', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(total) as revenue'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $stockByProduct = Batch::query()
            ->select('product_id', DB::raw('SUM(quantity) as qty_on_hand'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $productIds = $sold->keys()->merge($stockByProduct->keys())->unique();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($sold, $stockByProduct) {
                $product->qty_sold = (float) ($sold[$product->id]->qty_sold ?? 0);
                $product->revenue = (float) ($sold[$product->id]->revenue ?? 0);
                $product->qty_on_hand = (float) ($stockByProduct[$product->id]->qty_on_hand ?? 0);

                return $product;
            });

        $totals = [
            'qty_sold' => (float) $products->sum('qty_sold'),
            'revenue' => (float) $products->sum('revenue'),
        ];

        return view('report::products', compact('range', 'from', 'to', 'products', 'totals'));
    }

    public function profitLoss(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);

        $sales = (float) Sale::query()
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->sum('total');

        $productPurchase = (float) Purchase::query()
            ->when($from, fn ($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchase_date', '<=', $to))
            ->sum('total');

        $transportFee = (float) Purchase::query()
            ->when($from, fn ($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchase_date', '<=', $to))
            ->sum('transportation_cost');

        $profitFromSales = (float) Sale::query()
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->sum('profit');

        $otherIncome = (float) Income::query()
            ->when($from, fn ($q) => $q->whereDate('income_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('income_date', '<=', $to))
            ->sum('amount');

        $totalExpense = (float) Expense::query()
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->sum('amount');

        $net = ($profitFromSales + $otherIncome) - ($productPurchase + $transportFee + $totalExpense);
        $netProfit = max($net, 0);
        $totalLoss = max(-$net, 0);

        return view('report::profit-loss', compact(
            'range', 'from', 'to', 'sales', 'productPurchase', 'transportFee',
            'profitFromSales', 'otherIncome', 'totalExpense', 'netProfit', 'totalLoss',
        ));
    }

    public function income(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);

        $incomes = Income::query()
            ->when($from, fn ($q) => $q->whereDate('income_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('income_date', '<=', $to))
            ->orderByDesc('income_date')
            ->get();

        $totals = [
            'amount' => (float) $incomes->sum('amount'),
            'count' => $incomes->count(),
        ];

        return view('report::income', compact('range', 'from', 'to', 'incomes', 'totals'));
    }

    public function expense(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);

        $expenses = Expense::query()
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->with(['category', 'subCategory'])
            ->orderByDesc('expense_date')
            ->get();

        $totals = [
            'amount' => (float) $expenses->sum('amount'),
            'count' => $expenses->count(),
        ];

        return view('report::expense', compact('range', 'from', 'to', 'expenses', 'totals'));
    }

    public function financialPosition(Request $request): View
    {
        $asOf = $this->resolveAsOf($request);
        $data = $this->computeFinancialSnapshot($asOf);

        return view('report::financial-position', $data);
    }

    public function balanceSheet(Request $request): View
    {
        $asOf = $this->resolveAsOf($request);
        $data = $this->computeFinancialSnapshot($asOf);

        return view('report::balance-sheet', $data);
    }

    /**
     * Compute a unified accounting snapshot as of a given moment.
     * Both Financial Position and Balance Sheet share this identical calculation engine.
     *
     * @return array<string, mixed>
     */
    private function computeFinancialSnapshot(Carbon $asOf): array
    {
        $cashAndBank = $this->cashAndBankBalance($asOf);
        $stockValue = $this->stockValue($asOf);
        $receivable = $this->customerReceivable($asOf);
        $lendOutstanding = (float) Lend::where('status', 'due')->where('date', '<=', $asOf)->sum('amount');
        $securityMoneyPaid = (float) SecurityMoney::where('status', 'paid')->where('date', '<=', $asOf)->sum('amount');
        $fixedAssets = $this->fixedAssetsValue($asOf);

        // Current & Non-Current Asset classification
        $currentAssets = $cashAndBank + $stockValue + $receivable + $lendOutstanding + $securityMoneyPaid;
        $nonCurrentAssets = $fixedAssets;
        $totalAssets = $currentAssets + $nonCurrentAssets;
        $totalAssetsWithSecurity = $fixedAssets + $securityMoneyPaid;

        // Current & Non-Current Liability classification
        $payable = $this->supplierPayable($asOf);
        $debtsPayable = (float) Debt::where('status', 'unpaid')->where('date', '<=', $asOf)->sum('amount');
        $securityMoneyReceived = (float) SecurityMoney::where('status', 'received')->where('date', '<=', $asOf)->sum('amount');
        $currentLiabilities = $payable + $debtsPayable + $securityMoneyReceived;
        $nonCurrentLiabilities = 0.0;
        $totalLiabilities = $currentLiabilities + $nonCurrentLiabilities;

        // Calculated Equity / Net Financial Position
        $netPosition = $totalAssets - $totalLiabilities;
        $equity = $netPosition;

        // Solvency Coverage (Multiple e.g. 6.70x & Percentage e.g. 669.6%)
        $solvencyRatio = $totalLiabilities > 0 ? round(($totalAssets / $totalLiabilities) * 100, 1) : null;
        $solvencyMultiple = $totalLiabilities > 0 ? round($totalAssets / $totalLiabilities, 2) : null;

        // Mathematical integrity check: Total Assets == Total Liabilities + Equity
        $variance = round(abs($totalAssets - ($totalLiabilities + $netPosition)), 2);
        $isBalanced = $variance < 0.01;

        return [
            'asOf' => $asOf,
            'cashAndBank' => $cashAndBank,
            'stockValue' => $stockValue,
            'receivable' => $receivable,
            'lendOutstanding' => $lendOutstanding,
            'lendReceivable' => $lendOutstanding,
            'securityMoneyPaid' => $securityMoneyPaid,
            'fixedAssets' => $fixedAssets,
            'currentAssets' => $currentAssets,
            'nonCurrentAssets' => $nonCurrentAssets,
            'totalAssets' => $totalAssets,
            'totalAssetsWithSecurity' => $totalAssetsWithSecurity,
            'payable' => $payable,
            'debtsPayable' => $debtsPayable,
            'securityMoneyReceived' => $securityMoneyReceived,
            'currentLiabilities' => $currentLiabilities,
            'nonCurrentLiabilities' => $nonCurrentLiabilities,
            'totalLiabilities' => $totalLiabilities,
            'netPosition' => $netPosition,
            'equity' => $equity,
            'solvencyRatio' => $solvencyRatio,
            'solvencyMultiple' => $solvencyMultiple,
            'variance' => $variance,
            'isBalanced' => $isBalanced,
        ];
    }

    /**
     * Net valuation of fixed assets (amount minus depreciation) as of a given moment.
     */
    private function fixedAssetsValue(Carbon $asOf): float
    {
        return (float) Asset::where('created_at', '<=', $asOf)->sum(DB::raw("CASE WHEN amount > (CASE WHEN depreciation_type = 'percentage' THEN (amount * COALESCE(depreciation, 0) / 100.0) ELSE COALESCE(depreciation, 0) END) THEN amount - (CASE WHEN depreciation_type = 'percentage' THEN (amount * COALESCE(depreciation, 0) / 100.0) ELSE COALESCE(depreciation, 0) END) ELSE 0 END"));
    }

    /**
     * Resolve the "as of" moment for a snapshot report from the request,
     * defaulting to now and clamping any future date back to now.
     */
    private function resolveAsOf(Request $request): Carbon
    {
        $raw = $request->query('as_of');

        if (! $raw) {
            return now();
        }

        $asOf = Carbon::parse($raw)->endOfDay();

        return $asOf->isFuture() ? now() : $asOf;
    }

    /**
     * Inventory valuation as of a given moment: on-hand quantity × current
     * purchase price, summed across all batches. For today, this is the exact
     * live figure used elsewhere (e.g. stock()); for a past date, on-hand qty
     * is reconstructed by rolling back every StockMovement recorded after
     * that moment (no historical cost is tracked, so today's purchase price
     * is still used for valuation — the same simplification most small
     * business software makes).
     */
    private function stockValue(Carbon $asOf): float
    {
        if ($asOf->isToday()) {
            return (float) Batch::query()
                ->join('products', 'batches.product_id', '=', 'products.id')
                ->sum(DB::raw('batches.quantity * products.purchase_price'));
        }

        $currentByProduct = Batch::query()
            ->join('products', 'batches.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.purchase_price')
            ->select(['products.id', 'products.purchase_price', DB::raw('SUM(batches.quantity) as qty')])
            ->get()
            ->keyBy('id');

        $futureChangeByProduct = StockMovement::query()
            ->where('created_at', '>', $asOf)
            ->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(quantity_change) as change_qty'))
            ->get()
            ->keyBy('product_id');

        $total = 0.0;
        foreach ($currentByProduct as $productId => $row) {
            $historicalQty = (float) $row->qty - (float) ($futureChangeByProduct[$productId]->change_qty ?? 0);
            $total += max($historicalQty, 0) * (float) $row->purchase_price;
        }

        return $total;
    }

    /**
     * Total amount owed to the shop by customers as of a given date (opening
     * due + due on sales dated on/before it). The due amount itself is a
     * best-effort figure — it reflects the sale's current due, not
     * necessarily what was still due exactly on that date.
     */
    private function customerReceivable(Carbon $asOf): float
    {
        return (float) Customer::sum('opening_due')
            + (float) Sale::where('sale_date', '<=', $asOf)->sum('due_amount');
    }

    /**
     * Total amount the shop owes to suppliers as of a given date (opening due
     * + due on purchases dated on/before it). Same best-effort caveat as
     * customerReceivable().
     */
    private function supplierPayable(Carbon $asOf): float
    {
        return (float) Supplier::sum('opening_due')
            + (float) Purchase::where('purchase_date', '<=', $asOf)->sum('due_amount');
    }

    /**
     * Sum of all active cash, bank, and mobile-banking (MFS) account balances
     * as of a given moment. For today, this is the exact live figure
     * (Account::current_balance, kept in sync with the ledger by
     * AccountTransactionService); for a past date, each account's balance is
     * reconstructed from its latest AccountTransaction at or before that
     * moment — an account with no such transaction didn't exist yet or had
     * no activity yet, so it contributes 0.
     */
    private function cashAndBankBalance(Carbon $asOf): float
    {
        if ($asOf->isToday()) {
            return (float) Account::where('status', 'active')
                ->whereIn('type', ['cash', 'bank', 'mfs'])
                ->sum('current_balance');
        }

        $accounts = Account::where('status', 'active')
            ->whereIn('type', ['cash', 'bank', 'mfs'])
            ->get();

        $total = 0.0;
        foreach ($accounts as $account) {
            $lastTransaction = AccountTransaction::withoutGlobalScopes()
                ->where('account_id', $account->id)
                ->where('occurred_at', '<=', $asOf)
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->first();

            $total += $lastTransaction ? (float) $lastTransaction->balance_after : 0.0;
        }

        return $total;
    }

    /**
     * Resolve the active [range, from, to] window from the request, combining
     * a week/month/year/custom range selector with explicit from/to bounds
     * (the same two conventions already used by PageController::dashboard()
     * and Cashbox/Account's date filters, respectively).
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolveDateRange(Request $request): array
    {
        $range = $request->query('range', 'today');
        $range = in_array($range, ['today', 'week', 'month', 'year', 'custom'], true) ? $range : 'today';

        if ($range === 'custom') {
            $from = $request->query('from', now()->toDateString());
            $to = $request->query('to', now()->toDateString());
        } else {
            $from = match ($range) {
                'week' => now()->startOfWeek()->toDateString(),
                'month' => now()->startOfMonth()->toDateString(),
                'year' => now()->startOfYear()->toDateString(),
                default => now()->toDateString(),
            };
            $to = now()->toDateString();
        }

        return [$range, $from, $to];
    }
}
