<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Customer\Services\LoyaltyService;
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
            ->when($search !== '', function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($s) use ($search) {
                        $s->where('invoice_no', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($c) use ($search) {
                                $c->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalReturnsCount = SaleReturn::count();
        $totalRefundAmount = (float) SaleReturn::sum('refund_amount');
        $totalReturnSubtotal = (float) (SaleReturn::where('total', '>', 0)->sum('total') ?: SaleReturn::sum('subtotal'));
        $totalReturnDiscount = (float) (SaleReturn::sum('product_discount') + SaleReturn::sum('discount'));

        return view('sales::sale-returns.index', compact('returns', 'search', 'totalReturnsCount', 'totalRefundAmount', 'totalReturnSubtotal', 'totalReturnDiscount'));
    }

    public function create(Sale $sale): View
    {
        $sale->load(['items.product.units', 'items.unit', 'customer']);

        $returnedByItem = SaleReturnItem::whereIn('sale_item_id', $sale->items->pluck('id'))
            ->selectRaw('sale_item_id, SUM(quantity) as returned, SUM(discount) as returned_discount')
            ->groupBy('sale_item_id')
            ->get()
            ->keyBy('sale_item_id');

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
            $grossSubtotal = 0;
            $returnedProductDiscountTotal = 0;
            $returnedLinesSubtotal = 0;
            $lines = [];
            $returnedCostTotal = 0;

            foreach ($requestedItems as $row) {
                $saleItem = SaleItem::findOrFail($row['sale_item_id']);

                if ($saleItem->sale_id !== $sale->id) {
                    throw ValidationException::withMessages(['items' => 'অবৈধ আইটেম']);
                }

                $alreadyReturned = (float) SaleReturnItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                $alreadyReturnedDiscount = (float) SaleReturnItem::where('sale_item_id', $saleItem->id)->sum('discount');

                if ((float) $saleItem->quantity < $row['quantity'] + $alreadyReturned) {
                    throw ValidationException::withMessages(['items' => "'{$saleItem->product->name}' এর অনুমোদিত পরিমাণের বেশি ফেরত দেওয়া যাবে না"]);
                }

                $qty = (float) $row['quantity'];
                $soldQty = (float) $saleItem->quantity;
                $unitPrice = (float) $saleItem->unit_price;
                $lineGross = round($qty * $unitPrice, 2);

                $remainingQty = max(0, $soldQty - $alreadyReturned);
                $remainingDiscount = max(0, (float) $saleItem->discount - $alreadyReturnedDiscount);

                if ($qty >= $remainingQty) {
                    $lineDiscount = round($remainingDiscount, 2);
                } else {
                    $unitDiscount = $soldQty > 0 ? (float) $saleItem->discount / $soldQty : 0;
                    $lineDiscount = round($qty * $unitDiscount, 2);
                }

                $lineNet = round(max(0, $lineGross - $lineDiscount), 2);

                $grossSubtotal += $lineGross;
                $returnedProductDiscountTotal += $lineDiscount;
                $returnedLinesSubtotal += $lineNet;

                $soldCost = (float) ($saleItem->cost_total ?? 0);
                if ($soldCost <= 0 && $saleItem->product) {
                    $soldCost = (float) ($saleItem->product->purchase_price ?? 0) * $soldQty;
                }
                $unitCost = $soldQty > 0 ? $soldCost / $soldQty : 0;
                $lineCost = round($qty * $unitCost, 2);
                $returnedCostTotal += $lineCost;

                $lines[] = [
                    'saleItem' => $saleItem,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $lineDiscount,
                    'total' => $lineNet,
                    'cost' => $lineCost,
                ];
            }

            // Determine if all remaining returnable items of the invoice are returned
            $totalRemainingReturnable = 0;
            foreach ($sale->items as $it) {
                $retQty = (float) SaleReturnItem::where('sale_item_id', $it->id)->sum('quantity');
                $totalRemainingReturnable += max(0, (float) $it->quantity - $retQty);
            }
            $currentReturnQty = (float) collect($lines)->sum('quantity');
            $isFullRemainingReturn = $currentReturnQty >= round($totalRemainingReturnable, 4);

            // Prorate invoice-level discount and adjustment
            $remainingSaleSubtotal = (float) $sale->subtotal;
            $invoiceDiscount = (float) ($sale->discount ?? 0);
            $adjustment = (float) ($sale->adjustment ?? 0);

            if ($isFullRemainingReturn) {
                $returnedInvoiceDiscount = $invoiceDiscount;
                $returnedAdjustment = $adjustment;
            } elseif ($remainingSaleSubtotal > 0) {
                $subtotalRatio = min(1, $returnedLinesSubtotal / $remainingSaleSubtotal);
                $returnedInvoiceDiscount = round($invoiceDiscount * $subtotalRatio, 2);
                $returnedAdjustment = round($adjustment * $subtotalRatio, 2);
            } else {
                $returnedInvoiceDiscount = 0.0;
                $returnedAdjustment = 0.0;
            }

            // Net refundable amount for this return
            $netTotal = round(max(0, $returnedLinesSubtotal - $returnedInvoiceDiscount + $returnedAdjustment), 2);
            $totalReturnedDiscount = round($returnedProductDiscountTotal + $returnedInvoiceDiscount, 2);

            // Profit adjustment
            $grossProfitReturned = $returnedLinesSubtotal - $returnedCostTotal;
            $returnedProfit = round($grossProfitReturned - $returnedInvoiceDiscount, 2);

            $due = (float) $sale->due_amount;
            $reduceDue = min($due, $netTotal);
            $refund = round($netTotal - $reduceDue, 2);

            $saleReturn = SaleReturn::create([
                'shop_id' => $sale->shop_id,
                'sale_id' => $sale->id,
                'return_date' => $data['return_date'],
                'subtotal' => $grossSubtotal,
                'product_discount' => $returnedProductDiscountTotal,
                'discount' => $returnedInvoiceDiscount,
                'adjustment' => $returnedAdjustment,
                'total' => $netTotal,
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
                    'unit_id' => $saleItem->unit_id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount' => $line['discount'],
                    'total' => $line['total'],
                    'cost_total' => $line['cost'],
                ]);

                if ($saleItem->batch_id) {
                    $batch = Batch::where('id', $saleItem->batch_id)->lockForUpdate()->first();

                    if ($batch) {
                        $factor = $saleItem->unitConversionFactor();
                        $baseReturnQty = (float) $line['quantity'] * $factor;
                        $before = (float) $batch->quantity;
                        $batch->increment('quantity', $baseReturnQty);

                        StockMovement::create([
                            'product_id' => $saleItem->product_id,
                            'batch_id' => $batch->id,
                            'type' => 'sale_return',
                            'quantity_change' => $baseReturnQty,
                            'quantity_before' => $before,
                            'quantity_after' => (float) $batch->fresh()->quantity,
                            'reference_type' => SaleReturn::class,
                            'reference_id' => $saleReturn->id,
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            $newDue = max(0, round($due - $reduceDue, 2));
            $newPaid = max(0, round((float) $sale->paid_amount - $refund, 2));
            $newSubtotal = max(0, round((float) $sale->subtotal - $returnedLinesSubtotal, 2));
            $newProductDiscount = max(0, round((float) ($sale->product_discount ?? 0) - $returnedProductDiscountTotal, 2));
            $newDiscount = max(0, round((float) ($sale->discount ?? 0) - $returnedInvoiceDiscount, 2));
            $newAdjustment = round((float) ($sale->adjustment ?? 0) - $returnedAdjustment, 2);
            $newTotal = max(0, round((float) $sale->total - $netTotal, 2));
            $newProfit = $sale->profit !== null ? round((float) $sale->profit - $returnedProfit, 2) : null;

            if ($isFullRemainingReturn || $newSubtotal <= 0 || $newTotal <= 0) {
                $newSubtotal = 0;
                $newTotal = 0;
                $newPaid = 0;
                $newDue = 0;
                $newProductDiscount = 0;
                $newDiscount = 0;
                $newAdjustment = 0;
                if ($sale->profit !== null) {
                    $newProfit = 0.0;
                }
            }

            $status = $newDue <= 0 ? 'paid' : ($newPaid <= 0 ? 'due' : 'partial');

            $sale->update([
                'subtotal' => $newSubtotal,
                'product_discount' => $newProductDiscount,
                'discount' => $newDiscount,
                'adjustment' => $newAdjustment,
                'total' => $newTotal,
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'profit' => $newProfit,
                'payment_status' => $status,
            ]);
            app(LoyaltyService::class)->syncSale($sale);
        });

        return redirect()->route('sales.ledger')->with('status', 'বিক্রয় ফেরত সফলভাবে সম্পন্ন হয়েছে');
    }
}
