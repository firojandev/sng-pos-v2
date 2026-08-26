@php
    $productData = [];
    foreach ($products as $product) {
        $productData[$product->id] = [
            'label' => $product->name.' ('.$product->sku.')',
            'price' => (float) $product->sale_price,
            'batches' => $product->batches->where('quantity', '>', 0)->map(fn ($b) => [
                'id' => $b->id,
                'label' => $b->batch_no.' ('.rtrim(rtrim(number_format($b->quantity, 2), '0'), '.').' পিস)',
                'qty' => (float) $b->quantity,
            ])->values(),
        ];
    }

    $initialItems = old('items', $sale->exists
        ? $sale->items->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'batch_id' => (string) $item->batch_id,
            'quantity' => rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.'),
            'unit_price' => rtrim(rtrim(number_format($item->unit_price, 2, '.', ''), '0'), '.'),
        ])->toArray()
        : [['product_id' => '', 'batch_id' => '', 'quantity' => '', 'unit_price' => '']]
    );

    $paymentMethods = \Modules\Core\Support\PaymentMethods::all();
    $initialPayments = old('payments', $sale->exists
        ? $sale->payments->map(fn ($payment) => [
            'method' => $payment->method,
            'amount' => rtrim(rtrim(number_format($payment->amount, 2, '.', ''), '0'), '.'),
        ])->toArray()
        : []
    );
@endphp

<script id="sale-products-data" type="application/json">{!! json_encode($productData) !!}</script>

<div class="pos-header">
    <div>
        <div class="ttl bn">{{ $sale->exists ? 'বিক্রয় সম্পাদনা' : 'নতুন বিক্রয়' }}</div>
        <div class="ttl en" style="display:none;">{{ $sale->exists ? 'Edit Sale' : 'New Sale' }}</div>
        <div class="meta">
            <span class="bn">ইনভয়েস: </span><span class="en" style="display:none;">Invoice: </span>{{ $sale->invoice_no ?? 'স্বয়ংক্রিয়ভাবে তৈরি হবে' }}
        </div>
    </div>

    <div class="fld party">
        <label class="bn">গ্রাহক</label><label class="en" style="display:none;">Customer</label>
        <select name="customer_id">
            <option value="">-- ওয়াক-ইন গ্রাহক --</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" {{ (string) old('customer_id', $sale->customer_id) === (string) $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($sale->exists)
        <div class="fld">
            <label class="bn">গুদাম</label><label class="en" style="display:none;">Warehouse</label>
            <input type="text" value="{{ $sale->warehouse->name ?? '—' }}" disabled style="background:var(--paper);">
        </div>
    @else
        <div class="fld">
            <label class="bn">গুদাম</label><label class="en" style="display:none;">Warehouse</label>
            <select onchange="window.location.href = '{{ route('sales.create') }}?warehouse_id=' + this.value;">
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ (string) $warehouseId === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} @if($warehouse->branch) ({{ $warehouse->branch->name }}) @endif</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
    @endif

    <div class="fld">
        <label class="bn">তারিখ</label><label class="en" style="display:none;">Date</label>
        <input type="date" name="sale_date" value="{{ old('sale_date', optional($sale->sale_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>

    <a href="{{ route('sales.index') }}" class="pos-close" title="Back">&times;</a>
</div>

@error('items') <div class="field-error" style="margin:10px 22px 0;">{{ $message }}</div> @enderror

