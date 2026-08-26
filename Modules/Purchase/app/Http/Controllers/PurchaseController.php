<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Purchase\Http\Requests\StorePurchaseRequest;
use Modules\Purchase\Http\Requests\UpdatePurchaseRequest;
use Modules\Purchase\Models\Purchase;
use Modules\Supplier\Models\Supplier;

class PurchaseController extends Controller
{
    public function index(): View
    {
        $purchases = Purchase::with('supplier')->latest('purchase_date')->paginate(10);

        return view('purchase::purchase.index', compact('purchases'));
    }

    public function ledger(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());

        $query = Purchase::with(['supplier', 'items.product'])
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to);

        if ($search !== '') {
            $query->whereHas('supplier', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['paid', 'partial', 'due'], true)) {
            $query->where('payment_status', $status);
        }

        $totalAmount = (clone $query)->sum('total');

        $purchases = $query->latest('purchase_date')->paginate(10)->withQueryString();

        return view('purchase::purchase.ledger', [
            'purchases' => $purchases,
            'totalAmount' => $totalAmount,
            'search' => $search,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('purchase::purchase.create', [
            'purchase' => new Purchase,
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items) {
            [$subtotal, $discount, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
            ]);

            $purchase->update(['invoice_no' => 'PU-'.str_pad((string) $purchase->id, 4, '0', STR_PAD_LEFT)]);

            $this->applyItems($purchase, $items);
        });

        return redirect()->route('purchase.index')->with('status', 'ক্রয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Purchase $purchase): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $purchase->load('items');

        return view('purchase::purchase.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items, $purchase) {
            $this->revertItems($purchase);

            [$subtotal, $discount, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);

            $purchase->update([
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
            ]);

            $this->applyItems($purchase, $items);
        });

        return redirect()->route('purchase.index')->with('status', 'ক্রয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($purchase) {
            $this->revertItems($purchase);
            $purchase->delete();
        });

        return redirect()->route('purchase.index')->with('status', 'ক্রয় মুছে ফেলা হয়েছে');
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}
     */
    private function calculateTotals(array $items, array $data): array
    {
        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['purchase_price']);
        $discount = $data['discount'] ?? 0;
        $total = max($subtotal - $discount, 0);
        $paid = $data['paid_amount'] ?? 0;
        $due = max($total - $paid, 0);
        $status = $due <= 0 ? 'paid' : ($paid <= 0 ? 'due' : 'partial');

        return [$subtotal, $discount, $total, $paid, $due, $status];
    }

    private function applyItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $batch = Batch::where('product_id', $item['product_id'])
                ->where('batch_no', $item['batch_no'])
                ->lockForUpdate()
                ->first();

            if ($batch) {
                $batch->quantity += $item['quantity'];
                if (! empty($item['mfg_date'])) {
                    $batch->mfg_date = $item['mfg_date'];
                }
                if (! empty($item['expiry_date'])) {
                    $batch->expiry_date = $item['expiry_date'];
                }
                $batch->save();
            } else {
                $batch = Batch::create([
                    'product_id' => $item['product_id'],
                    'batch_no' => $item['batch_no'],
                    'quantity' => $item['quantity'],
                    'mfg_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'batch_id' => $batch->id,
                'batch_no' => $item['batch_no'],
                'mfg_date' => $item['mfg_date'] ?? null,
                'expiry_date' => $item['expiry_date'] ?? null,
                'quantity' => $item['quantity'],
                'purchase_price' => $item['purchase_price'],
                'total' => $item['quantity'] * $item['purchase_price'],
            ]);
        }
    }

    private function revertItems(Purchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            if ($item->batch_id) {
                $batch = Batch::find($item->batch_id);
                if ($batch) {
                    $batch->quantity = max($batch->quantity - $item->quantity, 0);
                    $batch->save();
                }
            }
        }

        $purchase->items()->delete();
    }
}
