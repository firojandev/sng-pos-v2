<x-core::layout
    title="স্টক ট্রান্সফার"
    title-en="Stock Transfer"
    subtitle="এক গুদাম থেকে অন্য গুদামে পণ্য স্থানান্তর করুন"
    subtitle-en="Move stock from one warehouse to another"
    active="stock-transfers"
>
    @php
        $productData = [];
        foreach ($products as $product) {
            $productData[$product->id] = ['label' => $product->name.' ('.$product->sku.')'];
        }
    @endphp

    <script id="transfer-products-data" type="application/json">{!! json_encode($productData) !!}</script>
    <script id="transfer-batches-data" type="application/json">{!! json_encode($batchesByWarehouseAndProduct) !!}</script>

    <div class="panel" style="margin-top:0; max-width:900px;">
        <div class="panel-head">
            <div class="panel-title bn">নতুন স্টক ট্রান্সফার</div>
            <div class="panel-title en" style="display:none;">New Stock Transfer</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('stock-transfers.store') }}">
                @csrf

                @error('items') <div class="field-error" style="margin-bottom:14px;">{{ $message }}</div> @enderror

                <div class="field-row">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">প্রেরণকারী গুদাম</label><label class="en" style="display:none;">From Warehouse</label>
                        <select name="from_warehouse_id" id="from-warehouse" required>
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ (string) old('from_warehouse_id') === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} @if($warehouse->branch) ({{ $warehouse->branch->name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="margin-top:0;">
                        <label class="bn">গ্রহণকারী গুদাম</label><label class="en" style="display:none;">To Warehouse</label>
                        <select name="to_warehouse_id" required>
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ (string) old('to_warehouse_id') === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} @if($warehouse->branch) ({{ $warehouse->branch->name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="overflow-x:auto; margin-top:18px;">
                    <table class="pos-table">
                        <thead>
                            <tr>
                                <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                                <th class="bn">উৎস ব্যাচ</th><th class="en" style="display:none;">Source Batch</th>
                                <th class="bn">পরিমাণ</th><th class="en" style="display:none;">Quantity</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="items-container">
                            <tr class="item-row" data-index="0">
                                <td style="min-width:220px;">
                                    <select name="items[0][product_id]" class="product-select" required>
                                        <option value="">-- নির্বাচন করুন --</option>
                                        @foreach ($productData as $pid => $info)
                                            <option value="{{ $pid }}">{{ $info['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="min-width:200px;">
                                    <select name="items[0][batch_id]" class="batch-select" required>
                                        <option value="">-- আগে গুদাম ও পণ্য নির্বাচন করুন --</option>
                                    </select>
                                </td>
                                <td style="width:120px;">
                                    <input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="qty-input" required>
                                </td>
                                <td style="width:40px;"><button type="button" class="pos-rm remove-item-btn" title="Remove">&times;</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button type="button" class="pos-addrow" id="add-item-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="bn">আইটেম যোগ করুন</span><span class="en">Add Item</span>
                </button>

                <div class="field" style="max-width:520px;">
                    <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
                    <textarea name="note" placeholder="ঐচ্ছিক নোট">{{ old('note') }}</textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px; max-width:320px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">অনুরোধ পাঠান</span><span class="en">Send Request</span>
                    </button>
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const productData = JSON.parse(document.getElementById('transfer-products-data').textContent);
        const batchesByWarehouse = JSON.parse(document.getElementById('transfer-batches-data').textContent);
        const fromWarehouse = document.getElementById('from-warehouse');
        const container = document.getElementById('items-container');
        const addBtn = document.getElementById('add-item-btn');
        let rowCount = 1;

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

        function populateBatches(row) {
            const batchSelect = row.querySelector('.batch-select');
            const productId = row.querySelector('.product-select').value;
            const warehouseId = fromWarehouse.value;
            let html = '<option value="">-- ব্যাচ নির্বাচন করুন --</option>';
            const batches = (batchesByWarehouse[warehouseId] || {})[productId] || [];
            batches.forEach((b) => {
                html += '<option value="'+b.id+'">'+escapeHtml(b.label)+'</option>';
            });
            batchSelect.innerHTML = html;
        }

        function newRowHtml(index) {
            return '<tr class="item-row" data-index="'+index+'">' +
                '<td style="min-width:220px;"><select name="items['+index+'][product_id]" class="product-select" required>'+buildProductOptions()+'</select></td>' +
                '<td style="min-width:200px;"><select name="items['+index+'][batch_id]" class="batch-select" required><option value="">-- আগে গুদাম ও পণ্য নির্বাচন করুন --</option></select></td>' +
                '<td style="width:120px;"><input type="number" step="0.01" min="0.01" name="items['+index+'][quantity]" class="qty-input" required></td>' +
                '<td style="width:40px;"><button type="button" class="pos-rm remove-item-btn" title="Remove">&times;</button></td>' +
            '</tr>';
        }

        fromWarehouse.addEventListener('change', () => {
            container.querySelectorAll('.item-row').forEach(populateBatches);
        });

        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('product-select')) {
                populateBatches(e.target.closest('.item-row'));
            }
        });

        container.addEventListener('click', (e) => {
            if (e.target.closest('.remove-item-btn')) {
                const rows = container.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                } else {
                    toast('অন্তত একটি আইটেম প্রয়োজন', 'At least one item is required');
                }
            }
        });

        addBtn.addEventListener('click', () => {
            container.insertAdjacentHTML('beforeend', newRowHtml(rowCount));
            rowCount++;
        });
    })();
    </script>
</x-core::layout>
