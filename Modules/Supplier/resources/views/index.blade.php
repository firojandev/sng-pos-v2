<x-core::layout
    title="সরবরাহকারী"
    title-en="Suppliers"
    subtitle="দোকানের সরবরাহকারীদের তথ্য পরিচালনা করুন"
    subtitle-en="Manage your shop's supplier records"
    active="suppliers"
>
    {{-- Summary Stat Cards --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="truck"
                color="gold"
                :value="number_format($metrics['totalSuppliers'])"
                label="মোট সরবরাহকারী"
                label-en="Total Suppliers"
                :subtext="'সক্রিয়: ' . number_format($metrics['activeSuppliers']) . ' জন'"
                :subtext-en="'Active: ' . number_format($metrics['activeSuppliers'])"
            />

            <x-core::stat-card
                icon="alert-circle"
                color="red"
                :value="'৳' . number_format($metrics['totalDue'], 2)"
                value-color="red"
                label="মোট প্রদেয় দেনা"
                label-en="Total Payable Due"
                :subtext="'দেনা রয়েছে: ' . number_format($metrics['dueSuppliersCount']) . ' জন'"
                :subtext-en="'Due Suppliers: ' . number_format($metrics['dueSuppliersCount'])"
            />

            <x-core::stat-card
                icon="package"
                color="blue"
                :value="'৳' . number_format($metrics['totalPurchaseAmount'], 2)"
                label="মোট ক্রয় পরিমাণ"
                label-en="Total Purchase Volume"
                :subtext="number_format($metrics['totalPurchaseCount']) . ' টি বিল'"
                :subtext-en="number_format($metrics['totalPurchaseCount']) . ' Bills'"
            />

            @php
                $paidPurchaseTotal = max(0, $metrics['totalPurchaseAmount'] - ($metrics['totalDue'] - (float) \Modules\Supplier\Models\Supplier::sum('opening_due')));
            @endphp
            <x-core::stat-card
                icon="check-circle"
                color="green"
                :value="'৳' . number_format($paidPurchaseTotal, 2)"
                value-color="green"
                label="মোট পরিশোধিত মূল্য"
                label-en="Total Paid Amount"
                subtext="সরবরাহকারীকে পরিশোধ"
                subtext-en="Paid to suppliers"
            />
        </div>
    @endif

    <div class="section-row" style="margin-bottom:16px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
            <div style="width:160px; flex-shrink:0;">
                <x-core::select
                    name="filter_status"
                    id="filter-status"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'সকল অবস্থা (Status)', 'active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
                />
            </div>
            <div style="width:180px; flex-shrink:0;">
                <x-core::select
                    name="filter_due"
                    id="filter-due"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'সকল বাকি (All)', 'has_due' => 'শুধুমাত্র দেনাদার (Has Due)', 'no_due' => 'পরিশোধিত (No Due)']"
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
        <x-core::button
            type="button"
            variant="solid"
            color="primary"
            size="sm"
            icon="plus"
            id="btn-open-create-supplier-modal"
        >
            <span class="bn">নতুন সরবরাহকারী</span>
            <span class="en" style="display:none;">New Supplier</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'suppliers-data-table']) !!}
        </div>
    </div>

    {{-- Create Supplier Modal --}}
    <div class="modal-backdrop" id="createSupplierModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="truck" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন সরবরাহকারী যোগ করুন</span>
                        <span class="en" style="display:none;">Create New Supplier</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('suppliers.store') }}" id="create_supplier_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="create_supplier_name"
                        label="সরবরাহকারীর নাম"
                        label-en="Supplier Name"
                        placeholder="যেমন: রহিম অ্যান্ড সন্স"
                        placeholder-en="e.g. Rahim & Sons"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="phone"
                            id="create_supplier_phone"
                            label="মোবাইল / ফোন নম্বর"
                            label-en="Phone Number"
                            placeholder="01XXXXXXXXX"
                            placeholder-en="01XXXXXXXXX"
                        />
                        <x-core::input
                            name="email"
                            id="create_supplier_email"
                            type="email"
                            label="ইমেইল"
                            label-en="Email"
                            placeholder="supplier@example.com"
                            placeholder-en="supplier@example.com"
                        />
                    </div>

                    <x-core::input
                        name="opening_due"
                        id="create_supplier_opening_due"
                        type="number"
                        step="0.01"
                        min="0"
                        label="প্রারম্ভিক বাকি (৳)"
                        label-en="Opening Due (৳)"
                        placeholder="0.00"
                        prefix="৳"
                    />

                    <x-core::textarea
                        name="address"
                        id="create_supplier_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="সরবরাহকারীর সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full supplier address"
                        rows="2"
                    />

                    <div>
                        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
                        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
                        <x-core::status-toggle
                            name="status"
                            id="create_supplier_status"
                            value="active"
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
                        variant="solid"
                        color="primary"
                        size="sm"
                        icon="check"
                        id="btn-save-create-supplier"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save Supplier</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Supplier Modal --}}
    <div class="modal-backdrop" id="editSupplierModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">সরবরাহকারী সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Supplier</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_supplier_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="edit_supplier_name"
                        label="সরবরাহকারীর নাম"
                        label-en="Supplier Name"
                        placeholder="যেমন: রহিম অ্যান্ড সন্স"
                        placeholder-en="e.g. Rahim & Sons"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="phone"
                            id="edit_supplier_phone"
                            label="মোবাইল / ফোন নম্বর"
                            label-en="Phone Number"
                            placeholder="01XXXXXXXXX"
                            placeholder-en="01XXXXXXXXX"
                        />
                        <x-core::input
                            name="email"
                            id="edit_supplier_email"
                            type="email"
                            label="ইমেইল"
                            label-en="Email"
                            placeholder="supplier@example.com"
                            placeholder-en="supplier@example.com"
                        />
                    </div>

                    <x-core::input
                        name="opening_due"
                        id="edit_supplier_opening_due"
                        type="number"
                        step="0.01"
                        min="0"
                        label="প্রারম্ভিক বাকি (৳)"
                        label-en="Opening Due (৳)"
                        placeholder="0.00"
                        prefix="৳"
                    />

                    <x-core::textarea
                        name="address"
                        id="edit_supplier_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="সরবরাহকারীর সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full supplier address"
                        rows="2"
                    />

                    <div>
                        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
                        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
                        <x-core::status-toggle
                            name="status"
                            id="edit_supplier_status"
                            value="active"
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
                        variant="solid"
                        color="primary"
                        size="sm"
                        icon="check"
                        id="btn-update-supplier"
                    >
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update Supplier</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Supplier Due Detail Drawer --}}
    <div class="drawer-backdrop" id="supplierDetailDrawer">
        <div class="drawer" id="supplierDetailDrawerContent">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    {{-- Supplier Due Payment Modal --}}
    <div class="modal-backdrop" id="supplierPaymentModal">
        <div class="modal-box" id="supplierPaymentModalContent" style="width:760px; max-width:96vw; max-height:92vh; display:flex; flex-direction:column; padding:0; overflow:hidden; background:var(--card);">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
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

            function clearFormErrors($form) {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.dynamic-error').remove();
            }

            function reloadSupplierTable() {
                if (window.LaravelDataTables && window.LaravelDataTables['suppliers-data-table']) {
                    window.LaravelDataTables['suppliers-data-table'].ajax.reload(null, false);
                } else if ($.fn.DataTable.isDataTable('#suppliers-data-table')) {
                    $('#suppliers-data-table').DataTable().ajax.reload(null, false);
                }
            }

            function setStatusToggleValue($container, statusVal) {
                var isActive = String(statusVal) === 'active';
                var $switcher = $container.find('[data-status-switcher]');
                var $toggle = $container.find('[data-status-toggle]');
                var $hidden = $container.find('[data-status-input], input[type="hidden"]');

                $toggle.prop('checked', !isActive);
                $switcher.toggleClass('is-active', isActive).toggleClass('is-inactive', !isActive);
                $switcher.find('.switch-opt-active').toggleClass('active', isActive);
                $switcher.find('.switch-opt-inactive').toggleClass('active', !isActive);
                $hidden.val(statusVal);
            }

            // Open Create Supplier Modal
            $(document).on('click', '#btn-open-create-supplier-modal', function (e) {
                e.preventDefault();
                var $form = $('#create_supplier_form');
                $form[0].reset();
                clearFormErrors($form);
                setStatusToggleValue($form, 'active');
                openModal('createSupplierModal');
            });

            // Open Edit Supplier Modal via AJAX
            $(document).on('click', '.btn-edit-supplier', function (e) {
                e.preventDefault();
                var url = $(this).data('url');
                if (!url) return;

                var $form = $('#edit_supplier_form');
                $form[0].reset();
                clearFormErrors($form);

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (data) {
                        $form.attr('action', data.update_url);
                        $('#edit_supplier_name').val(data.name || '');
                        $('#edit_supplier_phone').val(data.phone || '');
                        $('#edit_supplier_email').val(data.email || '');
                        $('#edit_supplier_opening_due').val(data.opening_due || 0);
                        $('#edit_supplier_address').val(data.address || '');
                        setStatusToggleValue($form, data.status || 'active');
                        openModal('editSupplierModal');
                    },
                    error: function () {
                        window.location.href = url;
                    }
                });
            });

            // Submit Create Supplier Form
            $('#create_supplier_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-supplier');
                var url = $form.attr('action');

                clearFormErrors($form);
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
                        closeModal('createSupplierModal');
                        $form[0].reset();
                        reloadSupplierTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'সরবরাহকারী সফলভাবে যোগ করা হয়েছে', 'Supplier created successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                showFormErrors($form, xhr.responseJSON.errors);
                            } else if (xhr.responseJSON.message) {
                                if (typeof window.toast === 'function') {
                                    window.toast(xhr.responseJSON.message, xhr.responseJSON.message);
                                }
                            }
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('সরবরাহকারী যোগ করতে সমস্যা হয়েছে', 'Failed to create supplier');
                            }
                        }
                    }
                });
            });

            // Submit Edit Supplier Form
            $('#edit_supplier_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-update-supplier');
                var url = $form.attr('action');

                clearFormErrors($form);
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
                        closeModal('editSupplierModal');
                        reloadSupplierTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'সরবরাহকারী হালনাগাদ করা হয়েছে', 'Supplier updated successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                showFormErrors($form, xhr.responseJSON.errors);
                            } else if (xhr.responseJSON.message) {
                                if (typeof window.toast === 'function') {
                                    window.toast(xhr.responseJSON.message, xhr.responseJSON.message);
                                }
                            }
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('সরবরাহকারী হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update supplier');
                            }
                        }
                    }
                });
            });

            // Close Modal Event Handlers
            $(document).on('click', '.modal-close-btn', function (e) {
                e.preventDefault();
                $(this).closest('.modal-backdrop').removeClass('open');
            });

            // Filters Change
            $('#filter-status, #filter-due').on('change', function () {
                reloadSupplierTable();
            });

            $('#btn-reset-filters').on('click', function () {
                $('#filter-status').val('');
                $('#filter-due').val('');
                reloadSupplierTable();
            });

            // Open Supplier Detail Drawer
            $(document).on('click', '.btn-view-supplier-due', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url');
                if (!url) return;

                var $content = $('#supplierDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:60px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#supplierDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load due details</div></div>');
                });
            });

            // Close Drawer
            $(document).on('click', '#supplierDetailDrawer .drawer-x', function () {
                $('#supplierDetailDrawer').removeClass('open');
            });

            $('#supplierDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#supplierDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Open Payment Modal
            $(document).on('click', '.btn-open-supplier-payment', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url');
                if (!url) return;

                var $content = $('#supplierPaymentModalContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:80px 20px; color:var(--ink-500);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#supplierPaymentModal').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:30px; color:var(--red-600); text-align:center;">পেমেন্ট ফর্ম লোড করতে সমস্যা হয়েছে</div>');
                });
            });

            // Close Payment Modal on backdrop
            $('#supplierPaymentModal').on('click', function (e) {
                if ($(e.target).is('#supplierPaymentModal')) {
                    $(this).removeClass('open');
                }
            });

            // Supplier Payment auto-allocation calculations
            function recalculateSupplierAllocations(totalVal) {
                var remaining = Math.max(parseFloat(totalVal) || 0, 0);
                var totalSupplierDue = 0;

                $('#supplier-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalSupplierDue += rowDue;
                    var allocate = Math.min(remaining, rowDue);
                    $(this).find('.allocation-input').val(allocate > 0 ? allocate.toFixed(2) : '0.00');
                    remaining = Math.max(remaining - allocate, 0);

                    var rowRemain = Math.max(rowDue - allocate, 0);
                    $(this).find('.cell-remaining').text('৳' + rowRemain.toFixed(2));
                    var $status = $(this).find('.cell-status');
                    if (rowRemain <= 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--green-100); color:var(--green-ink);">পরিশোধিত</span>');
                    } else if (allocate > 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--gold-100); color:var(--gold-ink);">আংশিক</span>');
                    } else {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600);">বাকি</span>');
                    }
                });

                var enteredTotal = Math.max(parseFloat(totalVal) || 0, 0);
                var afterDue = Math.max(totalSupplierDue - enteredTotal, 0);
                $('#supplier-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-supplier-pay-total').text('৳' + enteredTotal.toFixed(2));
            }

            function getSupplierActiveTotal() {
                var type = $('#supplier-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    return parseFloat($('#supplier-cash-amount-input').val()) || 0;
                } else if (type === 'bank') {
                    return parseFloat($('#supplier-bank-amount-input').val()) || 0;
                } else if (type === 'both') {
                    var c = parseFloat($('#supplier-both-cash-amount-input').val()) || 0;
                    var b = parseFloat($('#supplier-both-bank-amount-input').val()) || 0;
                    return c + b;
                }
                return 0;
            }

            function syncSupplierPaymentFromInputs() {
                var total = getSupplierActiveTotal();
                recalculateSupplierAllocations(total);
            }

            $(document).on('change', '#supplier-payment-type-select', function () {
                var type = $(this).val();
                $('.payment-mode-pane').hide();
                $('#supplier-mode-' + type).show();
                syncSupplierPaymentFromInputs();
            });

            $(document).on('input', '#supplier-cash-amount-input, #supplier-bank-amount-input, #supplier-both-cash-amount-input, #supplier-both-bank-amount-input', function () {
                syncSupplierPaymentFromInputs();
            });

            $(document).on('input', '#supplier-allocation-tbody .allocation-input', function () {
                var totalAllocated = 0;
                var totalSupplierDue = 0;
                $('#supplier-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalSupplierDue += rowDue;
                    var val = parseFloat($(this).find('.allocation-input').val()) || 0;
                    if (val > rowDue) {
                        val = rowDue;
                        $(this).find('.allocation-input').val(rowDue.toFixed(2));
                    }
                    totalAllocated += val;
                    var rowRemain = Math.max(rowDue - val, 0);
                    $(this).find('.cell-remaining').text('৳' + rowRemain.toFixed(2));
                    var $status = $(this).find('.cell-status');
                    if (rowRemain <= 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--green-100); color:var(--green-ink);">পরিশোধিত</span>');
                    } else if (val > 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--gold-100); color:var(--gold-ink);">আংশিক</span>');
                    } else {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600);">বাকি</span>');
                    }
                });

                var type = $('#supplier-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#supplier-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'bank') {
                    $('#supplier-bank-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#supplier-both-cash-amount-input').val()) || 0;
                    if (totalAllocated <= currentCash) {
                        $('#supplier-both-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                        $('#supplier-both-bank-amount-input').val('');
                    } else {
                        $('#supplier-both-bank-amount-input').val((totalAllocated - currentCash).toFixed(2));
                    }
                }

                var afterDue = Math.max(totalSupplierDue - totalAllocated, 0);
                $('#supplier-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-supplier-pay-total').text('৳' + totalAllocated.toFixed(2));
            });

            $(document).on('click', '#btn-supplier-pay-full', function () {
                var total = parseFloat($(this).data('total')) || 0;
                var type = $('#supplier-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#supplier-cash-amount-input').val(total.toFixed(2));
                } else if (type === 'bank') {
                    $('#supplier-bank-amount-input').val(total.toFixed(2));
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#supplier-both-cash-amount-input').val()) || 0;
                    if (currentCash > 0 && currentCash < total) {
                        $('#supplier-both-bank-amount-input').val((total - currentCash).toFixed(2));
                    } else {
                        $('#supplier-both-cash-amount-input').val(total.toFixed(2));
                        $('#supplier-both-bank-amount-input').val('');
                    }
                }
                syncSupplierPaymentFromInputs();
            });

            $(document).on('click', '#btn-supplier-pay-reset', function () {
                $('#supplier-cash-amount-input').val('');
                $('#supplier-bank-amount-input').val('');
                $('#supplier-both-cash-amount-input').val('');
                $('#supplier-both-bank-amount-input').val('');
                syncSupplierPaymentFromInputs();
            });

            // Submit supplier payment via AJAX
            $(document).on('submit', '#form-supplier-due-payment', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-submit-supplier-payment');
                $btn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function (res) {
                        $('#supplierPaymentModal').removeClass('open');
                        $('#supplierDetailDrawer').removeClass('open');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'বাকি পরিশোধ সফল হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        reloadSupplierTable();
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'বাকি পরিশোধ সংরক্ষণে সমস্যা হয়েছে';
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
        });
        </script>
    @endpush
</x-core::layout>
