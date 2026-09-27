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
use Modules\Core\Support\TenantContext;
use Modules\Product\DataTables\StockTransfersDataTable;
use Modules\Product\Http\Requests\StoreStockTransferRequest;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Product\Models\StockTransfer;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;

class StockTransferController extends Controller
{
    public function index(StockTransfersDataTable $dataTable): mixed
    {
        $warehouses = Warehouse::where('status', 'active')
            ->with('branch')
            ->orderBy('name')
            ->get();

        $products = Product::listedInShop()->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        $batches = Batch::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->where('quantity', '>', 0)
            ->get(['id', 'product_id', 'warehouse_id', 'batch_no', 'quantity']);

        $batchesByWarehouseAndProduct = [];
        foreach ($batches as $batch) {
            $batchesByWarehouseAndProduct[$batch->warehouse_id][$batch->product_id][] = [
                'id' => $batch->id,
                'label' => $batch->batch_no.' ('.rtrim(rtrim(number_format($batch->quantity, 2), '0'), '.').')',
                'quantity' => (float) $batch->quantity,
            ];
        }

        $totalTransfers = StockTransfer::count();
        $pendingCount = StockTransfer::where('status', 'pending')->count();
        $approvedCount = StockTransfer::where('status', 'approved')->count();
        $dispatchedCount = StockTransfer::where('status', 'dispatched')->count();
        $receivedCount = StockTransfer::where('status', 'received')->count();
        $cancelledCount = StockTransfer::where('status', 'cancelled')->count();

        $metrics = [
            'total' => $totalTransfers,
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'dispatched' => $dispatchedCount,
            'received' => $receivedCount,
            'cancelled' => $cancelledCount,
        ];

        $destinationWarehouses = $this->destinationWarehouses();

        return $dataTable->render('product::stock-transfers.index', compact(
            'warehouses',
            'destinationWarehouses',
            'products',
            'batchesByWarehouseAndProduct',
            'metrics'
        ));
    }

    public function show(StockTransfer $transfer, Request $request): View
    {
        $transfer->load([
            'fromWarehouse.branch',
            'toWarehouse.branch',
            'items.product',
            'items.batch',
            'requestedBy',
            'approvedBy',
            'dispatchedBy',
            'receivedBy',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return view('product::stock-transfers._detail_drawer', compact('transfer'));
        }

        return view('product::stock-transfers.show', compact('transfer'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $products = Product::listedInShop()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'sku']);

        $batches = Batch::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->where('quantity', '>', 0)
            ->get(['id', 'product_id', 'warehouse_id', 'batch_no', 'quantity']);

        $batchesByWarehouseAndProduct = [];
        foreach ($batches as $batch) {
            $batchesByWarehouseAndProduct[$batch->warehouse_id][$batch->product_id][] = [
                'id' => $batch->id,
                'label' => $batch->batch_no.' ('.rtrim(rtrim(number_format($batch->quantity, 2), '0'), '.').')',
                'quantity' => (float) $batch->quantity,
            ];
        }

        return view('product::stock-transfers.create', [
            'warehouses' => $warehouses,
            'destinationWarehouses' => $this->destinationWarehouses(),
            'products' => $products,
            'batchesByWarehouseAndProduct' => $batchesByWarehouseAndProduct,
        ]);
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $transfer = DB::transaction(function () use ($data) {
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

            return $transfer;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'স্টক ট্রান্সফারের অনুরোধ তৈরি করা হয়েছে',
                'message_en' => 'Stock transfer request created successfully',
                'transfer' => $transfer,
            ]);
        }

        return redirect()->route('stock-transfers.index')->with('status', 'স্টক ট্রান্সফারের অনুরোধ তৈরি করা হয়েছে');
    }

