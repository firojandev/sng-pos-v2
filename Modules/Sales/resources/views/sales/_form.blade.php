@php
    $productData = [];
    foreach ($products as $product) {
        $baseUnit = $product->units->firstWhere('pivot.is_base', true) ?? $product->units->first();

        $productData[$product->id] = [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) $product->sale_price,
            'stock' => (float) ($product->batches_sum_quantity ?? 0),
            'barcode' => $product->barcode,
            'hasWarranty' => (bool) $product->has_warranty,
            'warrantyDuration' => $product->warranty_duration,
            'warrantyType' => $product->warranty_type,
            'baseUnitId' => $baseUnit?->id,
            'units' => $product->units->map(function ($u) {
                $raw = (float) $u->pivot->conversion_factor;

                return [
                    'id' => $u->id,
                    'label' => $u->name.($u->short_code ? ' ('.$u->short_code.')' : ''),
                    'isBase' => (bool) $u->pivot->is_base,
                    // Effective factor: base units per 1 of this unit. Flipped for
                    // is_smaller_unit units (e.g. Litre when base is a Drum), where
                    // the stored value instead means "this many of this unit = 1 base".
                    'factor' => $u->pivot->is_smaller_unit && $raw > 0 ? 1 / $raw : $raw,
                ];
            })->values(),
        ];
    }

    $customerData = $customers->map(function ($c) {
        $due = round((float) ($c->opening_due ?? 0) + (float) ($c->sales_sum_due_amount ?? 0), 2);
        return [
            'id' => $c->id,
            'name' => $c->name,
            'phone' => $c->phone,
            'address' => $c->address,
            'due' => $due,
        ];
    })->values();

    $employeeData = $employees->map(fn ($e) => ['name' => $e->name, 'phone' => $e->phone])->values();

    $initialItems = old('items', $sale->exists
        ? $sale->items->map(fn ($item) => [
            'product_id' => (int) $item->product_id,
            'qty' => rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.'),
            'unitId' => $item->unit_id,
            'price' => rtrim(rtrim(number_format($item->unit_price, 2, '.', ''), '0'), '.'),
            'discount' => rtrim(rtrim(number_format($item->discount, 2, '.', ''), '0'), '.'),
            'barcode' => $item->product->barcode ?? '',
            'warrantyExpiresAt' => optional($item->warranty_expires_at)->format('Y-m-d') ?? '',
        ])->values()->toArray()
        : []
    );

    $initialDiscount = old('discount', $sale->discount ?? 0);
    $initialDeliveryCharge = old('delivery_charge', $sale->delivery_charge ?? 0);
    $initialNote = old('note', $sale->note);
    $initialInvoiceNo = old('invoice_no', $sale->exists ? $sale->invoice_no : '');
    $initialCustomerId = old('customer_id', $sale->customer_id ?? request('customer_id'));
    $customerFromReq = request('customer_id') ? $customers->firstWhere('id', request('customer_id')) : null;
    $initialCustomerName = old('customer_name', $sale->customer->name ?? $customerFromReq?->name ?? '');
    $initialCustomerPhone = old('customer_phone', $sale->customer->phone ?? $customerFromReq?->phone ?? '');
    $initialCustomerAddress = old('customer_address', $sale->customer->address ?? $customerFromReq?->address ?? '');
    $initialEmployeeName = old('employee_name', $sale->employee_name);
    $initialEmployeePhone = old('employee_phone', $sale->employee_phone);
    $initialAmount = $sale->exists ? $sale->paid_amount : 0;
    $defaultAccount = $accounts->firstWhere('is_default', true) ?? $accounts->first();
    $bankAccounts = $accounts->whereIn('type', ['bank', 'mfs'])->values();
    $cashAccounts = $accounts->where('type', 'cash')->values();
    $defaultCashAccount = $cashAccounts->firstWhere('is_default', true) ?? $cashAccounts->first() ?? $defaultAccount;
    $defaultBankAccount = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first();

    $existingPayments = $sale->exists ? $sale->payments : collect();
    $hasCashPayment = $existingPayments->contains(fn ($p) => $p->method === 'cash');
    $hasBankPayment = $existingPayments->contains(fn ($p) => in_array($p->method, ['bank', 'mobile_banking', 'card', 'other']));

    $initialPaymentType = 'cash';
    $initialCashAmount = 0;
    $initialBankAmount = 0;
    $initialBankAccountId = $defaultBankAccount?->id;

    if ($existingPayments->count() > 1 || ($hasCashPayment && $hasBankPayment)) {
        $initialPaymentType = 'both';
        $initialCashAmount = $existingPayments->firstWhere('method', 'cash')?->amount ?? 0;
        $bankPayment = $existingPayments->first(fn ($p) => in_array($p->method, ['bank', 'mobile_banking', 'card', 'other']));
        $initialBankAmount = $bankPayment?->amount ?? 0;
        if ($bankPayment?->account_id) {
            $initialBankAccountId = $bankPayment->account_id;
        }
    } elseif ($hasBankPayment) {
        $initialPaymentType = 'bank';
        $bankPayment = $existingPayments->first();
        $initialBankAmount = $bankPayment?->amount ?? 0;
        if ($bankPayment?->account_id) {
            $initialBankAccountId = $bankPayment->account_id;
        }
    } elseif ($hasCashPayment) {
        $initialPaymentType = 'cash';
        $cashPayment = $existingPayments->first();
        $initialCashAmount = $cashPayment?->amount ?? 0;
    }
@endphp

<script id="sale-products-data" type="application/json">{!! json_encode($productData) !!}</script>
<script id="sale-customers-data" type="application/json">{!! json_encode($customerData) !!}</script>
<script id="sale-initial-items" type="application/json">{!! json_encode($initialItems) !!}</script>
<script id="sale-default-cash-account-id" type="application/json">{{ $defaultCashAccount?->id ?? 'null' }}</script>

<datalist id="employees-datalist">
    @foreach ($employeeData as $e)
        <option value="{{ $e['name'] }}">
    @endforeach
</datalist>

<div class="pos-header">
    <div>
        <div class="ttl bn">{{ $sale->exists ? 'বিক্রয় সম্পাদনা' : 'নতুন বিক্রয়' }}</div>
        <div class="ttl en" style="display:none;">{{ $sale->exists ? 'Edit Sale' : 'New Sale' }}</div>
        <div class="meta">
            <span class="bn">ইনভয়েস: </span><span class="en" style="display:none;">Invoice: </span>{{ $sale->invoice_no ?? 'স্বয়ংক্রিয়ভাবে তৈরি হবে' }}
        </div>
    </div>

    @if ($sale->exists)
        <div class="fld">
            <label class="bn">গুদাম</label><label class="en" style="display:none;">Warehouse</label>
            <input type="text" value="{{ $sale->warehouse->name ?? '—' }}" disabled style="background:var(--paper);">
        </div>
        <input type="hidden" name="warehouse_id" value="{{ $sale->warehouse_id }}">
    @else
        <div class="fld">
            <label class="bn">গুদাম</label><label class="en" style="display:none;">Warehouse</label>
            <select onchange="window.location.href = '{{ route('sales.create') }}?warehouse_id=' + this.value;">
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ (string) $warehouseId === (string) $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }} @if($warehouse->is_default) [ডিফল্ট] @endif @if($warehouse->branch) ({{ $warehouse->branch->name }}) @endif
                    </option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
    @endif

    <div class="fld">
        <label class="bn">তারিখ</label><label class="en" style="display:none;">Date</label>
        <input type="date" name="sale_date" id="sale-date-input" value="{{ old('sale_date', optional($sale->sale_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>

    <a href="{{ route('sales.ledger') }}" class="pos-close" title="Back">&times;</a>
</div>

