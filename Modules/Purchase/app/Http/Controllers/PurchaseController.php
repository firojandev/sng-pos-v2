<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Purchase\DataTables\PurchasesDataTable;
use Modules\Purchase\Http\Requests\ReceivePurchaseRemainingRequest;
use Modules\Purchase\Http\Requests\StorePurchaseRequest;
use Modules\Purchase\Http\Requests\UpdatePurchaseRequest;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchaseItem;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;

class PurchaseController extends Controller
{
    public function index(): View
    {
        return $this->create();
    }

    public function ledger(PurchasesDataTable $dataTable)
    {
        $totals = Purchase::query()
            ->selectRaw('
                COALESCE(SUM(total), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(due_amount), 0) as total_due,
                COUNT(id) as total_count
            ')
            ->first();

        $totalAmount = (float) ($totals->total_amount ?? 0);
        $totalPaid = (float) ($totals->total_paid ?? 0);
        $totalDue = (float) ($totals->total_due ?? 0);
        $totalCount = (int) ($totals->total_count ?? 0);

        return $dataTable->render('purchase::purchase.ledger', compact('totalAmount', 'totalPaid', 'totalDue', 'totalCount'));
    }

    public function printLedger(Request $request): View
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $query = Purchase::with(['supplier', 'items.product', 'warehouse'])
            ->latest('purchase_date');

        if ($from) {
            $query->whereDate('purchase_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('purchase_date', '<=', $to);
        }
        if ($status && in_array($status, ['paid', 'partial', 'due'], true)) {
            $query->where('payment_status', $status);
        }
        if ($search !== '') {
            $searchClean = ltrim($search, '#');
            $query->where(function ($q) use ($search, $searchClean) {
                $q->where('invoice_no', 'like', "%{$searchClean}%")
                    ->orWhere('do_number', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('batch_no', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($pq) use ($search) {
                                $pq->where('name', 'like', "%{$search}%")
                                    ->orWhere('sku', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $totals = (clone $query)->selectRaw('
            COALESCE(SUM(total), 0) as total_amount,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(due_amount), 0) as total_due,
            COUNT(id) as total_count
        ')->first();

        $purchases = $query->get();
        $shop = Auth::user()?->shop;

        return view('purchase::purchase.print-ledger', [
            'purchases' => $purchases,
            'totals' => $totals,
            'shop' => $shop,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'warehouse', 'items.product.units', 'payments', 'receiptItems.receiver', 'receiptItems.product']);

        return view('purchase::purchase.detail-drawer', compact('purchase'));
    }

    public function receiveModal(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'warehouse', 'items.product.units', 'receiptItems.receiver']);

        return view('purchase::purchase._receive_modal', compact('purchase'));
    }

    public function receiptHistory(Purchase $purchase): View
    {
        $purchase->load([
            'supplier',
            'warehouse',
            'items.product.units',
            'receiptItems.product',
            'receiptItems.batch',
            'receiptItems.receiver',
        ]);

        return view('purchase::purchase._receipt_history_modal', compact('purchase'));
    }

    public function storeReceive(ReceivePurchaseRemainingRequest $request, Purchase $purchase): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $inputItems = collect($data['items']);

        $hasReceivedQty = $inputItems->contains(fn ($item) => (float) ($item['received_qty'] ?? 0) > 0);
        if (! $hasReceivedQty) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'কমপক্ষে একটি পণ্যের জন্য গ্রহণের পরিমাণ প্রদান করুন। / Please enter received quantity for at least one item.',
                ], 422);
            }

            return back()->withErrors(['items' => 'কমপক্ষে একটি পণ্যের জন্য গ্রহণের পরিমাণ প্রদান করুন।']);
        }

        $purchase->load(['items.product', 'warehouse']);

        DB::transaction(function () use ($data, $inputItems, $purchase) {
            $doNumber = trim((string) $data['do_number']);
            $doDate = $data['do_date'] ?? now()->toDateString();
            $vehicleNumber = $data['vehicle_number'] ?? null;
            $deliveryPersonName = $data['delivery_person_name'] ?? null;
            $note = $data['note'] ?? null;

            foreach ($inputItems as $input) {
                $enteredQty = (float) ($input['received_qty'] ?? 0);
                if ($enteredQty <= 0) {
                    continue;
                }

                /** @var PurchaseItem|null $purchaseItem */
                $purchaseItem = $purchase->items->firstWhere('id', (int) $input['purchase_item_id']);
                if (! $purchaseItem) {
                    continue;
                }

                $pending = $purchaseItem->pendingQuantity();
                if ($enteredQty > $pending) {
                    throw ValidationException::withMessages([
                        'items' => ["{$purchaseItem->product->name}-এর জন্য গ্রহণের পরিমাণ বাকি পরিমাণের চেয়ে বেশি হতে পারবে না (বাকি: {$pending})।"],
                    ]);
                }

                $batchNo = ! empty($input['batch_no'])
                    ? trim((string) $input['batch_no'])
                    : ($purchaseItem->batch_no ?: 'BT-'.now()->format('ymd').'-'.$purchaseItem->product_id.'-'.random_int(100, 999));

                $batch = Batch::where('product_id', $purchaseItem->product_id)
                    ->where('batch_no', $batchNo)
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                $before = $batch ? (float) $batch->quantity : 0.0;

                if ($batch) {
                    $batch->quantity += $enteredQty;
                    if (! empty($input['mfg_date'])) {
                        $batch->mfg_date = $input['mfg_date'];
                    }
                    if (! empty($input['expiry_date'])) {
                        $batch->expiry_date = $input['expiry_date'];
                    }
                    $batch->save();
                } else {
                    $batch = Batch::create([
                        'shop_id' => $purchase->shop_id,
                        'product_id' => $purchaseItem->product_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'batch_no' => $batchNo,
                        'quantity' => $enteredQty,
                        'mfg_date' => $input['mfg_date'] ?? null,
                        'expiry_date' => $input['expiry_date'] ?? null,
                    ]);
                }

                $purchaseItem->received_quantity = (float) $purchaseItem->received_quantity + $enteredQty;
                if (! $purchaseItem->batch_id) {
                    $purchaseItem->batch_id = $batch->id;
                    $purchaseItem->batch_no = $batchNo;
                }
                $purchaseItem->save();

                $purchase->receiptItems()->create([
                    'shop_id' => $purchase->shop_id,
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $purchaseItem->product_id,
                    'batch_id' => $batch->id,
                    'received_quantity' => $enteredQty,
                    'do_number' => $doNumber,
                    'do_date' => $doDate,
                    'vehicle_number' => $vehicleNumber,
                    'delivery_person_name' => $deliveryPersonName,
                    'note' => $note,
                    'received_by' => Auth::id(),
                ]);

                StockMovement::create([
                    'shop_id' => $purchase->shop_id,
                    'product_id' => $purchaseItem->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'purchase',
                    'quantity_change' => $enteredQty,
                    'quantity_before' => $before,
                    'quantity_after' => (float) $batch->quantity,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'note' => "Received remaining items (D.O. #{$doNumber}) for Purchase #{$purchase->invoice_no}",
                    'created_by' => Auth::id(),
                ]);
            }

            if (empty($purchase->do_number)) {
                $purchase->update([
                    'do_number' => $doNumber,
                    'do_date' => $doDate,
                    'vehicle_number' => $vehicleNumber,
                    'delivery_person_name' => $deliveryPersonName,
                ]);
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'পণ্য সফলভাবে গ্রহণ করা হয়েছে এবং ইনভেন্টরি স্টক হালনাগাদ করা হয়েছে।',
            ]);
        }

        return redirect()->route('purchase.ledger')->with('status', 'পণ্য সফলভাবে গ্রহণ করা হয়েছে এবং ইনভেন্টরি স্টক হালনাগাদ করা হয়েছে।');
    }

    public function findByDo(Request $request): JsonResponse
    {
        $doNumber = trim((string) $request->query('do_number', ''));
        if ($doNumber === '') {
            return response()->json(['success' => false, 'message' => 'দয়া করে ডিও নম্বর লিখুন। / Please enter a D.O. number.'], 422);
        }

        $clean = ltrim($doNumber, '#');

        $purchase = Purchase::query()
            ->where(function ($q) use ($doNumber, $clean) {
                $q->where('do_number', $doNumber)
                    ->orWhere('do_number', 'like', "%{$doNumber}%")
                    ->orWhere('invoice_no', $clean)
                    ->orWhere('invoice_no', 'like', "%{$clean}%")
                    ->orWhereHas('receiptItems', function ($rq) use ($doNumber) {
                        $rq->where('do_number', $doNumber);
                    });
            })
            ->with(['supplier', 'warehouse', 'items.product'])
            ->latest()
            ->first();

        if (! $purchase) {
            return response()->json([
                'success' => false,
                'message' => 'এই ডিও বা ইনভয়েস নম্বরের কোনো ক্রয় পাওয়া যায়নি। / No purchase found for this D.O. or Invoice number.',
            ], 404);
        }

        if (! $purchase->hasPendingItems()) {
            return response()->json([
                'success' => false,
                'message' => "ইনভয়েস #{$purchase->invoice_no}-এর সকল পণ্য ইতিমধ্যে শতভাগ গ্রহণ করা হয়েছে। / All items for invoice #{$purchase->invoice_no} have already been received.",
                'purchase_id' => $purchase->id,
                'fully_received' => true,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'purchase_id' => $purchase->id,
            'invoice_no' => $purchase->invoice_no,
            'modal_url' => route('purchase.receive.modal', $purchase),
        ]);
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', 'active')
            ->withSum('purchases', 'due_amount')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'address', 'opening_due']);
        $products = Product::where('status', 'active')->withSum('batches', 'quantity')->with('units')->orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('purchase::purchase.create', [
            'purchase' => new Purchase,
            'suppliers' => $suppliers,
            'products' => $products,
            'warehouses' => $warehouses,
            'employees' => $employees,
            'accounts' => $accounts,
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
                'transportation_cost' => (float) ($data['transportation_cost'] ?? 0),
                'adjustment_cost' => (float) ($data['adjustment_cost'] ?? 0),
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
                'do_number' => $data['do_number'] ?? null,
                'do_date' => $data['do_date'] ?? null,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'delivery_person_name' => $data['delivery_person_name'] ?? null,
            ]);

            $purchase->update([
                'invoice_no' => $data['invoice_no'] ?? 'PU-'.str_pad((string) $purchase->id, 4, '0', STR_PAD_LEFT),
            ]);

            $this->applyItems($purchase, $items);
            $this->applyPayments($purchase, $data['payments'] ?? []);
        });

        return redirect()->route('purchase.index')->with('status', 'ক্রয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Purchase $purchase): View|RedirectResponse
    {
        if ($purchase->hasUsedQuantity()) {
            $reason = $purchase->cannotBeEditedReason();

            return redirect()->route('purchase.ledger')
                ->with('error', $reason.' তাই ক্রয়টি সম্পাদনা করা যাবে না। / Therefore, this purchase cannot be edited.');
        }

        $suppliers = Supplier::where('status', 'active')
            ->withSum(['purchases' => fn ($q) => $q->where('id', '!=', $purchase->id)], 'due_amount')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'address', 'opening_due']);
        $products = Product::where('status', 'active')->withSum('batches', 'quantity')->with('units')->orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $purchase->load('items', 'payments');

        return view('purchase::purchase.edit', compact('purchase', 'suppliers', 'products', 'warehouses', 'employees', 'accounts'));
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse|JsonResponse
    {
        if ($purchase->hasUsedQuantity()) {
            $reason = $purchase->cannotBeEditedReason();
            $message = $reason.' তাই ক্রয়টি সম্পাদনা করা যাবে না। / Therefore, this purchase cannot be edited.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('purchase.ledger')->with('error', $message);
        }

        $data = $request->validated();
        $items = $data['items'];

        DB::transaction(function () use ($data, $items, $purchase) {
            // 1. Rollback old stock & receipt items & old items
            $this->revertItems($purchase);

            // 2. Rollback old payments (triggers PurchasePaymentAccountObserver to refund account balances)
            foreach ($purchase->payments()->get() as $payment) {
                $payment->delete();
            }

            // 3. Calculate new totals
            [$subtotal, $discount, $deliveryCharge, $total, $paid, $due, $status] = $this->calculateTotals($items, $data);

            // 4. Update purchase record (PurchaseCashObserver::saved updates CashTransaction and supplier due adjusts dynamically)
            $purchase->update([
                'supplier_id' => $this->resolveSupplierId($data),
                'warehouse_id' => $data['warehouse_id'],
                'purchase_date' => $data['purchase_date'],
                'invoice_no' => $data['invoice_no'] ?? $purchase->invoice_no,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'transportation_cost' => (float) ($data['transportation_cost'] ?? 0),
                'adjustment_cost' => (float) ($data['adjustment_cost'] ?? 0),
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
                'do_number' => $data['do_number'] ?? null,
                'do_date' => $data['do_date'] ?? null,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'delivery_person_name' => $data['delivery_person_name'] ?? null,
            ]);

            // 5. Apply new items (adds new stock, creates receipt items, logs stock movements)
            $this->applyItems($purchase, $items);

            // 6. Apply new payments (triggers PurchasePaymentAccountObserver to deduct from accounts)
            $this->applyPayments($purchase, $data['payments'] ?? []);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ক্রয় হালনাগাদ করা হয়েছে',
            ]);
        }

        return redirect()->route('purchase.index')->with('status', 'ক্রয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Purchase $purchase): JsonResponse|RedirectResponse
    {
        $purchase->load(['items.batch', 'items.product', 'payments', 'receiptItems', 'returns']);

        if ($reason = $purchase->cannotBeDeletedReason()) {
            $message = $reason.' তাই ক্রয়টি মুছে ফেলা যাবে না। / Therefore, this purchase cannot be deleted.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('purchase.ledger')->with('error', $message);
        }

        DB::transaction(function () use ($purchase) {
            $this->revertItems($purchase);

            foreach ($purchase->payments as $payment) {
                $payment->delete();
            }

            if ($purchase->deliveryReceipt) {
                $purchase->deliveryReceipt()->update(['purchase_id' => null]);
            }

            $purchase->delete();
        });

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ক্রয় বাতিল করা হয়েছে',
            ]);
        }

        return redirect()->route('purchase.ledger')->with('status', 'ক্রয় বাতিল করা হয়েছে');
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string}
     */
    private function calculateTotals(array $items, array $data): array
    {
        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['purchase_price']);
        $discount = (float) ($data['discount'] ?? 0);
        $deliveryCharge = (float) ($data['delivery_charge'] ?? 0);
        $transportationCost = (float) ($data['transportation_cost'] ?? 0);
        $adjustmentCost = (float) ($data['adjustment_cost'] ?? 0);
        $total = max($subtotal - $discount + $deliveryCharge + $transportationCost + $adjustmentCost, 0);
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
     * @param  array<int, array{account_id?: ?int, method: string, amount: float}>  $payments
     */
    private function applyPayments(Purchase $purchase, array $payments): void
    {
        foreach ($payments as $payment) {
            $purchase->payments()->create([
                'account_id' => $payment['account_id'] ?? null,
                'method' => $payment['method'] ?? 'cash',
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

            $conversionFactor = $this->unitConversionFactor((int) $item['product_id'], $item['unit_id'] ?? null);
            $baseQuantity = (float) $item['quantity'] * $conversionFactor;
            $receivedQtyInput = isset($item['received_qty']) ? (float) $item['received_qty'] : (float) $item['quantity'];
            $baseReceivedQuantity = $receivedQtyInput * $conversionFactor;
            $basePurchasePrice = (float) $item['purchase_price'] / $conversionFactor;
            $baseSalePrice = (float) $item['sale_price'] / $conversionFactor;

            Product::where('id', $item['product_id'])->update([
                'purchase_price' => $basePurchasePrice,
                'sale_price' => $baseSalePrice,
            ]);

            $batch = Batch::where('product_id', $item['product_id'])
                ->where('batch_no', $batchNo)
                ->where('warehouse_id', $purchase->warehouse_id)
                ->lockForUpdate()
                ->first();

            $before = $batch ? (float) $batch->quantity : 0.0;

            if ($batch) {
                $batch->quantity += $baseReceivedQuantity;
                if (! empty($item['mfg_date'])) {
                    $batch->mfg_date = $item['mfg_date'];
                }
                if (! empty($item['expiry_date'])) {
                    $batch->expiry_date = $item['expiry_date'];
                }
                $batch->save();
            } else {
                $batch = Batch::create([
                    'shop_id' => $purchase->shop_id,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $purchase->warehouse_id,
                    'batch_no' => $batchNo,
                    'quantity' => $baseReceivedQuantity,
                    'mfg_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            $purchaseItem = $purchase->items()->create([
                'product_id' => $item['product_id'],
                'batch_id' => $batch->id,
                'batch_no' => $batchNo,
                'mfg_date' => $item['mfg_date'] ?? null,
                'expiry_date' => $item['expiry_date'] ?? null,
                'quantity' => $baseQuantity,
                'received_quantity' => $baseReceivedQuantity,
                'purchase_price' => $basePurchasePrice,
                'total' => $baseQuantity * $basePurchasePrice,
            ]);

            $purchase->receiptItems()->create([
                'shop_id' => $purchase->shop_id,
                'purchase_item_id' => $purchaseItem->id,
                'product_id' => $item['product_id'],
                'batch_id' => $batch->id,
                'received_quantity' => $baseReceivedQuantity,
                'do_number' => $purchase->do_number,
                'do_date' => $purchase->do_date,
                'vehicle_number' => $purchase->vehicle_number,
                'delivery_person_name' => $purchase->delivery_person_name,
                'received_by' => Auth::id(),
            ]);

            if ($baseReceivedQuantity > 0) {
                StockMovement::create([
                    'shop_id' => $purchase->shop_id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $batch->id,
                    'type' => 'purchase',
                    'quantity_change' => $baseReceivedQuantity,
                    'quantity_before' => $before,
                    'quantity_after' => (float) $batch->quantity,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'created_by' => Auth::id(),
                ]);
            }
        }
    }

    /**
     * How many base units one unit of the item's chosen unit converts to (e.g. a
     * "Carton" unit with conversion_factor 12 means 1 Carton = 12 base Pieces).
     * Falls back to 1 (i.e. treat the entered quantity/prices as already in the
     * base unit) when no unit was chosen or it isn't actually linked to the product.
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
        // Carton holding 12 base Pieces).
        return $unit->pivot->is_smaller_unit ? 1 / $factor : $factor;
    }

    private function revertItems(Purchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            $receivedQty = (float) ($item->received_quantity ?? $item->quantity);
            if ($item->batch_id && $receivedQty > 0) {
                $batch = Batch::where('id', $item->batch_id)->lockForUpdate()->first();
                if ($batch) {
                    $before = (float) $batch->quantity;
                    $reverted = min($receivedQty, $before);
                    $batch->quantity = max($batch->quantity - $reverted, 0);
                    $batch->save();

                    StockMovement::create([
                        'shop_id' => $purchase->shop_id,
                        'product_id' => $item->product_id,
                        'batch_id' => $batch->id,
                        'type' => 'purchase_reversal',
                        'quantity_change' => -$reverted,
                        'quantity_before' => $before,
                        'quantity_after' => (float) $batch->quantity,
                        'reference_type' => Purchase::class,
                        'reference_id' => $purchase->id,
                        'note' => "Purchase #{$purchase->invoice_no} deleted (Stock rolled back)",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $previousItem = PurchaseItem::where('product_id', $item->product_id)
                ->where('purchase_id', '!=', $purchase->id)
                ->whereHas('purchase', fn ($q) => $q->whereNull('deleted_at'))
                ->latest('id')
                ->first();

            if ($previousItem) {
                Product::where('id', $item->product_id)->update([
                    'purchase_price' => $previousItem->purchase_price,
                ]);
            }
        }

        $purchase->receiptItems()->delete();
        $purchase->items()->delete();
    }
}