    public function approve(StockTransfer $transfer, Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSendingShop($transfer);

        if ($transfer->status !== 'pending') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'শুধুমাত্র অপেক্ষমাণ ট্রান্সফার অনুমোদন করা যায়'], 422);
            }

            return back()->with('status', 'শুধুমাত্র অপেক্ষমাণ ট্রান্সফার অনুমোদন করা যায়');
        }

        $transfer->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ট্রান্সফার সফলভাবে অনুমোদন করা হয়েছে',
                'message_en' => 'Transfer approved successfully',
            ]);
        }

        return back()->with('status', 'ট্রান্সফার অনুমোদন করা হয়েছে');
    }

    public function dispatch(StockTransfer $transfer, Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSendingShop($transfer);

        if ($transfer->status !== 'approved') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'শুধুমাত্র অনুমোদিত ট্রান্সফার প্রেরণ করা যায়'], 422);
            }

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
                $item->update(['unit_cost' => $batch->unit_cost]);

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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ট্রান্সফার সফলভাবে প্রেরণ করা হয়েছে',
                'message_en' => 'Transfer dispatched successfully',
            ]);
        }

        return back()->with('status', 'ট্রান্সফার প্রেরণ করা হয়েছে');
    }

    public function receive(StockTransfer $transfer, Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(
            $transfer->isReceivedBy(auth()->user()->shop_id) || auth()->user()->isSuperAdmin(),
            403,
            'শুধুমাত্র গ্রহণকারী দোকান ট্রান্সফার গ্রহণ করতে পারে (Only the receiving shop can receive this transfer)।'
        );

        if ($transfer->status !== 'dispatched') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'শুধুমাত্র প্রেরিত ট্রান্সফার গ্রহণ করা যায়'], 422);
            }

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

                $unitCost = (float) ($item->unit_cost ?? $sourceBatch?->unit_cost ?? 0);

                if ($destBatch) {
                    $destBatch->absorbCost((float) $item->quantity, $unitCost);
                    $destBatch->quantity = (float) $destBatch->quantity + (float) $item->quantity;
                    $destBatch->save();
                } else {
                    $destBatch = Batch::create([
                        'shop_id' => $transfer->to_shop_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $transfer->to_warehouse_id,
                        'batch_no' => $item->batch_no,
                        'quantity' => $item->quantity,
                        'unit_cost' => $unitCost,
                        'mfg_date' => $sourceBatch?->mfg_date,
                        'expiry_date' => $sourceBatch?->expiry_date,
                    ]);
                }

                // The receiving shop now sells the product.
                Product::find($item->product_id)?->listInShop($transfer->to_shop_id);

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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ট্রান্সফার সফলভাবে গ্রহণ করা হয়েছে',
                'message_en' => 'Transfer received successfully',
            ]);
        }

        return back()->with('status', 'ট্রান্সফার গৃহীত হয়েছে');
    }

    public function cancel(StockTransfer $transfer, Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSendingShop($transfer);

        if (! in_array($transfer->status, ['pending', 'approved'], true)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'প্রেরিত বা গৃহীত ট্রান্সফার বাতিল করা যায় না'], 422);
            }

            return back()->with('status', 'প্রেরিত বা গৃহীত ট্রান্সফার বাতিল করা যায় না');
        }

        $transfer->update(['status' => 'cancelled']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ট্রান্সফার বাতিল করা হয়েছে',
                'message_en' => 'Transfer cancelled successfully',
            ]);
        }

        return back()->with('status', 'ট্রান্সফার বাতিল করা হয়েছে');
    }

    /**
     * Warehouses stock can be sent to: the current shop's and those of the
     * company's other shops.
     */
    private function destinationWarehouses()
    {
        $companyShopIds = Shop::where('company_id', app(TenantContext::class)->companyId())->pluck('id');

        return Warehouse::withoutGlobalScope('shop')
            ->whereIn('shop_id', $companyShopIds)
            ->where('status', 'active')
            ->with('shop:id,name')
            ->orderBy('shop_id')
            ->orderBy('name')
            ->get();
    }

    private function ensureSendingShop(StockTransfer $transfer): void
    {
        abort_unless(
            $transfer->isSentBy(auth()->user()->shop_id) || auth()->user()->isSuperAdmin(),
            403,
            'শুধুমাত্র প্রেরণকারী দোকান এই কাজটি করতে পারে (Only the sending shop can do this)।'
        );
    }
}
