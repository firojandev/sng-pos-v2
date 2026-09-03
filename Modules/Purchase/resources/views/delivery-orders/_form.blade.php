@php
    $productData = [];
    foreach ($products as $p) {
        $baseUnit = $p->units->firstWhere('pivot.is_base', true) ?? $p->units->first();
        $productData[$p->id] = [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'barcode' => $p->barcode,
            'purchase_price' => (float) $p->purchase_price,
            'current_stock' => (float) ($p->batches_sum_quantity ?? 0),
            'units' => $p->units->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'conversion_factor' => (float) $u->pivot->conversion_factor,
            ])->values(),
        ];
    }

    $initialItems = old('items', $order->exists ? $order->items->map(fn($item) => [
        'product_id' => $item->product_id,
        'quantity' => (float) $item->ordered_quantity,
        'unit_id' => $item->unit_id,
        'purchase_price' => (float) $item->purchase_price,
    ])->toArray() : []);
@endphp

<script id="pdo-products-json" type="application/json">{!! json_encode($productData) !!}</script>
<script id="pdo-initial-items" type="application/json">{!! json_encode($initialItems) !!}</script>

<style>
    .pdo-search-item:hover { background: var(--paper) !important; }
</style>

<div class="panel" style="margin-top:0;">
    <div class="panel-body">
        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; align-items:start;">
            <!-- Left: Items & Product Selector -->
            <div>
                <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:16px; margin-bottom:20px;">
                    <div style="font-weight:600; font-size:15px; margin-bottom:12px; color:var(--ink-900);" class="bn">পণ্য যোগ করুন</div>
                    <div style="display:flex; gap:12px;">
                        <div style="flex:2; position:relative;">
                            <input type="text" id="pdo-product-search" placeholder="পণ্যের নাম বা SKU দিয়ে খুঁজুন..." style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 12px; font-size:13px;">
                            <div id="pdo-search-results" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--card); border:1px solid var(--border); border-radius:6px; box-shadow:var(--shadow-card); max-height:240px; overflow-y:auto; z-index:50; margin-top:4px;"></div>
                        </div>
                        <div style="flex:1;">
                            <input type="text" id="pdo-barcode-input" placeholder="বারকোড স্ক্যান..." style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 12px; font-size:13px;">
                        </div>
                    </div>
                </div>

                @if ($errors->has('items'))
                    <div style="color:#ef4444; font-size:13px; margin-bottom:12px;">{{ $errors->first('items') }}</div>
                @endif

                <div class="table-wrap" style="border:1px solid var(--border); border-radius:10px; overflow:hidden;">
                    <table style="margin:0;">
                        <thead style="background:var(--paper);">
                            <tr>
                                <th style="width:35%; font-size:12px;" class="bn">পণ্য</th>
                                <th style="width:15%; font-size:12px;" class="bn">একক</th>
                                <th style="width:18%; font-size:12px;" class="bn">অর্ডার পরিমাণ</th>
                                <th style="width:18%; font-size:12px;" class="bn">ক্রয়মূল্য (একক)</th>
                                <th style="width:14%; font-size:12px; text-align:right;" class="bn">মোট</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="pdo-items-tbody">
                            <tr id="pdo-empty-row">
                                <td colspan="6" style="text-align:center; padding:32px; color:var(--ink-400); font-size:13px;">
                                    <div class="bn">এখনও কোনো পণ্য যোগ করা হয়নি। উপরের সার্চবক্স থেকে পণ্য যোগ করুন।</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--paper-line); padding:10px 16px; border-radius:6px; margin-top:12px; font-size:13px;">
                    <span style="font-weight:600; color:var(--ink-600);" class="bn">মোট আইটেম: <b id="pdo-item-count" style="color:var(--ink-900);">0</b></span>
                    <span style="font-weight:600; color:var(--ink-600);" class="bn">মোট পরিমাণ: <b id="pdo-qty-count" style="color:var(--ink-900);">0</b></span>
                    <span style="font-weight:700; font-size:14px; color:var(--ink-900);" class="bn">সাবটোটাল: ৳<span id="pdo-subtotal-display">0.00</span></span>
                </div>
            </div>

            <!-- Right: Order Details & Pricing -->
            <div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:16px; box-shadow:var(--shadow-card);">
                <div style="font-weight:700; font-size:15px; margin-bottom:14px; color:var(--ink-900); border-bottom:1px solid var(--border); padding-bottom:8px;" class="bn">
                    অর্ডার ও ডেলিভারি তথ্য
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">গন্তব্য গুদাম *</label>
                    <select name="warehouse_id" required style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                        <option value="">-- গুদাম নির্বাচন করুন --</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" @selected((string) old('warehouse_id', $order->warehouse_id) === (string) $w->id)>
                                {{ $w->name }} @if($w->branch) ({{ $w->branch->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">অর্ডারের তারিখ *</label>
                        <input type="date" name="order_date" value="{{ old('order_date', optional($order->order_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">প্রত্যাশিত তারিখ</label>
                        <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', optional($order->expected_delivery_date)->format('Y-m-d')) }}" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px;">
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">কাস্টম অর্ডার নং (ঐচ্ছিক)</label>
                    <input type="text" name="order_no" value="{{ old('order_no', $order->order_no) }}" placeholder="স্বয়ংক্রিয়ভাবে তৈরি হবে" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                </div>

                <!-- Supplier info -->
                <div style="border-top:1px solid var(--border); padding-top:12px; margin-top:12px; margin-bottom:12px;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">সরবরাহকারী নির্বাচন</label>
                    <select name="supplier_id" id="pdo-supplier-select" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px; margin-bottom:6px;">
                        <option value="">-- নতুন / সাধারণ সরবরাহকারী --</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}" @selected((string) old('supplier_id', $order->supplier_id) === (string) $s->id) data-phone="{{ $s->phone }}" data-address="{{ $s->address }}">
                                {{ $s->name }} @if($s->phone) ({{ $s->phone }}) @endif
                            </option>
                        @endforeach
                    </select>

                    <div id="pdo-custom-supplier-box">
                        <input type="text" name="supplier_name" value="{{ old('supplier_name', $order->supplier?->name) }}" placeholder="সরবরাহকারীর নাম" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px; margin-bottom:6px;">
                        <input type="text" name="supplier_phone" value="{{ old('supplier_phone', $order->supplier?->phone) }}" placeholder="মোবাইল নম্বর" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                    </div>
                </div>

                <!-- Delivery contact info -->
                <div style="border-top:1px solid var(--border); padding-top:12px; margin-bottom:12px;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">ডেলিভারি ম্যান / কুরিয়ার তথ্য</label>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                        <input type="text" name="delivery_person_name" value="{{ old('delivery_person_name', $order->delivery_person_name) }}" placeholder="নাম" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px;">
                        <input type="text" name="delivery_person_phone" value="{{ old('delivery_person_phone', $order->delivery_person_phone) }}" placeholder="মোবাইল নম্বর" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px;">
                    </div>
                </div>
                <!-- Financial Calculation -->
                <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:14px; margin-bottom:16px; margin-top: 16px">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                        <span class="bn" style="color:var(--ink-700);">সাবটোটাল</span>
                        <b style="color:var(--ink-900);">৳<span id="calc-subtotal">0.00</span></b>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px;">
                        <span class="bn" style="color:var(--ink-700);">ছাড় (ডিসকাউন্ট)</span>
                        <input type="number" step="0.01" min="0" name="discount" id="calc-discount" value="{{ old('discount', $order->discount ?? 0) }}" style="width:90px; text-align:right; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:4px 8px; font-size:13px;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px;">
                        <span class="bn" style="color:var(--ink-700);">ডেলিভারি চার্জ</span>
                        <input type="number" step="0.01" min="0" name="delivery_charge" id="calc-delivery-charge" value="{{ old('delivery_charge', $order->delivery_charge ?? 0) }}" style="width:90px; text-align:right; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:4px 8px; font-size:13px;">
                    </div>
                    <div style="border-top:2px dashed var(--border); margin-top:8px; padding-top:10px; display:flex; justify-content:space-between; font-size:16px; font-weight:700; color:var(--ink-900);">
                        <span class="bn">সর্বমোট টাকা</span>
                        <span style="color:#10b981;">৳<span id="calc-grand-total">0.00</span></span>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--ink-700);" class="bn">নোট বা বিশেষ নির্দেশনা</label>
                    <textarea name="note" rows="2" placeholder="অর্ডার সংক্রান্ত বিশেষ তথ্য লিখুন..." style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:8px; padding:8px 12px; font-size:13px;">{{ old('note', $order->note) }}</textarea>
                </div>

                <x-core::button size="sm" color="primary" type="submit" style="width:100%; justify-content:center;" icon="check">
                    <span class="bn">{{ $order->exists ? 'অর্ডার হালনাগাদ করুন' : 'অর্ডার সম্পন্ন করুন' }}</span>
                </x-core::button>

                <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.index') }}" style="width:100%; justify-content:center; margin-top:8px;">
                    <span class="bn">ফিরে যান</span>
                </x-core::button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    const products = JSON.parse($('#pdo-products-json').html() || '{}');
    const initialItems = JSON.parse($('#pdo-initial-items').html() || '[]');
    let items = [];

    // Render items table
    function renderItems() {
        const $tbody = $('#pdo-items-tbody');
        $tbody.empty();

        if (items.length === 0) {
            $tbody.append(`
                <tr id="pdo-empty-row">
                    <td colspan="6" style="text-align:center; padding:32px; color:#94a3b8;">
                        <div class="bn">এখনও কোনো পণ্য যোগ করা হয়নি। উপরের সার্চবক্স থেকে পণ্য যোগ করুন।</div>
                    </td>
                </tr>
            `);
            $('#pdo-item-count').text(0);
            $('#pdo-qty-count').text(0);
            $('#pdo-subtotal-display').text('0.00');
            recalculateSummary();
            return;
        }

        let totalQty = 0;
        let subtotal = 0;

        items.forEach((item, index) => {
            const prod = products[item.product_id] || {};
            const lineTotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.purchase_price) || 0);
            totalQty += parseFloat(item.quantity) || 0;
            subtotal += lineTotal;

            let unitsOptions = `<option value="">মূল একক</option>`;
            if (prod.units && prod.units.length > 0) {
                prod.units.forEach(u => {
                    const selected = (String(u.id) === String(item.unit_id)) ? 'selected' : '';
                    unitsOptions += `<option value="${u.id}" ${selected}>${u.name}</option>`;
                });
            }

            const row = `
                <tr data-index="${index}">
                    <td>
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        <div style="font-weight:600; color:var(--ink-900);">${prod.name || 'Unknown'}</div>
                        <div style="font-size:12px; color:var(--ink-400);">SKU: ${prod.sku || '—'} | বর্তমান স্টক: ${prod.current_stock || 0}</div>
                    </td>
                    <td>
                        <select name="items[${index}][unit_id]" class="pdo-item-unit" data-index="${index}" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px;">
                            ${unitsOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0.01" name="items[${index}][quantity]" value="${item.quantity}" class="pdo-item-qty" data-index="${index}" required style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px; font-weight:600;">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="items[${index}][purchase_price]" value="${item.purchase_price}" class="pdo-item-price" data-index="${index}" required style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px;">
                    </td>
                    <td style="text-align:right; font-weight:600; color:var(--ink-900);">
                        ৳${lineTotal.toFixed(2)}
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="pdo-item-remove" data-index="${index}" style="background:none; border:none; cursor:pointer; color:#ef4444; padding:4px;" title="বাতিল">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </td>
                </tr>
            `;
            $tbody.append(row);
        });

        $('#pdo-item-count').text(items.length);
        $('#pdo-qty-count').text(totalQty.toFixed(2));
        $('#pdo-subtotal-display').text(subtotal.toFixed(2));

        recalculateSummary();
    }

    function recalculateSummary() {
        let subtotal = 0;
        items.forEach(item => {
            const line = (parseFloat(item.quantity) || 0) * (parseFloat(item.purchase_price) || 0);
            subtotal += line;
        });

        const discount = parseFloat($('#calc-discount').val()) || 0;
        const deliveryCharge = parseFloat($('#calc-delivery-charge').val()) || 0;
        const grandTotal = Math.max(0, subtotal - discount + deliveryCharge);

        $('#calc-subtotal').text(subtotal.toFixed(2));
        $('#calc-grand-total').text(grandTotal.toFixed(2));
    }

    function addItem(productId) {
        const prod = products[productId];
        if (!prod) return;

        const existing = items.find(i => String(i.product_id) === String(productId));
        if (existing) {
            existing.quantity = (parseFloat(existing.quantity) || 0) + 1;
        } else {
            items.push({
                product_id: prod.id,
                quantity: 1,
                unit_id: null,
                purchase_price: prod.purchase_price || 0,
            });
        }
        renderItems();
    }

    // Search product
    $('#pdo-product-search').on('input', function () {
        const q = $(this).val().toLowerCase().trim();
        const $res = $('#pdo-search-results');
        if (q.length === 0) {
            $res.hide().empty();
            return;
        }

        const matches = Object.values(products).filter(p =>
            p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q))
        ).slice(0, 10);

        $res.empty();
        if (matches.length === 0) {
            $res.append(`<div style="padding:10px 14px; color:var(--ink-400); font-size:13px;">কোনো পণ্য পাওয়া যায়নি</div>`);
        } else {
            matches.forEach(p => {
                $res.append(`
                    <div class="pdo-search-item" data-id="${p.id}" style="padding:10px 14px; cursor:pointer; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:600; font-size:13px; color:var(--ink-900);">${p.name}</div>
                            <div style="font-size:12px; color:var(--ink-400);">SKU: ${p.sku || '—'} | স্টক: ${p.current_stock}</div>
                        </div>
                        <span style="font-weight:700; color:#10b981; font-size:13px;">৳${p.purchase_price.toFixed(2)}</span>
                    </div>
                `);
            });
        }
        $res.show();
    });

    $(document).on('click', '.pdo-search-item', function () {
        const id = $(this).data('id');
        addItem(id);
        $('#pdo-product-search').val('');
        $('#pdo-search-results').hide().empty();
    });

    // Close search on click outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#pdo-product-search, #pdo-search-results').length) {
            $('#pdo-search-results').hide();
        }
    });

    // Barcode scanner input
    $('#pdo-barcode-input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = $(this).val().trim();
            if (code) {
                const found = Object.values(products).find(p => p.barcode === code);
                if (found) {
                    addItem(found.id);
                    $(this).val('');
                } else {
                    alert('বারকোড মিলছে না');
                }
            }
        }
    });

    // Change qty or price
    $(document).on('input', '.pdo-item-qty', function () {
        const index = $(this).data('index');
        items[index].quantity = parseFloat($(this).val()) || 0;
        renderItems();
    });

    $(document).on('input', '.pdo-item-price', function () {
        const index = $(this).data('index');
        items[index].purchase_price = parseFloat($(this).val()) || 0;
        renderItems();
    });

    $(document).on('change', '.pdo-item-unit', function () {
        const index = $(this).data('index');
        items[index].unit_id = $(this).val() || null;
    });

    // Remove item
    $(document).on('click', '.pdo-item-remove', function () {
        const index = $(this).data('index');
        items.splice(index, 1);
        renderItems();
    });

    // Recalculate on discount / delivery charge input
    $('#calc-discount, #calc-delivery-charge').on('input', function () {
        recalculateSummary();
    });

    // Supplier select behavior
    $('#pdo-supplier-select').on('change', function () {
        if ($(this).val()) {
            $('#pdo-custom-supplier-box').hide();
        } else {
            $('#pdo-custom-supplier-box').show();
        }
    });
    if ($('#pdo-supplier-select').val()) {
        $('#pdo-custom-supplier-box').hide();
    }

    // Load initial items if edit or old
    if (initialItems && initialItems.length > 0) {
        items = initialItems;
        renderItems();
    }
});
</script>