@error('items') <div class="field-error" style="margin:10px 22px 0;">{{ $message }}</div> @enderror

<div class="pos-body pos-cart-body">
    <div class="pos-catalog">
        <h3><span class="bn">বিক্রি করার জন্য পণ্য নির্বাচন করুন</span><span class="en" style="display:none;">Select products to sell</span></h3>
        <div class="cat-search-row">
            <div class="search-inline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#8B978F" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="#8B978F" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" id="catalog-search" placeholder="Search...">
            </div>
            <div class="search-inline" style="max-width:150px;">
                <input type="text" id="catalog-barcode" placeholder="Barcode" class="bn-ph">
            </div>
            <a href="{{ route('products.create') }}" target="_blank" class="cat-icbtn" title="নতুন পণ্য">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </a>
            <button type="button" class="cat-icbtn" id="catalog-refresh" title="Refresh">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 0 1 13.7-5.7L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 0 1-13.7 5.7L4 15.5M4 20v-4.5h4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
        <div id="catalog-list" class="catalog-list"></div>
    </div>

    <div class="pos-cart">
        <div class="cart-head">
            <div class="ct"><span class="bn">পণ্য নির্বাচন করেছেন: (</span><span class="en" style="display:none;">Products selected: (</span><span id="cart-count">0</span>)</div>
            <x-core::button type="button" variant="soft" color="danger" size="sm" icon="trash-2" id="clear-cart-btn">
                <span class="bn">কার্ট খালি করুন</span><span class="en">Clear cart</span>
            </x-core::button>
        </div>
        <div id="cart-list" class="cart-list">
            <div class="cart-empty">
                <div class="cart-empty-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <span class="cart-empty-badge">0</span>
                </div>
                <div class="cart-empty-title">
                    <span class="bn">কার্ট খালি রয়েছে</span>
                    <span class="en" style="display:none;">Your cart is empty</span>
                </div>
                <div class="cart-empty-desc">
                    <span class="bn">বিক্রয় তালিকায় যোগ করতে ক্যাটালগ থেকে পণ্য নির্বাচন করুন</span>
                    <span class="en" style="display:none;">Select items from the catalog to add to invoice</span>
                </div>
                <div class="cart-empty-hint">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span class="bn">ক্যাটালগ থেকে পণ্য যোগ করুন</span>
                    <span class="en" style="display:none;">Choose items from catalog</span>
                </div>
            </div>
        </div>

        <div class="cart-totals" style="display: flex; flex-direction: column; align-items: flex-end;">
            <span id="subtotal-display" style="display:none;">0.00</span>
            <div class="sum-row total" style="display: flex; justify-content: flex-end; align-items: baseline; gap: 8px; width: 100%; border-bottom: none; padding: 4px 0 10px;">
                <span class="sum-label bn" style="color:var(--ink-900); font-weight:700; font-size:15px;">সর্বমোট</span>
                <span class="sum-label en" style="display:none; color:var(--ink-900); font-weight:700; font-size:15px;">Total Amount</span>
                <b id="total-display" style="font-family:'Plus Jakarta Sans','Manrope',sans-serif; font-weight:800; font-size:20px; color:var(--teal-800);">0.00</b>
            </div>

            <div class="cart-cta" style="display: flex; justify-content: flex-end; width: 100%; margin-top: 6px;">
                <x-core::button
                    type="button"
                    color="primary"
                    size="sm"
                    id="make-sale-btn"
                    icon-after="arrow-right"
                    style="flex: 0 0 auto; height: 38px; font-weight: 700; font-size: 13.5px; padding: 0 22px; justify-content: center;"
                >
                    <span class="bn">Make Sale</span><span class="en" style="display:none;">Make Sale</span>
                </x-core::button>
            </div>
        </div>
    </div>
</div>

<div id="hidden-fields-container"></div>
<input type="hidden" name="discount" id="discount-hidden" value="0">
<div id="hidden-payments-container"></div>

<style>
    #sale-date-wrapper .form-group, #drawer-payment-type-group .form-group {
        margin-top: 0 !important;
    }
</style>

