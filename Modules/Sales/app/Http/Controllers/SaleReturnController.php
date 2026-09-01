<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Product\Models\Batch;
use Modules\Product\Models\StockMovement;
use Modules\Sales\Http\Requests\StoreSaleReturnRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\Models\SaleReturn;
use Modules\Sales\Models\SaleReturnItem;

class SaleReturnController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $returns = SaleReturn::with(['sale.customer', 'items.product', 'creator'])
            ->when($search !== '', fn ($q) => $q->whereHas('sale', fn ($s) => $s->where('invoice_no', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales::sale-returns.index', compact('returns', 'search'));
    }

    public function create(Sale $sale): View
    {
        $sale->load(['items.product', 'customer']);

        $returnedByItem = SaleReturnItem::whereIn('sale_item_id', $sale->items->pluck('id'))
            ->selectRaw('sale_item_id, SUM(quantity) as returned')
            ->groupBy('sale_item_id')
            ->pluck('returned', 'sale_item_id');

        return view('sales::sale-returns.create', compact('sale', 'returnedByItem'));
    }

    public function store(Sale $sale, StoreSaleReturnRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $requestedItems = collect($data['items'])->filter(fn ($row) => $row['quantity'] > 0)->values();

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'অন্তত একটি আইটেমের ফেরত পরিমাণ দিতে হবে']);
        }

        DB::transaction(function () use ($sale, $data, $requestedItems) {
            $subtotal = 0;
            $lines = [];

            foreach ($requestedItems as $row) {
                $saleItem = SaleItem::findOrFail($row['sale_item_id']);

                if ($saleItem->sale_id !== $sale->id) {
                    throw ValidationException::withMessages(['items' => 'অবৈধ আইটেম']);
                }

                $alreadyReturned = (float) SaleReturnItem::where('sale_item_id', $saleItem->id)->sum('quantity');

                if ((float) $saleItem->quantity < $row['quantity'] + $alreadyReturned) {
                    throw ValidationException::withMessages(['items' => "'{$saleItem->product->name}' এর অনুমোদিত পরিমাণের বেশি ফেরত দেওয়া যাবে না"]);
                }

                $lineTotal = $row['quantity'] * $saleItem->unit_price;
                $subtotal += $lineTotal;
                $lines[] = ['saleItem' => $saleItem, 'quantity' => $row['quantity'], 'total' => $lineTotal];
            }

            $due = (float) $sale->due_amount;
            $reduceDue = min($due, $subtotal);
            $refund = round($subtotal - $reduceDue, 2);

            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'return_date' => $data['return_date'],
                'subtotal' => $subtotal,
                'refund_amount' => $refund,
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $saleReturn->update(['return_no' => 'RT-SL-'.str_pad((string) $saleReturn->id, 4, '0', STR_PAD_LEFT)]);

            foreach ($lines as $line) {
                $saleItem = $line['saleItem'];

                $saleReturn->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'batch_id' => $saleItem->batch_id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $saleItem->unit_price,
                    'total' => $line['total'],
                ]);

                if ($saleItem->batch_id) {
                    $batch = Batch::where('id', $saleItem->batch_id)->lockForUpdate()->first();

                    if ($batch) {
                        $before = (float) $batch->quantity;
                        $batch->increment('quantity', $line['quantity']);

                        StockMovement::create([
                            'product_id' => $saleItem->product_id,
                            'batch_id' => $batch->id,
                            'type' => 'sale_return',
                            'quantity_change' => $line['quantity'],
                            'quantity_before' => $before,
                            'quantity_after' => (float) $batch->fresh()->quantity,
                            'reference_type' => SaleReturn::class,
                            'reference_id' => $saleReturn->id,
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            $newDue = max($due - $reduceDue, 0);
            $status = $newDue <= 0 ? 'paid' : ($sale->paid_amount <= 0 ? 'due' : 'partial');
            $sale->update(['due_amount' => $newDue, 'payment_status' => $status]);
        });

        return redirect()->route('sales.ledger')->with('status', 'বিক্রয় ফেরত সফলভাবে সম্পন্ন হয়েছে');
    }
}
