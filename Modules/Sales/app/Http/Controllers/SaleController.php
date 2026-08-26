<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Sales\Http\Requests\StoreSaleRequest;
use Modules\Sales\Http\Requests\UpdateSaleRequest;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Warehouse;

class SaleController extends Controller
{
    public function index(): View
    {
        $sales = Sale::with('customer')->latest('sale_date')->paginate(10);

        return view('sales::sales.index', compact('sales'));
    }

    public function ledger(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());

        $query = Sale::with(['customer', 'items.product', 'items.batch', 'payments'])
            ->whereDate('sale_date', '>=', $from)
            ->whereDate('sale_date', '<=', $to);

        if ($search !== '') {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['paid', 'partial', 'due'], true)) {
            $query->where('payment_status', $status);
        }

        $totalAmount = (clone $query)->sum('total');

        $sales = $query->latest('sale_date')->paginate(10)->withQueryString();

        return view('sales::sales.ledger', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'search' => $search,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(Request $request): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $warehouseId = $request->query('warehouse_id', optional($warehouses->first())->id);
        $products = Product::where('status', 'active')
            ->with(['batches' => fn ($q) => $q->where('warehouse_id', $warehouseId)])
            ->orderBy('name')->get();

        return view('sales::sales.create', [
            'sale' => new Sale,
            'customers' => $customers,
            'products' => $products,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items) {
            [$subtotal, $discount, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);

            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'sale_date' => $data['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
            ]);

            $sale->update(['invoice_no' => 'SL-'.str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT)]);

            $this->applyItems($sale, $items);
            $this->applyPayments($sale, $data['payments'] ?? []);
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Sale $sale): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')
            ->with(['batches' => fn ($q) => $q->where('warehouse_id', $sale->warehouse_id)])
            ->orderBy('name')->get();
        $sale->load('items', 'warehouse', 'payments');

        // Add each existing item's reserved quantity back so the batch dropdown
        // shows the quantity as if this sale hadn't consumed it yet.
        foreach ($sale->items as $item) {
            $product = $products->firstWhere('id', $item->product_id);
            $batch = $product?->batches->firstWhere('id', $item->batch_id);
            if ($batch) {
                $batch->quantity += $item->quantity;
            }
        }

        return view('sales::sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items, $sale) {
            $this->revertItems($sale);

            [$subtotal, $discount, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);

            $sale->update([
                'customer_id' => $data['customer_id'] ?? null,
                'sale_date' => $data['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
            ]);

            $this->applyItems($sale, $items);

            $sale->payments()->delete();
            $this->applyPayments($sale, $data['payments'] ?? []);
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($sale) {
            $this->revertItems($sale);
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় বাতিল করা হয়েছে');
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}
     */
    private function calculateTotals(array $items, array $data): array
    {
        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $discount = $data['discount'] ?? 0;
        $total = max($subtotal - $discount, 0);
        $paid = collect($data['payments'] ?? [])->sum('amount');
        $due = max($total - $paid, 0);
        $status = $due <= 0 ? 'paid' : ($paid <= 0 ? 'due' : 'partial');

        return [$subtotal, $discount, $total, $paid, $due, $status];
    }

    /**
     * @param  array<int, array{method: string, amount: float}>  $payments
     */
    private function applyPayments(Sale $sale, array $payments): void
    {
        foreach ($payments as $payment) {
            $sale->payments()->create([
                'method' => $payment['method'],
                'amount' => $payment['amount'],
            ]);
        }
    }

    private function applyItems(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            $batch = Batch::where('id', $item['batch_id'])
                ->where('product_id', $item['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $batch || $batch->quantity < $item['quantity']) {
                throw ValidationException::withMessages(['items' => 'নির্বাচিত ব্যাচে পর্যাপ্ত স্টক নেই']);
            }

            $before = (float) $batch->quantity;
            $batch->decrement('quantity', $item['quantity']);

            $sale->items()->create([
                'product_id' => $item['product_id'],
                'batch_id' => $item['batch_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);

            StockMovement::create([
                'product_id' => $item['product_id'],
                'batch_id' => $batch->id,
                'type' => 'sale',
                'quantity_change' => -$item['quantity'],
                'quantity_before' => $before,
                'quantity_after' => (float) $batch->fresh()->quantity,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'created_by' => Auth::id(),
            ]);
        }
    }

    private function revertItems(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            if ($item->batch_id) {
                $batch = Batch::where('id', $item->batch_id)->lockForUpdate()->first();
                if ($batch) {
                    $before = (float) $batch->quantity;
                    $batch->increment('quantity', $item->quantity);

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'batch_id' => $batch->id,
                        'type' => 'sale_reversal',
                        'quantity_change' => $item->quantity,
                        'quantity_before' => $before,
                        'quantity_after' => (float) $batch->fresh()->quantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        }

        $sale->items()->delete();
    }
}
