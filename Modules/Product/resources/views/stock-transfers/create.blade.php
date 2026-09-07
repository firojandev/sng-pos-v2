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
            $productData[$product->id] = ['label' => $product->sku ? $product->name.' ('.$product->sku.')' : $product->name];
        }

        $warehouseOptions = [];
        foreach ($warehouses as $warehouse) {
            $whName = $warehouse->name . ($warehouse->branch ? ' (' . $warehouse->branch->name . ')' : '');
            $warehouseOptions[$warehouse->id] = $whName;
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
            <form method="POST" action="{{ route('stock-transfers.store') }}" id="stock-transfer-form">
                @csrf

                @error('items') <div class="field-error" style="margin-bottom:14px;">{{ $message }}</div> @enderror

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <x-core::select
                        name="from_warehouse_id"
                        id="from-warehouse"
                        label="প্রেরণকারী গুদাম"
                        label-en="From Warehouse"
                        placeholder="-- নির্বাচন করুন --"
                        placeholder-en="-- Select Warehouse --"
                        :options="$warehouseOptions"
                        :value="old('from_warehouse_id')"
                        size="sm"
                        :required="true"
                    />

                    <x-core::select
                        name="to_warehouse_id"
                        id="to-warehouse"
                        label="গ্রহণকারী গুদাম"
                        label-en="To Warehouse"
                        placeholder="-- নির্বাচন করুন --"
                        placeholder-en="-- Select Warehouse --"
                        :options="$warehouseOptions"
                        :value="old('to_warehouse_id')"
                        size="sm"
                        :required="true"
                    />
                </div>

                <div style="overflow-x:auto; margin-top:20px;">
                    <table class="app-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                                <th style="padding:10px 12px; text-align:left; font-size:12.5px; font-weight:700; color:var(--ink-700);"><span class="bn">পণ্য</span><span class="en" style="display:none;">Product</span></th>
                                <th style="padding:10px 12px; text-align:left; font-size:12.5px; font-weight:700; color:var(--ink-700);"><span class="bn">উৎস ব্যাচ</span><span class="en" style="display:none;">Source Batch</span></th>
                                <th style="padding:10px 12px; text-align:left; font-size:12.5px; font-weight:700; color:var(--ink-700); width:130px;"><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Quantity</span></th>
                                <th style="width:44px; text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody id="items-container">
                            <tr class="item-row" data-index="0" style="border-bottom:1px solid var(--border);">
                                <td style="min-width:220px; padding:8px 10px;">
                                    <select name="items[0][product_id]" class="form-control form-select form-control-sm product-select" required>
                                        <option value="">-- নির্বাচন করুন --</option>
                                        @foreach ($productData as $pid => $info)
                                            <option value="{{ $pid }}">{{ $info['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="min-width:200px; padding:8px 10px;">
                                    <select name="items[0][batch_id]" class="form-control form-select form-control-sm batch-select" required>
                                        <option value="">-- আগে গুদাম ও পণ্য নির্বাচন করুন --</option>
                                    </select>
                                </td>
                                <td style="width:130px; padding:8px 10px;">
                                    <input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control form-control-sm qty-input" placeholder="0.00" required>
                                </td>
                                <td style="width:44px; text-align:center; padding:8px 10px;">
                                    <button type="button" class="remove-item-btn" style="width:30px; height:30px; border-radius:8px; border:none; background:transparent; color:var(--red-600); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:background-color 0.15s ease;" title="Remove">
                                        <x-core::icon name="trash-2" size="15" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:12px;">
                    <x-core::button type="button" variant="secondary" size="sm" icon="plus" id="add-item-btn">
                        <span class="bn">আইটেম যোগ করুন</span>
                        <span class="en" style="display:none;">Add Item</span>
                    </x-core::button>
                </div>

                <div style="max-width:520px; margin-top:20px;">
                    <x-core::textarea
                        name="note"
                        label="নোট"
                        label-en="Note"
                        placeholder="ঐচ্ছিক নোট"
                        placeholder-en="Optional note"
                        :value="old('note')"
                        rows="2"
                        size="sm"
                    />
                </div>

                <div style="display:flex; gap:10px; margin-top:24px; max-width:320px;">
                    <x-core::button type="submit" color="primary" size="sm" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">অনুরোধ পাঠান</span><span class="en" style="display:none;">Send Request</span>
                    </x-core::button>
                    <x-core::button as="a" href="{{ route('stock-transfers.index') }}" variant="secondary" size="sm" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    $(function () {
        var productData = {};
        var batchesByWarehouse = {};
        try {
            productData = JSON.parse($('#transfer-products-data').text()) || {};
            batchesByWarehouse = JSON.parse($('#transfer-batches-data').text()) || {};
        } catch (e) {
            productData = {};
            batchesByWarehouse = {};
        }

        var rowCount = 1;

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function buildProductOptions() {
            var html = '<option value="">-- নির্বাচন করুন --</option>';
            $.each(productData, function (pid, info) {
                html += '<option value="' + pid + '">' + escapeHtml(info.label) + '</option>';
            });
            return html;
        }

        function populateBatches($row) {
            var $batchSelect = $row.find('.batch-select');
            var productId = $row.find('.product-select').val();
            var warehouseId = $('#from-warehouse').val();
            var html = '<option value="">-- ব্যাচ নির্বাচন করুন --</option>';
            var batches = (batchesByWarehouse[warehouseId] || {})[productId] || [];
            $.each(batches, function (i, b) {
                html += '<option value="' + b.id + '">' + escapeHtml(b.label) + '</option>';
            });
            $batchSelect.html(html);
        }

        function newRowHtml(index) {
            return '<tr class="item-row" data-index="' + index + '" style="border-bottom:1px solid var(--border);">' +
                '<td style="min-width:220px; padding:8px 10px;">' +
                    '<select name="items[' + index + '][product_id]" class="form-control form-select form-control-sm product-select" required>' +
                        buildProductOptions() +
                    '</select>' +
                '</td>' +
                '<td style="min-width:200px; padding:8px 10px;">' +
                    '<select name="items[' + index + '][batch_id]" class="form-control form-select form-control-sm batch-select" required>' +
                        '<option value="">-- আগে গুদাম ও পণ্য নির্বাচন করুন --</option>' +
                    '</select>' +
                '</td>' +
                '<td style="width:130px; padding:8px 10px;">' +
                    '<input type="number" step="0.01" min="0.01" name="items[' + index + '][quantity]" class="form-control form-control-sm qty-input" placeholder="0.00" required>' +
                '</td>' +
                '<td style="width:44px; text-align:center; padding:8px 10px;">' +
                    '<button type="button" class="remove-item-btn" style="width:30px; height:30px; border-radius:8px; border:none; background:transparent; color:var(--red-600); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:background-color 0.15s ease;" title="Remove">' +
                        '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2M10 11v6M14 11v6"/></svg>' +
                    '</button>' +
                '</td>' +
            '</tr>';
        }

        $(document).on('change', '#from-warehouse', function () {
            $('#items-container .item-row').each(function () {
                populateBatches($(this));
            });
        });

        $(document).on('change', '.product-select', function () {
            populateBatches($(this).closest('.item-row'));
        });

        $(document).on('click', '.remove-item-btn', function () {
            var $rows = $('#items-container .item-row');
            if ($rows.length > 1) {
                $(this).closest('.item-row').remove();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'সতর্কতা',
                        text: 'অন্তত একটি আইটেম প্রয়োজন',
                        confirmButtonColor: 'var(--teal-700)'
                    });
                } else if (typeof window.toast === 'function') {
                    window.toast('অন্তত একটি আইটেম প্রয়োজন', 'At least one item is required');
                }
            }
        });

        $('#add-item-btn').on('click', function () {
            $('#items-container').append(newRowHtml(rowCount));
            rowCount++;
        });
    });
    </script>
    @endpush
</x-core::layout>
