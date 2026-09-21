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
use Modules\Sales\Models\SaleReturn;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;

class ReportController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const ROUTES = [
        'report-sales' => 'reports.sales',
        'report-sales-vat' => 'reports.sales-vat',
        'report-purchase' => 'reports.purchase',
        'report-stock' => 'reports.stock',
        'report-products' => 'reports.products',
        'report-product-profit-loss' => 'reports.product-profit-loss',
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

    public function salesVat(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);
        $user = $request->user();
        $shop = $user?->shop;

        $warehouseId = $request->query('warehouse_id');
        $warehouses = $shop ? Warehouse::active()->orderBy('name')->get(['id', 'name', 'branch_id']) : collect();

        // 1. Query eligible Sales within the date range
        $sales = Sale::query()
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->with([
                'customer:id,name,phone',
                'items.product:id,name,is_vat,vat_percentage',
            ])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->get();

        // 2. Query eligible Sales Returns within the date range
        $returns = SaleReturn::query()
            ->when($from, fn ($q) => $q->whereDate('return_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('return_date', '<=', $to))
            ->when($warehouseId, fn ($q) => $q->whereHas('sale', fn ($s) => $s->where('warehouse_id', $warehouseId)))
            ->with([
                'sale.customer:id,name,phone',
                'items.product:id,name,is_vat,vat_percentage',
            ])
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get();

        // 3. Process Sales Output VAT & Sales VAT Details
        $salesDetails = [];
        $grossSales = (float) $sales->sum('total');
        $grossTaxableSales = 0.0;
        $grossOutputVat = 0.0;
        $salesRateTotals = [];

        foreach ($sales as $sale) {
            $saleRateGroups = [];

            foreach ($sale->items as $item) {
                $p = $item->product;
                $isVat = (bool) ($p?->is_vat ?? false);
                $rate = $isVat ? (float) ($p?->vat_percentage ?? 0) : 0.0;
                $lineAmount = (float) $item->total;

                if ($isVat && $rate > 0) {
                    $vatAmount = round($lineAmount * ($rate / 100), 2);
                    $rateKey = rtrim(rtrim(number_format($rate, 2), '0'), '.').'%';

                    if (! isset($saleRateGroups[$rateKey])) {
                        $saleRateGroups[$rateKey] = [
                            'rate' => $rate,
                            'rate_label' => $rateKey,
                            'taxable' => 0.0,
                            'vat' => 0.0,
                        ];
                    }
                    $saleRateGroups[$rateKey]['taxable'] += $lineAmount;
                    $saleRateGroups[$rateKey]['vat'] += $vatAmount;

                    if (! isset($salesRateTotals[$rateKey])) {
                        $salesRateTotals[$rateKey] = ['rate' => $rate, 'rate_label' => $rateKey, 'taxable' => 0.0, 'vat' => 0.0];
                    }
                    $salesRateTotals[$rateKey]['taxable'] += $lineAmount;
                    $salesRateTotals[$rateKey]['vat'] += $vatAmount;

                    $grossTaxableSales += $lineAmount;
                    $grossOutputVat += $vatAmount;
                } else {
                    $rateKey = '0% / Exempt';
                    if (! isset($salesRateTotals[$rateKey])) {
                        $salesRateTotals[$rateKey] = ['rate' => 0.0, 'rate_label' => $rateKey, 'taxable' => 0.0, 'vat' => 0.0];
                    }
                    $salesRateTotals[$rateKey]['taxable'] += $lineAmount;
                }
            }

            if (! empty($saleRateGroups)) {
                foreach ($saleRateGroups as $group) {
                    $salesDetails[] = [
                        'date' => $sale->sale_date,
                        'invoice_no' => $sale->invoice_no,
                        'customer_name' => $sale->customer?->name ?? 'ওয়াক-ইন গ্রাহক / Walk-in',
                        'customer_phone' => $sale->customer?->phone,
                        'taxable_value' => round($group['taxable'], 2),
                        'vat_rate' => $group['rate_label'],
                        'vat_amount' => round($group['vat'], 2),
                    ];
                }
            } elseif ((float) ($sale->tax ?? 0) > 0) {
                $storedTax = (float) $sale->tax;
                $grossOutputVat += $storedTax;
                $salesDetails[] = [
                    'date' => $sale->sale_date,
                    'invoice_no' => $sale->invoice_no,
                    'customer_name' => $sale->customer?->name ?? 'ওয়াক-ইন গ্রাহক / Walk-in',
                    'customer_phone' => $sale->customer?->phone,
                    'taxable_value' => (float) $sale->subtotal,
                    'vat_rate' => 'N/A',
                    'vat_amount' => $storedTax,
                ];
            }
        }

        // 4. Process Sales Return VAT Details
        $returnDetails = [];
        $salesReturnTotal = (float) $returns->sum('subtotal');
        $returnTaxableSales = 0.0;
        $returnOutputVat = 0.0;
        $returnRateTotals = [];

        foreach ($returns as $ret) {
            $retRateGroups = [];

            foreach ($ret->items as $item) {
                $p = $item->product;
                $isVat = (bool) ($p?->is_vat ?? false);
                $rate = $isVat ? (float) ($p?->vat_percentage ?? 0) : 0.0;
                $lineAmount = (float) $item->total;

                if ($isVat && $rate > 0) {
                    $vatAmount = round($lineAmount * ($rate / 100), 2);
                    $rateKey = rtrim(rtrim(number_format($rate, 2), '0'), '.').'%';

                    if (! isset($retRateGroups[$rateKey])) {
                        $retRateGroups[$rateKey] = [
                            'rate' => $rate,
                            'rate_label' => $rateKey,
                            'taxable' => 0.0,
                            'vat' => 0.0,
                        ];
                    }
                    $retRateGroups[$rateKey]['taxable'] += $lineAmount;
                    $retRateGroups[$rateKey]['vat'] += $vatAmount;

                    if (! isset($returnRateTotals[$rateKey])) {
                        $returnRateTotals[$rateKey] = ['rate' => $rate, 'rate_label' => $rateKey, 'taxable' => 0.0, 'vat' => 0.0];
                    }
                    $returnRateTotals[$rateKey]['taxable'] += $lineAmount;
                    $returnRateTotals[$rateKey]['vat'] += $vatAmount;

                    $returnTaxableSales += $lineAmount;
                    $returnOutputVat += $vatAmount;
                } else {
                    $rateKey = '0% / Exempt';
                    if (! isset($returnRateTotals[$rateKey])) {
                        $returnRateTotals[$rateKey] = ['rate' => 0.0, 'rate_label' => $rateKey, 'taxable' => 0.0, 'vat' => 0.0];
                    }
                    $returnRateTotals[$rateKey]['taxable'] += $lineAmount;
                }
            }

            if (! empty($retRateGroups)) {
                foreach ($retRateGroups as $group) {
                    $returnDetails[] = [
                        'date' => $ret->return_date,
                        'return_no' => $ret->return_no,
                        'invoice_no' => $ret->sale?->invoice_no,
                        'customer_name' => $ret->sale?->customer?->name ?? 'ওয়াক-ইন গ্রাহক / Walk-in',
                        'customer_phone' => $ret->sale?->customer?->phone,
                        'taxable_value' => round($group['taxable'], 2),
                        'vat_rate' => $group['rate_label'],
                        'vat_amount' => round($group['vat'], 2),
                    ];
                }
            }
        }

        // 5. Summary Calculations
        $netSales = round($grossSales - $salesReturnTotal, 2);
        $taxableSalesValue = round($grossTaxableSales - $returnTaxableSales, 2);
        $netOutputVat = round($grossOutputVat - $returnOutputVat, 2);

        // 6. VAT Rate-wise Summary Calculation
        $allRateKeys = collect(array_keys($salesRateTotals))
            ->merge(array_keys($returnRateTotals))
            ->unique();

        $taxableRates = $allRateKeys->filter(fn ($k) => $k !== '0% / Exempt')->sort(function ($a, $b) {
            return (float) str_replace('%', '', $b) <=> (float) str_replace('%', '', $a);
        });

        if ($allRateKeys->contains('0% / Exempt')) {
            $sortedRates = $taxableRates->push('0% / Exempt');
        } else {
            $sortedRates = $taxableRates;
        }

        $rateWiseSummary = [];
        $rateWiseTotalTaxable = 0.0;
        $rateWiseTotalVat = 0.0;

        foreach ($sortedRates as $rateKey) {
            $sTaxable = (float) ($salesRateTotals[$rateKey]['taxable'] ?? 0);
            $rTaxable = (float) ($returnRateTotals[$rateKey]['taxable'] ?? 0);
            $netTaxable = round($sTaxable - $rTaxable, 2);

            $sVat = (float) ($salesRateTotals[$rateKey]['vat'] ?? 0);
            $rVat = (float) ($returnRateTotals[$rateKey]['vat'] ?? 0);
            $netVat = round($sVat - $rVat, 2);

            $rateWiseSummary[] = [
                'rate' => $rateKey,
                'gross_taxable' => $sTaxable,
                'return_taxable' => $rTaxable,
                'taxable_sales' => $netTaxable,
                'gross_vat' => $sVat,
                'return_vat' => $rVat,
                'output_vat' => $netVat,
            ];

            if ($rateKey !== '0% / Exempt') {
                $rateWiseTotalTaxable += $netTaxable;
            }
            $rateWiseTotalVat += $netVat;
        }

        // 7. Internal Reconciliation Validation
        $reconciliation = [
            'net_sales_reconciled' => abs(($grossSales - $salesReturnTotal) - $netSales) < 0.01,
            'taxable_sales_reconciled' => abs(($grossTaxableSales - $returnTaxableSales) - $taxableSalesValue) < 0.01
                && abs($rateWiseTotalTaxable - $taxableSalesValue) < 0.01,
            'output_vat_reconciled' => abs(($grossOutputVat - $returnOutputVat) - $netOutputVat) < 0.01
                && abs($rateWiseTotalVat - $netOutputVat) < 0.01,
            'is_valid' => true,
        ];
        $reconciliation['is_valid'] = $reconciliation['net_sales_reconciled']
            && $reconciliation['taxable_sales_reconciled']
            && $reconciliation['output_vat_reconciled'];

        $summary = [
            'gross_sales' => $grossSales,
            'sales_return' => $salesReturnTotal,
            'net_sales' => $netSales,
            'gross_taxable_sales' => $grossTaxableSales,
            'return_taxable_sales' => $returnTaxableSales,
            'taxable_sales_value' => $taxableSalesValue,
            'gross_output_vat' => $grossOutputVat,
            'return_output_vat' => $returnOutputVat,
            'output_vat' => $netOutputVat,
        ];

        return view('report::sales-vat', compact(
            'range', 'from', 'to', 'warehouseId', 'warehouses',
            'summary', 'salesDetails', 'returnDetails', 'rateWiseSummary',
            'rateWiseTotalTaxable', 'rateWiseTotalVat', 'reconciliation'
        ));
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

    public function productProfitLoss(Request $request): View
    {
        [$range, $from, $to] = $this->resolveDateRange($request);
        $user = $request->user();
        $shop = $user?->shop;

        $warehouseId = $request->query('warehouse_id');
        $search = trim((string) $request->query('search', ''));
        $warehouses = $shop ? Warehouse::active()->orderBy('name')->get(['id', 'name', 'branch_id']) : collect();

        $saleItems = SaleItem::query()
            ->whereHas('sale', function ($q) use ($from, $to, $warehouseId) {
                $q->when($from, fn ($qq) => $qq->whereDate('sale_date', '>=', $from))
                    ->when($to, fn ($qq) => $qq->whereDate('sale_date', '<=', $to))
                    ->when($warehouseId, fn ($qq) => $qq->where('warehouse_id', $warehouseId));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->with([
                'product:id,name,sku,purchase_price,sale_price',
                'batch:id,batch_no,mfg_date,expiry_date',
                'unit',
            ])
            ->get();

        $grouped = [];

        foreach ($saleItems as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $productId = $product->id;
            $batchId = $item->batch_id ?? 0;
            $batchNo = $item->batch?->batch_no ?? ($item->batch_id ? "BATCH-{$item->batch_id}" : 'ডিফল্ট / Default');
            $isDefaultBatch = empty($item->batch_id);

            $conversionFactor = $item->unitConversionFactor();
            $baseQty = (float) $item->quantity * $conversionFactor;
            $purchasePriceUnit = (float) ($product->purchase_price ?? 0);
            $purchaseCost = round($baseQty * $purchasePriceUnit, 2);
            $saleRevenue = (float) $item->total;
            $profit = round($saleRevenue - $purchaseCost, 2);

            if (! isset($grouped[$productId])) {
                $grouped[$productId] = [
                    'id' => $productId,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'purchase_price_unit' => $purchasePriceUnit,
                    'batches' => [],
                    'total_qty' => 0.0,
                    'total_purchase_cost' => 0.0,
                    'total_sale_revenue' => 0.0,
                    'total_profit' => 0.0,
                ];
            }

            if (! isset($grouped[$productId]['batches'][$batchId])) {
                $grouped[$productId]['batches'][$batchId] = [
                    'batch_id' => $batchId,
                    'batch_no' => $batchNo,
                    'is_default' => $isDefaultBatch,
                    'qty' => 0.0,
                    'purchase_cost' => 0.0,
                    'sale_revenue' => 0.0,
                    'profit' => 0.0,
                ];
            }

            $grouped[$productId]['batches'][$batchId]['qty'] += $baseQty;
            $grouped[$productId]['batches'][$batchId]['purchase_cost'] += $purchaseCost;
            $grouped[$productId]['batches'][$batchId]['sale_revenue'] += $saleRevenue;
            $grouped[$productId]['batches'][$batchId]['profit'] += $profit;

            $grouped[$productId]['total_qty'] += $baseQty;
            $grouped[$productId]['total_purchase_cost'] += $purchaseCost;
            $grouped[$productId]['total_sale_revenue'] += $saleRevenue;
            $grouped[$productId]['total_profit'] += $profit;
        }

        $reportData = collect($grouped)->sortBy('name')->values()->map(function ($prod) {
            $prod['batches'] = array_values($prod['batches']);

            return $prod;
        });

        $totals = [
            'qty' => (float) $reportData->sum('total_qty'),
            'purchase_cost' => (float) $reportData->sum('total_purchase_cost'),
            'sale_revenue' => (float) $reportData->sum('total_sale_revenue'),
            'profit' => (float) $reportData->sum('total_profit'),
            'products_count' => $reportData->count(),
            'batches_count' => $reportData->sum(fn ($p) => count($p['batches'])),
        ];

        return view('report::product-profit-loss', compact(
            'range', 'from', 'to', 'warehouseId', 'warehouses', 'search',
            'reportData', 'totals'
        ));
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
