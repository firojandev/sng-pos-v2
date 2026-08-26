<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Employee\Models\Employee;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\Http\Requests\StorePurchaseRequest;
use Modules\Purchase\Http\Requests\UpdatePurchaseRequest;
use Modules\Purchase\Models\Purchase;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;

class PurchaseController extends Controller
{
    public function index(): View
    {
        return $this->create();
    }

    public function ledger(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());

        $query = Purchase::with(['supplier', 'items.product', 'payments'])
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
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $products = Product::where('status', 'active')->withSum('batches', 'quantity')->orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);

        return view('purchase::purchase.create', [
            'purchase' => new Purchase,
            'suppliers' => $suppliers,
            'products' => $products,
            'warehouses' => $warehouses,
            'employees' => $employees,
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items) {
            [$subtotal, $discount, $deliveryCharge, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);

            $purchase = Purchase::create([
                'supplier_id' => $this->resolveSupplierId($data),
                'warehouse_id' => $data['warehouse_id'],
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
            ]);

            $purchase->update([
                'invoice_no' => $data['invoice_no'] ?? 'PU-'.str_pad((string) $purchase->id, 4, '0', STR_PAD_LEFT),
            ]);

            $this->applyItems($purchase, $items);
            $this->applyPayments($purchase, $data['payments'] ?? []);
        });

        return redirect()->route('purchase.index')->with('status', 'ক্রয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Purchase $purchase): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $products = Product::where('status', 'active')->withSum('batches', 'quantity')->orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $purchase->load('items', 'payments');

        return view('purchase::purchase.edit', compact('purchase', 'suppliers', 'products', 'warehouses', 'employees'));
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items, $purchase) {
            $this->revertItems($purchase);

            [$subtotal, $discount, $deliveryCharge, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);

            $purchase->update([
                'supplier_id' => $this->resolveSupplierId($data),
                'warehouse_id' => $data['warehouse_id'],
                'purchase_date' => $data['purchase_date'],
                'invoice_no' => $data['invoice_no'] ?? $purchase->invoice_no,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
            ]);

            $this->applyItems($purchase, $items);

            $purchase->payments()->delete();
            $this->applyPayments($purchase, $data['payments'] ?? []);
        });

        return redirect()->route('purchase.index')->with('status', 'ক্রয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($purchase) {
            $this->revertItems($purchase);
            $purchase->delete();
        });

        return redirect()->route('purchase.index')->with('status', 'ক্রয় বাতিল করা হয়েছে');
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string}
     */
    private function calculateTotals(array $items, array $data): array
    {
        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['purchase_price']);
        $discount = $data['discount'] ?? 0;
        $deliveryCharge = $data['delivery_charge'] ?? 0;
        $total = max($subtotal - $discount + $deliveryCharge, 0);
        $paid = collect($data['payments'] ?? [])->sum('amount');
        $due = max($total - $paid, 0);
        $status = $due <= 0 ? 'paid' : ($paid <= 0 ? 'due' : 'partial');

        return [$subtotal, $discount, $deliveryCharge, $total, $paid, $due, $status];
    }

    /**
     * Resolve the supplier for this purchase: use the picked supplier_id if present,
     * otherwise look up (or quick-create) a supplier from the free-text name/phone
     * entered in the confirm-payment drawer.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveSupplierId(array $data): ?int
    {
        if (! empty($data['supplier_id'])) {
            return (int) $data['supplier_id'];
        }

        $phone = trim((string) ($data['supplier_phone'] ?? ''));
        $name = trim((string) ($data['supplier_name'] ?? ''));

        if ($phone === '' && $name === '') {
            return null;
        }

        $attributes = $phone !== '' ? ['phone' => $phone] : ['name' => $name, 'phone' => null];

        $supplier = Supplier::firstOrCreate($attributes, [
            'name' => $name !== '' ? $name : $phone,
            'address' => $data['supplier_address'] ?? null,
            'status' => 'active',
        ]);

        return $supplier->id;
    }

    /**
     * @param  array<int, array{method: string, amount: float}>  $payments
     */
    private function applyPayments(Purchase $purchase, array $payments): void
    {
        foreach ($payments as $payment) {
            $purchase->payments()->create([
                'method' => $payment['method'],
                'amount' => $payment['amount'],
            ]);
        }
    }

    private function applyItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $batchNo = trim((string) ($item['batch_no'] ?? ''));
            if ($batchNo === '') {
                $batchNo = 'BT-'.now()->format('ymd').'-'.$item['product_id'].'-'.random_int(100, 999);
            }

            if (! empty($item['barcode']) && ! Product::where('barcode', $item['barcode'])->where('id', '!=', $item['product_id'])->exists()) {
                Product::where('id', $item['product_id'])
                    ->where(fn ($q) => $q->whereNull('barcode')->orWhere('barcode', '!=', $item['barcode']))
                    ->update(['has_barcode' => true, 'barcode' => $item['barcode']]);
            }

            $batch = Batch::where('product_id', $item['product_id'])
                ->where('batch_no', $batchNo)
                ->where('warehouse_id', $purchase->warehouse_id)
                ->lockForUpdate()
                ->first();

            $before = $batch ? (float) $batch->quantity : 0.0;

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
                    'warehouse_id' => $purchase->warehouse_id,
                    'batch_no' => $batchNo,
                    'quantity' => $item['quantity'],
                    'mfg_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'batch_id' => $batch->id,
                'batch_no' => $batchNo,
                'mfg_date' => $item['mfg_date'] ?? null,
                'expiry_date' => $item['expiry_date'] ?? null,
                'quantity' => $item['quantity'],
                'purchase_price' => $item['purchase_price'],
                'total' => $item['quantity'] * $item['purchase_price'],
            ]);

            StockMovement::create([
                'product_id' => $item['product_id'],
                'batch_id' => $batch->id,
                'type' => 'purchase',
                'quantity_change' => $item['quantity'],
                'quantity_before' => $before,
                'quantity_after' => (float) $batch->quantity,
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'created_by' => Auth::id(),
            ]);
        }
    }

    private function revertItems(Purchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            if ($item->batch_id) {
                $batch = Batch::where('id', $item->batch_id)->lockForUpdate()->first();
                if ($batch) {
                    $before = (float) $batch->quantity;
                    $reverted = min((float) $item->quantity, $before);
                    $batch->quantity = max($batch->quantity - $item->quantity, 0);
                    $batch->save();

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'batch_id' => $batch->id,
                        'type' => 'purchase_reversal',
                        'quantity_change' => -$reverted,
                        'quantity_before' => $before,
                        'quantity_after' => (float) $batch->quantity,
                        'reference_type' => Purchase::class,
                        'reference_id' => $purchase->id,
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        }

        $purchase->items()->delete();
    }
}
