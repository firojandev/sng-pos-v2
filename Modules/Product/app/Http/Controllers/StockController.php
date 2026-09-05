<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreStockAdjustmentRequest;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockAdjustment;
use Modules\Product\Models\StockMovement;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'newest');
        $filter = $request->query('filter', 'all');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 10;

        $products = Product::withSum('batches', 'quantity')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
            ->get()
            ->map(function (Product $product) {
                $product->stock_qty = (float) ($product->batches_sum_quantity ?? 0);
                $product->stock_value = round($product->stock_qty * (float) $product->purchase_price, 2);

                return $product;
            });

        $allCount = $products->count();
        $lowCount = $products->filter($this->isLowStock(...))->count();
        $outCount = $products->filter(fn (Product $p) => $p->stock_qty <= 0)->count();

        $filtered = match ($filter) {
            'low' => $products->filter($this->isLowStock(...)),
            'out' => $products->filter(fn (Product $p) => $p->stock_qty <= 0),
            default => $products,
        };

        $sorted = (match ($sort) {
            'oldest' => $filtered->sortBy('created_at'),
            'qty_desc' => $filtered->sortByDesc('stock_qty'),
            'qty_asc' => $filtered->sortBy('stock_qty'),
            default => $filtered->sortByDesc('created_at'),
        })->values();

        $paginated = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $allProducts = Product::orderBy('name')->get(['id', 'name']);
        $batches = Batch::whereIn('product_id', $allProducts->pluck('id'))->orderByDesc('quantity')->get(['id', 'product_id', 'batch_no', 'quantity']);

        $batchesByProduct = [];
        foreach ($batches as $batch) {
            $batchesByProduct[$batch->product_id][] = [
                'id' => $batch->id,
                'label' => $batch->batch_no.' ('.rtrim(rtrim(number_format($batch->quantity, 2), '0'), '.').')',
            ];
        }

        return view('product::stock.index', [
            'products' => $paginated,
            'totalQty' => $sorted->sum('stock_qty'),
            'totalValue' => $sorted->sum('stock_value'),
            'allCount' => $allCount,
            'lowCount' => $lowCount,
            'outCount' => $outCount,
            'search' => $search,
            'sort' => $sort,
            'filter' => $filter,
            'allProducts' => $allProducts,
            'batchesByProduct' => $batchesByProduct,
        ]);
    }

    public function adjust(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $batch = Batch::where('id', $data['batch_id'])->lockForUpdate()->firstOrFail();
            $before = (float) $batch->quantity;
            $quantity = (float) $data['quantity'];

            if ($data['type'] === 'increase') {
                $after = $before + $quantity;
            } else {
                if ($before < $quantity) {
                    throw ValidationException::withMessages(['quantity' => 'পর্যাপ্ত স্টক নেই']);
                }
                $after = $before - $quantity;
            }

            $batch->update(['quantity' => $after]);

            $adjustment = StockAdjustment::create([
                'product_id' => $data['product_id'],
                'batch_id' => $batch->id,
                'type' => $data['type'],
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $data['reason'] ?? null,
                'created_by' => Auth::id(),
            ]);

            StockMovement::create([
                'product_id' => $data['product_id'],
                'batch_id' => $batch->id,
                'type' => $data['type'] === 'increase' ? 'adjustment_increase' : 'adjustment_decrease',
                'quantity_change' => $data['type'] === 'increase' ? $quantity : -$quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'note' => $data['reason'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('stock.index')->with('status', 'স্টক সফলভাবে সমন্বয় করা হয়েছে');
    }

    public function history(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $productId = $request->query('product_id');
        $type = $request->query('type', 'all');

        $product = $productId ? Product::find($productId) : null;

        $movements = StockMovement::with(['product', 'batch', 'creator', 'reference'])
            ->when($product, fn ($q) => $q->where('product_id', $product->id))
            ->when($search !== '', fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")))
            ->when(in_array($type, ['in', 'out'], true), fn ($q) => $q->where('quantity_change', $type === 'in' ? '>' : '<', 0))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('product::stock.history', compact('movements', 'search', 'type', 'product'));
    }

    private function isLowStock(Product $product): bool
    {
        return $product->alert_qty > 0 && $product->stock_qty > 0 && $product->stock_qty <= $product->alert_qty;
    }
}
