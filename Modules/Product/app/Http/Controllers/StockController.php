<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Product\DataTables\StockDataTable;
use Modules\Product\Http\Requests\StoreStockAdjustmentRequest;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockAdjustment;
use Modules\Product\Models\StockMovement;

class StockController extends Controller
{
    public function index(StockDataTable $dataTable): mixed
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        $totalProducts = Product::count();
        $totalStockQty = (float) Batch::sum('quantity');
        $totalStockValue = (float) DB::table('batches')
            ->join('products', 'batches.product_id', '=', 'products.id')
            ->sum(DB::raw('batches.quantity * products.purchase_price'));

        $outOfStockCount = Product::whereRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) <= 0')
            ->count();

        $lowStockCount = Product::where('alert_qty', '>', 0)
            ->whereRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) > 0')
            ->whereRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) <= products.alert_qty')
            ->count();

        $metrics = [
            'totalProducts' => $totalProducts,
            'totalQty' => $totalStockQty,
            'totalValue' => $totalStockValue,
            'lowCount' => $lowStockCount,
            'outCount' => $outOfStockCount,
        ];

        $allProducts = Product::orderBy('name')->get(['id', 'name']);
        $batches = Batch::whereIn('product_id', $allProducts->pluck('id'))
            ->orderByDesc('quantity')
            ->get(['id', 'product_id', 'batch_no', 'quantity']);

        $batchesByProduct = [];
        foreach ($batches as $batch) {
            $batchesByProduct[$batch->product_id][] = [
                'id' => $batch->id,
                'label' => $batch->batch_no.' ('.rtrim(rtrim(number_format((float) $batch->quantity, 2), '0'), '.').')',
                'quantity' => (float) $batch->quantity,
            ];
        }

        return $dataTable->render('product::stock.index', compact(
            'metrics',
            'categories',
            'brands',
            'allProducts',
            'batchesByProduct'
        ));
    }

    public function adjust(StoreStockAdjustmentRequest $request): JsonResponse|RedirectResponse
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
                    throw ValidationException::withMessages(['quantity' => 'পর্যাপ্ত স্টক নেই / Insufficient stock available in this batch']);
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'স্টক সফলভাবে সমন্বয় করা হয়েছে',
                'message_en' => 'Stock adjusted successfully',
            ]);
        }

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
}
