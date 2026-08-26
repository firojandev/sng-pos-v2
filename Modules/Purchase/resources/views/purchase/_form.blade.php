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
            'units' => $product->units->map(fn ($u) => [
                'id' => $u->id,
                'label' => $u->name.($u->short_code ? ' ('.$u->short_code.')' : ''),
                'isBase' => (bool) $u->pivot->is_base,
                'factor' => (float) $u->pivot->conversion_factor,
            ])->values(),
        ];
    }

    $supplierData = $suppliers->map(fn ($s) => [
        'id' => $s->id, 'name' => $s->name, 'phone' => $s->phone, 'address' => $s->address,
    ])->values();

    $employeeData = $employees->map(fn ($e) => ['name' => $e->name, 'phone' => $e->phone])->values();

    $initialItems = old('items', $purchase->exists
        ? $purchase->items->map(fn ($item) => [
            'product_id' => (int) $item->product_id,
            'qty' => rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.'),
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

    <div class="fld">
        <label class="bn">গুদাম</label><label class="en" style="display:none;">Warehouse</label>
        <select name="warehouse_id" required>
            <option value="">-- নির্বাচন করুন --</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" {{ (string) old('warehouse_id', $purchase->warehouse_id ?? optional($warehouses->first())->id) === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} @if($warehouse->branch) ({{ $warehouse->branch->name }}) @endif</option>
            @endforeach
        </select>
    </div>

    <div class="fld">
        <label class="bn">তারিখ</label><label class="en" style="display:none;">Date</label>
        <input type="date" name="purchase_date" id="purchase-date-input" value="{{ old('purchase_date', optional($purchase->purchase_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>

    <a href="{{ route('purchase.ledger') }}" class="pos-close" title="Back">&times;</a>
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
            <button type="button" class="clear-cart" id="clear-cart-btn"><span class="bn">কার্ট খালি করুন</span><span class="en">Clear cart</span></button>
        </div>
        <div id="cart-list" class="cart-list"></div>

        <div class="cart-totals">
            <div class="sum-row">
                <span class="sum-label bn">মোট</span><span class="sum-label en" style="display:none;">Total</span>
                <span id="subtotal-display">0.00</span>
            </div>
            <div class="sum-row">
                <span class="sum-label bn">ডিস্কাউন্ট</span><span class="sum-label en" style="display:none;">Discount</span>
                <div class="disc-row">
                    <input type="number" step="0.01" min="0" id="discount-raw-input" value="{{ is_numeric($initialDiscount) ? rtrim(rtrim(number_format($initialDiscount, 2, '.', ''), '0'), '.') : 0 }}">
                    <select id="discount-type-select">
                        <option value="flat">৳</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </div>
            <div class="sum-row">
                <span class="sum-label bn">ডেলিভারী চার্জ</span><span class="sum-label en" style="display:none;">Delivery Charge</span>
                <input type="number" step="0.01" min="0" name="delivery_charge" id="delivery-charge-input" value="{{ rtrim(rtrim(number_format($initialDeliveryCharge, 2, '.', ''), '0'), '.') }}" style="width:100px; text-align:right; border:1px solid var(--border); border-radius:8px; padding:6px 8px; font-family:'Manrope',sans-serif;">
            </div>
            <div class="sum-row total">
                <span class="sum-label bn">সর্বমোট</span><span class="sum-label en" style="display:none;">Grand Total</span>
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
<input type="hidden" name="discount" id="discount-hidden">
<input type="hidden" name="supplier_id" id="supplier-id-hidden" value="{{ $initialSupplierId }}">
<input type="hidden" name="payments[0][method]" id="payment-method-hidden" value="cash">
<input type="hidden" name="payments[0][amount]" id="payment-amount-hidden" value="0">

<div class="drawer-backdrop" id="confirmPaymentDrawer">
    <div class="drawer">
        <div class="drawer-head">
            <div class="drawer-title" id="drawer-title-bn">Confirm Payment</div>
            <button type="button" class="drawer-x" id="drawer-close-btn">&times;</button>
        </div>

        <div class="tx-section" id="drawer-total-banner" style="display:none; text-align:center; font-weight:700;">
            <span class="bn">মোট প্রদেয় </span><span class="en" style="display:none;">Total Payable </span><span id="drawer-total-payable">0.00</span>
        </div>

        <div class="field">
            <label class="bn">ক্রয়ের তারিখ</label><label class="en" style="display:none;">Purchase Date</label>
            <input type="text" id="drawer-date-display" readonly>
        </div>

        <div class="field">
            <label class="bn" id="drawer-amount-label-bn">টাকার পরিমান</label><label class="en" style="display:none;">Amount</label>
            <input type="number" step="0.01" min="0" id="drawer-amount-input" value="{{ $initialAmount }}">
        </div>

        <div class="field">
            <label class="bn">মন্তব্য লিখুন</label><label class="en" style="display:none;">Note</label>
            <textarea name="note" id="drawer-note-input" placeholder="ঐচ্ছিক নোট">{{ $initialNote }}</textarea>
        </div>

        <div class="field">
            <label class="bn">সাপ্লায়ার নাম</label><label class="en" style="display:none;">Supplier Name</label>
            <input type="text" name="supplier_name" id="supplier-name-input" list="suppliers-datalist" value="{{ $initialSupplierName }}" placeholder="সাপ্লায়ার নাম">
            <datalist id="suppliers-datalist">
                @foreach ($supplierData as $s)
                    <option value="{{ $s['name'] }}">
                @endforeach
            </datalist>
        </div>

        <div class="field">
            <label class="bn">সাপ্লায়ার মোবাইল নম্বর</label><label class="en" style="display:none;">Supplier Mobile Number</label>
            <input type="text" name="supplier_phone" id="supplier-phone-input" value="{{ $initialSupplierPhone }}" placeholder="+88 XXXXXXXXXX">
        </div>

        <div class="field">
            <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
            <input type="text" name="supplier_address" id="supplier-address-input" value="{{ $initialSupplierAddress }}" placeholder="ঠিকানা">
        </div>

        <div class="tx-row" style="margin-bottom:10px;">
            <span class="lbl bn">কাস্টম ইনভয়েস নম্বর</span><span class="lbl en" style="display:none;">Custom Invoice Number</span>
            <label class="switch">
                <input type="checkbox" id="custom-invoice-toggle" {{ $initialInvoiceNo && $purchase->exists ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </div>
        <div class="field" id="custom-invoice-field" style="display:{{ $initialInvoiceNo && $purchase->exists ? 'block' : 'none' }};">
            <input type="text" name="invoice_no" id="invoice-no-input" value="{{ $initialInvoiceNo }}" placeholder="INV-0001">
        </div>

        <div class="tx-row" style="margin-bottom:10px;">
            <span class="lbl bn">কর্মচারীর তথ্য</span><span class="lbl en" style="display:none;">Employee Info</span>
            <label class="switch">
                <input type="checkbox" id="employee-toggle" {{ $initialEmployeeName ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </div>
        <div id="employee-fields" style="display:{{ $initialEmployeeName ? 'block' : 'none' }};">
            <div class="field">
                <label class="bn">কর্মচারীর নাম</label><label class="en" style="display:none;">Employee Name</label>
                <input type="text" name="employee_name" id="employee-name-input" list="employees-datalist" value="{{ $initialEmployeeName }}" placeholder="কর্মচারীর নাম">
            </div>
            <div class="field">
                <label class="bn">কর্মচারীর মোবাইল নম্বর</label><label class="en" style="display:none;">Employee Mobile Number</label>
                <input type="text" name="employee_phone" id="employee-phone-input" value="{{ $initialEmployeePhone }}" placeholder="+88 XXXXXXXXXX">
            </div>
        </div>

        <button type="button" class="btn btn-gold" id="drawer-save-btn" style="width:100%; justify-content:center; margin-top:16px;">
            <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
        </button>
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
                '<div class="add-btn"><button type="button" class="add-to-cart-btn" data-id="' + pid + '">Add</button>' + (count ? '<span class="count">' + fmt(count).replace(/\.00$/, '') + '</span>' : '') + '</div>' +
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
                productId: productId, qty: 1, price: p.price, salePrice: p.salePrice || 0,
                unitId: p.baseUnitId || '', batchNo: '', mfgDate: '', expiryDate: '', barcode: p.barcode || '',
            });
        }
        renderAll();
    }

    catalogList.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-to-cart-btn');
        if (btn) addToCart(btn.dataset.id);
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
            cartList.innerHTML = '<div class="cart-empty"><span class="bn">কার্ট খালি</span><span class="en">Cart is empty</span></div>';
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
                '<div class="ci-grid ci-grid-5">' +
                    '<div><label class="bn">ক্রয় মূল্য</label><label class="en" style="display:none;">Purchase Price</label><input type="number" step="0.01" min="0" class="ci-price" value="' + item.price + '"></div>' +
                    '<div><label class="bn">বিক্রয় মূল্য</label><label class="en" style="display:none;">Sale Price</label><input type="number" step="0.01" min="0" class="ci-sale-price" value="' + item.salePrice + '"></div>' +
                    '<div><label class="bn">পরিমাণ</label><label class="en" style="display:none;">Qty</label><input type="number" step="0.01" min="0.01" class="ci-qty" value="' + item.qty + '"></div>' +
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

    function discountAmount() {
        const raw = parseFloat(document.getElementById('discount-raw-input').value) || 0;
        const type = document.getElementById('discount-type-select').value;
        return type === 'percent' ? subtotal() * (raw / 100) : raw;
    }

    function recalcGrand() {
        const sub = subtotal();
        const discount = Math.min(discountAmount(), sub);
        const deliveryCharge = parseFloat(document.getElementById('delivery-charge-input').value) || 0;
        const total = Math.max(sub - discount + deliveryCharge, 0);

        document.getElementById('subtotal-display').textContent = fmt(sub);
        document.getElementById('total-display').textContent = fmt(total);
        document.getElementById('discount-hidden').value = fmt(discount);

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

    document.getElementById('discount-raw-input').addEventListener('input', recalcGrand);
    document.getElementById('discount-type-select').addEventListener('change', recalcGrand);
    document.getElementById('delivery-charge-input').addEventListener('input', recalcGrand);

    /* ---------------- Confirm payment drawer ---------------- */
    const drawer = document.getElementById('confirmPaymentDrawer');
    let drawerMode = 'cash';

    function openDrawer(mode) {
        if (cart.length === 0) {
            toast('কার্টে অন্তত একটি পণ্য যোগ করুন', 'Add at least one product to the cart');
            return;
        }
        drawerMode = mode;
        const total = recalcGrand();
        document.getElementById('payment-method-hidden').value = 'cash';
        document.getElementById('drawer-date-display').value = document.getElementById('purchase-date-input').value;
        document.getElementById('drawer-total-banner').style.display = mode === 'due' ? 'block' : 'none';
        document.getElementById('drawer-total-payable').textContent = fmt(total);
        document.getElementById('drawer-amount-label-bn').textContent = mode === 'due' ? 'ক্যাশ দিয়েছি' : 'টাকার পরিমান';
        document.getElementById('drawer-amount-input').value = mode === 'due' ? 0 : fmt(total);
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

    document.getElementById('supplier-name-input').addEventListener('change', (e) => {
        const match = supplierData.find((s) => s.name === e.target.value);
        document.getElementById('supplier-id-hidden').value = match ? match.id : '';
        if (match) {
            document.getElementById('supplier-phone-input').value = match.phone || '';
            document.getElementById('supplier-address-input').value = match.address || '';
        }
    });

    document.getElementById('drawer-save-btn').addEventListener('click', () => {
        const total = recalcGrand();
        let amount = parseFloat(document.getElementById('drawer-amount-input').value) || 0;
        if (drawerMode === 'cash' && amount <= 0) amount = total;
        amount = Math.min(Math.max(amount, 0), total);

        const methodInput = document.getElementById('payment-method-hidden');
        const amountInput = document.getElementById('payment-amount-hidden');
        const hasPayment = amount > 0;
        methodInput.disabled = !hasPayment;
        amountInput.disabled = !hasPayment;
        if (hasPayment) amountInput.value = fmt(amount);

        renderHiddenFields();
        document.getElementById('purchase-form').submit();
    });

    renderAll();
})();
</script>
