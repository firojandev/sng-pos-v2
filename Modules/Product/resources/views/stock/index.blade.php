<x-core::layout
    title="স্টক খাতা"
    title-en="Stock Ledger"
    subtitle="মজুদ পণ্য, দর ও স্টক অবস্থা পর্যবেক্ষণ করুন"
    subtitle-en="Monitor stock levels, valuations, and batch adjustments"
    active="stock"
>

    {{-- Summary Metric Cards --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
            <x-core::stat-card
                icon="package"
                color="teal"
                :value="number_format($metrics['totalProducts'])"
                label="মোট পণ্য"
                label-en="Total Products"
                subtext="ক্যাটালগ পণ্য"
                subtext-en="Catalog items"
            />

            <x-core::stat-card
                icon="boxes"
                color="blue"
                :value="rtrim(rtrim(number_format($metrics['totalQty'], 2), '0'), '.')"
                label="মোট মজুদ একক"
                label-en="Total Stock Units"
                subtext="সকল ব্যাচ মিলিয়ে"
                subtext-en="Across all batches"
            />

            <x-core::stat-card
                icon="banknote"
                color="green"
                :value="'৳' . number_format($metrics['totalValue'], 2)"
                value-color="green"
                label="মোট মজুদ মূল্য"
                label-en="Stock Valuation"
                subtext="ক্রয়মূল্য অনুসারে"
                subtext-en="At purchase cost"
            />

            <x-core::stat-card
                icon="alert-triangle"
                color="gold"
                :value="number_format($metrics['lowCount'])"
                value-color="gold"
                label="কম মজুদ পণ্য"
                label-en="Low Stock Alert"
                subtext="সতর্কতা সীমার নিচে"
                subtext-en="Below alert limit"
            />

            <x-core::stat-card
                icon="x-circle"
                color="red"
                :value="number_format($metrics['outCount'])"
                value-color="red"
                label="স্টক আউট"
                label-en="Out of Stock"
                subtext="শূন্য মজুদ পণ্য"
                subtext-en="Zero quantity left"
            />
        </div>
    @endif

    {{-- Filter Toolbar & Actions Row --}}
    <div class="section-row" style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_stock_status"
                    id="filter-stock-status"
                    size="sm"
                    :no-margin="true"
                    :options="[
                        '' => 'সকল স্টক অবস্থা (All)',
                        'in' => 'মজুদ আছে (In Stock)',
                        'low' => 'কম মজুদ (Low Stock)',
                        'out' => 'স্টক আউট (Out of Stock)'
                    ]"
                />
            </div>
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_category"
                    id="filter-category"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'সকল ক্যাটাগরি (All)'] + $categories->pluck('name', 'id')->toArray()"
                />
            </div>
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_brand"
                    id="filter-brand"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'সকল ব্র্যান্ড (All)'] + $brands->pluck('name', 'id')->toArray()"
                />
            </div>
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reset-filters"
                title="রিসেট / Reset"
            >
                <span class="bn">রিসেট</span>
                <span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>

        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="edit"
                id="btn-open-adjust-modal"
            >
                <span class="bn">স্টক সমন্বয়</span>
                <span class="en" style="display:none;">Adjust Stock</span>
            </x-core::button>

            <x-core::button
                :href="route('purchase.create')"
                size="sm"
                variant="secondary"
                icon="shopping-cart"
            >
                <span class="bn">নতুন ক্রয়</span>
                <span class="en" style="display:none;">New Purchase</span>
            </x-core::button>

            <x-core::button
                :href="route('products.create')"
                size="sm"
                color="primary"
                icon="plus"
            >
                <span class="bn">নতুন পণ্য</span>
                <span class="en" style="display:none;">Add Product</span>
            </x-core::button>
        </div>
    </div>

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'stock-data-table']) !!}
        </div>
    </div>

    {{-- Stock Adjustment Modal --}}
    <div class="modal-backdrop" id="stockAdjustModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700; color:var(--ink-900);">
                        <span class="bn">স্টক সমন্বয় করুন</span>
                        <span class="en" style="display:none;">Adjust Stock</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>

            <form method="POST" action="{{ route('stock.adjust') }}" id="form-stock-adjust">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <x-core::select
                            name="product_id"
                            id="adjust-product"
                            size="sm"
                            label="পণ্য"
                            label-en="Product"
                            :required="true"
                            placeholder="-- পণ্য নির্বাচন করুন --"
                            placeholder-en="-- Select Product --"
                            :options="$allProducts->pluck('name', 'id')->toArray()"
                        />
                    </div>

                    <div>
                        <x-core::select
                            name="batch_id"
                            id="adjust-batch"
                            size="sm"
                            label="ব্যাচ ও বর্তমান মজুদ"
                            label-en="Batch & Current Stock"
                            :required="true"
                            placeholder="-- আগে পণ্য নির্বাচন করুন --"
                            placeholder-en="-- Select Product First --"
                        />
                    </div>

                    <div>
                        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">সমন্বয়ের ধরন</label>
                        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Adjustment Type</label>
                        <div class="seg-toggle-group" style="display:flex; gap:6px; background:var(--paper-line); padding:4px; border-radius:8px;">
                            <button type="button" class="btn-seg-opt active" data-type="increase" style="flex:1; padding:7px 12px; font-size:12.5px; font-weight:600; border-radius:6px; border:none; cursor:pointer; background:var(--card); color:var(--teal-800); box-shadow:var(--shadow-sm); transition:all 0.15s ease;">
                                <span class="bn">+ স্টক বৃদ্ধি (Increase)</span>
                                <span class="en" style="display:none;">+ Increase</span>
                            </button>
                            <button type="button" class="btn-seg-opt" data-type="decrease" style="flex:1; padding:7px 12px; font-size:12.5px; font-weight:600; border-radius:6px; border:none; cursor:pointer; background:transparent; color:var(--ink-600); transition:all 0.15s ease;">
                                <span class="bn">- স্টক হ্রাস (Decrease)</span>
                                <span class="en" style="display:none;">- Decrease</span>
                            </button>
                        </div>
                        <input type="hidden" name="type" id="adjust-type" value="increase">
                    </div>

                    <div>
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="quantity"
                            id="adjust-quantity"
                            size="sm"
                            label="সমন্বয়ের পরিমাণ"
                            label-en="Adjustment Quantity"
                            placeholder="0.00"
                            :required="true"
                        />
                    </div>

                    <div>
                        <x-core::textarea
                            name="reason"
                            id="adjust-reason"
                            size="sm"
                            label="কারণ / মন্তব্য"
                            label-en="Reason / Remarks"
                            placeholder="যেমনঃ নষ্ট পণ্য, গণনা সংশোধন, স্থানান্তর ইত্যাদি"
                            placeholder-en="e.g. Damaged item, inventory count correction"
                            rows="2"
                        />
                    </div>
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
                        color="primary"
                        size="sm"
                        icon="check"
                        id="btn-submit-stock-adjust"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save Adjustment</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stock History Modal Container --}}
    <div id="stockHistoryModalContainer"></div>

    {{-- Batches Data Cache --}}
    <script id="stock-batches-data" type="application/json">{!! json_encode($batchesByProduct) !!}</script>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            var batchesByProduct = {};
            try {
                batchesByProduct = JSON.parse(document.getElementById('stock-batches-data').textContent) || {};
            } catch (e) {
                batchesByProduct = {};
            }

            function clearFormErrors($form) {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.field-error.dynamic-error').remove();
            }

            function showFormErrors($form, errors) {
                clearFormErrors($form);
                $.each(errors, function (field, messages) {
                    var $field = $form.find('[name="' + field + '"]');
                    if ($field.length) {
                        $field.addClass('is-invalid');
                        var msg = messages[0];
                        var $errorEl = $('<div class="field-error dynamic-error" style="color:var(--red-600); font-size:12px; margin-top:4px; font-weight:500;">' + msg + '</div>');
                        var $group = $field.closest('.form-group, .field, div');
                        $group.append($errorEl);
                    }
                });
            }

            function reloadStockTable() {
                var tableId = 'stock-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            function populateBatches(productId, selectedBatchId) {
                var batches = batchesByProduct[productId] || [];
                var $batchSelect = $('#adjust-batch');
                $batchSelect.empty();

                if (!batches.length) {
                    $batchSelect.append('<option value="">কোনো ব্যাচ পাওয়া যায়নি / No batches available</option>');
                    return;
                }

                $batchSelect.append('<option value="">-- ব্যাচ নির্বাচন করুন --</option>');
                $.each(batches, function (i, b) {
                    var isSel = (selectedBatchId && String(selectedBatchId) === String(b.id)) ? ' selected' : '';
                    $batchSelect.append('<option value="' + b.id + '"' + isSel + '>' + b.label + '</option>');
                });
            }

            function setAdjustType(type) {
                $('#adjust-type').val(type);
                $('.btn-seg-opt').removeClass('active').css({
                    'background': 'transparent',
                    'color': 'var(--ink-600)',
                    'box-shadow': 'none'
                });
                var $activeBtn = $('.btn-seg-opt[data-type="' + type + '"]');
                $activeBtn.addClass('active').css({
                    'background': 'var(--card)',
                    'color': type === 'increase' ? 'var(--teal-800)' : 'var(--red-600)',
                    'box-shadow': 'var(--shadow-sm)'
                });
            }

            function openAdjustStockModal(productId, batchId) {
                var $form = $('#form-stock-adjust');
                clearFormErrors($form);

                if (productId) {
                    $('#adjust-product').val(productId);
                    populateBatches(productId, batchId);
                } else {
                    $('#adjust-product').val('');
                    $('#adjust-batch').html('<option value="">-- আগে পণ্য নির্বাচন করুন --</option>');
                }

                $('#adjust-quantity').val('');
                $('#adjust-reason').val('');
                setAdjustType('increase');
                openModal('stockAdjustModal');
            }

            // Expose globally
            window.openAdjustModal = openAdjustStockModal;

            // Product selection change inside Adjust Modal
            $(document).on('change', '#adjust-product', function () {
                populateBatches($(this).val());
            });

            // Segmented Type Button Toggle
            $(document).on('click', '.btn-seg-opt', function (e) {
                e.preventDefault();
                setAdjustType($(this).data('type'));
            });

            // Open Adjust Modal from Header Button
            $(document).on('click', '#btn-open-adjust-modal', function (e) {
                e.preventDefault();
                openAdjustStockModal();
            });

            // Open Adjust Modal from Table Row Action
            $(document).on('click', '.btn-adjust-stock', function (e) {
                e.preventDefault();
                var productId = $(this).data('product-id');
                openAdjustStockModal(productId);
            });

            // Close Modal Handlers
            $(document).on('click', '.modal-close-btn', function (e) {
                e.preventDefault();
                $(this).closest('.modal-backdrop').removeClass('open');
            });

            $('#stockAdjustModal').on('click', function (e) {
                if ($(e.target).is('#stockAdjustModal')) {
                    closeModal('stockAdjustModal');
                }
            });

            // Filters Change
            $(document).on('change', '#filter-stock-status, #filter-category, #filter-brand', function () {
                reloadStockTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-stock-status').val('');
                $('#filter-category').val('');
                $('#filter-brand').val('');
                reloadStockTable();
            });

            // AJAX Form Submit for Stock Adjustment
            $('#form-stock-adjust').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-submit-stock-adjust');
                var url = $form.attr('action');

                clearFormErrors($form);
                $btn.prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (res) {
                        $btn.prop('disabled', false);
                        closeModal('stockAdjustModal');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'স্টক সফলভাবে সমন্বয় করা হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else if (typeof window.toast === 'function') {
                            window.toast(res.message || 'স্টক সফলভাবে সমন্বয় করা হয়েছে', res.message_en || 'Stock adjusted successfully');
                        }
                        reloadStockTable();
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'স্টক সমন্বয় করতে সমস্যা হয়েছে';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি!',
                                    text: msg
                                });
                            }
                        }
                    }
                });
            });

            // Stock History Modal AJAX Handler
            $(document).on('click', '.btn-stock-history', function (e) {
                e.preventDefault();
                var url = $(this).data('url') || $(this).attr('href');
                if (!url) return;

                $.get(url, function (html) {
                    $('#stockHistoryModalContainer').html(html);
                    openModal('stockHistoryModal');
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                }).fail(function () {
                    window.location.href = url;
                });
            });
        });
        </script>
    @endpush
</x-core::layout>
