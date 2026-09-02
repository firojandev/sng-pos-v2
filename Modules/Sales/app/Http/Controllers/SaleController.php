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
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Sales\Http\Requests\StoreSaleRequest;
use Modules\Sales\Http\Requests\UpdateSaleRequest;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Warehouse;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        return $this->create($request);
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
        $customers = Customer::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $warehouseId = $request->query('warehouse_id', optional($warehouses->first())->id);
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::where('status', 'active')
            ->withSum(['batches as batches_sum_quantity' => fn ($q) => $q->where('warehouse_id', $warehouseId)], 'quantity')
            ->with('units')
            ->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('sales::sales.create', [
            'sale' => new Sale,
            'customers' => $customers,
            'products' => $products,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
            'employees' => $employees,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items) {
            [$subtotal, $discount, $deliveryCharge, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);
            $profit = $this->calculateProfit($items, $discount);

            $sale = Sale::create([
                'customer_id' => $this->resolveCustomerId($data),
                'warehouse_id' => $data['warehouse_id'],
                'sale_date' => $data['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'profit' => $profit,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
            ]);

            $sale->update([
                'invoice_no' => $data['invoice_no'] ?? 'SL-'.str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT),
            ]);

            $this->applyItems($sale, $items);
            $this->applyPayments($sale, $data['payments'] ?? []);
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Sale $sale): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::where('status', 'active')
            ->withSum(['batches as batches_sum_quantity' => fn ($q) => $q->where('warehouse_id', $sale->warehouse_id)], 'quantity')
            ->with('units')
            ->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $sale->load('items', 'warehouse', 'payments');

        return view('sales::sales.edit', compact('sale', 'customers', 'products', 'employees', 'accounts'));
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items, $sale) {
            $this->revertItems($sale);

            [$subtotal, $discount, $deliveryCharge, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);
            $profit = $this->calculateProfit($items, $discount);

            $sale->update([
                'customer_id' => $this->resolveCustomerId($data),
                'sale_date' => $data['sale_date'],
                'invoice_no' => $data['invoice_no'] ?? $sale->invoice_no,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'profit' => $profit,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
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
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string}
     */
    private function calculateTotals(array $items, array $data): array
    {
        $subtotal = collect($items)->sum(fn ($item) => $this->lineAmount($item));
        $discount = $data['discount'] ?? 0;
        $deliveryCharge = $data['delivery_charge'] ?? 0;
        $total = max($subtotal - $discount + $deliveryCharge, 0);
        $paid = collect($data['payments'] ?? [])->sum('amount');
        $due = max($total - $paid, 0);
        $status = $due <= 0 ? 'paid' : ($paid <= 0 ? 'due' : 'partial');

        return [$subtotal, $discount, $deliveryCharge, $total, $paid, $due, $status];
    }

    /**
     * A cart line's amount after its own per-item discount (qty x unit price,
     * both expressed in whichever unit -- pcs, carton, box -- the item was sold
     * in, minus the flat discount amount for that line).
     */
    private function lineAmount(array $item): float
    {
        return ($item['quantity'] * $item['unit_price']) - (float) ($item['discount'] ?? 0);
    }

    /**
     * Gross profit for the sale: each line's amount (already net of its own
     * per-item discount) minus the true cost of goods sold for that line --
     * quantity converted to the product's base unit x its purchase price --
     * summed across items, less the invoice-level discount. Delivery charge is
     * a pass-through cost, so it isn't counted as margin.
     */
    private function calculateProfit(array $items, string|float $discount): float
    {
        $productCosts = Product::whereIn('id', collect($items)->pluck('product_id'))->pluck('purchase_price', 'id');

        $grossProfit = collect($items)->sum(function ($item) use ($productCosts) {
            $cost = (float) ($productCosts[$item['product_id']] ?? 0);
            $conversionFactor = $this->unitConversionFactor((int) $item['product_id'], $item['unit_id'] ?? null);
            $baseQuantity = (float) $item['quantity'] * $conversionFactor;

            return $this->lineAmount($item) - ($baseQuantity * $cost);
        });

        return round($grossProfit - (float) $discount, 2);
    }

    /**
     * How many base units one unit of the item's chosen unit converts to (e.g. a
     * "Box" unit with conversion_factor 4 means 1 Box = 4 base Pieces). Falls
     * back to 1 (i.e. treat the entered quantity/price as already in the base
     * unit) when no unit was chosen or it isn't actually linked to the product.
     */
    private function unitConversionFactor(int $productId, ?int $unitId): float
    {
        if (! $unitId) {
            return 1.0;
        }

        $product = Product::with('units')->find($productId);
        $unit = $product?->units->firstWhere('id', $unitId);
        $factor = $unit ? (float) $unit->pivot->conversion_factor : 0.0;

        if ($factor <= 0) {
            return 1.0;
        }

        // is_smaller_unit means the stored factor is "X of this unit = 1 base
        // unit" (e.g. Litre when the base unit is a Drum: 1 Drum = 204 Litres),
        // the inverse of the default "1 of this unit = X base units" (e.g. a
        // Box holding 4 base Pieces).
        return $unit->pivot->is_smaller_unit ? 1 / $factor : $factor;
    }

    /**
     * Resolve the customer for this sale: use the picked customer_id if present,
     * otherwise look up (or quick-create) a customer from the free-text name/phone
     * entered in the confirm-payment drawer. Returns null for a walk-in customer.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveCustomerId(array $data): ?int
    {
        if (! empty($data['customer_id'])) {
            return (int) $data['customer_id'];
        }

        $phone = trim((string) ($data['customer_phone'] ?? ''));
        $name = trim((string) ($data['customer_name'] ?? ''));

        if ($phone === '' && $name === '') {
            return null;
        }

        $attributes = $phone !== '' ? ['phone' => $phone] : ['name' => $name, 'phone' => null];

        $customer = Customer::firstOrCreate($attributes, [
            'name' => $name !== '' ? $name : $phone,
            'address' => $data['customer_address'] ?? null,
            'status' => 'active',
        ]);

        return $customer->id;
    }

    /**
     * @param  array<int, array{account_id?: ?int, method: string, amount: float}>  $payments
     */
    private function applyPayments(Sale $sale, array $payments): void
    {
        foreach ($payments as $payment) {
            $sale->payments()->create([
                'account_id' => $payment['account_id'] ?? null,
                'method' => $payment['method'] ?? 'cash',
                'amount' => $payment['amount'],
            ]);
        }
    }

    /**
     * Consume stock FEFO (first-expiring-first-out) across a product's batches in
     * this sale's warehouse. A single cart line can end up split across multiple
     * batches (and so multiple sale_items rows) if one batch can't cover the qty.
     */
    private function applyItems(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            if (! empty($item['barcode'])) {
                $this->applyBarcode((int) $item['product_id'], $item['barcode']);
            }

            $unitId = $item['unit_id'] ?? null;
            $conversionFactor = $this->unitConversionFactor((int) $item['product_id'], $unitId);
            $baseQuantity = (float) $item['quantity'] * $conversionFactor;
            $baseUnitPrice = (float) $item['unit_price'] / $conversionFactor;
            $lineDiscount = (float) ($item['discount'] ?? 0);

            $remaining = $baseQuantity;

            $batches = Batch::where('product_id', $item['product_id'])
                ->where('warehouse_id', $sale->warehouse_id)
                ->where('quantity', '>', 0)
                ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($batches->sum('quantity') < $remaining) {
                throw ValidationException::withMessages(['items' => 'নির্বাচিত পণ্যের পর্যাপ্ত স্টক নেই']);
            }

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (float) $batch->quantity);
                $before = (float) $batch->quantity;
                $batch->decrement('quantity', $take);

                // A single cart line can split across multiple batches; prorate its
                // discount by each split's share of the line's total base quantity
                // so the split rows' totals still sum to the intended line amount.
                $discountShare = $baseQuantity > 0 ? $lineDiscount * ($take / $baseQuantity) : 0;

                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'batch_id' => $batch->id,
                    'unit_id' => $unitId,
                    'quantity' => $take,
                    'unit_price' => round($baseUnitPrice, 4),
                    'discount' => round($discountShare, 2),
                    'total' => round(($take * $baseUnitPrice) - $discountShare, 2),
                    'warranty_expires_at' => $item['warranty_expires_at'] ?? null,
                ]);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'batch_id' => $batch->id,
                    'type' => 'sale',
                    'quantity_change' => -$take,
                    'quantity_before' => $before,
                    'quantity_after' => (float) $batch->fresh()->quantity,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'created_by' => Auth::id(),
                ]);

                $remaining -= $take;
            }
        }
    }

    private function applyBarcode(int $productId, string $barcode): void
    {
        if (Product::where('barcode', $barcode)->where('id', '!=', $productId)->exists()) {
            return;
        }

        Product::where('id', $productId)
            ->where(fn ($q) => $q->whereNull('barcode')->orWhere('barcode', '!=', $barcode))
            ->update(['has_barcode' => true, 'barcode' => $barcode]);
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
