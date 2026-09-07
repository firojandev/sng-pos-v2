<x-core::layout
    title="স্টক ট্রান্সফার"
    title-en="Stock Transfers"
    subtitle="গুদামের মধ্যে পণ্য স্থানান্তর পরিচালনা করুন"
    subtitle-en="Manage and track stock transfers between warehouses"
    active="stock-transfers"
>
    {{-- Top Tab Navigation --}}
{{--    <div class="tabbar" style="margin-bottom:16px;">--}}
{{--        <a href="{{ route('stock-transfers.index') }}" class="tabbtn active">--}}
{{--            <span class="bn">স্টক ট্রান্সফার</span><span class="en" style="display:none;">Stock Transfers</span>--}}
{{--        </a>--}}
{{--        <a href="{{ route('stock.history') }}" class="tabbtn">--}}
{{--            <span class="bn">স্টকের ইতিহাস</span><span class="en" style="display:none;">Stock History</span>--}}
{{--        </a>--}}
{{--    </div>--}}

    {{-- Summary Stat Cards --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));">
            <x-core::stat-card
                icon="arrow-left-right"
                color="teal"
                :value="number_format($metrics['total'])"
                label="মোট ট্রান্সফার"
                label-en="Total Transfers"
                subtext="সকল চালান"
                subtext-en="All transfer requests"
            />

            <x-core::stat-card
                icon="clock"
                color="gold"
                :value="number_format($metrics['pending'])"
                value-color="gold"
                label="অপেক্ষমাণ অনুমোদন"
                label-en="Pending Approval"
                subtext="অনুমোদনের অপেক্ষায়"
                subtext-en="Awaiting sign-off"
            />

            <x-core::stat-card
                icon="check"
                color="blue"
                :value="number_format($metrics['approved'])"
                value-color="blue"
                label="অনুমোদিত"
                label-en="Approved"
                subtext="প্রেরণের অপেক্ষায়"
                subtext-en="Ready to dispatch"
            />

            <x-core::stat-card
                icon="arrow-right"
                color="blue"
                :value="number_format($metrics['dispatched'])"
                label="প্রেরিত (চলমান)"
                label-en="Dispatched"
                subtext="পথিমধ্যে রয়েছে"
                subtext-en="In transit"
            />

            <x-core::stat-card
                icon="check-circle"
                color="green"
                :value="number_format($metrics['received'])"
                value-color="green"
                label="সফলভাবে গৃহীত"
                label-en="Completed"
                subtext="গন্তব্যে পৌঁছেছে"
                subtext-en="Stock added to warehouse"
            />
        </div>
    @endif

    {{-- Filter Toolbar & Header Action --}}
    <div class="section-row" style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_status"
                    id="filter-status"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'সকল অবস্থা (All Status)'] + collect(\Modules\Product\Models\StockTransfer::statusLabels())->mapWithKeys(fn($item, $key) => [$key => $item['bn'] . ' (' . $item['en'] . ')'])->toArray()"
                />
            </div>
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_from_warehouse"
                    id="filter-from-warehouse"
                    size="sm"
                    :no-margin="true"
                >
                    <option value="" data-text-bn="উৎস গুদাম" data-text-en="From Warehouse">উৎস গুদাম (From)</option>
                    @foreach ($warehouses as $warehouse)
                        <option
                            value="{{ $warehouse->id }}"
                            data-text-bn="{{ $warehouse->name }}@if($warehouse->is_default) [ডিফল্ট]@endif @if($warehouse->branch) ({{ $warehouse->branch->name }})@endif"
                            data-text-en="{{ $warehouse->name }}@if($warehouse->is_default) [Default]@endif @if($warehouse->branch) ({{ $warehouse->branch->name }})@endif">
                            {{ $warehouse->name }} @if($warehouse->is_default) [ডিফল্ট] @endif @if($warehouse->branch)
                                ({{ $warehouse->branch->name }})
                            @endif
                        </option>
                    @endforeach
                </x-core::select>
            </div>
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_to_warehouse"
                    id="filter-to-warehouse"
                    size="sm"
                    :no-margin="true"
                >
                    <option value="" data-text-bn="গন্তব্য গুদাম" data-text-en="To Warehouse">গন্তব্য গুদাম (To)</option>
                    @foreach ($warehouses as $warehouse)
                        <option
                            value="{{ $warehouse->id }}"
                            data-text-bn="{{ $warehouse->name }}@if($warehouse->is_default) [ডিফল্ট]@endif @if($warehouse->branch) ({{ $warehouse->branch->name }})@endif"
                            data-text-en="{{ $warehouse->name }}@if($warehouse->is_default) [Default]@endif @if($warehouse->branch) ({{ $warehouse->branch->name }})@endif">
                            {{ $warehouse->name }} @if($warehouse->is_default) [ডিফল্ট] @endif @if($warehouse->branch)
                                ({{ $warehouse->branch->name }})
                            @endif
                        </option>
                    @endforeach
                </x-core::select>
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

        @canany(['stock.create', 'stock.transfer'])
        <x-core::button
            type="button"
            size="sm"
            color="primary"
            icon="plus"
            id="btn-open-create-transfer-modal"
        >
            <span class="bn">নতুন ট্রান্সফার</span>
            <span class="en" style="display:none;">New Transfer</span>
        </x-core::button>
        @endcanany
    </div>

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'stock-transfers-data-table']) !!}
        </div>
    </div>

    {{-- Transfer Detail Drawer --}}
    <div class="drawer-backdrop" id="transferDetailDrawer">
        <div class="drawer" id="transferDetailDrawerContent" style="width:580px; max-width:95vw; background:var(--card);">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    {{-- Create Stock Transfer Modal --}}
    @include('product::stock-transfers._create_modal')

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            function reloadTransferTable() {
                var tableId = 'stock-transfers-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filters change
            $(document).on('change', '#filter-status, #filter-from-warehouse, #filter-to-warehouse', function () {
                reloadTransferTable();
            });

            // Reset filters
            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-status').val('');
                $('#filter-from-warehouse').val('');
                $('#filter-to-warehouse').val('');
                reloadTransferTable();
            });

            // Open Transfer Detail Drawer via AJAX
            $(document).on('click', '.btn-view-transfer-detail', function (e) {
                e.preventDefault();
                var url = $(this).data('url') || $(this).attr('href');
                if (!url) return;

                var $content = $('#transferDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:80px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#transferDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load transfer details</div></div>');
                });
            });

            // Close Drawer Handlers
            $(document).on('click', '#transferDetailDrawer .drawer-x', function () {
                $('#transferDetailDrawer').removeClass('open');
            });

            $('#transferDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#transferDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Handle AJAX form actions inside Drawer or Table
            $(document).on('submit', '.inline-transfer-action-form, .inline-drawer-action-form', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                var url = $form.attr('action');

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
                        $('#transferDetailDrawer').removeClass('open');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'কার্যক্রম সফল হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else if (typeof window.toast === 'function') {
                            window.toast(res.message || 'কার্যক্রম সফল হয়েছে', res.message_en || 'Action completed successfully');
                        }
                        reloadTransferTable();
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'কার্যক্রম সম্পন্ন করতে সমস্যা হয়েছে';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি!',
                                text: msg
                            });
                        }
                    }
                });
            });

            /* ---------------- Create Stock Transfer Modal ---------------- */
            var productData = {};
            var batchesByWarehouse = {};
            try {
                productData = JSON.parse($('#modal-transfer-products-data').text()) || {};
                batchesByWarehouse = JSON.parse($('#modal-transfer-batches-data').text()) || {};
            } catch (e) {
                productData = {};
                batchesByWarehouse = {};
            }

            var modalRowCount = 1;

            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function buildModalProductOptions() {
                var html = '<option value="">-- নির্বাচন করুন --</option>';
                $.each(productData, function (pid, info) {
                    html += '<option value="' + pid + '">' + escapeHtml(info.label) + '</option>';
                });
                return html;
            }

            function populateModalBatches($row) {
                var $batchSelect = $row.find('.modal-batch-select');
                var productId = $row.find('.modal-product-select').val();
                var warehouseId = $('#modal-from-warehouse').val();
                var $qtyInput = $row.find('.modal-qty-input');
                var $maxHint = $row.find('.modal-batch-max-hint');

                $qtyInput.removeAttr('max');
                $maxHint.hide().empty();

                if (!warehouseId) {
                    $batchSelect.html('<option value="">-- আগে প্রেরণকারী গুদাম নির্বাচন করুন --</option>');
                    return;
                }
                if (!productId) {
                    $batchSelect.html('<option value="">-- পণ্য নির্বাচন করুন --</option>');
                    return;
                }

                var batches = (batchesByWarehouse[warehouseId] || {})[productId] || [];
                if (!batches || batches.length === 0) {
                    $batchSelect.html('<option value="">-- এই গুদামে কোনো ব্যাচ/স্টক নেই --</option>');
                    return;
                }

                var html = '<option value="">-- ব্যাচ নির্বাচন করুন --</option>';
                $.each(batches, function (i, b) {
                    html += '<option value="' + b.id + '" data-qty="' + (b.quantity || 0) + '">' + escapeHtml(b.label) + '</option>';
                });
                $batchSelect.html(html);
            }

            function newModalRowHtml(index) {
                return '<tr class="modal-item-row" data-index="' + index + '" style="border-bottom:1px solid var(--border);">' +
                    '<td style="min-width:220px; padding:8px 10px;">' +
                        '<select name="items[' + index + '][product_id]" class="form-control form-select form-control-sm modal-product-select" style="font-size:13px; font-family:\'Noto Sans Bengali\', sans-serif;" required>' +
                            buildModalProductOptions() +
                        '</select>' +
                    '</td>' +
                    '<td style="min-width:200px; padding:8px 10px;">' +
                        '<select name="items[' + index + '][batch_id]" class="form-control form-select form-control-sm modal-batch-select" style="font-size:13px; font-family:\'Noto Sans Bengali\', sans-serif;" required>' +
                            '<option value="">-- আগে প্রেরণকারী গুদাম নির্বাচন করুন --</option>' +
                        '</select>' +
                    '</td>' +
                    '<td style="width:140px; padding:8px 10px;">' +
                        '<input type="number" step="0.01" min="0.01" name="items[' + index + '][quantity]" class="form-control form-control-sm modal-qty-input" style="font-size:13px; font-family:var(--font-mono, monospace);" placeholder="0.00" required>' +
                        '<div class="modal-batch-max-hint" style="font-size:11px; color:var(--ink-500); margin-top:2px; display:none;"></div>' +
                    '</td>' +
                    '<td style="width:44px; text-align:center; padding:8px 10px;">' +
                        '<button type="button" class="modal-remove-item-btn" style="width:30px; height:30px; border-radius:8px; border:none; background:transparent; color:var(--red-600); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:background-color 0.15s ease;" title="Remove">' +
                            '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2M10 11v6M14 11v6"/></svg>' +
                        '</button>' +
                    '</td>' +
                '</tr>';
            }

            function resetModalForm() {
                var $form = $('#create_stock_transfer_form');
                $form[0].reset();
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.dynamic-error').remove();
                $('#modal-transfer-error-box').hide().empty();

                modalRowCount = 1;
                $('#modal-items-container').html(newModalRowHtml(0));

                if ($('#modal-from-warehouse').val()) {
                    $('#modal-items-container .modal-item-row').each(function () {
                        populateModalBatches($(this));
                    });
                }
            }

            // Open Transfer Create Modal
            $(document).on('click', '#btn-open-create-transfer-modal', function (e) {
                e.preventDefault();
                resetModalForm();
                openModal('createStockTransferModal');
            });

            // Auto-open if #create hash or query parameter
            if (window.location.hash === '#create' || new URLSearchParams(window.location.search).get('create') === '1') {
                resetModalForm();
                openModal('createStockTransferModal');
            }

            // From Warehouse change updates all rows
            $(document).on('change', '#modal-from-warehouse', function () {
                var fromWh = $(this).val();
                var toWh = $('#modal-to-warehouse').val();
                if (fromWh && toWh && fromWh === toWh) {
                    $('#modal-transfer-error-box').text('প্রেরণকারী গুদাম এবং গ্রহণকারী গুদাম একই হতে পারে না').show();
                } else {
                    $('#modal-transfer-error-box').hide().empty();
                }

                $('#modal-items-container .modal-item-row').each(function () {
                    populateModalBatches($(this));
                });
            });

            // To Warehouse change check
            $(document).on('change', '#modal-to-warehouse', function () {
                var fromWh = $('#modal-from-warehouse').val();
                var toWh = $(this).val();
                if (fromWh && toWh && fromWh === toWh) {
                    $('#modal-transfer-error-box').text('প্রেরণকারী গুদাম এবং গ্রহণকারী গুদাম একই হতে পারে না').show();
                } else {
                    $('#modal-transfer-error-box').hide().empty();
                }
            });

            // Product change
            $(document).on('change', '.modal-product-select', function () {
                populateModalBatches($(this).closest('.modal-item-row'));
            });

            // Batch selection updates max attribute and hint
            $(document).on('change', '.modal-batch-select', function () {
                var $row = $(this).closest('.modal-item-row');
                var $opt = $(this).find('option:selected');
                var maxQty = parseFloat($opt.data('qty'));
                var $qtyInput = $row.find('.modal-qty-input');
                var $hint = $row.find('.modal-batch-max-hint');

                if (!isNaN(maxQty) && maxQty > 0) {
                    $qtyInput.attr('max', maxQty);
                    $hint.text('মজুদ: ' + maxQty).show();
                } else {
                    $qtyInput.removeAttr('max');
                    $hint.hide().empty();
                }
            });

            // Remove item row
            $(document).on('click', '.modal-remove-item-btn', function () {
                var $rows = $('#modal-items-container .modal-item-row');
                if ($rows.length > 1) {
                    $(this).closest('.modal-item-row').remove();
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

            // Add item row
            $('#modal-add-item-btn').on('click', function () {
                $('#modal-items-container').append(newModalRowHtml(modalRowCount));
                modalRowCount++;
            });

            // Close Modal Handlers
            $(document).on('click', '.modal-close-btn', function (e) {
                e.preventDefault();
                $(this).closest('.modal-backdrop').removeClass('open');
            });

            $('.modal-backdrop').on('click', function (e) {
                if ($(e.target).hasClass('modal-backdrop')) {
                    $(this).removeClass('open');
                }
            });

            // Submit Create Stock Transfer Form via AJAX
            $('#create_stock_transfer_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-stock-transfer');
                var url = $form.attr('action');

                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.dynamic-error').remove();
                $('#modal-transfer-error-box').hide().empty();

                var fromWh = $('#modal-from-warehouse').val();
                var toWh = $('#modal-to-warehouse').val();

                if (!fromWh) {
                    $('#modal-transfer-error-box').text('অনুগ্রহ করে প্রেরণকারী গুদাম নির্বাচন করুন').show();
                    return;
                }
                if (!toWh) {
                    $('#modal-transfer-error-box').text('অনুগ্রহ করে গ্রহণকারী গুদাম নির্বাচন করুন').show();
                    return;
                }
                if (fromWh === toWh) {
                    $('#modal-transfer-error-box').text('প্রেরণকারী গুদাম এবং গ্রহণকারী গুদাম একই হতে পারে না').show();
                    return;
                }

                var hasInvalidBatch = false;
                $form.find('.modal-batch-select').each(function () {
                    if (!$(this).val()) {
                        hasInvalidBatch = true;
                        $(this).addClass('is-invalid');
                    }
                });
                if (hasInvalidBatch) {
                    $('#modal-transfer-error-box').text('সকল পণ্যের জন্য বৈধ ব্যাচ নির্বাচন করুন').show();
                    return;
                }

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
                        closeModal('createStockTransferModal');
                        resetModalForm();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'স্টক ট্রান্সফারের অনুরোধ তৈরি করা হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else if (typeof window.toast === 'function') {
                            window.toast(res.message || 'স্টক ট্রান্সফারের অনুরোধ তৈরি করা হয়েছে', res.message_en || 'Stock transfer created');
                        }
                        reloadTransferTable();
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON) {
                            var errors = xhr.responseJSON.errors;
                            if (errors) {
                                var errorList = [];
                                $.each(errors, function (key, msgs) {
                                    errorList.push(msgs[0]);
                                    var $field = $form.find('[name="' + key + '"]');
                                    if ($field.length) {
                                        $field.addClass('is-invalid');
                                        var $err = $('<div class="field-error dynamic-error" style="color:var(--red-600); font-size:12px; margin-top:4px;">' + msgs[0] + '</div>');
                                        $field.closest('.form-group, .field, td, div').append($err);
                                    }
                                });
                                $('#modal-transfer-error-box').html(errorList.join('<br>')).show();
                            } else if (xhr.responseJSON.message) {
                                $('#modal-transfer-error-box').text(xhr.responseJSON.message).show();
                            }
                        } else {
                            var errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'স্টক ট্রান্সফার করতে সমস্যা হয়েছে';
                            $('#modal-transfer-error-box').text(errMsg).show();
                        }
                    }
                });
            });
        });
        </script>
    @endpush
</x-core::layout>
