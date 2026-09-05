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
use Modules\Finance\Models\AccountTransaction;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\StockMovement;
use Modules\Sales\Http\Requests\StoreSaleRequest;
use Modules\Sales\Http\Requests\UpdateSaleRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SalePayment;
use Modules\Shop\Models\Warehouse;

class SaleController extends Controller
{
    public function __construct(
        protected AccountTransactionService $accountTransactionService
    ) {}

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

        $query = Sale::with(['customer', 'items.product', 'items.batch', 'items.unit', 'payments'])
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

        $invoiceSale = null;
        if (session('show_invoice_sale_id')) {
            $invoiceSale = Sale::with(['customer', 'warehouse', 'items.product.units', 'items.unit', 'items.batch', 'payments'])
                ->find(session('show_invoice_sale_id'));
        }

        return view('sales::sales.ledger', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'search' => $search,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'invoiceSale' => $invoiceSale,
        ]);
    }

    public function invoiceModal(Sale $sale): View
    {
        $sale->load(['customer', 'warehouse', 'items.product.units', 'items.unit', 'items.batch', 'payments']);

        return view('sales::sales._invoice_modal', compact('sale'));
    }

    public function printInvoice(Sale $sale): View
    {
        $sale->load(['customer', 'warehouse', 'items.product.units', 'items.unit', 'items.batch', 'payments']);

        return view('sales::sales.print-invoice', compact('sale'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::where('status', 'active')
            ->withSum('sales as sales_sum_due_amount', 'due_amount')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'address', 'opening_due']);
        $warehouses = Warehouse::where('status', 'active')->with('branch')->orderBy('name')->get();
        $defaultWarehouse = $warehouses->firstWhere('is_default', true);
        $warehouseId = $request->query('warehouse_id', $defaultWarehouse?->id ?? optional($warehouses->first())->id);
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::where('status', 'active')
            ->withSum(['batches as batches_sum_quantity' => fn ($q) => $q->where('warehouse_id', $warehouseId)], 'quantity')
            ->with('units')
            ->orderBy('name')->get();
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        $invoiceSale = null;
        if (session('show_invoice_sale_id')) {
            $invoiceSale = Sale::with(['customer', 'warehouse', 'items.product.units', 'items.unit', 'items.batch', 'payments'])
                ->find(session('show_invoice_sale_id'));
        }

        return view('sales::sales.create', [
            'sale' => new Sale,
            'invoiceSale' => $invoiceSale,
            'customers' => $customers,
            'products' => $products,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
            'defaultWarehouse' => $defaultWarehouse,
            'employees' => $employees,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];

        $sale = DB::transaction(function () use ($data, $items) {
            $customerId = $this->resolveCustomerId($data);
            $customer = $customerId
                ? Customer::where('id', $customerId)->lockForUpdate()->first()
                : null;

            [$subtotal, $discount, $deliveryCharge, $total] = $this->calculateBaseTotals($items, $data);
            $profit = $this->calculateProfit($items, $discount);

            $customerPreviousDue = 0.0;
            if ($customer) {
                $customerPreviousDue = round(
                    (float) $customer->opening_due +
                    (float) $customer->sales()->where('due_amount', '>', 0)->sum('due_amount'),
                    2
                );
            }

            $submittedPayments = $data['payments'] ?? [];
            $totalSubmittedPaid = round(collect($submittedPayments)->sum(fn ($p) => (float) ($p['amount'] ?? 0)), 2);
            $maxPayable = round($total + $customerPreviousDue, 2);

            if ($customer && round($totalSubmittedPaid - $maxPayable, 2) > 0.01) {
                throw ValidationException::withMessages([
                    'payments' => 'পরিশোধের পরিমাণ মোট প্রদেয় টাকার চেয়ে বেশি হতে পারে না (সর্বোচ্চ: ৳'.number_format($maxPayable, 2).') / Payment amount cannot exceed total payable amount.',
                ]);
            }

            if (! $customer && round($totalSubmittedPaid - $total, 2) > 0.01) {
                throw ValidationException::withMessages([
                    'payments' => 'পরিশোধের পরিমাণ বিক্রয়ের মোট মূল্যের চেয়ে বেশি হতে পারে না (সর্বোচ্চ: ৳'.number_format($total, 2).') / Payment amount cannot exceed sale total.',
                ]);
            }

            $salePaid = min($totalSubmittedPaid, $total);
            $saleDue = round(max($total - $salePaid, 0), 2);
            $saleStatus = $saleDue <= 0 ? 'paid' : ($salePaid <= 0 ? 'due' : 'partial');

            $sale = Sale::create([
                'customer_id' => $customerId,
                'warehouse_id' => $data['warehouse_id'],
                'sale_date' => $data['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total' => $total,
                'paid_amount' => $salePaid,
                'due_amount' => $saleDue,
                'profit' => $profit,
                'payment_status' => $saleStatus,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
            ]);

            $sale->update([
                'invoice_no' => $data['invoice_no'] ?? 'SL-'.str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT),
            ]);

            $this->applyItems($sale, $items);
            $this->applyPaymentsAndPreviousDue($sale, $customer, $submittedPayments, $total, $salePaid);

            return $sale;
        });

        return redirect()->route('sales.index')
            ->with('status', 'বিক্রয় সফলভাবে যোগ করা হয়েছে')
            ->with('show_invoice_sale_id', $sale?->id);
    }

    public function edit(Sale $sale): View
    {
        $customers = Customer::where('status', 'active')
            ->withSum(['sales as sales_sum_due_amount' => fn ($q) => $q->where('id', '!=', $sale->id)], 'due_amount')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'address', 'opening_due']);
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

            $customerId = $this->resolveCustomerId($data);
            $customer = $customerId
                ? Customer::where('id', $customerId)->lockForUpdate()->first()
                : null;

            [$subtotal, $discount, $deliveryCharge, $total] = $this->calculateBaseTotals($items, $data);
            $profit = $this->calculateProfit($items, $discount);

            $customerPreviousDue = 0.0;
            if ($customer) {
                $customerPreviousDue = round(
                    (float) $customer->opening_due +
                    (float) $customer->sales()->where('id', '!=', $sale->id)->where('due_amount', '>', 0)->sum('due_amount'),
                    2
                );
            }

            $submittedPayments = $data['payments'] ?? [];
            $totalSubmittedPaid = round(collect($submittedPayments)->sum(fn ($p) => (float) ($p['amount'] ?? 0)), 2);
            $maxPayable = round($total + $customerPreviousDue, 2);

            if ($customer && round($totalSubmittedPaid - $maxPayable, 2) > 0.01) {
                throw ValidationException::withMessages([
                    'payments' => 'পরিশোধের পরিমাণ মোট প্রদেয় টাকার চেয়ে বেশি হতে পারে না (সর্বোচ্চ: ৳'.number_format($maxPayable, 2).') / Payment amount cannot exceed total payable amount.',
                ]);
            }

            if (! $customer && round($totalSubmittedPaid - $total, 2) > 0.01) {
                throw ValidationException::withMessages([
                    'payments' => 'পরিশোধের পরিমাণ বিক্রয়ের মোট মূল্যের চেয়ে বেশি হতে পারে না (সর্বোচ্চ: ৳'.number_format($total, 2).') / Payment amount cannot exceed sale total.',
                ]);
            }

            $salePaid = min($totalSubmittedPaid, $total);
            $saleDue = round(max($total - $salePaid, 0), 2);
            $saleStatus = $saleDue <= 0 ? 'paid' : ($salePaid <= 0 ? 'due' : 'partial');

            $sale->update([
                'customer_id' => $customerId,
                'sale_date' => $data['sale_date'],
                'invoice_no' => $data['invoice_no'] ?? $sale->invoice_no,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'total' => $total,
                'paid_amount' => $salePaid,
                'due_amount' => $saleDue,
                'profit' => $profit,
                'payment_status' => $saleStatus,
                'note' => $data['note'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'employee_phone' => $data['employee_phone'] ?? null,
            ]);

            $this->applyItems($sale, $items);

            $this->revertExcessPreviousDuePayments($sale);

            foreach ($sale->payments()->get() as $payment) {
                $payment->delete();
            }
            $this->applyPaymentsAndPreviousDue($sale, $customer, $submittedPayments, $total, $salePaid);
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($sale) {
            $this->revertItems($sale);

            $this->revertExcessPreviousDuePayments($sale);

            foreach ($sale->payments()->get() as $payment) {
                $payment->delete();
            }

            $sale->delete();
        });

        return redirect()->route('sales.index')->with('status', 'বিক্রয় বাতিল করা হয়েছে');
    }

    /**
     * Revert any payments on earlier sales or customer opening due reductions
     * that were settled from this sale's payment drawer.
     */
    private function revertExcessPreviousDuePayments(Sale $sale): void
    {
        $linkedPayments = SalePayment::where('note', 'like', "%(বিক্রয় ইনভয়েস: {$sale->invoice_no} থেকে সমন্বয়কৃত)%")->get();
        foreach ($linkedPayments as $lp) {
            $prevSale = $lp->sale;
            if ($prevSale) {
                $newPaid = round(max((float) $prevSale->paid_amount - (float) $lp->amount, 0), 2);
                $newDue = round(min((float) $prevSale->due_amount + (float) $lp->amount, (float) $prevSale->total), 2);
                $prevSale->update([
                    'paid_amount' => $newPaid,
                    'due_amount' => $newDue,
                    'payment_status' => $newDue <= 0 ? 'paid' : ($newPaid <= 0 ? 'due' : 'partial'),
                ]);
            }
            $lp->delete();
        }

        $linkedOpeningTxs = AccountTransaction::where('source', 'sale')
            ->where('note', 'like', "%(বিক্রয় ইনভয়েস: {$sale->invoice_no} থেকে সমন্বয়কৃত)%")
            ->get();
        foreach ($linkedOpeningTxs as $tx) {
            if ($tx->sourceable instanceof Customer) {
                $tx->sourceable->increment('opening_due', (float) $tx->amount);
            }
            $tx->delete();
        }
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function calculateBaseTotals(array $items, array $data): array
    {
        $subtotal = round((float) collect($items)->sum(fn ($item) => $this->lineAmount($item)), 2);
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $deliveryCharge = round((float) ($data['delivery_charge'] ?? 0), 2);
        $total = round(max($subtotal - $discount + $deliveryCharge, 0), 2);

        return [$subtotal, $discount, $deliveryCharge, $total];
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
     * @param  array<int, array{account_id?: ?int, method: string, amount: float}>  $submittedPayments
     */
    private function applyPaymentsAndPreviousDue(
        Sale $sale,
        ?Customer $customer,
        array $submittedPayments,
        float $saleTotal,
        float $salePaid
    ): void {
        $pools = [];
        foreach ($submittedPayments as $payment) {
            $amt = round((float) ($payment['amount'] ?? 0), 2);
            if ($amt <= 0) {
                continue;
            }
            $pools[] = [
                'account_id' => ! empty($payment['account_id']) ? (int) $payment['account_id'] : null,
                'method' => $payment['method'] ?? 'cash',
                'amount' => $amt,
                'remaining' => $amt,
            ];
        }

        // 1. Allocate up to $salePaid to this sale
        $neededForSale = $salePaid;
        foreach ($pools as &$pool) {
            if ($neededForSale <= 0) {
                break;
            }
            if ($pool['remaining'] <= 0) {
                continue;
            }

            $take = min($neededForSale, $pool['remaining']);
            $pool['remaining'] = round($pool['remaining'] - $take, 2);
            $neededForSale = round($neededForSale - $take, 2);

            $sale->payments()->create([
                'account_id' => $pool['account_id'],
                'method' => $pool['method'],
                'amount' => $take,
                'payment_date' => $sale->sale_date ?? now()->toDateString(),
            ]);
        }
        unset($pool);

        // 2. If there is excess payment and a customer exists, allocate FIFO across opening_due and previous sales
        $totalSubmitted = round(collect($submittedPayments)->sum(fn ($p) => (float) ($p['amount'] ?? 0)), 2);
        $excessForPreviousDue = round(max($totalSubmitted - $saleTotal, 0), 2);

        if ($excessForPreviousDue > 0 && $customer) {
            $allocations = [];
            $remainingToAllocate = $excessForPreviousDue;

            // (a) First, opening due
            if ((float) $customer->opening_due > 0 && $remainingToAllocate > 0) {
                $deductOpening = min($remainingToAllocate, (float) $customer->opening_due);
                $allocations[] = [
                    'type' => 'opening',
                    'needed' => $deductOpening,
                ];
                $remainingToAllocate = round($remainingToAllocate - $deductOpening, 2);
            }

            // (b) Second, earlier sales with remaining due
            if ($remainingToAllocate > 0) {
                $previousSales = Sale::where('customer_id', $customer->id)
                    ->where('id', '!=', $sale->id)
                    ->where('due_amount', '>', 0)
                    ->orderBy('sale_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($previousSales as $prevSale) {
                    if ($remainingToAllocate <= 0) {
                        break;
                    }

                    $pay = min($remainingToAllocate, (float) $prevSale->due_amount);
                    $allocations[] = [
                        'type' => 'sale',
                        'model' => $prevSale,
                        'needed' => $pay,
                    ];
                    $remainingToAllocate = round($remainingToAllocate - $pay, 2);
                }
            }

            // (c) Apply allocations from the remaining amounts in $pools
            foreach ($allocations as $item) {
                $needed = $item['needed'];

                foreach ($pools as &$pool) {
                    if ($needed <= 0) {
                        break;
                    }
                    if ($pool['remaining'] <= 0) {
                        continue;
                    }

                    $payPortion = min($needed, $pool['remaining']);
                    $pool['remaining'] = round($pool['remaining'] - $payPortion, 2);
                    $needed = round($needed - $payPortion, 2);

                    $account = $pool['account_id']
                        ? Account::withoutGlobalScopes()->find($pool['account_id'])
                        : $this->accountTransactionService->getDefaultAccount($sale->shop_id);

                    if ($item['type'] === 'opening') {
                        $customer->opening_due = round(max((float) $customer->opening_due - $payPortion, 0), 2);
                        $customer->save();

                        if ($account) {
                            $this->accountTransactionService->recordTransaction(
                                account: $account,
                                type: 'in',
                                amount: $payPortion,
                                source: 'sale',
                                sourceable: $customer,
                                note: 'গ্রাহক প্রারম্ভিক বাকি পরিশোধ: '.$customer->name.' (বিক্রয় ইনভয়েস: '.$sale->invoice_no.' থেকে সমন্বয়কৃত)',
                                occurredAt: $sale->sale_date ? $sale->sale_date->format('Y-m-d').' '.now()->format('H:i:s') : now(),
                                userId: Auth::id()
                            );
                        }
                    } elseif ($item['type'] === 'sale') {
                        /** @var Sale $prevSale */
                        $prevSale = $item['model'];
                        $prevSale->payments()->create([
                            'account_id' => $pool['account_id'],
                            'method' => $pool['method'],
                            'amount' => $payPortion,
                            'payment_date' => $sale->sale_date ?? now()->toDateString(),
                            'note' => 'বাকি পরিশোধ - বিল: '.$prevSale->invoice_no.' (বিক্রয় ইনভয়েস: '.$sale->invoice_no.' থেকে সমন্বয়কৃত)',
                        ]);

                        $newPaid = round((float) $prevSale->paid_amount + $payPortion, 2);
                        $newDue = round(max((float) $prevSale->due_amount - $payPortion, 0), 2);
                        $prevSale->update([
                            'paid_amount' => $newPaid,
                            'due_amount' => $newDue,
                            'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
                        ]);
                    }
                }
                unset($pool);
            }
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
            $enteredQuantity = (float) $item['quantity'];
            $enteredUnitPrice = (float) $item['unit_price'];
            $baseQuantity = $enteredQuantity * $conversionFactor;
            $baseUnitPrice = $conversionFactor > 0 ? $enteredUnitPrice / $conversionFactor : $enteredUnitPrice;
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
                $itemQuantity = $conversionFactor > 0 ? ($take / $conversionFactor) : $take;

                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'batch_id' => $batch->id,
                    'unit_id' => $unitId,
                    'quantity' => round($itemQuantity, 4),
                    'unit_price' => round($enteredUnitPrice, 4),
                    'discount' => round($discountShare, 2),
                    'total' => round(($itemQuantity * $enteredUnitPrice) - $discountShare, 2),
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
                    $baseQuantity = $item->baseQuantity();
                    $batch->increment('quantity', $baseQuantity);

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'batch_id' => $batch->id,
                        'type' => 'sale_reversal',
                        'quantity_change' => $baseQuantity,
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
