@php
    $productData = [];
    foreach ($products as $product) {
        $baseUnit = $product->units->firstWhere('pivot.is_base', true) ?? $product->units->first();

        $productData[$product->id] = [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) $product->purchase_price,
            'salePrice' => (float) $product->sale_price,
            'stock' => (float) ($product->batches_sum_quantity ?? 0),
            'barcode' => $product->barcode,
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

    $supplierData = $suppliers->map(function ($s) {
        $due = round((float) ($s->opening_due ?? 0) + (float) ($s->purchases_sum_due_amount ?? 0), 2);
        return [
            'id' => $s->id,
            'name' => $s->name,
            'phone' => $s->phone,
            'address' => $s->address,
            'due' => $due,
        ];
    })->values();

    $employeeData = $employees->map(fn ($e) => ['name' => $e->name, 'phone' => $e->phone])->values();

    $initialItems = old('items', $purchase->exists
        ? $purchase->items->map(fn ($item) => [
            'product_id' => (int) $item->product_id,
            'qty' => rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.'),
            'received_qty' => rtrim(rtrim(number_format($item->received_qty ?? 0, 2, '.', ''), '0'), '.'),
            'price' => rtrim(rtrim(number_format($item->purchase_price, 2, '.', ''), '0'), '.'),
            'salePrice' => rtrim(rtrim(number_format($item->product->sale_price ?? 0, 2, '.', ''), '0'), '.'),
            'unitId' => null,
            'batchNo' => $item->batch_no,
            'mfgDate' => optional($item->mfg_date)->format('Y-m-d'),
            'expiryDate' => optional($item->expiry_date)->format('Y-m-d'),
            'barcode' => $item->product->barcode ?? '',
        ])->values()->toArray()
        : []
    );

    $initialDiscount = old('discount', $purchase->discount ?? 0);
    $initialDeliveryCharge = old('delivery_charge', $purchase->delivery_charge ?? 0);
    $initialNote = old('note', $purchase->note);
    $initialInvoiceNo = old('invoice_no', $purchase->exists ? $purchase->invoice_no : '');
    $initialSupplierId = old('supplier_id', $purchase->supplier_id);
    $initialSupplierName = old('supplier_name', $purchase->supplier->name ?? '');
    $initialSupplierPhone = old('supplier_phone', $purchase->supplier->phone ?? '');
    $initialSupplierAddress = old('supplier_address', $purchase->supplier->address ?? '');
    $initialEmployeeName = old('employee_name', $purchase->employee_name);
    $initialEmployeePhone = old('employee_phone', $purchase->employee_phone);
    $initialAmount = $purchase->exists ? $purchase->paid_amount : 0;
    $defaultAccount = $accounts->firstWhere('is_default', true) ?? $accounts->first();
    $initialAccountId = old('payments.0.account_id', $purchase->exists ? optional($purchase->payments->first())->account_id : $defaultAccount?->id);
    $initialPaymentMethod = old('payments.0.method', $purchase->exists ? optional($purchase->payments->first())->method : 'cash');
    $initialDoNumber = old('do_number', $purchase->do_number ?? '');
    $initialDoDate = old('do_date', isset($purchase->do_date) ? (is_string($purchase->do_date) ? $purchase->do_date : optional($purchase->do_date)->format('Y-m-d')) : '');
    $initialVehicleNumber = old('vehicle_number', $purchase->vehicle_number ?? '');
    $initialDeliveryPersonName = old('delivery_person_name', $purchase->delivery_person_name ?? '');
@endphp

<script id="purchase-products-data" type="application/json">{!! json_encode($productData) !!}</script>
<script id="purchase-suppliers-data" type="application/json">{!! json_encode($supplierData) !!}</script>
<script id="purchase-employees-data" type="application/json">{!! json_encode($employeeData) !!}</script>
<script id="purchase-initial-items" type="application/json">{!! json_encode($initialItems) !!}</script>

<datalist id="employees-datalist">
    @foreach ($employeeData as $e)
        <option value="{{ $e['name'] }}">
    @endforeach
</datalist>

<div class="pos-header">
    <div>
        <div class="ttl bn">{{ $purchase->exists ? 'ক্রয় সম্পাদনা' : 'নতুন ক্রয়' }}</div>
        <div class="ttl en" style="display:none;">{{ $purchase->exists ? 'Edit Purchase' : 'New Purchase' }}</div>
        <div class="meta">
            <span class="bn">ইনভয়েস: </span><span class="en" style="display:none;">Invoice: </span>{{ $purchase->invoice_no ?? 'স্বয়ংক্রিয়ভাবে তৈরি হবে' }}
        </div>
    </div>


    <div style="width: 190px; flex-shrink: 0;">
        <x-core::select
            name="warehouse_id"
            label="গুদাম"
            label-en="Warehouse"
            size="sm"
            required
            :no-margin="true"
        >
            <option value="">-- নির্বাচন করুন --</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" {{ (string) old('warehouse_id', $purchase->warehouse_id ?? optional($warehouses->first())->id) === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} @if($warehouse->branch) ({{ $warehouse->branch->name }}) @endif</option>
            @endforeach
        </x-core::select>
    </div>

    <div style="width: 145px; flex-shrink: 0;">
        <x-core::input
            type="date"
            name="purchase_date"
            id="purchase-date-input"
            label="তারিখ"
            label-en="Date"
            size="sm"
            required
            :no-margin="true"
            value="{{ old('purchase_date', optional($purchase->purchase_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
        />
    </div>

    <div style="width: 140px; flex-shrink: 0;">
        <x-core::input
            type="text"
            name="do_number"
            id="do-number-input"
            label="ডিও নম্বর"
            label-en="D.O. Number"
            size="sm"
            :no-margin="true"
            placeholder="ডিও নম্বর"
            placeholder-en="D.O. No"
            value="{{ $initialDoNumber }}"
            required
        />
    </div>

    <div style="width: 145px; flex-shrink: 0;">
        <x-core::input
            type="date"
            name="do_date"
            id="do-date-input"
            label="ডিও তারিখ"
            label-en="D.O. Date"
            size="sm"
            :no-margin="true"
            value="{{ $initialDoDate }}"
            required
        />
    </div>

    <div style="width: 155px; flex-shrink: 0;">
        <x-core::input
            type="text"
            name="vehicle_number"
            id="vehicle-number-input"
            label="গাড়ির নম্বর"
            label-en="Vehicle Number"
            size="sm"
            :no-margin="true"
            placeholder="গাড়ির নম্বর"
            placeholder-en="Vehicle No"
            value="{{ $initialVehicleNumber }}"
        />
    </div>
    <div style="width: 155px; flex-shrink: 0;">
        <x-core::input
            type="text"
            name="delivery_person_name"
            id="delivery-person-name-input"
            label="ডেলিভারি ব্যক্তির নাম"
            label-en="Delivery Person Name"
            size="sm"
            :no-margin="true"
            placeholder="েলিভারি ব্যক্তির নাম"
            placeholder-en="Delivery Person Name"
            value="{{ $initialDeliveryPersonName }}"
        />
    </div>
</div>

@error('items') <div class="field-error" style="margin:10px 22px 0;">{{ $message }}</div> @enderror

<div class="pos-body pos-cart-body">
    <div class="pos-catalog">
        <h3><span class="bn">ক্রয় করার জন্য পণ্য নির্বাচন করুন</span><span class="en" style="display:none;">Select products to purchase</span></h3>
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
                    <span class="bn">ক্রয় তালিকায় যোগ করতে ক্যাটালগ থেকে পণ্য নির্বাচন করুন</span>
                    <span class="en" style="display:none;">Select items from the catalog to add to purchase</span>
                </div>
                <div class="cart-empty-hint">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span class="bn">ক্যাটালগ থেকে পণ্য যোগ করুন</span>
                    <span class="en" style="display:none;">Choose items from catalog</span>
                </div>
            </div>
        </div>

        <div class="cart-totals">
            <span id="subtotal-display" style="display:none;">0.00</span>
            <div class="sum-row total">
                <span class="sum-label bn">সর্বমোট</span><span class="sum-label en" style="display:none;">Total Amount</span>
                <b id="total-display">0.00</b>
            </div>

            <div class="cart-cta">
                <button type="button" class="cta-cash" id="open-cash-btn">
                    <span class="bn">নগদ টাকা</span><span class="en">Cash</span> →
                </button>
                <button type="button" class="cta-due" id="open-due-btn">
                    <span class="bn">বাকি</span><span class="en">Due</span> →
                </button>
            </div>
        </div>
    </div>
</div>

<div id="hidden-fields-container"></div>
<input type="hidden" name="discount" id="discount-hidden" value="0">
<input type="hidden" name="delivery_charge" id="delivery-charge-hidden" value="0">
<input type="hidden" name="payments[0][account_id]" id="payment-account-hidden" value="{{ $initialAccountId }}">
<input type="hidden" name="payments[0][method]" id="payment-method-hidden" value="{{ $initialPaymentMethod }}">
<input type="hidden" name="payments[0][amount]" id="payment-amount-hidden" value="0">

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

        <div class="tx-section" id="drawer-total-banner" style="display:none; text-align:center; font-weight:700; margin-bottom:14px;">
            <span class="bn">মোট প্রদেয় </span><span class="en" style="display:none;">Total Payable </span><span id="drawer-total-payable">0.00</span>
        </div>

        <div style="margin-bottom:14px;">
            <x-core::input
                type="text"
                id="drawer-date-display"
                label="ক্রয়ের তারিখ"
                label-en="Purchase Date"
                size="sm"
                :readonly="true"
            />
        </div>

        <div style="margin-bottom:14px;" id="drawer-account-group">
            <x-core::select
                id="drawer-account-select"
                label="পেমেন্ট অ্যাকাউন্ট"
                label-en="Payment Account"
                size="sm"
            >
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}" data-type="{{ $acc->type }}" {{ (int) $initialAccountId === $acc->id ? 'selected' : '' }}>
                        {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) @if($acc->is_default) [ডিফল্ট] @endif
                    </option>
                @endforeach
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
                        name="supplier_id"
                        id="supplier-id-select"
                        label="সাপ্লায়ার"
                        label-en="Supplier"
                        size="sm"
                        :no-margin="true"
                    >
                        <option value="">-- নির্বাচন করুন --</option>
                        <option value="__create_new__" style="font-weight:700; color:var(--teal-800);">+ নতুন সরবরাহকারী যোগ করুন</option>
                        @foreach ($suppliers as $supplier)
                            @php
                                $sDue = round((float) ($supplier->opening_due ?? 0) + (float) ($supplier->purchases_sum_due_amount ?? 0), 2);
                            @endphp
                            <option value="{{ $supplier->id }}" data-due="{{ $sDue }}" {{ (string) old('supplier_id', $purchase->supplier_id ?? '') === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->name }} @if($supplier->phone) ({{ $supplier->phone }}) @endif</option>
                        @endforeach
                    </x-core::select>
                </div>
                <x-core::button
                    type="button"
                    variant="soft"
                    color="primary"
                    size="sm"
                    icon="plus"
                    class="btn-open-quick-supplier"
                    title="নতুন সরবরাহকারী যোগ করুন"
                    style="height: 32px; width: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
                />
            </div>

        </div>


        <div style="margin-bottom:14px;">
            <x-core::input
                type="text"
                name="supplier_address"
                id="supplier-address-input"
                label="ঠিকানা"
                label-en="Address"
                size="sm"
                placeholder="ঠিকানা"
                placeholder-en="Address"
                value="{{ $initialSupplierAddress }}"
            />
        </div>

        <div style="margin-top:16px; margin-bottom:10px;">
            <x-core::toggle
                id="custom-invoice-toggle"
                label="কাস্টম ইনভয়েস নম্বর"
                label-en="Custom Invoice Number"
                size="sm"
                color="primary"
                :checked="(bool) ($initialInvoiceNo && $purchase->exists)"
            />
        </div>
        <div style="margin-bottom:14px; display:{{ $initialInvoiceNo && $purchase->exists ? 'block' : 'none' }};" id="custom-invoice-field">
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
            <div style="margin-bottom:14px;">
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
            <div style="margin-bottom:14px;">
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

        <div style="display: flex; gap: 10px; margin-bottom: 6px;">
            <div style="flex: 1; min-width: 0;">
                <x-core::input
                    type="number"
                    step="0.01"
                    min="0"
                    id="drawer-amount-input"/>
                    label="টাকার পরিমান"
                    label-en="Amount"
                    size="sm"
                    value="{{ $initialAmount }}"
                />
            </div>
            <div style="flex: 1; min-width: 0; display: none;" id="supplier-due-alert">
                <x-core::input
                    type="number"
                    step="0.01"
                    min="0"
                    id="total_previous_due"
                    label="পূর্ববর্তী মোট বকেয়া"
                    label-en="Previous Total Due"
                    size="sm"
                    value="0"
                    :readonly="true"
                />
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 6px;">
            <div style="flex: 1; min-width: 0;">
                <x-core::input
                    type="number"
                    step="0.01"
                    id="adjustment_cost"
                    label="সামঞ্জস্য বিধানের ব্যয়"
                    label-en="Adjustment Cost"
                    size="sm"
                    value="0"
                />
            </div>
            <div style="flex: 1; min-width: 0;">
                <x-core::input
                    type="number"
                    step="0.01"
                    min="0"
                    id="transportation_cost"
                    label="পরিবহন খরচ"
                    label-en="Transportation Cost"
                    size="sm"
                    value="0"
                />
            </div>
        </div>
        <div style="margin-bottom:14px;">
            <x-core::input
                type="number"
                step="0.01"
                min="0"
                id="grand_total_cost"
                label="সর্বমোট খরচ"
                label-en="Grand Total Cost"
                size="sm"
                value="0"
                :readonly="true"
            />
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
    const productData = JSON.parse(document.getElementById('purchase-products-data').textContent);
    const supplierData = JSON.parse(document.getElementById('purchase-suppliers-data').textContent);
    const initialItems = JSON.parse(document.getElementById('purchase-initial-items').textContent);

    let cart = initialItems.map((row) => ({
        productId: row.product_id,
        qty: parseFloat(row.qty) || 1,
        receivedQty: parseFloat(row.received_qty ?? row.receivedQty) || 0,
        price: parseFloat(row.price) || 0,
        salePrice: parseFloat(row.salePrice) || 0,
        unitId: row.unitId || (productData[row.product_id]?.baseUnitId ?? ''),
        batchNo: row.batchNo || '',
        mfgDate: row.mfgDate || '',
        expiryDate: row.expiryDate || '',
        barcode: row.barcode || '',
    }));

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function fmt(n) {
        return (Math.round(n * 100) / 100).toFixed(2);
    }

    /**
     * Conversion factor of a product's unit (how many base units 1 of this unit is
     * worth), used to rescale the per-unit price whenever the cart line's unit
     * changes -- e.g. switching "pcs" to a "box" of 4 should default the price to
     * 4x, not silently keep the pcs price against a box quantity.
     */
    function unitFactor(p, unitId) {
        const u = (p.units || []).find((x) => String(x.id) === String(unitId));
        return u ? (u.factor || 1) : 1;
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
        const existing = cart.find((c) => c.productId === productId && !c.batchNo && !c.mfgDate && !c.expiryDate);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                productId: productId, qty: 1, receivedQty: 0, price: p.price, salePrice: p.salePrice || 0,
                unitId: p.baseUnitId || '', batchNo: '', mfgDate: '', expiryDate: '', barcode: p.barcode || '',
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

    $(document).on('blur', '.ci-qty', function(){
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
                    '<span class="bn">ক্রয় তালিকায় যোগ করতে ক্যাটালগ থেকে পণ্য নির্বাচন করুন</span>' +
                    '<span class="en" style="display:none;">Select items from the catalog to add to purchase</span>' +
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
            const p = productData[item.productId] || { name: 'Unknown', stock: 0 };
            const hasValidity = item.mfgDate || item.expiryDate || item.batchNo;
            const hasBarcode = !!item.barcode;
            return '<div class="cart-item" data-index="' + i + '">' +
                '<div class="ci-head">' +
                    '<div class="thumb"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/></svg></div>' +
                    '<div class="nm">' + escapeHtml(p.name) + '</div>' +
                    '<div class="ci-actions">' +
                        '<button type="button" class="barcode-toggle-btn' + (hasBarcode ? ' has-value' : '') + '" title="Barcode"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 5v14M8 5v14M11 5v14M15 5v14M17 5v14M20 5v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button>' +
                        '<button type="button" class="validity-toggle-btn' + (hasValidity ? ' has-value' : '') + '" title="মেয়াদ"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button>' +
                        '<button type="button" class="ci-remove" title="Remove">&times;</button>' +
                    '</div>' +
                '</div>' +
                '<div class="ci-grid ci-grid-6 ci-grid-6-purchase">' +
                    '<div><label class="bn">ক্রয় মূল্য</label><label class="en" style="display:none;">Purchase Price</label><input type="number" step="0.01" min="0" class="ci-price" value="' + item.price + '"></div>' +
                    '<div><label class="bn">বিক্রয় মূল্য</label><label class="en" style="display:none;">Sale Price</label><input type="number" step="0.01" min="0" class="ci-sale-price" value="' + item.salePrice + '"></div>' +
                    '<div><label class="bn">পরিমাণ</label><label class="en" style="display:none;">Qty</label><input type="number" step="1" min="1" class="ci-qty" value="' + item.qty + '"></div>' +
                    '<div><label class="bn">গ্রহণের পরিমাণ</label><label class="en" style="display:none;">Received Qty</label><input type="number" step="1" min="0" name="received_qty" class="ci-received-qty" value="' + (item.receivedQty !== undefined ? item.receivedQty : 0) + '"></div>' +
                    '<div><label class="bn">একক</label><label class="en" style="display:none;">Unit</label><select class="ci-unit-select">' +
                        (p.units || []).map((u) => '<option value="' + u.id + '"' + (String(u.id) === String(item.unitId) ? ' selected' : '') + '>' + escapeHtml(u.label) + '</option>').join('') +
                    '</select></div>' +
                    '<div><label class="bn">মোট</label><label class="en" style="display:none;">Total</label><input type="text" class="ci-total" value="' + fmt(item.qty * item.price) + '" readonly></div>' +
                '</div>' +
                '<div class="item-popover barcode-popover">' +
                    '<div class="fld"><label class="bn">বারকোড</label><label class="en" style="display:none;">Barcode</label><input type="text" class="ci-barcode-input" value="' + escapeHtml(item.barcode) + '" placeholder="বারকোড স্ক্যান/লিখুন"></div>' +
                '</div>' +
                '<div class="item-popover validity-popover">' +
                    '<div class="fld"><label class="bn">ব্যাচ নং</label><label class="en" style="display:none;">Batch No</label><input type="text" class="ci-batch-input" value="' + escapeHtml(item.batchNo) + '" placeholder="স্বয়ংক্রিয়"></div>' +
                    '<div class="fld"><label class="bn">উৎপাদন তারিখ</label><label class="en" style="display:none;">Mfg Date</label><input type="date" class="ci-mfg-input" value="' + item.mfgDate + '"></div>' +
                    '<div class="fld"><label class="bn">মেয়াদ উত্তীর্ণের তারিখ</label><label class="en" style="display:none;">Expiry Date</label><input type="date" class="ci-expiry-input" value="' + item.expiryDate + '"></div>' +
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
            html += '<input type="hidden" name="items[' + i + '][received_qty]" value="' + (item.receivedQty !== undefined ? item.receivedQty : 0) + '">';
            html += '<input type="hidden" name="items[' + i + '][unit_id]" value="' + (item.unitId || '') + '">';
            html += '<input type="hidden" name="items[' + i + '][purchase_price]" value="' + item.price + '">';
            html += '<input type="hidden" name="items[' + i + '][sale_price]" value="' + item.salePrice + '">';
            html += '<input type="hidden" name="items[' + i + '][batch_no]" value="' + escapeHtml(item.batchNo) + '">';
            html += '<input type="hidden" name="items[' + i + '][barcode]" value="' + escapeHtml(item.barcode) + '">';
            html += '<input type="hidden" name="items[' + i + '][mfg_date]" value="' + item.mfgDate + '">';
            html += '<input type="hidden" name="items[' + i + '][expiry_date]" value="' + item.expiryDate + '">';
        });
        container.innerHTML = html;
    }

    function subtotal() {
        return cart.reduce((sum, item) => sum + item.qty * item.price, 0);
    }

    function recalcGrand() {
        const total = subtotal();

        const subtotalEl = document.getElementById('subtotal-display');
        if (subtotalEl) subtotalEl.textContent = fmt(total);
        const totalEl = document.getElementById('total-display');
        if (totalEl) totalEl.textContent = fmt(total);

        const discountHidden = document.getElementById('discount-hidden');
        if (discountHidden) discountHidden.value = '0.00';
        const deliveryChargeHidden = document.getElementById('delivery-charge-hidden');
        if (deliveryChargeHidden) deliveryChargeHidden.value = '0.00';

        updateGrandTotalCostDisplay();

        return total;
    }

    function calcGrandTotalCost() {
        const itemsSubtotal = subtotal();
        const adjVal = parseFloat($('#adjustment_cost').val()) || 0;
        const transVal = parseFloat($('#transportation_cost').val()) || 0;
        const prevDueVal = parseFloat($('#total_previous_due').val()) || 0;
        return Math.max(0, itemsSubtotal + transVal + adjVal + prevDueVal);
    }

    let amountManuallyEdited = false;
    $(document).on('input', '#drawer-amount-input', function () {
        amountManuallyEdited = true;
    });

    function updateGrandTotalCostDisplay() {
        const grandTotal = calcGrandTotalCost();
        const formatted = fmt(grandTotal);

        const grandEl = document.getElementById('grand_total_cost');
        if (grandEl) grandEl.value = formatted;
        $('#grand_total_cost').val(formatted);

        const totalPayableEl = document.getElementById('drawer-total-payable');
        if (totalPayableEl) totalPayableEl.textContent = formatted;

        if (drawerMode === 'cash' && !amountManuallyEdited) {
            const amountInput = document.getElementById('drawer-amount-input');
            if (amountInput) amountInput.value = formatted;
            $('#drawer-amount-input').val(formatted);
        }
    }

    $(document).on('input change', '#adjustment_cost, #transportation_cost, #total_previous_due', function () {
        updateGrandTotalCostDisplay();
    });

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

        if (e.target.closest('.ci-remove')) {
            cart.splice(index, 1);
            renderAll();
            return;
        }
        if (e.target.closest('.barcode-toggle-btn')) {
            row.querySelector('.barcode-popover').classList.toggle('open');
            row.querySelector('.validity-popover').classList.remove('open');
            return;
        }
        if (e.target.closest('.validity-toggle-btn')) {
            row.querySelector('.validity-popover').classList.toggle('open');
            row.querySelector('.barcode-popover').classList.remove('open');
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
        if (e.target.classList.contains('ci-received-qty')) item.receivedQty = parseFloat(e.target.value) || 0;
        if (e.target.classList.contains('ci-price')) item.price = parseFloat(e.target.value) || 0;
        if (e.target.classList.contains('ci-sale-price')) item.salePrice = parseFloat(e.target.value) || 0;
        if (e.target.classList.contains('ci-barcode-input')) item.barcode = e.target.value;
        if (e.target.classList.contains('ci-batch-input')) item.batchNo = e.target.value;
        if (e.target.classList.contains('ci-mfg-input')) item.mfgDate = e.target.value;
        if (e.target.classList.contains('ci-expiry-input')) item.expiryDate = e.target.value;

        if (e.target.classList.contains('ci-unit-select')) {
            const p = productData[item.productId];
            const factor = unitFactor(p, e.target.value);
            item.unitId = e.target.value;
            // Reset the per-unit prices to a sensible default for the newly chosen
            // unit (base price x factor) so Total stays meaningful -- the shop admin
            // can still adjust it afterwards for a negotiated wholesale price.
            item.price = Math.round(p.price * factor * 100) / 100;
            item.salePrice = Math.round(p.salePrice * factor * 100) / 100;
            renderAll();
            return;
        }

        if (e.target.classList.contains('ci-qty') || e.target.classList.contains('ci-price')) {
            row.querySelector('.ci-total').value = fmt(item.qty * item.price);
        }
        renderHiddenFields();
        recalcGrand();
    });

    document.getElementById('clear-cart-btn').addEventListener('click', () => {
        if (cart.length === 0) return;
        cart = [];
        renderAll();
    });


    /* ---------------- Confirm payment drawer ---------------- */
    const drawer = document.getElementById('confirmPaymentDrawer');
    let drawerMode = 'cash';

    function openDrawer(mode) {
        if (cart.length === 0) {
            toast('কার্টে অন্তত একটি পণ্য যোগ করুন', 'Add at least one product to the cart');
            return;
        }
        drawerMode = mode;
        amountManuallyEdited = false;
        recalcGrand();
        const total = calcGrandTotalCost();
        document.getElementById('payment-method-hidden').value = 'cash';
        document.getElementById('drawer-date-display').value = document.getElementById('purchase-date-input').value;
        document.getElementById('drawer-total-banner').style.display = mode === 'due' ? 'block' : 'none';
        document.getElementById('drawer-total-payable').textContent = fmt(total);
        const amountLabelBn = document.getElementById('drawer-amount-label-bn');
        if (amountLabelBn) {
            amountLabelBn.textContent = mode === 'due' ? 'ক্যাশ দিয়েছি' : 'টাকার পরিমান';
        } else {
            $('#drawer-amount-input').closest('.form-input-group, .form-group').find('.form-label .bn').text(mode === 'due' ? 'ক্যাশ দিয়েছি' : 'টাকার পরিমান');
        }

        updateGrandTotalCostDisplay();

        if (mode === 'due') {
            document.getElementById('drawer-amount-input').value = '0';
            $('#drawer-amount-input').val('0');
        }

        const currentSupplierId = $('#supplier-id-select').val();
        updateSupplierDueNotice(currentSupplierId);

        drawer.classList.add('open');
    }

    document.getElementById('open-cash-btn').addEventListener('click', () => openDrawer('cash'));
    document.getElementById('open-due-btn').addEventListener('click', () => openDrawer('due'));
    document.getElementById('drawer-close-btn').addEventListener('click', () => drawer.classList.remove('open'));

    document.getElementById('custom-invoice-toggle').addEventListener('change', (e) => {
        document.getElementById('custom-invoice-field').style.display = e.target.checked ? 'block' : 'none';
        if (!e.target.checked) document.getElementById('invoice-no-input').value = '';
    });

    document.getElementById('employee-toggle').addEventListener('change', (e) => {
        document.getElementById('employee-fields').style.display = e.target.checked ? 'block' : 'none';
        if (!e.target.checked) {
            document.getElementById('employee-name-input').value = '';
            document.getElementById('employee-phone-input').value = '';
        }
    });

    /* ---------------- Quick Add Supplier Modal ---------------- */
    $(function () {
        const $quickModal = $('#quickSupplierModal');
        if ($quickModal.length && !$quickModal.parent().is('body')) {
            $('body').append($quickModal);
        }
    });

    let lastSupplierId = $('#supplier-id-select').val() || '';

    function openQuickSupplierModal() {
        const $modal = $('#quickSupplierModal');
        if ($modal.length && !$modal.parent().is('body')) {
            $('body').append($modal);
        }
        const $form = $('#quick_supplier_form');
        if ($form.length && $form[0]) {
            $form[0].reset();
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.dynamic-error').remove();
            $('#quick_supplier_opening_due').val('0');
        }
        openModal('quickSupplierModal');
        setTimeout(() => $('#quick_supplier_name').focus(), 150);
    }

    function revertSupplierSelectIfNeeded() {
        if ($('#supplier-id-select').val() === '__create_new__') {
            $('#supplier-id-select').val(lastSupplierId || '');
        }
    }

    $(document).on('click', '.btn-open-quick-supplier', function (e) {
        e.preventDefault();
        openQuickSupplierModal();
    });

    $(document).on('click', '#quickSupplierModal .modal-close-btn', function (e) {
        e.preventDefault();
        closeModal('quickSupplierModal');
        revertSupplierSelectIfNeeded();
    });

    $(document).on('click', '#quickSupplierModal', function (e) {
        if ($(e.target).hasClass('modal-backdrop')) {
            closeModal('quickSupplierModal');
            revertSupplierSelectIfNeeded();
        }
    });

    function submitQuickSupplier() {
        const $form = $('#quick_supplier_form');
        const $btn = $('#btn-save-quick-supplier');
        const url = $form.attr('action') || '{{ route('suppliers.store') }}';

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
                if (response.success && response.supplier) {
                    const s = response.supplier;
                    const due = parseFloat(s.opening_due) || 0;
                    supplierData.push({
                        id: s.id,
                        name: s.name,
                        phone: s.phone || '',
                        address: s.address || '',
                        due: due
                    });

                    const phoneTxt = s.phone ? ` (${s.phone})` : '';
                    const $newOption = $('<option></option>').val(s.id).attr('data-due', due).text(s.name + phoneTxt);
                    $('#supplier-id-select').append($newOption);
                    $('#supplier-id-select').val(s.id);
                    lastSupplierId = s.id;

                    const $datalistOpt = $('<option></option>').val(s.name);
                    $('#suppliers-datalist').append($datalistOpt);

                    $('#supplier-name-input').val(s.name);
                    $('#supplier-phone-input').val(s.phone || '');
                    $('#supplier-address-input').val(s.address || '');

                    $('#supplier-id-select').trigger('change');

                    closeModal('quickSupplierModal');
                    if ($form.length && $form[0]) {
                        $form[0].reset();
                    }
                    toast(response.message || 'সরবরাহকারী সফলভাবে যোগ করা হয়েছে', 'Supplier created successfully');
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
                    toast('সরবরাহকারী যোগ করতে সমস্যা হয়েছে', 'Failed to create supplier');
                }
            }
        });
    }

    $(document).on('click', '#btn-save-quick-supplier', function (e) {
        e.preventDefault();
        e.stopPropagation();
        submitQuickSupplier();
        return false;
    });

    $(document).on('submit', '#quick_supplier_form', function (e) {
        e.preventDefault();
        e.stopPropagation();
        submitQuickSupplier();
        return false;
    });

    $(document).on('keydown', '#quick_supplier_form input', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            submitQuickSupplier();
            return false;
        }
    });

    function updateSupplierDueNotice(supplierId) {
        const alertEl = document.getElementById('supplier-due-alert');
        const amountEl = document.getElementById('total_previous_due');
        const $amountInput = $('#total_previous_due');

        const sid = parseInt(supplierId, 10);
        if (!sid) {
            if (alertEl) alertEl.style.display = 'none';
            if (amountEl) amountEl.value = '0';
            $amountInput.val('0');
            updateGrandTotalCostDisplay();
            return;
        }

        const match = supplierData.find((s) => s.id === sid);
        let due = 0;
        if (match && match.due !== undefined) {
            due = parseFloat(match.due) || 0;
        } else {
            const selectedOpt = $('#supplier-id-select option[value="' + sid + '"]');
            if (selectedOpt.length && selectedOpt.attr('data-due') !== undefined) {
                due = parseFloat(selectedOpt.attr('data-due')) || 0;
            }
        }

        const formattedDue = fmt(due);

        if (amountEl) {
            amountEl.value = formattedDue;
            amountEl.textContent = formattedDue;
            if (due > 0) {
                amountEl.style.color = 'var(--red-600)';
                amountEl.style.fontWeight = '700';
            } else {
                amountEl.style.color = '';
                amountEl.style.fontWeight = '';
            }
        }
        $amountInput.val(formattedDue);

        if (alertEl) {
            alertEl.style.display = 'block';
        }

        updateGrandTotalCostDisplay();
    }

    const supplierSelect = document.getElementById('supplier-id-select');
    if (supplierSelect) {
        supplierSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val === '__create_new__') {
                openQuickSupplierModal();
                return;
            }
            lastSupplierId = val;
            const sid = parseInt(val, 10);
            const match = supplierData.find((s) => s.id === sid);
            const nameInput = document.getElementById('supplier-name-input');
            const phoneInput = document.getElementById('supplier-phone-input');
            const addressInput = document.getElementById('supplier-address-input');
            if (nameInput) nameInput.value = match ? match.name : '';
            if (phoneInput) phoneInput.value = match ? (match.phone || '') : '';
            if (addressInput) addressInput.value = match ? (match.address || '') : '';

            updateSupplierDueNotice(val);
        });
    }

    $(document).on('change', '#supplier-id-select', function () {
        updateSupplierDueNotice($(this).val());
    });

    const supplierNameInput = document.getElementById('supplier-name-input');
    if (supplierNameInput) {
        supplierNameInput.addEventListener('change', (e) => {
            const match = supplierData.find((s) => s.name === e.target.value);
            if (supplierSelect) {
                supplierSelect.value = match ? match.id : '';
                lastSupplierId = match ? match.id : '';
            }
            if (match) {
                const phoneInput = document.getElementById('supplier-phone-input');
                const addressInput = document.getElementById('supplier-address-input');
                if (phoneInput) phoneInput.value = match.phone || '';
                if (addressInput) addressInput.value = match.address || '';
            }
        });
    }

    document.getElementById('drawer-save-btn').addEventListener('click', () => {
        const total = calcGrandTotalCost();
        let amount = parseFloat(document.getElementById('drawer-amount-input').value) || 0;
        if (drawerMode === 'cash' && amount <= 0) amount = total;
        amount = Math.min(Math.max(amount, 0), total);

        const accountSelect = document.getElementById('drawer-account-select');
        const selectedOpt = accountSelect && accountSelect.selectedIndex >= 0 ? accountSelect.options[accountSelect.selectedIndex] : null;
        const accountType = selectedOpt ? selectedOpt.getAttribute('data-type') : 'cash';
        const methodMap = { cash: 'cash', bank: 'bank', mfs: 'mobile_banking' };

        const accountInput = document.getElementById('payment-account-hidden');
        const methodInput = document.getElementById('payment-method-hidden');
        const amountInput = document.getElementById('payment-amount-hidden');
        const hasPayment = amount > 0;

        if (accountInput) {
            accountInput.disabled = !hasPayment;
            accountInput.value = selectedOpt ? selectedOpt.value : '';
        }
        methodInput.disabled = !hasPayment;
        methodInput.value = methodMap[accountType] || 'cash';
        amountInput.disabled = !hasPayment;
        if (hasPayment) amountInput.value = fmt(amount);

        renderHiddenFields();
        document.getElementById('purchase-form').submit();
    });

    renderAll();
    updateSupplierDueNotice($('#supplier-id-select').val());
})();
</script>
