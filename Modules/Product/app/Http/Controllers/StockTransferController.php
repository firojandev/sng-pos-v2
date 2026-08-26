<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreStockTransferRequest;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Product\Models\StockTransfer;
use Modules\Shop\Models\Warehouse;

class StockTransferController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])
            ->when(array_key_exists($status, StockTransfer::statusLabels()), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('product::stock-transfers.index', [
            'transfers' => $transfers,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name', 'sku']);

        $batches = Batch::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->where('quantity', '>', 0)
            ->get(['id', 'product_id', 'warehouse_id', 'batch_no', 'quantity']);

        $batchesByWarehouseAndProduct = [];
        foreach ($batches as $batch) {
            $batchesByWarehouseAndProduct[$batch->warehouse_id][$batch->product_id][] = [
                'id' => $batch->id,
                'label' => $batch->batch_no.' ('.rtrim(rtrim(number_format($batch->quantity, 2), '0'), '.').')',
            ];
        }

        return view('product::stock-transfers.create', [
            'warehouses' => $warehouses,
            'products' => $products,
            'batchesByWarehouseAndProduct' => $batchesByWarehouseAndProduct,
        ]);
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $transfer = StockTransfer::create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'status' => 'pending',
                'requested_by' => Auth::id(),
                'note' => $data['note'] ?? null,
            ]);

            $transfer->update(['transfer_no' => 'TR-'.str_pad((string) $transfer->id, 4, '0', STR_PAD_LEFT)]);

            foreach ($data['items'] as $item) {
                $batch = Batch::findOrFail($item['batch_id']);

                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('stock-transfers.index')->with('status', 'স্টক ট্রান্সফারের অনুরোধ তৈরি করা হয়েছে');
    }

    public function approve(StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status !== 'pending') {
            return back()->with('status', 'শুধুমাত্র অপেক্ষমাণ ট্রান্সফার অনুমোদন করা যায়');
        }

        $transfer->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('status', 'ট্রান্সফার অনুমোদন করা হয়েছে');
    }

    public function dispatch(StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status !== 'approved') {
            return back()->with('status', 'শুধুমাত্র অনুমোদিত ট্রান্সফার প্রেরণ করা যায়');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                $batch = Batch::where('id', $item->batch_id)->lockForUpdate()->firstOrFail();

                if ($batch->quantity < $item->quantity) {
                    throw ValidationException::withMessages(['items' => "'{$item->batch_no}' ব্যাচে পর্যাপ্ত স্টক নেই"]);
                }

                $before = (float) $batch->quantity;
                $batch->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'transfer_out',
                    'quantity_change' => -$item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => (float) $batch->fresh()->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'created_by' => Auth::id(),
                ]);
            }

            $transfer->update(['status' => 'dispatched', 'dispatched_by' => Auth::id(), 'dispatched_at' => now()]);
        });

        return back()->with('status', 'ট্রান্সফার প্রেরণ করা হয়েছে');
    }

    public function receive(StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status !== 'dispatched') {
            return back()->with('status', 'শুধুমাত্র প্রেরিত ট্রান্সফার গ্রহণ করা যায়');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                $sourceBatch = $item->batch;

                $destBatch = Batch::where('product_id', $item->product_id)
                    ->where('batch_no', $item->batch_no)
                    ->where('warehouse_id', $transfer->to_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                $before = $destBatch ? (float) $destBatch->quantity : 0.0;

                if ($destBatch) {
                    $destBatch->increment('quantity', $item->quantity);
                } else {
                    $destBatch = Batch::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $transfer->to_warehouse_id,
                        'batch_no' => $item->batch_no,
                        'quantity' => $item->quantity,
                        'mfg_date' => $sourceBatch?->mfg_date,
                        'expiry_date' => $sourceBatch?->expiry_date,
                    ]);
                }

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'batch_id' => $destBatch->id,
                    'type' => 'transfer_in',
                    'quantity_change' => $item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => (float) $destBatch->fresh()->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'created_by' => Auth::id(),
                ]);
            }

            $transfer->update(['status' => 'received', 'received_by' => Auth::id(), 'received_at' => now()]);
        });

        return back()->with('status', 'ট্রান্সফার গৃহীত হয়েছে');
    }

    public function cancel(StockTransfer $transfer): RedirectResponse
    {
        if (! in_array($transfer->status, ['pending', 'approved'], true)) {
            return back()->with('status', 'প্রেরিত বা গৃহীত ট্রান্সফার বাতিল করা যায় না');
        }

        $transfer->update(['status' => 'cancelled']);

        return back()->with('status', 'ট্রান্সফার বাতিল করা হয়েছে');
    }
}
