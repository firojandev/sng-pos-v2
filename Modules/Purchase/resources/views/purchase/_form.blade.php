@php
    $productData = [];
    foreach ($products as $product) {
        $productData[$product->id] = [
            'label' => $product->name.' ('.$product->sku.')',
            'price' => (float) $product->purchase_price,
        ];
    }

    $initialItems = old('items', $purchase->exists
        ? $purchase->items->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'batch_no' => $item->batch_no,
            'mfg_date' => optional($item->mfg_date)->format('Y-m-d'),
            'expiry_date' => optional($item->expiry_date)->format('Y-m-d'),
            'quantity' => rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.'),
            'purchase_price' => rtrim(rtrim(number_format($item->purchase_price, 2, '.', ''), '0'), '.'),
        ])->toArray()
        : [['product_id' => '', 'batch_no' => '', 'mfg_date' => '', 'expiry_date' => '', 'quantity' => '', 'purchase_price' => '']]
    );
@endphp

<script id="purchase-products-data" type="application/json">{!! json_encode($productData) !!}</script>

<div class="pos-header">
    <div>
        <div class="ttl bn">{{ $purchase->exists ? 'ক্রয় সম্পাদনা' : 'নতুন ক্রয়' }}</div>
        <div class="ttl en" style="display:none;">{{ $purchase->exists ? 'Edit Purchase' : 'New Purchase' }}</div>
        <div class="meta">
            <span class="bn">ইনভয়েস: </span><span class="en" style="display:none;">Invoice: </span>{{ $purchase->invoice_no ?? 'স্বয়ংক্রিয়ভাবে তৈরি হবে' }}
        </div>
    </div>

    <div class="fld party">
        <label class="bn">সরবরাহকারী</label><label class="en" style="display:none;">Supplier</label>
        <select name="supplier_id">
            <option value="">-- নির্বাচন করুন --</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ (string) old('supplier_id', $purchase->supplier_id) === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="fld">
        <label class="bn">তারিখ</label><label class="en" style="display:none;">Date</label>
        <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($purchase->purchase_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>

    <a href="{{ route('purchase.index') }}" class="pos-close" title="Back">&times;</a>
</div>

@error('items') <div class="field-error" style="margin:10px 22px 0;">{{ $message }}</div> @enderror

<div class="pos-body">
    <div class="pos-items-wrap">
        <div style="overflow-x:auto;">
            <table class="pos-table">
                <thead>
                    <tr>
                        <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                        <th class="bn">ব্যাচ নং</th><th class="en" style="display:none;">Batch No</th>
                        <th class="bn">উৎপাদন</th><th class="en" style="display:none;">Mfg</th>
                        <th class="bn">মেয়াদ</th><th class="en" style="display:none;">Exp</th>
                        <th class="bn">পরিমাণ</th><th class="en" style="display:none;">Qty</th>
                        <th class="bn">মূল্য</th><th class="en" style="display:none;">Price</th>
                        <th class="bn">মোট</th><th class="en" style="display:none;">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-container">
                    @foreach ($initialItems as $i => $row)
                        <tr class="item-row" data-index="{{ $i }}">
                            <td style="min-width:220px;">
                                <select name="items[{{ $i }}][product_id]" class="product-select" required>
                                    <option value="">-- নির্বাচন করুন --</option>
                                    @foreach ($productData as $pid => $info)
                                        <option value="{{ $pid }}" {{ (string) ($row['product_id'] ?? '') === (string) $pid ? 'selected' : '' }}>{{ $info['label'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="min-width:130px;">
                                <input type="text" name="items[{{ $i }}][batch_no]" class="batch-input" value="{{ $row['batch_no'] ?? '' }}" placeholder="BT-001" required>
                            </td>
                            <td style="min-width:140px;">
                                <input type="date" name="items[{{ $i }}][mfg_date]" value="{{ $row['mfg_date'] ?? '' }}">
                            </td>
                            <td style="min-width:140px;">
                                <input type="date" name="items[{{ $i }}][expiry_date]" value="{{ $row['expiry_date'] ?? '' }}">
                            </td>
                            <td style="width:100px;">
                                <input type="number" step="0.01" min="0.01" name="items[{{ $i }}][quantity]" class="qty-input" value="{{ $row['quantity'] ?? '' }}" required>
                            </td>
                            <td style="width:120px;">
                                <input type="number" step="0.01" min="0" name="items[{{ $i }}][purchase_price]" class="price-input" value="{{ $row['purchase_price'] ?? '' }}" required>
                            </td>
                            <td class="lt"><span class="line-total">0.00</span><input type="hidden" class="total-hidden" value="0"></td>
                            <td style="width:40px;"><button type="button" class="pos-rm remove-item-btn" title="Remove">&times;</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="button" class="pos-addrow" id="add-item-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <span class="bn">আইটেম যোগ করুন</span><span class="en">Add Item</span>
        </button>

        <div class="field" style="margin-top:18px; max-width:520px;">
            <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
            <textarea name="note" placeholder="ঐচ্ছিক নোট">{{ old('note', $purchase->note) }}</textarea>
        </div>
    </div>

    <div class="pos-summary">
        <div class="sum-row">
            <span class="sum-label bn">উপমোট</span><span class="sum-label en" style="display:none;">Subtotal</span>
            <span id="subtotal-display">0.00</span>
        </div>
        <div class="sum-row">
            <span class="sum-label bn">ছাড়</span><span class="sum-label en" style="display:none;">Discount</span>
            <input type="number" step="0.01" min="0" name="discount" id="discount-input" value="{{ old('discount', $purchase->discount ?? 0) }}" style="width:100px; text-align:right; border:1px solid var(--border); border-radius:8px; padding:6px 8px; font-family:'Manrope',sans-serif;">
        </div>
        <div class="sum-row total">
            <span class="sum-label bn">সর্বমোট</span><span class="sum-label en" style="display:none;">Total</span>
            <b id="total-display">0.00</b>
        </div>

        <div class="field">
            <label class="bn">পরিশোধিত (৳)</label><label class="en" style="display:none;">Paid Amount (৳)</label>
            <input type="number" step="0.01" min="0" name="paid_amount" id="paid-input" value="{{ old('paid_amount', $purchase->paid_amount ?? 0) }}">
        </div>

        <div class="sum-row">
            <span class="sum-label bn">বাকি</span><span class="sum-label en" style="display:none;">Due</span>
            <span id="due-display" style="font-weight:800; color:var(--red-600);">0.00</span>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
            </button>
            <a href="{{ route('purchase.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                <span class="bn">বাতিল</span><span class="en">Cancel</span>
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    const productData = JSON.parse(document.getElementById('purchase-products-data').textContent);
    const container = document.getElementById('items-container');
    const addBtn = document.getElementById('add-item-btn');
    let rowCount = container.querySelectorAll('.item-row').length;

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function buildProductOptions() {
        let html = '<option value="">-- নির্বাচন করুন --</option>';
        Object.keys(productData).forEach((pid) => {
            html += '<option value="'+pid+'">'+escapeHtml(productData[pid].label)+'</option>';
        });
        return html;
    }

    function newRowHtml(index) {
        return '<tr class="item-row" data-index="'+index+'">' +
            '<td style="min-width:220px;"><select name="items['+index+'][product_id]" class="product-select" required>'+buildProductOptions()+'</select></td>' +
            '<td style="min-width:130px;"><input type="text" name="items['+index+'][batch_no]" class="batch-input" placeholder="BT-001" required></td>' +
            '<td style="min-width:140px;"><input type="date" name="items['+index+'][mfg_date]"></td>' +
            '<td style="min-width:140px;"><input type="date" name="items['+index+'][expiry_date]"></td>' +
            '<td style="width:100px;"><input type="number" step="0.01" min="0.01" name="items['+index+'][quantity]" class="qty-input" required></td>' +
            '<td style="width:120px;"><input type="number" step="0.01" min="0" name="items['+index+'][purchase_price]" class="price-input" required></td>' +
            '<td class="lt"><span class="line-total">0.00</span><input type="hidden" class="total-hidden" value="0"></td>' +
            '<td style="width:40px;"><button type="button" class="pos-rm remove-item-btn" title="Remove">&times;</button></td>' +
        '</tr>';
    }

    function recalcRow(row) {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const total = qty * price;
        row.querySelector('.line-total').textContent = total.toFixed(2);
        row.querySelector('.total-hidden').value = total.toFixed(2);
        recalcGrand();
    }

    function recalcGrand() {
        let subtotal = 0;
        container.querySelectorAll('.item-row').forEach((row) => {
            subtotal += parseFloat(row.querySelector('.total-hidden').value) || 0;
        });
        const discount = parseFloat(document.getElementById('discount-input')?.value) || 0;
        const total = Math.max(subtotal - discount, 0);
        const paid = parseFloat(document.getElementById('paid-input')?.value) || 0;
        const due = Math.max(total - paid, 0);
        document.getElementById('subtotal-display').textContent = subtotal.toFixed(2);
        document.getElementById('total-display').textContent = total.toFixed(2);
        document.getElementById('due-display').textContent = due.toFixed(2);
    }

    container.addEventListener('change', (e) => {
        const row = e.target.closest('.item-row');
        if (!row) return;
        if (e.target.classList.contains('product-select')) {
            const productId = e.target.value;
            const info = productData[productId];
            const priceInput = row.querySelector('.price-input');
            if (priceInput && info && !priceInput.value) priceInput.value = info.price;
            recalcRow(row);
        }
    });

    container.addEventListener('input', (e) => {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            recalcRow(e.target.closest('.item-row'));
        }
    });

    container.addEventListener('click', (e) => {
        if (e.target.closest('.remove-item-btn')) {
            const rows = container.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
                recalcGrand();
            } else {
                toast('অন্তত একটি আইটেম প্রয়োজন', 'At least one item is required');
            }
        }
    });

    addBtn.addEventListener('click', () => {
        container.insertAdjacentHTML('beforeend', newRowHtml(rowCount));
        rowCount++;
    });

    document.getElementById('discount-input')?.addEventListener('input', recalcGrand);
    document.getElementById('paid-input')?.addEventListener('input', recalcGrand);

    container.querySelectorAll('.item-row').forEach(recalcRow);
    recalcGrand();
})();
</script>
