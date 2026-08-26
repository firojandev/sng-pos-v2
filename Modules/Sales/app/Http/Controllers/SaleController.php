<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Sales\Http\Requests\StoreSaleRequest;
use Modules\Sales\Http\Requests\UpdateSaleRequest;
use Modules\Sales\Models\Sale;

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

        $query = Sale::with(['customer', 'items.product', 'items.batch'])
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

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->with('batches')->orderBy('name')->get();

        return view('sales::sales.create', [
            'sale' => new Sale,
            'customers' => $customers,
            'products' => $products,
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
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Sale $sale): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->with('batches')->orderBy('name')->get();
        $sale->load('items');

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
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($sale) {
            $this->revertItems($sale);
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় মুছে ফেলা হয়েছে');
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}
     */
    private function calculateTotals(array $items, array $data): array
    {
        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $discount = $data['discount'] ?? 0;
        $total = max($subtotal - $discount, 0);
        $paid = $data['paid_amount'] ?? 0;
        $due = max($total - $paid, 0);
        $status = $due <= 0 ? 'paid' : ($paid <= 0 ? 'due' : 'partial');

        return [$subtotal, $discount, $total, $paid, $due, $status];
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

            $batch->decrement('quantity', $item['quantity']);

            $sale->items()->create([
                'product_id' => $item['product_id'],
                'batch_id' => $item['batch_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }
    }

    private function revertItems(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            if ($item->batch_id) {
                Batch::where('id', $item->batch_id)->increment('quantity', $item->quantity);
            }
        }

        $sale->items()->delete();
    }
}
