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
use Modules\Purchase\Http\Requests\ReceivePurchaseDeliveryOrderRequest;
use Modules\Purchase\Http\Requests\StorePurchaseDeliveryOrderRequest;
use Modules\Purchase\Http\Requests\UpdatePurchaseDeliveryOrderRequest;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchaseDeliveryOrder;
use Modules\Purchase\Models\PurchaseDeliveryReceipt;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;

class PurchaseDeliveryOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $supplierId = $request->query('supplier_id');
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());

        $query = PurchaseDeliveryOrder::with(['supplier', 'warehouse', 'items.product', 'receipts'])
            ->whereDate('order_date', '>=', $from)
            ->whereDate('order_date', '<=', $to);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if (in_array($status, ['pending', 'partial_received', 'received', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        $summary = [
            'total_count' => (clone $query)->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'partial_count' => (clone $query)->where('status', 'partial_received')->count(),
            'received_count' => (clone $query)->where('status', 'received')->count(),
            'total_amount' => (clone $query)->sum('total_amount'),
        ];

        $orders = $query->latest('order_date')->latest('id')->paginate(15)->withQueryString();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('purchase::delivery-orders.index', [
            'orders' => $orders,
            'summary' => $summary,
            'suppliers' => $suppliers,
            'search' => $search,
            'status' => $status,
            'supplierId' => $supplierId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $products = Product::where('status', 'active')->withSum('batches', 'quantity')->with('units')->orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);

        return view('purchase::delivery-orders.create', [
            'order' => new PurchaseDeliveryOrder,
            'suppliers' => $suppliers,
            'products' => $products,
            'warehouses' => $warehouses,
            'employees' => $employees,
        ]);
    }

    public function store(StorePurchaseDeliveryOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        $order = DB::transaction(function () use ($data, $items) {
            $subtotal = collect($items)->sum(fn ($item) => (float) $item['quantity'] * (float) $item['purchase_price']);
            $discount = (float) ($data['discount'] ?? 0);
            $deliveryCharge = (float) ($data['delivery_charge'] ?? 0);
            $total = max($subtotal - $discount + $deliveryCharge, 0);

            $orderNo = trim((string) ($data['order_no'] ?? ''));
            if ($orderNo === '') {
                $orderNo = 'PDO-'.now()->format('ymd').'-'.str_pad((string) (PurchaseDeliveryOrder::max('id') + 1), 4, '0', STR_PAD_LEFT);
            }

            $order = PurchaseDeliveryOrder::create([
                'supplier_id' => $this->resolveSupplierId($data),
                'warehouse_id' => $data['warehouse_id'],
                'order_no' => $orderNo,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $total,
                'paid_amount' => 0,
                'due_amount' => $total,
                'payment_status' => 'unpaid',
                'delivery_person_name' => $data['delivery_person_name'] ?? null,
                'delivery_person_phone' => $data['delivery_person_phone'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['purchase_price'];

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'ordered_quantity' => $qty,
                    'received_quantity' => 0,
                    'purchase_price' => $price,
                    'subtotal' => $qty * $price,
                ]);
            }

            return $order;
        });

        return redirect()->route('purchase-delivery-orders.show', $order)
            ->with('status', 'পারচেজ ডেলিভারি অর্ডার সফলভাবে তৈরি করা হয়েছে');
    }

    public function show(PurchaseDeliveryOrder $deliveryOrder): View
    {
        $deliveryOrder->load([
            'supplier',
            'warehouse.branch',
            'items.product.units',
            'items.unit',
            'receipts.warehouse',
            'receipts.receiver',
            'receipts.items.product',
            'receipts.purchase',
            'creator',
        ]);

        return view('purchase::delivery-orders.show', [
            'order' => $deliveryOrder,
        ]);
    }

    public function edit(PurchaseDeliveryOrder $deliveryOrder): View|RedirectResponse
    {
        if (! $deliveryOrder->canBeEdited()) {
            return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
                ->with('error', 'এই অর্ডারের জন্য পণ্য ইতিমধ্যে গ্রহণ করা হয়েছে অথবা অর্ডারটি বাতিল করা হয়েছে। তাই সম্পাদনা সম্ভব নয়।');
        }

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $products = Product::where('status', 'active')->withSum('batches', 'quantity')->with('units')->orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);

        $deliveryOrder->load('items');

        return view('purchase::delivery-orders.edit', [
            'order' => $deliveryOrder,
            'suppliers' => $suppliers,
            'products' => $products,
            'warehouses' => $warehouses,
            'employees' => $employees,
        ]);
    }

    public function update(UpdatePurchaseDeliveryOrderRequest $request, PurchaseDeliveryOrder $deliveryOrder): RedirectResponse
    {
        if (! $deliveryOrder->canBeEdited()) {
            return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
                ->with('error', 'এই অর্ডারের পণ্য গ্রহণ শুরু হওয়ায় এটি সম্পাদনা করা যাবে না।');
        }

        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items, $deliveryOrder) {
            $subtotal = collect($items)->sum(fn ($item) => (float) $item['quantity'] * (float) $item['purchase_price']);
            $discount = (float) ($data['discount'] ?? 0);
            $deliveryCharge = (float) ($data['delivery_charge'] ?? 0);
            $total = max($subtotal - $discount + $deliveryCharge, 0);

            $deliveryOrder->update([
                'supplier_id' => $this->resolveSupplierId($data),
                'warehouse_id' => $data['warehouse_id'],
                'order_no' => $data['order_no'] ?? $deliveryOrder->order_no,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $total,
                'due_amount' => $total,
                'delivery_person_name' => $data['delivery_person_name'] ?? null,
                'delivery_person_phone' => $data['delivery_person_phone'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            $deliveryOrder->items()->delete();

            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['purchase_price'];

                $deliveryOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'ordered_quantity' => $qty,
                    'received_quantity' => 0,
                    'purchase_price' => $price,
                    'subtotal' => $qty * $price,
                ]);
            }
        });

        return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
            ->with('status', 'ডেলিভারি অর্ডার সফলভাবে হালনাগাদ করা হয়েছে');
    }

    public function cancel(PurchaseDeliveryOrder $deliveryOrder): RedirectResponse
    {
        if (! $deliveryOrder->canBeCancelled()) {
            return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
                ->with('error', 'ইতিমধ্যে ডেলিভারি গৃহীত হওয়ায় এই অর্ডারটি বাতিল করা সম্ভব নয়।');
        }

        $deliveryOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
            ->with('status', 'ডেলিভারি অর্ডার সফলভাবে বাতিল করা হয়েছে');
    }

    public function receiveForm(PurchaseDeliveryOrder $deliveryOrder): View|RedirectResponse
    {
        if (! $deliveryOrder->canBeReceived()) {
            return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
                ->with('error', 'এই অর্ডারের সকল পণ্য ইতিমধ্যে গৃহীত হয়েছে অথবা অর্ডারটি বাতিল করা হয়েছে।');
        }

        $deliveryOrder->load([
            'supplier',
            'warehouse.branch',
            'items.product.units',
            'items.unit',
        ]);

        return view('purchase::delivery-orders.receive', [
            'order' => $deliveryOrder,
        ]);
    }

    public function storeReceive(ReceivePurchaseDeliveryOrderRequest $request, PurchaseDeliveryOrder $deliveryOrder): RedirectResponse
    {
        if (! $deliveryOrder->canBeReceived()) {
            return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
                ->with('error', 'এই অর্ডারে নতুন ডেলিভারি গ্রহণ করা সম্ভব নয়।');
        }

        $data = $request->validated();
        $inputItems = collect($data['items'])->keyBy('order_item_id');

        $hasReceivedQty = $inputItems->contains(fn ($item) => (float) ($item['received_quantity'] ?? 0) > 0);
        if (! $hasReceivedQty) {
            return back()->withInput()->with('error', 'কমপক্ষে একটি পণ্যের গ্রহণের পরিমাণ ০ এর বেশি হতে হবে।');
        }

        $orderItems = $deliveryOrder->items()->with('product.units')->get();

        // Validate pending quantities
        foreach ($orderItems as $orderItem) {
            $input = $inputItems->get($orderItem->id);
            if (! $input) {
                continue;
            }

            $receivedQty = (float) ($input['received_quantity'] ?? 0);
            if ($receivedQty > $orderItem->pendingQuantity() + 0.0001) {
                return back()->withInput()->with('error', "{$orderItem->product->name} এর গ্রহণের পরিমাণ বাকি থাকা পরিমাণের ({$orderItem->pendingQuantity()}) চেয়ে বেশি হতে পারে না।");
            }
        }

        $receipt = DB::transaction(function () use ($data, $inputItems, $orderItems, $deliveryOrder) {
            $receiptNo = 'PDR-'.now()->format('ymd').'-'.str_pad((string) (PurchaseDeliveryReceipt::max('id') + 1), 4, '0', STR_PAD_LEFT);

            $receipt = PurchaseDeliveryReceipt::create([
                'shop_id' => $deliveryOrder->shop_id,
                'purchase_delivery_order_id' => $deliveryOrder->id,
                'receipt_no' => $receiptNo,
                'challan_no' => $data['challan_no'] ?? null,
                'warehouse_id' => $deliveryOrder->warehouse_id,
                'delivery_date' => $data['delivery_date'],
                'delivery_person_name' => $data['delivery_person_name'] ?? null,
                'delivery_person_phone' => $data['delivery_person_phone'] ?? null,
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'total_amount' => 0,
                'note' => $data['note'] ?? null,
                'received_by' => Auth::id(),
            ]);

            $receiptTotal = 0.0;
            $purchaseItemsData = [];

            foreach ($orderItems as $orderItem) {
                $input = $inputItems->get($orderItem->id);
                if (! $input) {
                    continue;
                }

                $enteredQty = (float) ($input['received_quantity'] ?? 0);
                if ($enteredQty <= 0) {
                    continue;
                }

                $batchNo = trim((string) ($input['batch_no'] ?? ''));
                if ($batchNo === '') {
                    $batchNo = 'BT-'.now()->format('ymd').'-'.$orderItem->product_id.'-'.random_int(100, 999);
                }

                $conversionFactor = $this->unitConversionFactor((int) $orderItem->product_id, $orderItem->unit_id);
                $baseQuantity = $enteredQty * $conversionFactor;
                $baseCost = (float) $orderItem->purchase_price / $conversionFactor;
                $lineSubtotal = $baseQuantity * $baseCost;
                $receiptTotal += $lineSubtotal;

                // Update product purchase price to latest
                Product::where('id', $orderItem->product_id)->update([
                    'purchase_price' => $baseCost,
                ]);

                // Update / create Batch in destination warehouse
                $batch = Batch::where('product_id', $orderItem->product_id)
                    ->where('batch_no', $batchNo)
                    ->where('warehouse_id', $deliveryOrder->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                $before = $batch ? (float) $batch->quantity : 0.0;

                if ($batch) {
                    $batch->quantity += $baseQuantity;
                    if (! empty($input['mfg_date'])) {
                        $batch->mfg_date = $input['mfg_date'];
                    }
                    if (! empty($input['expiry_date'])) {
                        $batch->expiry_date = $input['expiry_date'];
                    }
                    $batch->save();
                } else {
                    $batch = Batch::create([
                        'product_id' => $orderItem->product_id,
                        'warehouse_id' => $deliveryOrder->warehouse_id,
                        'batch_no' => $batchNo,
                        'quantity' => $baseQuantity,
                        'mfg_date' => $input['mfg_date'] ?? null,
                        'expiry_date' => $input['expiry_date'] ?? null,
                    ]);
                }

                // Log Stock Movement
                StockMovement::create([
                    'shop_id' => $deliveryOrder->shop_id,
                    'product_id' => $orderItem->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'purchase',
                    'quantity_change' => $baseQuantity,
                    'quantity_before' => $before,
                    'quantity_after' => (float) $batch->quantity,
                    'reference_type' => PurchaseDeliveryReceipt::class,
                    'reference_id' => $receipt->id,
                    'note' => "Received from Delivery Order #{$deliveryOrder->order_no} (Challan: {$receipt->challan_no})",
                    'created_by' => Auth::id(),
                ]);

                // Create Receipt Item
                $receipt->items()->create([
                    'purchase_delivery_order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'batch_id' => $batch->id,
                    'batch_no' => $batchNo,
                    'mfg_date' => $input['mfg_date'] ?? null,
                    'expiry_date' => $input['expiry_date'] ?? null,
                    'received_quantity' => $enteredQty,
                    'unit_cost' => (float) $orderItem->purchase_price,
                    'subtotal' => $lineSubtotal,
                ]);

                // Update order item accumulated received quantity
                $orderItem->increment('received_quantity', $enteredQty);

                $purchaseItemsData[] = [
                    'product_id' => $orderItem->product_id,
                    'batch_id' => $batch->id,
                    'batch_no' => $batchNo,
                    'mfg_date' => $input['mfg_date'] ?? null,
                    'expiry_date' => $input['expiry_date'] ?? null,
                    'quantity' => $baseQuantity,
                    'purchase_price' => $baseCost,
                    'total' => $lineSubtotal,
                ];
            }

            $receipt->update(['total_amount' => $receiptTotal]);

            // Sync with Purchase Ledger: Create a Purchase record linked to this receipt
            $purchase = Purchase::create([
                'shop_id' => $deliveryOrder->shop_id,
                'supplier_id' => $deliveryOrder->supplier_id,
                'warehouse_id' => $deliveryOrder->warehouse_id,
                'invoice_no' => $receipt->challan_no ?: 'PU-PDO-'.$deliveryOrder->id.'-'.$receipt->id,
                'purchase_date' => $data['delivery_date'],
                'subtotal' => $receiptTotal,
                'discount' => 0,
                'delivery_charge' => 0,
                'total' => $receiptTotal,
                'paid_amount' => 0,
                'due_amount' => $receiptTotal,
                'payment_status' => 'due',
                'note' => "স্বয়ংক্রিয় তৈরি: ডেলিভারি চালান #{$receipt->receipt_no} (অর্ডার #{$deliveryOrder->order_no})",
            ]);

            foreach ($purchaseItemsData as $pItem) {
                $purchase->items()->create($pItem);
            }

            $receipt->update(['purchase_id' => $purchase->id]);

            // Re-evaluate overall Delivery Order status
            $freshOrderItems = $deliveryOrder->items()->get();
            $allReceived = $freshOrderItems->every(fn ($item) => (float) $item->received_quantity >= (float) $item->ordered_quantity);

            $deliveryOrder->update([
                'status' => $allReceived ? 'received' : 'partial_received',
            ]);

            return $receipt;
        });

        return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
            ->with('status', 'ডেলিভারি চালান সফলভাবে গ্রহণ করা হয়েছে এবং ইনভেন্টরি স্টক হালনাগাদ করা হয়েছে');
    }

    public function printOrder(PurchaseDeliveryOrder $deliveryOrder): View
    {
        $deliveryOrder->load([
            'supplier',
            'warehouse.branch',
            'items.product',
            'items.unit',
            'creator',
        ]);

        return view('purchase::delivery-orders.print-order', [
            'order' => $deliveryOrder,
        ]);
    }

    public function printReceipt(PurchaseDeliveryReceipt $receipt): View
    {
        $receipt->load([
            'order.supplier',
            'warehouse.branch',
            'receiver',
            'items.product',
        ]);

        return view('purchase::delivery-orders.print-receipt', [
            'receipt' => $receipt,
        ]);
    }

    public function destroy(PurchaseDeliveryOrder $deliveryOrder): RedirectResponse
    {
        if ($deliveryOrder->receipts()->exists()) {
            return redirect()->route('purchase-delivery-orders.show', $deliveryOrder)
                ->with('error', 'ডেলিভারি গ্রহণ করা হয়েছে এমন অর্ডার মুছে ফেলা যাবে না।');
        }

        $deliveryOrder->items()->delete();
        $deliveryOrder->delete();

        return redirect()->route('purchase-delivery-orders.index')
            ->with('status', 'ডেলিভারি অর্ডার সফলভাবে মুছে ফেলা হয়েছে');
    }

    /**
     * Resolve supplier from picked ID or quick-create with name/phone.
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
     * Conversion factor for units.
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

        return $unit->pivot->is_smaller_unit ? 1 / $factor : $factor;
    }
}
