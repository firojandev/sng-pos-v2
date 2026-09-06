<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Product\Models\Batch;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\Http\Requests\StorePurchaseReturnRequest;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchaseItem;
use Modules\Purchase\Models\PurchaseReturn;
use Modules\Purchase\Models\PurchaseReturnItem;

class PurchaseReturnController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $returns = PurchaseReturn::with(['purchase.supplier', 'items.product', 'creator'])
            ->when($search !== '', fn ($q) => $q->whereHas('purchase', fn ($s) => $s->where('invoice_no', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase::purchase-returns.index', compact('returns', 'search'));
    }

    public function create(Purchase $purchase): View
    {
        $purchase->load(['items.product', 'supplier']);

        $returnedByItem = PurchaseReturnItem::whereIn('purchase_item_id', $purchase->items->pluck('id'))
            ->selectRaw('purchase_item_id, SUM(quantity) as returned')
            ->groupBy('purchase_item_id')
            ->pluck('returned', 'purchase_item_id');

        return view('purchase::purchase-returns.create', compact('purchase', 'returnedByItem'));
    }

    public function store(Purchase $purchase, StorePurchaseReturnRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $requestedItems = collect($data['items'])->filter(fn ($row) => $row['quantity'] > 0)->values();

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'অন্তত একটি আইটেমের ফেরত পরিমাণ দিতে হবে']);
        }

        DB::transaction(function () use ($purchase, $data, $requestedItems) {
            $subtotal = 0;
            $lines = [];

            foreach ($requestedItems as $row) {
                $purchaseItem = PurchaseItem::findOrFail($row['purchase_item_id']);

                if ($purchaseItem->purchase_id !== $purchase->id) {
                    throw ValidationException::withMessages(['items' => 'অবৈধ আইটেম']);
                }

                $alreadyReturned = (float) PurchaseReturnItem::where('purchase_item_id', $purchaseItem->id)->sum('quantity');

                if ((float) $purchaseItem->quantity < $row['quantity'] + $alreadyReturned) {
                    throw ValidationException::withMessages(['items' => "'{$purchaseItem->product->name}' এর অনুমোদিত পরিমাণের বেশি ফেরত দেওয়া যাবে না"]);
                }

                $lineTotal = $row['quantity'] * $purchaseItem->purchase_price;
                $subtotal += $lineTotal;
                $lines[] = ['purchaseItem' => $purchaseItem, 'quantity' => $row['quantity'], 'total' => $lineTotal];
            }

            $due = (float) $purchase->due_amount;
            $reduceDue = min($due, $subtotal);
            $refund = round($subtotal - $reduceDue, 2);

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_date' => $data['return_date'],
                'subtotal' => $subtotal,
                'refund_amount' => $refund,
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $purchaseReturn->update(['return_no' => 'RT-PU-'.str_pad((string) $purchaseReturn->id, 4, '0', STR_PAD_LEFT)]);

            foreach ($lines as $line) {
                $purchaseItem = $line['purchaseItem'];

                $purchaseReturn->items()->create([
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $purchaseItem->product_id,
                    'batch_id' => $purchaseItem->batch_id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $purchaseItem->purchase_price,
                    'total' => $line['total'],
                ]);

                if ($purchaseItem->batch_id) {
                    $batch = Batch::where('id', $purchaseItem->batch_id)->lockForUpdate()->first();

                    if ($batch) {
                        $factor = $purchaseItem->unitConversionFactor();
                        $baseReturnQty = (float) $line['quantity'] * $factor;
                        $before = (float) $batch->quantity;
                        $reverted = min($baseReturnQty, $before);
                        $batch->quantity = max($batch->quantity - $reverted, 0);
                        $batch->save();

                        StockMovement::create([
                            'product_id' => $purchaseItem->product_id,
                            'batch_id' => $batch->id,
                            'type' => 'purchase_return',
                            'quantity_change' => -$reverted,
                            'quantity_before' => $before,
                            'quantity_after' => (float) $batch->quantity,
                            'reference_type' => PurchaseReturn::class,
                            'reference_id' => $purchaseReturn->id,
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            $newDue = max($due - $reduceDue, 0);
            $status = $newDue <= 0 ? 'paid' : ($purchase->paid_amount <= 0 ? 'due' : 'partial');
            $purchase->update(['due_amount' => $newDue, 'payment_status' => $status]);
        });

        return redirect()->route('purchase.ledger')->with('status', 'ক্রয় ফেরত সফলভাবে সম্পন্ন হয়েছে');
    }
}
