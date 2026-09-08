<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Core\Support\Features;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Income;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;

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
        $range = $request->query('range', 'month');
        $range = in_array($range, ['week', 'month', 'year', 'custom'], true) ? $range : 'month';

        if ($range === 'custom') {
            $from = $request->query('from', now()->startOfMonth()->toDateString());
            $to = $request->query('to', now()->toDateString());
        } else {
            $from = match ($range) {
                'week' => now()->startOfWeek()->toDateString(),
                'year' => now()->startOfYear()->toDateString(),
                default => now()->startOfMonth()->toDateString(),
            };
            $to = now()->toDateString();
        }

        return [$range, $from, $to];
    }
}
