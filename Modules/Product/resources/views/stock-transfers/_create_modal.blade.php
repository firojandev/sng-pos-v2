@php
    $productData = [];
    if (isset($products)) {
        foreach ($products as $product) {
            $productData[$product->id] = ['label' => $product->sku ? $product->name.' ('.$product->sku.')' : $product->name];
        }
    }

    $warehouseOptions = [];
    if (isset($warehouses)) {
        foreach ($warehouses as $warehouse) {
            $whName = $warehouse->name . ($warehouse->branch ? ' (' . $warehouse->branch->name . ')' : '');
            $warehouseOptions[$warehouse->id] = $whName;
        }
    }
@endphp

<script id="modal-transfer-products-data" type="application/json">{!! json_encode($productData) !!}</script>
<script id="modal-transfer-batches-data" type="application/json">{!! json_encode($batchesByWarehouseAndProduct ?? []) !!}</script>

{{-- Create Stock Transfer Modal --}}
<div class="modal-backdrop" id="createStockTransferModal" style="z-index:999;">
    <div class="modal-box" style="width:840px; max-width:96vw; max-height:92vh; overflow-y:auto; padding:24px; border-radius:16px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
        {{-- Modal Header --}}
        <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                    <x-core::icon name="arrow-left-right" size="18" />
                </div>
                <div class="modal-title" style="font-size:16.5px; font-weight:700; color:var(--ink-900);">
                    <span class="bn">নতুন স্টক ট্রান্সফার</span>
                    <span class="en" style="display:none;">New Stock Transfer</span>
                </div>
            </div>
            <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('stock-transfers.store') }}" id="create_stock_transfer_form">
            @csrf

            <div id="modal-transfer-error-box" class="field-error" style="display:none; margin-bottom:14px; padding:10px 14px; background:var(--red-100); border:1px solid var(--border); border-radius:8px; color:var(--red-600); font-size:13px; font-weight:500;"></div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <x-core::select
                    name="from_warehouse_id"
                    id="modal-from-warehouse"
                    label="প্রেরণকারী গুদাম"
                    label-en="From Warehouse"
                    placeholder="-- নির্বাচন করুন --"
                    placeholder-en="-- Select Warehouse --"
                    :options="$warehouseOptions"
                    size="sm"
                    :required="true"
                />

                <x-core::select
                    name="to_warehouse_id"
                    id="modal-to-warehouse"
                    label="গ্রহণকারী গুদাম"
                    label-en="To Warehouse"
                    placeholder="-- নির্বাচন করুন --"
                    placeholder-en="-- Select Warehouse --"
                    :options="$warehouseOptions"
                    size="sm"
                    :required="true"
                />
            </div>

            <div style="overflow-x:auto; margin-top:16px; border:1px solid var(--border); border-radius:10px;">
                <table class="app-table" style="width:100%; border-collapse:collapse; margin:0;">
                    <thead>
                        <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                            <th style="padding:10px 12px; text-align:left; font-size:12.5px; font-weight:700; color:var(--ink-700);"><span class="bn">পণ্য</span><span class="en" style="display:none;">Product</span></th>
                            <th style="padding:10px 12px; text-align:left; font-size:12.5px; font-weight:700; color:var(--ink-700);"><span class="bn">উৎস ব্যাচ</span><span class="en" style="display:none;">Source Batch</span></th>
                            <th style="padding:10px 12px; text-align:left; font-size:12.5px; font-weight:700; color:var(--ink-700); width:140px;"><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Quantity</span></th>
                            <th style="width:44px; text-align:center;"></th>
                        </tr>
                    </thead>
                    <tbody id="modal-items-container">
                        <tr class="modal-item-row" data-index="0" style="border-bottom:1px solid var(--border);">
                            <td style="min-width:220px; padding:8px 10px;">
                                <select name="items[0][product_id]" class="form-control form-select form-control-sm modal-product-select" style="font-size:13px; font-family:'Noto Sans Bengali', sans-serif;" required>
                                    <option value="">-- নির্বাচন করুন --</option>
                                    @foreach ($productData as $pid => $info)
                                        <option value="{{ $pid }}">{{ $info['label'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="min-width:200px; padding:8px 10px;">
                                <select name="items[0][batch_id]" class="form-control form-select form-control-sm modal-batch-select" style="font-size:13px; font-family:'Noto Sans Bengali', sans-serif;" required>
                                    <option value="">-- আগে প্রেরণকারী গুদাম নির্বাচন করুন --</option>
                                </select>
                            </td>
                            <td style="width:140px; padding:8px 10px;">
                                <input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control form-control-sm modal-qty-input" style="font-size:13px; font-family:var(--font-mono, monospace);" placeholder="0.00" required>
                                <div class="modal-batch-max-hint" style="font-size:11px; color:var(--ink-500); margin-top:2px; display:none;"></div>
                            </td>
                            <td style="width:44px; text-align:center; padding:8px 10px;">
                                <button type="button" class="modal-remove-item-btn" style="width:30px; height:30px; border-radius:8px; border:none; background:transparent; color:var(--red-600); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:background-color 0.15s ease;" title="Remove">
                                    <x-core::icon name="trash-2" size="15" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:12px;">
                <x-core::button type="button" variant="secondary" size="sm" icon="plus" id="modal-add-item-btn">
                    <span class="bn">আইটেম যোগ করুন</span>
                    <span class="en" style="display:none;">Add Item</span>
                </x-core::button>
            </div>

            <div style="margin-top:18px;">
                <x-core::textarea
                    name="note"
                    id="modal_transfer_note"
                    label="নোট"
                    label-en="Note"
                    placeholder="ঐচ্ছিক নোট"
                    placeholder-en="Optional note"
                    rows="2"
                    size="sm"
                />
            </div>

            <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="modal-close-btn"
                >
                    <span class="bn">বাতিল</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>
                <x-core::button
                    type="submit"
                    variant="solid"
                    color="primary"
                    size="sm"
                    icon="check"
                    id="btn-save-stock-transfer"
                >
                    <span class="bn">অনুরোধ পাঠান</span>
                    <span class="en" style="display:none;">Send Request</span>
                </x-core::button>
            </div>
        </form>
    </div>
</div>