<div class="drawer-backdrop" id="confirmPaymentDrawer">
    <div class="drawer">
        <div class="drawer-head">
            <div class="drawer-title" id="drawer-title-bn">Confirm Payment</div>
            <x-core::button
                type="button"
                variant="ghost"
                size="sm"
                id="drawer-close-btn"
                class="drawer-x"
                icon="x"
                title="বন্ধ করুন"
                style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
            />
        </div>

        <div class="tx-section" id="drawer-total-banner"
             style="display:none; text-align:center; font-weight:700; margin-bottom:14px;">
            <span class="bn">মোট প্রদেয় </span><span class="en" style="display:none;">Total Payable </span><span
                id="drawer-total-payable">0.00</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1; min-width: 0;margin-bottom:6px;" id="sale-date-wrapper">
                <x-core::input
                    type="text"
                    id="drawer-date-display"
                    label="বিক্রির তারিখ"
                    label-en="Sale Date"
                    size="sm"
                    style="margin-top: 0 !important"

                />
            </div>

            <div style="flex: 1; min-width: 0;margin-bottom:6px;" id="drawer-payment-type-group">
                <x-core::select
                    id="drawer-payment-type-select"
                    label="পেমেন্টের মাধ্যম"
                    label-en="Payment Method"
                    size="sm"
                >
                    <option value="cash" {{ $initialPaymentType === 'cash' ? 'selected' : '' }}>নগদ (Cash)</option>
                    <option value="bank" {{ $initialPaymentType === 'bank' ? 'selected' : '' }}>ব্যাংক / MFS (Bank)</option>
                    <option value="both" {{ $initialPaymentType === 'both' ? 'selected' : '' }}>উভয় (ক্যাশ + ব্যাংক)</option>
                </x-core::select>
            </div>
        </div>

        <div style="margin-bottom:14px; display:{{ $initialPaymentType === 'cash' ? 'none' : 'block' }};"
             id="drawer-account-group">
            <x-core::select
                id="drawer-account-select"
                label="পেমেন্ট অ্যাকাউন্ট"
                label-en="Payment Account"
                size="sm"
            >
                @forelse ($bankAccounts as $acc)
                    @php
                        $accNum = $acc->account_number ? ' (' . $acc->account_number . ')' : ($acc->bank_name ? ' (' . $acc->bank_name . ')' : '');
                        $defBadge = $acc->is_default ? ' [ডিফল্ট]' : '';
                        $accTitle = \Illuminate\Support\Str::limit($acc->name . $accNum . $defBadge, 45);
                    @endphp
                    <option value="{{ $acc->id }}"
                            data-type="{{ $acc->type }}"
                            title="{{ $acc->display_name }}"
                            {{ (int) $initialBankAccountId === $acc->id ? 'selected' : '' }}>
                        {{ $accTitle }}
                    </option>
                @empty
                    <option value="">কোনো ব্যাংক বা MFS অ্যাকাউন্ট পাওয়া যায়নি</option>
                @endforelse
            </x-core::select>
        </div>

        <div style="margin-bottom:14px;">
            <x-core::textarea
                name="note"
                id="drawer-note-input"
                label="মন্তব্য লিখুন"
                label-en="Note"
                size="sm"
                placeholder="ঐচ্ছিক নোট"
                placeholder-en="Optional note"
                :rows="2"
                value="{{ $initialNote }}"
            />
        </div>

        <div style="margin-bottom:14px;">
            <div style="display: flex; align-items: flex-end; gap: 6px;">
                <div style="flex: 1; min-width: 0;">
                    <x-core::select
                        name="customer_id"
                        id="customer-id-select"
                        label="কাস্টমার"
                        label-en="Customer"
                        size="sm"
                        :no-margin="true"
                    >
                        <option value="">-- ওয়াক-ইন গ্রাহক (Walk-in Customer) --</option>
                        <option value="__create_new__" style="font-weight:700; color:var(--teal-800);">+ নতুন গ্রাহক যোগ করুন</option>
                        @foreach ($customers as $customer)
                            @php
                                $cDue = round((float) ($customer->opening_due ?? 0) + (float) ($customer->sales_sum_due_amount ?? 0), 2);
                            @endphp
                            <option value="{{ $customer->id }}"
                                    data-phone="{{ $customer->phone ?? '' }}"
                                    data-address="{{ $customer->address ?? '' }}"
                                    data-due="{{ $cDue }}"
                                    {{ (string) $initialCustomerId === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} @if($customer->phone) ({{ $customer->phone }}) @endif
                            </option>
                        @endforeach
                    </x-core::select>
                </div>
                <x-core::button
                    type="button"
                    variant="soft"
                    color="primary"
                    size="sm"
                    icon="plus"
                    class="btn-open-quick-customer"
                    title="নতুন গ্রাহক যোগ করুন"
                    style="height: 32px; width: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
                />
            </div>
        </div>

        <input type="hidden" name="customer_name" id="customer-name-input" value="{{ $initialCustomerName }}">

        <div style="display: flex; gap: 10px; margin-bottom:14px;">
            <div style="flex: 1; min-width: 0;">
                <x-core::input
                    type="text"
                    name="customer_phone"
                    id="customer-phone-input"
                    label="কাস্টমার মোবাইল নম্বর"
                    label-en="Customer Mobile Number"
                    size="sm"
                    placeholder="+88 XXXXXXXXXX"
                    value="{{ $initialCustomerPhone }}"
                />
            </div>
            <div style="flex: 1; min-width: 0;">
                <x-core::input
                    type="text"
                    name="customer_address"
                    id="customer-address-input"
                    label="ঠিকানা"
                    label-en="Address"
                    size="sm"
                    placeholder="ঠিকানা"
                    placeholder-en="Address"
                    value="{{ $initialCustomerAddress }}"
                />
            </div>
        </div>

        <div style="margin-top:16px; margin-bottom:10px;">
            <x-core::toggle
                id="custom-invoice-toggle"
                label="কাস্টম ইনভয়েস নম্বর"
                label-en="Custom Invoice Number"
                size="sm"
                color="primary"
                :checked="(bool) ($initialInvoiceNo && $sale->exists)"
            />
        </div>
        <div style="margin-bottom:14px; display:{{ $initialInvoiceNo && $sale->exists ? 'block' : 'none' }};"
             id="custom-invoice-field">
            <x-core::input
                type="text"
                name="invoice_no"
                id="invoice-no-input"
                label="ইনভয়েস নম্বর"
                label-en="Invoice Number"
                size="sm"
                placeholder="INV-0001"
                value="{{ $initialInvoiceNo }}"
            />
        </div>

        <div style="margin-top:16px; margin-bottom:10px;">
            <x-core::toggle
                id="employee-toggle"
                label="কর্মচারীর তথ্য"
                label-en="Employee Info"
                size="sm"
                color="primary"
                :checked="(bool) $initialEmployeeName"
            />
        </div>
        <div id="employee-fields" style="display:{{ $initialEmployeeName ? 'block' : 'none' }};">
            <div style="display: flex; gap: 10px;">
                <div style="margin-bottom:14px; flex: 1; min-width: 0;">
                    <x-core::input
                        type="text"
                        name="employee_name"
                        id="employee-name-input"
                        label="কর্মচারীর নাম"
                        label-en="Employee Name"
                        size="sm"
                        placeholder="কর্মচারীর নাম"
                        placeholder-en="Employee Name"
                        value="{{ $initialEmployeeName }}"
                        list="employees-datalist"
                    />
                </div>
                <div style="margin-bottom:14px; flex: 1; min-width: 0;">
                    <x-core::input
                        type="text"
                        name="employee_phone"
                        id="employee-phone-input"
                        label="কর্মচারীর মোবাইল নম্বর"
                        label-en="Employee Mobile Number"
                        size="sm"
                        placeholder="+88 XXXXXXXXXX"
                        value="{{ $initialEmployeePhone }}"
                    />
                </div>
            </div>
        </div>

        <!-- Financial Calculation Summary Card -->
        <div
            style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:14px; margin-bottom:14px; margin-top:14px;">
            <div
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px;">
                <span class="bn" style="color:var(--ink-700);">সাবটোটাল</span>
                <span class="en" style="color:var(--ink-700); display:none;">Subtotal</span>
                <b style="color:var(--ink-900);">৳<span id="drawer-calc-subtotal">0.00</span></b>
            </div>
            <div
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px;">
                <span class="bn" style="color:var(--ink-700);">ডিস্কাউন্ট</span>
                <span class="en" style="color:var(--ink-700); display:none;">Discount</span>
                <div class="disc-row" style="display:inline-flex; align-items:center; border:1px solid var(--border); border-radius:6px; background:var(--card); overflow:hidden;">
                    <input type="number" step="0.01" min="0" id="discount-raw-input" value="{{ is_numeric($initialDiscount) ? rtrim(rtrim(number_format($initialDiscount, 2, '.', ''), '0'), '.') : 0 }}"
                           style="width:75px; text-align:right; border:none; background:transparent; color:var(--ink-900); padding:4px 8px; font-size:13px; font-family:'Noto Sans Bengali','SolaimanLipi',sans-serif; outline:none;">
                    <select id="discount-type-select" style="border:none; border-left:1px solid var(--border); background:var(--paper); color:var(--ink-700); padding:4px 8px; font-size:12px; outline:none; cursor:pointer;">
                        <option value="flat">৳</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </div>
            <div
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px;">
                <span class="bn" style="color:var(--ink-700);">ডেলিভারী চার্জ</span>
                <span class="en" style="color:var(--ink-700); display:none;">Delivery Charge</span>
                <input type="number" step="0.01" min="0" name="delivery_charge" id="delivery-charge-input" value="{{ rtrim(rtrim(number_format($initialDeliveryCharge, 2, '.', ''), '0'), '.') }}"
                       style="width:90px; text-align:right; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:4px 8px; font-size:13px; font-family:'Noto Sans Bengali','SolaimanLipi',sans-serif; outline:none;">
            </div>
            <div id="customer-due-alert"
                 style="display:none; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px;">
                <span class="bn" style="color:var(--red-600); font-weight:600;">পূর্ববর্তী মোট বকেয়া</span>
                <span class="en" style="color:var(--red-600); font-weight:600; display:none;">Previous Total Due</span>
                <b style="color:var(--red-600);">৳<span id="total_previous_due_display">0.00</span></b>
                <input type="hidden" id="total_previous_due" value="0">
            </div>
            <div
                style="border-top:2px dashed var(--border); margin-top:8px; padding-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:16px; font-weight:700; color:var(--ink-900);">
                <span class="bn">সর্বমোট</span>
                <span class="en" style="display:none;">Grand Total</span>
                <span style="color:#10b981;">৳<span id="grand_total_cost_display">0.00</span></span>
                <input type="hidden" name="grand_total_cost" id="grand_total_cost" value="0">
            </div>
        </div>

        <div style="margin-bottom:14px; display:{{ $initialPaymentType === 'both' ? 'none' : 'block' }};"
             id="drawer-amount-group">
            <x-core::input
                type="number"
                step="0.01"
                min="0"
                id="drawer-amount-input"
                label="টাকার পরিমান"
                label-en="Amount"
                size="sm"
                style="text-align: right;"
                value="{{ $initialPaymentType === 'bank' ? $initialBankAmount : ($initialPaymentType === 'cash' ? $initialCashAmount : $initialAmount) }}"
                :stepper="false"
            />
            <div id="drawer-single-summary"
                style="display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; margin-top: 6px; padding: 6px 10px; background: var(--paper); border: 1px dashed var(--border); border-radius: 6px;">
                <span style="color: var(--ink-700);">
                    <span class="bn">মোট প্রদান: </span><span class="en" style="display:none;">Total Paid: </span>
                    <strong style="color: var(--teal-800);">৳<span id="drawer-single-total-paid">0.00</span></strong>
                </span>
                <span style="color: var(--ink-700);">
                    <span class="bn">বাকি: </span><span class="en" style="display:none;">Remaining Due: </span>
                    <strong style="color: var(--red-600);">৳<span id="drawer-single-remaining-due">0.00</span></strong>
                </span>
            </div>
        </div>

        <div style="margin-bottom:14px; display:{{ $initialPaymentType === 'both' ? 'block' : 'none' }};"
             id="drawer-both-amount-group">
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1; min-width: 0;">
                    <x-core::input
                        type="number"
                        step="0.01"
                        min="0"
                        id="drawer-cash-amount-input"
                        label="ক্যাশ প্রদান"
                        label-en="Cash Paid"
                        size="sm"
                        style="text-align: right;"
                        value="{{ $initialCashAmount }}"
                        :stepper="false"
                    />
                </div>
                <div style="flex: 1; min-width: 0;">
                    <x-core::input
                        type="number"
                        step="0.01"
                        min="0"
                        id="drawer-bank-amount-input"
                        label="ব্যাংক প্রদান"
                        label-en="Bank Paid"
                        size="sm"
                        style="text-align: right;"
                        value="{{ $initialBankAmount }}"
                        :stepper="false"
                    />
                </div>
            </div>
            <div
                style="display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; margin-top: 6px; padding: 6px 10px; background: var(--paper); border: 1px dashed var(--border); border-radius: 6px;">
                <span style="color: var(--ink-700);">
                    <span class="bn">মোট প্রদান: </span><span class="en" style="display:none;">Total Paid: </span>
                    <strong style="color: var(--teal-800);">৳<span id="drawer-both-total-paid">0.00</span></strong>
                </span>
                <span style="color: var(--ink-700);">
                    <span class="bn">বাকি: </span><span class="en" style="display:none;">Remaining Due: </span>
                    <strong style="color: var(--red-600);">৳<span id="drawer-both-remaining-due">0.00</span></strong>
                </span>
            </div>
        </div>

        <div style="margin-top:20px;">
            <x-core::button
                type="button"
                color="primary"
                size="sm"
                id="drawer-save-btn"
                style="width:100%; justify-content:center; height:38px; font-size:13.5px;"
            >
                <span class="bn">সংরক্ষণ করুন</span><span class="en" style="display:none;">Save</span>
            </x-core::button>
        </div>
    </div>
</div>

<script>
(function () {
    const productData = JSON.parse(document.getElementById('sale-products-data').textContent);
    let customerData = JSON.parse(document.getElementById('sale-customers-data').textContent);
    const initialItems = JSON.parse(document.getElementById('sale-initial-items').textContent);

    const warrantyUnitLabels = { day: 'দিন', month: 'মাস', year: 'বছর' };
    const warrantyPresets = [
        { n: 7, u: 'day' }, { n: 30, u: 'day' }, { n: 365, u: 'day' },
        { n: 6, u: 'month' }, { n: 1, u: 'year' }, { n: 2, u: 'year' }, { n: 5, u: 'year' }, { n: 10, u: 'year' },
    ];

    let cart = initialItems.map((row) => ({
        productId: row.product_id,
        qty: parseFloat(row.qty) || 1,
        unitId: row.unitId || (productData[row.product_id]?.baseUnitId ?? ''),
        price: parseFloat(row.price) || 0,
        discountRaw: parseFloat(row.discount) || 0,
        discountType: 'flat',
        barcode: row.barcode || '',
        warrantyExpiresAt: row.warrantyExpiresAt || '',
    }));

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function fmt(n) {
        return (Math.round(n * 100) / 100).toFixed(2);
    }

    /**
     * Conversion factor of a product's unit (how many base units 1 of this unit is
     * worth), used to rescale the reference/selling price whenever the cart
     * line's unit changes -- e.g. switching "pcs" to a "Box" of 4 should default
     * the price to 4x, not silently keep the pcs price against a box quantity.
     */
    function unitFactor(p, unitId) {
        const u = (p.units || []).find((x) => String(x.id) === String(unitId));
        return u ? (u.factor || 1) : 1;
    }

    function lineDiscountAmount(item) {
        const gross = item.qty * item.price;
        const amount = item.discountType === 'percent' ? gross * (item.discountRaw / 100) : item.discountRaw;
        return Math.min(Math.max(amount, 0), gross);
    }

    function lineAmount(item) {
        return (item.qty * item.price) - lineDiscountAmount(item);
    }

    function addDuration(dateStr, amount, unit) {
        const base = dateStr ? new Date(dateStr + 'T00:00:00') : new Date();
        if (unit === 'day') base.setDate(base.getDate() + amount);
        if (unit === 'month') base.setMonth(base.getMonth() + amount);
        if (unit === 'year') base.setFullYear(base.getFullYear() + amount);
        return base.toISOString().slice(0, 10);
    }

    function formatDisplayDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr + 'T00:00:00');
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const day = d.getDate();
        const suffix = (day % 10 === 1 && day !== 11) ? 'st' : (day % 10 === 2 && day !== 12) ? 'nd' : (day % 10 === 3 && day !== 13) ? 'rd' : 'th';
        return months[d.getMonth()] + ' ' + day + suffix + ', ' + d.getFullYear();
    }

    /* ---------------- Catalog (left pane) ---------------- */
    const catalogList = document.getElementById('catalog-list');
    const catalogSearch = document.getElementById('catalog-search');
    const catalogBarcode = document.getElementById('catalog-barcode');

    function cartQtyFor(productId) {
        return cart.filter((c) => c.productId === productId).reduce((sum, c) => sum + c.qty, 0);
    }

    function renderCatalog() {
        const term = catalogSearch.value.trim().toLowerCase();
        const ids = Object.keys(productData).filter((pid) => {
            if (term === '') return true;
            const p = productData[pid];
            return p.name.toLowerCase().includes(term) || (p.sku || '').toLowerCase().includes(term);
        });

        if (ids.length === 0) {
            catalogList.innerHTML = '<div class="catalog-empty">কোনো পণ্য পাওয়া যায়নি</div>';
            return;
        }

        catalogList.innerHTML = ids.map((pid) => {
            const p = productData[pid];
            const count = cartQtyFor(pid);
            return '<div class="catalog-item" data-id="' + pid + '">' +
                '<div class="thumb"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/></svg></div>' +
                '<div class="info"><div class="nm">' + escapeHtml(p.name) + '</div><div class="meta">৳' + fmt(p.price) + ' | স্টক: ' + fmt(p.stock).replace(/\.00$/, '') + '</div></div>' +
                '<div class="add-btn">' +
                    '<button type="button" class="decrease-from-cart-btn" data-id="' + pid + '" title="পরিমাণ হ্রাস করুন"' + (count > 0 ? '' : ' disabled') + '><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg></button>' +
                    (count ? '<span class="count">' + fmt(count).replace(/\.00$/, '') + '</span>' : '') +
                    '<button type="button" class="add-to-cart-btn" data-id="' + pid + '" title="পরিমাণ বৃদ্ধি করুন"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function addToCart(productId) {
        const p = productData[productId];
        if (!p) return;
        const existing = cart.find((c) => c.productId === productId && !c.warrantyExpiresAt);
        if (existing) {
            existing.qty += 1;
        } else {
            let warrantyExpiresAt = '';
            if (p.hasWarranty && p.warrantyDuration && p.warrantyType) {
                warrantyExpiresAt = addDuration(document.getElementById('sale-date-input').value, p.warrantyDuration, p.warrantyType);
            }
            cart.push({
                productId: productId, qty: 1, unitId: p.baseUnitId || '', price: p.price,
                discountRaw: 0, discountType: 'flat', barcode: p.barcode || '', warrantyExpiresAt: warrantyExpiresAt,
            });
        }
        renderAll();
    }

    function decreaseFromCart(productId) {
        let index = -1;
        for (let i = cart.length - 1; i >= 0; i--) {
            if (String(cart[i].productId) === String(productId)) {
                index = i;
                break;
            }
        }
        if (index === -1) return;

        if (cart[index].qty > 1) {
            cart[index].qty -= 1;
        } else {
            cart.splice(index, 1);
        }
        renderAll();
    }

    catalogList.addEventListener('click', (e) => {
        const incBtn = e.target.closest('.add-to-cart-btn');
        if (incBtn) {
            addToCart(incBtn.dataset.id);
            return;
        }
        const decBtn = e.target.closest('.decrease-from-cart-btn');
        if (decBtn && !decBtn.disabled) {
            decreaseFromCart(decBtn.dataset.id);
            return;
        }
    });

    catalogSearch.addEventListener('input', renderCatalog);

    catalogBarcode.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const code = catalogBarcode.value.trim();
        if (code === '') return;
        const match = Object.keys(productData).find((pid) => productData[pid].barcode === code);
        if (match) {
            addToCart(match);
            catalogBarcode.value = '';
        } else {
            toast('এই বারকোডে কোনো পণ্য পাওয়া যায়নি', 'No product found for this barcode');
        }
    });

    document.getElementById('catalog-refresh').addEventListener('click', () => {
        catalogSearch.value = '';
        catalogBarcode.value = '';
        renderCatalog();
    });

    /* ---------------- Cart (right pane) ---------------- */
    const cartList = document.getElementById('cart-list');
    const cartCount = document.getElementById('cart-count');

    function renderCart() {
        cartCount.textContent = cart.length;

        if (cart.length === 0) {
            cartList.innerHTML = '<div class="cart-empty">' +
                '<div class="cart-empty-icon">' +
                    '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>' +
                    '<span class="cart-empty-badge">0</span>' +
                '</div>' +
                '<div class="cart-empty-title">' +
                    '<span class="bn">কার্ট খালি রয়েছে</span>' +
                    '<span class="en" style="display:none;">Your cart is empty</span>' +
                '</div>' +
                '<div class="cart-empty-desc">' +
                    '<span class="bn">বিক্রয় তালিকায় যোগ করতে ক্যাটালগ থেকে পণ্য নির্বাচন করুন</span>' +
                    '<span class="en" style="display:none;">Select items from the catalog to add to invoice</span>' +
                '</div>' +
                '<div class="cart-empty-hint">' +
                    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>' +
                    '<span class="bn">ক্যাটালগ থেকে পণ্য যোগ করুন</span>' +
                    '<span class="en" style="display:none;">Choose items from catalog</span>' +
                '</div>' +
            '</div>';
            return;
        }

        cartList.innerHTML = cart.map((item, i) => {
            const p = productData[item.productId] || { name: 'Unknown', price: 0, units: [] };
            const factor = unitFactor(p, item.unitId);
            const referenceUnitPrice = p.price * factor;
            const hasBarcode = !!item.barcode;
            const hasWarranty = !!item.warrantyExpiresAt;
            const warrantyLabel = hasWarranty ? formatDisplayDate(item.warrantyExpiresAt) : '';
            return '<div class="cart-item" data-index="' + i + '">' +
                '<div class="ci-head">' +
                    '<div class="thumb"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/></svg></div>' +
                    '<div class="nm" title="' + escapeHtml(p.name) + '">' + escapeHtml(p.name) + '</div>' +
                    '<div class="ci-head-popovers">' +
                        '<div class="item-popover barcode-popover">' +
                            '<div class="fld"><label class="bn">বারকোড</label><label class="en" style="display:none;">Barcode</label><input type="text" class="ci-barcode-input" value="' + escapeHtml(item.barcode) + '" placeholder="বারকোড স্ক্যান/লিখুন"></div>' +
                        '</div>' +
                        '<div class="item-popover warranty-popover">' +
                            '<div class="warranty-presets">' +
                                warrantyPresets.map((w) => '<button type="button" class="warranty-preset-btn" data-n="' + w.n + '" data-u="' + w.u + '">' + w.n + ' ' + warrantyUnitLabels[w.u] + '</button>').join('') +
                            '</div>' +
                            '<div class="warranty-custom">' +
                                '<input type="number" min="1" class="ci-warranty-custom-n" placeholder="সংখ্যা">' +
                                '<select class="ci-warranty-custom-u"><option value="day">দিন</option><option value="week">সপ্তাহ</option><option value="month">মাস</option><option value="year">বছর</option></select>' +
                                '<button type="button" class="ci-warranty-set-btn btn btn-outline btn-sm">সেট করুন</button>' +
                            '</div>' +
                            (hasWarranty ? '<div class="warranty-clear"><button type="button" class="ci-warranty-clear-btn">ওয়ারেন্টি মুছে ফেলুন</button></div>' : '') +
                        '</div>' +
                    '</div>' +
                    '<div class="ci-actions">' +
                        '<button type="button" class="barcode-toggle-btn' + (hasBarcode ? ' has-value' : '') + '" title="Barcode"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 5v14M8 5v14M11 5v14M15 5v14M17 5v14M20 5v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button>' +
                        '<button type="button" class="warranty-toggle-btn' + (hasWarranty ? ' has-value' : '') + '" title="ওয়ারেন্টি">' +
                            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' +
                            (hasWarranty ? '<span class="ci-warranty-label bn">ওয়ারেন্টি</span><span class="ci-warranty-label en" style="display:none;">Warranty</span><span class="ci-warranty-date">' + warrantyLabel + '</span>' : '') +
                        '</button>' +
                        '<button type="button" class="ci-remove" title="Remove">&times;</button>' +
                    '</div>' +
                '</div>' +
                '<div class="ci-grid ci-grid-6">' +
                    '<div><label class="bn">পরিমাণ</label><label class="en" style="display:none;">Qty</label><input type="number" step="0.01" min="0.01" class="ci-qty" value="' + item.qty + '"></div>' +
                    '<div><label class="bn">একক</label><label class="en" style="display:none;">Unit</label><select class="ci-unit-select">' +
                        (p.units || []).map((u) => '<option value="' + u.id + '"' + (String(u.id) === String(item.unitId) ? ' selected' : '') + '>' + escapeHtml(u.label) + '</option>').join('') +
                    '</select></div>' +
                    '<div><label class="bn">একক মূল্য</label><label class="en" style="display:none;">Unit Price</label><input type="text" class="ci-unit-price" value="' + fmt(referenceUnitPrice) + '" readonly></div>' +
                    '<div><label class="bn">বিক্রয় মূল্য</label><label class="en" style="display:none;">Selling Price</label><input type="number" step="0.01" min="0" class="ci-price" value="' + item.price + '"></div>' +
                    '<div><label class="bn">ছাড়</label><label class="en" style="display:none;">Discount</label>' +
                        '<div class="disc-row ci-disc-row">' +
                            '<input type="number" step="0.01" min="0" class="ci-discount-raw" value="' + item.discountRaw + '">' +
                            '<select class="ci-discount-type"><option value="flat"' + (item.discountType === 'flat' ? ' selected' : '') + '>৳</option><option value="percent"' + (item.discountType === 'percent' ? ' selected' : '') + '>%</option></select>' +
                        '</div>' +
                    '</div>' +
                    '<div><label class="bn">মোট</label><label class="en" style="display:none;">Amount</label><input type="text" class="ci-total" value="' + fmt(lineAmount(item)) + '" readonly></div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function renderHiddenFields() {
        const container = document.getElementById('hidden-fields-container');
        let html = '';
        cart.forEach((item, i) => {
            html += '<input type="hidden" name="items[' + i + '][product_id]" value="' + item.productId + '">';
            html += '<input type="hidden" name="items[' + i + '][quantity]" value="' + item.qty + '">';
            html += '<input type="hidden" name="items[' + i + '][unit_id]" value="' + (item.unitId || '') + '">';
            html += '<input type="hidden" name="items[' + i + '][unit_price]" value="' + item.price + '">';
            html += '<input type="hidden" name="items[' + i + '][discount]" value="' + fmt(lineDiscountAmount(item)) + '">';
            html += '<input type="hidden" name="items[' + i + '][barcode]" value="' + escapeHtml(item.barcode) + '">';
            html += '<input type="hidden" name="items[' + i + '][warranty_expires_at]" value="' + item.warrantyExpiresAt + '">';
        });
        container.innerHTML = html;
    }

    function subtotal() {
        return cart.reduce((sum, item) => sum + lineAmount(item), 0);
    }

    function discountAmount() {
        const raw = parseFloat($('#discount-raw-input').val()) || 0;
        const type = $('#discount-type-select').val() || 'flat';
        return type === 'percent' ? subtotal() * (raw / 100) : raw;
    }

    function calcGrandTotalCost() {
        const sub = subtotal();
        const discount = Math.min(discountAmount(), sub);
        const deliveryCharge = parseFloat($('#delivery-charge-input').val()) || 0;
        const prevDueVal = parseFloat($('#total_previous_due').val()) || 0;
        return Math.max(0, sub - discount + deliveryCharge + prevDueVal);
    }

    let drawerMode = 'cash';
    let amountManuallyEdited = false;
    let bothCashManuallyEdited = false;
    let bothBankManuallyEdited = false;

    function syncPaymentTypeUI(type) {
        if (!type) {
            type = $('#drawer-payment-type-select').val() || 'cash';
        }
        const $accountGroup = $('#drawer-account-group');
        const $amountGroup = $('#drawer-amount-group');
        const $bothGroup = $('#drawer-both-amount-group');

        if (type === 'cash') {
            $accountGroup.slideUp(120);
            $amountGroup.show();
            $bothGroup.hide();
        } else if (type === 'bank') {
            $accountGroup.slideDown(120);
            $amountGroup.show();
            $bothGroup.hide();
        } else if (type === 'both') {
            $accountGroup.slideDown(120);
            $amountGroup.hide();
            $bothGroup.show();
        }
    }

    function updateBothAmountsSummary() {
        const total = calcGrandTotalCost();
        const cashVal = parseFloat($('#drawer-cash-amount-input').val()) || 0;
        const bankVal = parseFloat($('#drawer-bank-amount-input').val()) || 0;
        const totalPaid = cashVal + bankVal;
        const remainingDue = Math.max(0, total - totalPaid);

        $('#drawer-both-total-paid').text(fmt(totalPaid));
        $('#drawer-both-remaining-due').text(fmt(remainingDue));
    }

    function updateSingleAmountSummary() {
        const total = calcGrandTotalCost();
        const paidVal = parseFloat($('#drawer-amount-input').val()) || 0;
        const remainingDue = Math.max(0, total - paidVal);

        $('#drawer-single-total-paid').text(fmt(paidVal));
        $('#drawer-single-remaining-due').text(fmt(remainingDue));
    }

    function updateGrandTotalCostDisplay() {
        const grandTotal = calcGrandTotalCost();
        const formatted = fmt(grandTotal);

        $('#grand_total_cost').val(formatted);
        $('#grand_total_cost_display').text(formatted);
        $('#drawer-calc-subtotal').text(fmt(subtotal()));
        $('#drawer-total-payable').text(formatted);

        if (drawerMode === 'cash') {
            if (!amountManuallyEdited) {
                $('#drawer-amount-input').val(formatted);
            }
            if (!bothCashManuallyEdited && !bothBankManuallyEdited) {
                $('#drawer-cash-amount-input').val(formatted);
                $('#drawer-bank-amount-input').val('0.00');
            } else if (!bothBankManuallyEdited && bothCashManuallyEdited) {
                const cashVal = parseFloat($('#drawer-cash-amount-input').val()) || 0;
                $('#drawer-bank-amount-input').val(fmt(Math.max(0, grandTotal - cashVal)));
            } else if (!bothCashManuallyEdited && bothBankManuallyEdited) {
                const bankVal = parseFloat($('#drawer-bank-amount-input').val()) || 0;
                $('#drawer-cash-amount-input').val(fmt(Math.max(0, grandTotal - bankVal)));
            }
        }

        updateBothAmountsSummary();
        updateSingleAmountSummary();
    }

    function recalcGrand() {
        const sub = subtotal();
        const discount = Math.min(discountAmount(), sub);
        const deliveryCharge = parseFloat($('#delivery-charge-input').val()) || 0;
        const total = Math.max(sub - discount + deliveryCharge, 0);

        $('#subtotal-display').text(fmt(sub));
        $('#total-display').text(fmt(total));
        $('#discount-hidden').val(fmt(discount));

        updateGrandTotalCostDisplay();

        return total;
    }

    function renderAll() {
        renderCatalog();
        renderCart();
        renderHiddenFields();
        recalcGrand();
    }

    cartList.addEventListener('click', (e) => {
        const row = e.target.closest('.cart-item');
        if (!row) return;
        const index = parseInt(row.dataset.index, 10);
        const item = cart[index];

        if (e.target.closest('.ci-remove')) {
            cart.splice(index, 1);
            renderAll();
            return;
        }
        if (e.target.closest('.barcode-toggle-btn')) {
            const btn = e.target.closest('.barcode-toggle-btn');
            const warBtn = row.querySelector('.warranty-toggle-btn');
            const bcPopover = row.querySelector('.barcode-popover');
            const isOpen = bcPopover.classList.toggle('open');
            btn.classList.toggle('active', isOpen);
            row.querySelector('.warranty-popover').classList.remove('open');
            if (warBtn) warBtn.classList.remove('active');
            if (isOpen) {
                const input = bcPopover.querySelector('.ci-barcode-input');
                if (input) setTimeout(() => input.focus(), 50);
            }
            return;
        }
        if (e.target.closest('.warranty-toggle-btn')) {
            const btn = e.target.closest('.warranty-toggle-btn');
            const bcBtn = row.querySelector('.barcode-toggle-btn');
            const warPopover = row.querySelector('.warranty-popover');
            const isOpen = warPopover.classList.toggle('open');
            btn.classList.toggle('active', isOpen);
            row.querySelector('.barcode-popover').classList.remove('open');
            if (bcBtn) bcBtn.classList.remove('active');
            return;
        }
        const presetBtn = e.target.closest('.warranty-preset-btn');
        if (presetBtn && item) {
            item.warrantyExpiresAt = addDuration(document.getElementById('sale-date-input').value, parseInt(presetBtn.dataset.n, 10), presetBtn.dataset.u);
            renderAll();
            return;
        }
        if (e.target.closest('.ci-warranty-set-btn') && item) {
            const n = parseInt(row.querySelector('.ci-warranty-custom-n').value, 10) || 0;
            const u = row.querySelector('.ci-warranty-custom-u').value;
            if (n > 0) {
                item.warrantyExpiresAt = addDuration(document.getElementById('sale-date-input').value, n, u);
                renderAll();
            }
            return;
        }
        if (e.target.closest('.ci-warranty-clear-btn') && item) {
            item.warrantyExpiresAt = '';
            renderAll();
            return;
        }
    });

    cartList.addEventListener('input', (e) => {
        const row = e.target.closest('.cart-item');
        if (!row) return;
        const index = parseInt(row.dataset.index, 10);
        const item = cart[index];
        if (!item) return;

        if (e.target.classList.contains('ci-qty')) item.qty = parseFloat(e.target.value) || 0;
        if (e.target.classList.contains('ci-price')) item.price = parseFloat(e.target.value) || 0;
        if (e.target.classList.contains('ci-discount-raw')) item.discountRaw = parseFloat(e.target.value) || 0;
        if (e.target.classList.contains('ci-discount-type')) item.discountType = e.target.value;
        if (e.target.classList.contains('ci-barcode-input')) {
            item.barcode = e.target.value;
            const btn = row.querySelector('.barcode-toggle-btn');
            if (btn) btn.classList.toggle('has-value', !!item.barcode);
        }

        if (e.target.classList.contains('ci-unit-select')) {
            const p = productData[item.productId];
            const factor = unitFactor(p, e.target.value);
            item.unitId = e.target.value;
            // Reset the selling price to a sensible default for the newly chosen
            // unit (base sale price x factor) so Amount stays meaningful -- the
            // shop admin can still adjust it afterwards for a wholesale price.
            item.price = Math.round(p.price * factor * 100) / 100;
            renderAll();
            return;
        }

        if (
            e.target.classList.contains('ci-qty') || e.target.classList.contains('ci-price')
            || e.target.classList.contains('ci-discount-raw') || e.target.classList.contains('ci-discount-type')
        ) {
            row.querySelector('.ci-total').value = fmt(lineAmount(item));
        }
        renderHiddenFields();
        recalcGrand();
    });

    $(document).on('click', '#clear-cart-btn', function () {
        if (cart.length === 0) return;
        cart = [];
        renderAll();
    });

    $(document).on('input change', '#discount-raw-input, #discount-type-select, #delivery-charge-input, #total_previous_due', function () {
        recalcGrand();
    });

    $(document).on('input', '#drawer-amount-input', function () {
        amountManuallyEdited = true;
        updateSingleAmountSummary();
    });

    $(document).on('input', '#drawer-cash-amount-input', function () {
        bothCashManuallyEdited = true;
        const total = calcGrandTotalCost();
        const cashVal = parseFloat($(this).val()) || 0;
        if (!bothBankManuallyEdited && drawerMode === 'cash') {
            const autoBank = Math.max(0, total - cashVal);
            $('#drawer-bank-amount-input').val(fmt(autoBank));
        }
        updateBothAmountsSummary();
    });

    $(document).on('input', '#drawer-bank-amount-input', function () {
        bothBankManuallyEdited = true;
        const total = calcGrandTotalCost();
        const bankVal = parseFloat($(this).val()) || 0;
        if (!bothCashManuallyEdited && drawerMode === 'cash') {
            const autoCash = Math.max(0, total - bankVal);
            $('#drawer-cash-amount-input').val(fmt(autoCash));
        }
        updateBothAmountsSummary();
    });

    $(document).on('change', '#drawer-payment-type-select', function () {
        const type = $(this).val();
        syncPaymentTypeUI(type);
        const total = calcGrandTotalCost();
        if (drawerMode === 'cash') {
            if (type === 'both') {
                if (!bothCashManuallyEdited && !bothBankManuallyEdited) {
                    $('#drawer-cash-amount-input').val(fmt(total));
                    $('#drawer-bank-amount-input').val('0.00');
                    updateBothAmountsSummary();
                }
            } else {
                if (!amountManuallyEdited) {
                    $('#drawer-amount-input').val(fmt(total));
                }
                updateSingleAmountSummary();
            }
        }
    });

    /* ---------------- Confirm payment drawer ---------------- */
    function openDrawer(mode = 'cash') {
        if (cart.length === 0) {
            toast('কার্টে অন্তত একটি পণ্য যোগ করুন', 'Add at least one product to the cart');
            return;
        }
        drawerMode = mode;
        amountManuallyEdited = false;
        bothCashManuallyEdited = false;
        bothBankManuallyEdited = false;
        recalcGrand();

        $('#drawer-date-display').val($('#sale-date-input').val());

        const currentType = $('#drawer-payment-type-select').val() || 'cash';
        syncPaymentTypeUI(currentType);

        const currentCustomerId = $('#customer-id-select').val();
        updateCustomerDueNotice(currentCustomerId);

        const total = calcGrandTotalCost();

        if (mode === 'due') {
            $('#drawer-amount-input').val('0.00');
            $('#drawer-cash-amount-input').val('0.00');
            $('#drawer-bank-amount-input').val('0.00');
            amountManuallyEdited = true;
            bothCashManuallyEdited = true;
            bothBankManuallyEdited = true;
        } else {
            $('#drawer-amount-input').val(fmt(total));
            $('#drawer-cash-amount-input').val(fmt(total));
            $('#drawer-bank-amount-input').val('0.00');
        }

        updateGrandTotalCostDisplay();
        $('#confirmPaymentDrawer').addClass('open');
    }

    $(document).on('click', '#make-sale-btn, #open-cash-btn', function () {
        openDrawer('cash');
    });

    $(document).on('click', '#open-due-btn', function () {
        openDrawer('due');
    });

    $(document).on('click', '#drawer-close-btn', function () {
        $('#confirmPaymentDrawer').removeClass('open');
    });

    $(document).on('click', '#confirmPaymentDrawer', function (e) {
        if ($(e.target).is('#confirmPaymentDrawer')) {
            $(this).removeClass('open');
        }
    });

    $(document).on('change', '#custom-invoice-toggle', function () {
        if (this.checked) {
            $('#custom-invoice-field').show();
        } else {
            $('#custom-invoice-field').hide();
            $('#invoice-no-input').val('');
        }
    });

    $(document).on('change', '#employee-toggle', function () {
        if (this.checked) {
            $('#employee-fields').show();
        } else {
            $('#employee-fields').hide();
            $('#employee-name-input').val('');
            $('#employee-phone-input').val('');
        }
    });

    /* ---------------- Quick Add Customer Modal ---------------- */
    $(function () {
        const $quickModal = $('#quickCustomerModal');
        if ($quickModal.length && !$quickModal.parent().is('body')) {
            $('body').append($quickModal);
        }
    });

    let lastCustomerId = $('#customer-id-select').val() || '';

    function openQuickCustomerModal() {
        const $modal = $('#quickCustomerModal');
        if ($modal.length && !$modal.parent().is('body')) {
            $('body').append($modal);
        }
        const $form = $('#quick_customer_form');
        if ($form.length && $form[0]) {
            $form[0].reset();
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.dynamic-error').remove();
            $('#quick_customer_opening_due').val('0');
        }
        openModal('quickCustomerModal');
        setTimeout(() => $('#quick_customer_name').focus(), 150);
    }

    function revertCustomerSelectIfNeeded() {
        if ($('#customer-id-select').val() === '__create_new__') {
            $('#customer-id-select').val(lastCustomerId || '');
        }
    }

    $(document).on('click', '.btn-open-quick-customer', function (e) {
        e.preventDefault();
        openQuickCustomerModal();
    });

    $(document).on('click', '#quickCustomerModal .modal-close-btn', function (e) {
        e.preventDefault();
        closeModal('quickCustomerModal');
        revertCustomerSelectIfNeeded();
    });

    $(document).on('click', '#quickCustomerModal', function (e) {
        if ($(e.target).hasClass('modal-backdrop')) {
            closeModal('quickCustomerModal');
            revertCustomerSelectIfNeeded();
        }
    });

    function submitQuickCustomer() {
        const $form = $('#quick_customer_form');
        const $btn = $('#btn-save-quick-customer');
        const url = $form.attr('action') || '{{ route('customers.store') }}';

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.dynamic-error').remove();
        $btn.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                $btn.prop('disabled', false);
                if (response.success && response.customer) {
                    const c = response.customer;
                    const due = parseFloat(c.opening_due) || 0;
                    customerData.push({
                        id: c.id,
                        name: c.name,
                        phone: c.phone || '',
                        address: c.address || '',
                        due: due
                    });

                    const phoneTxt = c.phone ? ` (${c.phone})` : '';
                    const $newOption = $('<option></option>')
                        .val(c.id)
                        .attr('data-due', due)
                        .attr('data-phone', c.phone || '')
                        .attr('data-address', c.address || '')
                        .text(c.name + phoneTxt);
                    $('#customer-id-select').append($newOption);
                    $('#customer-id-select').val(c.id);
                    lastCustomerId = c.id;

                    $('#customer-name-input').val(c.name);
                    $('#customer-phone-input').val(c.phone || '');
                    $('#customer-address-input').val(c.address || '');

                    $('#customer-id-select').trigger('change');

                    closeModal('quickCustomerModal');
                    if ($form.length && $form[0]) {
                        $form[0].reset();
                    }
                    toast(response.message || 'গ্রাহক সফলভাবে যোগ করা হয়েছে', 'Customer created successfully');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        const $field = $form.find('[name="' + field + '"]');
                        if ($field.length) {
                            $field.addClass('is-invalid');
                            const $err = $('<div class="field-error dynamic-error" style="color:var(--red-600); font-size:11.5px; margin-top:3px; font-weight:600;">' + messages[0] + '</div>');
                            $field.closest('.form-group, .field, div').append($err);
                        }
                    });
                } else {
                    toast('গ্রাহক যোগ করতে সমস্যা হয়েছে', 'Failed to create customer');
                }
            }
        });
    }

    $(document).on('click', '#btn-save-quick-customer', function (e) {
        e.preventDefault();
        e.stopPropagation();
        submitQuickCustomer();
        return false;
    });

    $(document).on('submit', '#quick_customer_form', function (e) {
        e.preventDefault();
        e.stopPropagation();
        submitQuickCustomer();
        return false;
    });

    $(document).on('keydown', '#quick_customer_form input', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            submitQuickCustomer();
            return false;
        }
    });

    function updateCustomerDueNotice(customerId) {
        const $alertEl = $('#customer-due-alert');
        const cid = parseInt(customerId, 10);
        if (!cid) {
            $alertEl.hide();
            $('#total_previous_due').val('0');
            $('#total_previous_due_display').text('0.00');
            updateGrandTotalCostDisplay();
            return;
        }

        const match = customerData.find((c) => c.id === cid);
        let due = 0;
        if (match && match.due !== undefined) {
            due = parseFloat(match.due) || 0;
        } else {
            const selectedOpt = $('#customer-id-select option[value="' + cid + '"]');
            if (selectedOpt.length && selectedOpt.attr('data-due') !== undefined) {
                due = parseFloat(selectedOpt.attr('data-due')) || 0;
            }
        }

        const formattedDue = fmt(due);
        $('#total_previous_due').val(due > 0 ? due : 0);
        $('#total_previous_due_display').text(formattedDue);

        if (due > 0) {
            $alertEl.css('display', 'flex');
        } else {
            $alertEl.hide();
        }

        updateGrandTotalCostDisplay();
    }

    $(document).on('change', '#customer-id-select', function () {
        const val = $(this).val();
        if (val === '__create_new__') {
            openQuickCustomerModal();
            return;
        }
        lastCustomerId = val;
        const cid = parseInt(val, 10);
        const match = customerData.find((c) => c.id === cid);
        const $selectedOpt = $(this).find('option:selected');

        const name = match ? match.name : ($selectedOpt.length && cid ? $selectedOpt.text().split(' (')[0].trim() : '');
        const phone = match ? (match.phone || '') : ($selectedOpt.data('phone') || '');
        const address = match ? (match.address || '') : ($selectedOpt.data('address') || '');

        $('#customer-name-input').val(name);
        $('#customer-phone-input').val(phone);
        $('#customer-address-input').val(address);

        updateCustomerDueNotice(val);
    });

    $(document).on('click', '#drawer-save-btn', function () {
        const total = calcGrandTotalCost();
        const paymentType = $('#drawer-payment-type-select').val() || 'cash';
        const defaultCashAccountId = document.getElementById('sale-default-cash-account-id') ? JSON.parse(document.getElementById('sale-default-cash-account-id').textContent) : null;

        const accountSelect = document.getElementById('drawer-account-select');
        const selectedOpt = accountSelect && accountSelect.selectedIndex >= 0 ? accountSelect.options[accountSelect.selectedIndex] : null;
        const selectedBankAccountId = selectedOpt ? selectedOpt.value : null;
        const selectedAccountType = selectedOpt ? selectedOpt.getAttribute('data-type') : 'bank';
        const bankMethod = selectedAccountType === 'mfs' ? 'mobile_banking' : 'bank';

        let paymentsToSubmit = [];

        if (paymentType === 'cash') {
            let amount = parseFloat($('#drawer-amount-input').val()) || 0;
            if (drawerMode === 'cash' && amount <= 0 && !amountManuallyEdited) amount = total;
            amount = Math.min(Math.max(amount, 0), total);
            if (amount > 0) {
                paymentsToSubmit.push({
                    account_id: defaultCashAccountId || '',
                    method: 'cash',
                    amount: fmt(amount),
                });
            }
        } else if (paymentType === 'bank') {
            let amount = parseFloat($('#drawer-amount-input').val()) || 0;
            if (drawerMode === 'cash' && amount <= 0 && !amountManuallyEdited) amount = total;
            amount = Math.min(Math.max(amount, 0), total);
            if (amount > 0) {
                paymentsToSubmit.push({
                    account_id: selectedBankAccountId || '',
                    method: bankMethod,
                    amount: fmt(amount),
                });
            }
        } else if (paymentType === 'both') {
            let cashAmount = parseFloat($('#drawer-cash-amount-input').val()) || 0;
            let bankAmount = parseFloat($('#drawer-bank-amount-input').val()) || 0;
            cashAmount = Math.max(0, cashAmount);
            bankAmount = Math.max(0, bankAmount);

            if (cashAmount + bankAmount > total) {
                if (cashAmount > total) {
                    cashAmount = total;
                    bankAmount = 0;
                } else {
                    bankAmount = Math.max(0, total - cashAmount);
                }
            }

            if (cashAmount > 0) {
                paymentsToSubmit.push({
                    account_id: defaultCashAccountId || '',
                    method: 'cash',
                    amount: fmt(cashAmount),
                });
            }
            if (bankAmount > 0) {
                paymentsToSubmit.push({
                    account_id: selectedBankAccountId || '',
                    method: bankMethod,
                    amount: fmt(bankAmount),
                });
            }
        }

        const $hiddenPayments = $('#hidden-payments-container');
        $hiddenPayments.empty();

        paymentsToSubmit.forEach((p, idx) => {
            if (p.account_id) {
                $hiddenPayments.append(`<input type="hidden" name="payments[${idx}][account_id]" value="${escapeHtml(p.account_id)}">`);
            }
            $hiddenPayments.append(`<input type="hidden" name="payments[${idx}][method]" value="${escapeHtml(p.method)}">`);
            $hiddenPayments.append(`<input type="hidden" name="payments[${idx}][amount]" value="${p.amount}">`);
        });

        renderHiddenFields();
        document.getElementById('sale-form').submit();
    });

    syncPaymentTypeUI();
    renderAll();
    updateCustomerDueNotice($('#customer-id-select').val());
})();
</script>