<div class="pos-body">
    <div class="pos-items-wrap">
        <div style="overflow-x:auto;">
        <table class="pos-table">
            <thead>
                <tr>
                    <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                    <th class="bn">ব্যাচ</th><th class="en" style="display:none;">Batch</th>
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
                        <td style="min-width:200px;">
                            <select name="items[{{ $i }}][batch_id]" class="batch-select" required>
                                <option value="">-- ব্যাচ --</option>
                                @if (! empty($row['product_id']) && isset($productData[$row['product_id']]))
                                    @foreach ($productData[$row['product_id']]['batches'] as $batch)
                                        <option value="{{ $batch['id'] }}" {{ (string) ($row['batch_id'] ?? '') === (string) $batch['id'] ? 'selected' : '' }}>{{ $batch['label'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td style="width:100px;">
                            <input type="number" step="0.01" min="0.01" name="items[{{ $i }}][quantity]" class="qty-input" value="{{ $row['quantity'] ?? '' }}" required>
                        </td>
                        <td style="width:120px;">
                            <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" class="price-input" value="{{ $row['unit_price'] ?? '' }}" required>
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
            <textarea name="note" placeholder="ঐচ্ছিক নোট">{{ old('note', $sale->note) }}</textarea>
        </div>
    </div>

    <div class="pos-summary">
        <div class="sum-row">
            <span class="sum-label bn">উপমোট</span><span class="sum-label en" style="display:none;">Subtotal</span>
            <span id="subtotal-display">0.00</span>
        </div>
        <div class="sum-row">
            <span class="sum-label bn">ছাড়</span><span class="sum-label en" style="display:none;">Discount</span>
            <input type="number" step="0.01" min="0" name="discount" id="discount-input" value="{{ old('discount', $sale->discount ?? 0) }}" style="width:100px; text-align:right; border:1px solid var(--border); border-radius:8px; padding:6px 8px; font-family:'Manrope',sans-serif;">
        </div>
        <div class="sum-row total">
            <span class="sum-label bn">সর্বমোট</span><span class="sum-label en" style="display:none;">Total</span>
            <b id="total-display">0.00</b>
        </div>

        <div class="field">
            <label class="bn">পেমেন্ট</label><label class="en" style="display:none;">Payment</label>
            <div id="payment-lines">
                @foreach ($initialPayments as $i => $row)
                    <div class="payment-line" data-index="{{ $i }}">
                        <select name="payments[{{ $i }}][method]" class="payment-method-select">
                            @foreach ($paymentMethods as $key => $label)
                                <option value="{{ $key }}" {{ ($row['method'] ?? 'cash') === $key ? 'selected' : '' }}>{{ $label['bn'] }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" min="0.01" name="payments[{{ $i }}][amount]" class="payment-amount-input" value="{{ $row['amount'] ?? '' }}" placeholder="পরিমাণ">
                        <button type="button" class="pos-rm remove-payment-btn" title="Remove">&times;</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="add-payment-btn" id="add-payment-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="bn">পেমেন্ট যোগ করুন</span><span class="en">Add Payment</span>
            </button>
        </div>

        <div class="sum-row">
            <span class="sum-label bn">বাকি</span><span class="sum-label en" style="display:none;">Due</span>
            <span id="due-display" style="font-weight:800; color:var(--red-600);">0.00</span>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                <span class="bn">বাতিল</span><span class="en">Cancel</span>
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    const productData = JSON.parse(document.getElementById('sale-products-data').textContent);
    const container = document.getElementById('items-container');
    const addBtn = document.getElementById('add-item-btn');
    let rowCount = container.querySelectorAll('.item-row').length;

    const paymentLines = document.getElementById('payment-lines');
    const addPaymentBtn = document.getElementById('add-payment-btn');
    const paymentMethodOptions = {!! json_encode(collect($paymentMethods)->map(fn ($label, $key) => '<option value="'.$key.'">'.$label['bn'].'</option>')->implode('')) !!};
    let paymentCount = paymentLines.querySelectorAll('.payment-line').length;

    function newPaymentLineHtml(index) {
        return '<div class="payment-line" data-index="'+index+'">' +
            '<select name="payments['+index+'][method]" class="payment-method-select">'+paymentMethodOptions+'</select>' +
            '<input type="number" step="0.01" min="0.01" name="payments['+index+'][amount]" class="payment-amount-input" placeholder="পরিমাণ">' +
            '<button type="button" class="pos-rm remove-payment-btn" title="Remove">&times;</button>' +
        '</div>';
    }

    addPaymentBtn.addEventListener('click', () => {
        paymentLines.insertAdjacentHTML('beforeend', newPaymentLineHtml(paymentCount));
        paymentCount++;
    });

    paymentLines.addEventListener('click', (e) => {
        if (e.target.closest('.remove-payment-btn')) {
            e.target.closest('.payment-line').remove();
            recalcGrand();
        }
    });

    paymentLines.addEventListener('input', (e) => {
        if (e.target.classList.contains('payment-amount-input')) {
            recalcGrand();
        }
    });

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
            '<td style="min-width:200px;"><select name="items['+index+'][batch_id]" class="batch-select" required><option value="">-- ব্যাচ --</option></select></td>' +
            '<td style="width:100px;"><input type="number" step="0.01" min="0.01" name="items['+index+'][quantity]" class="qty-input" required></td>' +
            '<td style="width:120px;"><input type="number" step="0.01" min="0" name="items['+index+'][unit_price]" class="price-input" required></td>' +
            '<td class="lt"><span class="line-total">0.00</span><input type="hidden" class="total-hidden" value="0"></td>' +
            '<td style="width:40px;"><button type="button" class="pos-rm remove-item-btn" title="Remove">&times;</button></td>' +
        '</tr>';
    }

    function populateBatches(row, productId, selectedBatchId) {
        const batchSelect = row.querySelector('.batch-select');
        if (!batchSelect) return;
        let html = '<option value="">-- ব্যাচ --</option>';
        const info = productData[productId];
        if (info) {
            info.batches.forEach((b) => {
                const sel = String(b.id) === String(selectedBatchId) ? ' selected' : '';
                html += '<option value="'+b.id+'"'+sel+'>'+escapeHtml(b.label)+'</option>';
            });
        }
        batchSelect.innerHTML = html;
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
        let paid = 0;
        paymentLines.querySelectorAll('.payment-amount-input').forEach((input) => {
            paid += parseFloat(input.value) || 0;
        });
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
            if (priceInput && info) priceInput.value = info.price;
            populateBatches(row, productId, null);
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

    container.querySelectorAll('.item-row').forEach(recalcRow);
    recalcGrand();
})();
</script>
