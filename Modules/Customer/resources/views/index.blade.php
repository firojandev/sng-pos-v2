<x-core::layout
    title="গ্রাহক"
    title-en="Customers"
    subtitle="দোকানের গ্রাহকদের তথ্য পরিচালনা করুন"
    subtitle-en="Manage your shop's customer records"
    active="customers"
>
    {{-- Summary Stat Cards --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="users"
                color="teal"
                :value="number_format($metrics['totalCustomers'])"
                label="মোট গ্রাহক"
                label-en="Total Customers"
                :subtext="'সক্রিয়: ' . number_format($metrics['activeCustomers']) . ' জন'"
                :subtext-en="'Active: ' . number_format($metrics['activeCustomers'])"
            />

            <x-core::stat-card
                icon="credit-card"
                color="red"
                :value="'৳' . number_format($metrics['totalDue'], 2)"
                value-color="red"
                label="মোট বকেয়া বাকি"
                label-en="Total Outstanding Due"
                :subtext="'বাকি রয়েছে: ' . number_format($metrics['dueCustomersCount']) . ' জন'"
                :subtext-en="'Due Customers: ' . number_format($metrics['dueCustomersCount'])"
            />

            <x-core::stat-card
                icon="shopping-bag"
                color="blue"
                :value="'৳' . number_format($metrics['totalSalesAmount'], 2)"
                label="মোট বিক্রয় পরিমাণ"
                label-en="Total Sales Volume"
                :subtext="number_format($metrics['totalSalesCount']) . ' টি চালান'"
                :subtext-en="number_format($metrics['totalSalesCount']) . ' Invoices'"
            />

            @php
                $paidTotal = max(0, $metrics['totalSalesAmount'] - ($metrics['totalDue'] - (float) \Modules\Customer\Models\Customer::sum('opening_due')));
            @endphp
            <x-core::stat-card
                icon="check-circle"
                color="green"
                :value="'৳' . number_format($paidTotal, 2)"
                value-color="green"
                label="মোট পরিশোধিত আদায়"
                label-en="Total Collected Paid"
                subtext="বিক্রয় বাবদ আদায়"
                subtext-en="Collected from sales"
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
                    :options="['' => 'সকল বাকি (All)', 'has_due' => 'শুধুমাত্র বাকিদার (Has Due)', 'no_due' => 'পরিশোধিত (No Due)']"
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
            id="btn-open-create-customer-modal"
        >
            <span class="bn">নতুন গ্রাহক</span>
            <span class="en" style="display:none;">New Customer</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'customers-data-table']) !!}
        </div>
    </div>

    {{-- Create Customer Modal --}}
    <div class="modal-backdrop" id="createCustomerModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="user-plus" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন গ্রাহক যোগ করুন</span>
                        <span class="en" style="display:none;">Create New Customer</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('customers.store') }}" id="create_customer_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="create_customer_name"
                        label="গ্রাহকের নাম"
                        label-en="Customer Name"
                        placeholder="যেমন: মোঃ করিম হোসেন"
                        placeholder-en="e.g. Md. Karim Hossain"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="phone"
                            id="create_customer_phone"
                            label="মোবাইল / ফোন নম্বর"
                            label-en="Phone Number"
                            placeholder="01XXXXXXXXX"
                            placeholder-en="01XXXXXXXXX"
                        />
                        <x-core::input
                            name="email"
                            id="create_customer_email"
                            type="email"
                            label="ইমেইল"
                            label-en="Email"
                            placeholder="customer@example.com"
                            placeholder-en="customer@example.com"
                        />
                    </div>

                    <x-core::input
                        name="opening_due"
                        id="create_customer_opening_due"
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
                        id="create_customer_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="গ্রাহকের সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full customer address"
                        rows="2"
                    />

                    <div>
                        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
                        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
                        <x-core::status-toggle
                            name="status"
                            id="create_customer_status"
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
                        id="btn-save-create-customer"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save Customer</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Customer Modal --}}
    <div class="modal-backdrop" id="editCustomerModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">গ্রাহক সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Customer</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_customer_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="edit_customer_name"
                        label="গ্রাহকের নাম"
                        label-en="Customer Name"
                        placeholder="যেমন: মোঃ করিম হোসেন"
                        placeholder-en="e.g. Md. Karim Hossain"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="phone"
                            id="edit_customer_phone"
                            label="মোবাইল / ফোন নম্বর"
                            label-en="Phone Number"
                            placeholder="01XXXXXXXXX"
                            placeholder-en="01XXXXXXXXX"
                        />
                        <x-core::input
                            name="email"
                            id="edit_customer_email"
                            type="email"
                            label="ইমেইল"
                            label-en="Email"
                            placeholder="customer@example.com"
                            placeholder-en="customer@example.com"
                        />
                    </div>

                    <x-core::input
                        name="opening_due"
                        id="edit_customer_opening_due"
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
                        id="edit_customer_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="গ্রাহকের সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full customer address"
                        rows="2"
                    />

                    <div>
                        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
                        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
                        <x-core::status-toggle
                            name="status"
                            id="edit_customer_status"
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
                        id="btn-update-customer"
                    >
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update Customer</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Customer Due Detail Drawer --}}
    <div class="drawer-backdrop" id="customerDetailDrawer">
        <div class="drawer" id="customerDetailDrawerContent">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    {{-- Customer Due Payment Modal --}}
    <div class="modal-backdrop" id="customerPaymentModal">
        <div class="modal-box" id="customerPaymentModalContent" style="width:760px; max-width:96vw; max-height:92vh; display:flex; flex-direction:column; padding:0; overflow:hidden; background:var(--card);">
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

            function reloadCustomerTable() {
                if (window.LaravelDataTables && window.LaravelDataTables['customers-data-table']) {
                    window.LaravelDataTables['customers-data-table'].ajax.reload(null, false);
                } else if ($.fn.DataTable.isDataTable('#customers-data-table')) {
                    $('#customers-data-table').DataTable().ajax.reload(null, false);
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

            // Open Create Customer Modal
            $(document).on('click', '#btn-open-create-customer-modal', function (e) {
                e.preventDefault();
                var $form = $('#create_customer_form');
                $form[0].reset();
                clearFormErrors($form);
                setStatusToggleValue($form, 'active');
                openModal('createCustomerModal');
            });

            // Open Edit Customer Modal via AJAX
            $(document).on('click', '.btn-edit-customer', function (e) {
                e.preventDefault();
                var url = $(this).data('url');
                if (!url) return;

                var $form = $('#edit_customer_form');
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
                        $('#edit_customer_name').val(data.name || '');
                        $('#edit_customer_phone').val(data.phone || '');
                        $('#edit_customer_email').val(data.email || '');
                        $('#edit_customer_opening_due').val(data.opening_due || 0);
                        $('#edit_customer_address').val(data.address || '');
                        setStatusToggleValue($form, data.status || 'active');
                        openModal('editCustomerModal');
                    },
                    error: function () {
                        window.location.href = url;
                    }
                });
            });

            // Submit Create Customer Form
            $('#create_customer_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-customer');
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
                        closeModal('createCustomerModal');
                        $form[0].reset();
                        reloadCustomerTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'গ্রাহক সফলভাবে যোগ করা হয়েছে', 'Customer created successfully');
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
                                window.toast('গ্রাহক যোগ করতে সমস্যা হয়েছে', 'Failed to create customer');
                            }
                        }
                    }
                });
            });

            // Submit Edit Customer Form
            $('#edit_customer_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-update-customer');
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
                        closeModal('editCustomerModal');
                        reloadCustomerTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'গ্রাহক হালনাগাদ করা হয়েছে', 'Customer updated successfully');
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
                                window.toast('গ্রাহক হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update customer');
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
                reloadCustomerTable();
            });

            $('#btn-reset-filters').on('click', function () {
                $('#filter-status').val('');
                $('#filter-due').val('');
                reloadCustomerTable();
            });

            // Open Customer Detail Drawer
            $(document).on('click', '.btn-view-customer-due', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url');
                if (!url) return;

                var $content = $('#customerDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:60px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#customerDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load due details</div></div>');
                });
            });

            // Close Drawer
            $(document).on('click', '#customerDetailDrawer .drawer-x', function () {
                $('#customerDetailDrawer').removeClass('open');
            });

            $('#customerDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#customerDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Open Payment Modal
            $(document).on('click', '.btn-open-customer-payment', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url');
                if (!url) return;

                var $content = $('#customerPaymentModalContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:80px 20px; color:var(--ink-500);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#customerPaymentModal').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:30px; color:var(--red-600); text-align:center;">পেমেন্ট ফর্ম লোড করতে সমস্যা হয়েছে</div>');
                });
            });

            // Close Payment Modal on backdrop
            $('#customerPaymentModal').on('click', function (e) {
                if ($(e.target).is('#customerPaymentModal')) {
                    $(this).removeClass('open');
                }
            });

            // Customer Payment auto-allocation calculations
            function recalculateCustomerAllocations(totalVal) {
                var remaining = Math.max(parseFloat(totalVal) || 0, 0);
                var totalCustomerDue = 0;

                $('#customer-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalCustomerDue += rowDue;
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
                var afterDue = Math.max(totalCustomerDue - enteredTotal, 0);
                $('#customer-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-customer-pay-total').text('৳' + enteredTotal.toFixed(2));
            }

            function getCustomerActiveTotal() {
                var type = $('#customer-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    return parseFloat($('#customer-cash-amount-input').val()) || 0;
                } else if (type === 'bank') {
                    return parseFloat($('#customer-bank-amount-input').val()) || 0;
                } else if (type === 'both') {
                    var c = parseFloat($('#customer-both-cash-amount-input').val()) || 0;
                    var b = parseFloat($('#customer-both-bank-amount-input').val()) || 0;
                    return c + b;
                }
                return 0;
            }

            function syncCustomerPaymentFromInputs() {
                var total = getCustomerActiveTotal();
                recalculateCustomerAllocations(total);
            }

            $(document).on('change', '#customer-payment-type-select', function () {
                var type = $(this).val();
                $('.payment-mode-pane').hide();
                $('#customer-mode-' + type).show();
                syncCustomerPaymentFromInputs();
            });

            $(document).on('input', '#customer-cash-amount-input, #customer-bank-amount-input, #customer-both-cash-amount-input, #customer-both-bank-amount-input', function () {
                syncCustomerPaymentFromInputs();
            });

            $(document).on('input', '#customer-allocation-tbody .allocation-input', function () {
                var totalAllocated = 0;
                var totalCustomerDue = 0;
                $('#customer-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalCustomerDue += rowDue;
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

                var type = $('#customer-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#customer-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'bank') {
                    $('#customer-bank-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#customer-both-cash-amount-input').val()) || 0;
                    if (totalAllocated <= currentCash) {
                        $('#customer-both-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                        $('#customer-both-bank-amount-input').val('');
                    } else {
                        $('#customer-both-bank-amount-input').val((totalAllocated - currentCash).toFixed(2));
                    }
                }

                var afterDue = Math.max(totalCustomerDue - totalAllocated, 0);
                $('#customer-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-customer-pay-total').text('৳' + totalAllocated.toFixed(2));
            });

            $(document).on('click', '#btn-customer-pay-full', function () {
                var total = parseFloat($(this).data('total')) || 0;
                var type = $('#customer-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#customer-cash-amount-input').val(total.toFixed(2));
                } else if (type === 'bank') {
                    $('#customer-bank-amount-input').val(total.toFixed(2));
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#customer-both-cash-amount-input').val()) || 0;
                    if (currentCash > 0 && currentCash < total) {
                        $('#customer-both-bank-amount-input').val((total - currentCash).toFixed(2));
                    } else {
                        $('#customer-both-cash-amount-input').val(total.toFixed(2));
                        $('#customer-both-bank-amount-input').val('');
                    }
                }
                syncCustomerPaymentFromInputs();
            });

            $(document).on('click', '#btn-customer-pay-reset', function () {
                $('#customer-cash-amount-input').val('');
                $('#customer-bank-amount-input').val('');
                $('#customer-both-cash-amount-input').val('');
                $('#customer-both-bank-amount-input').val('');
                syncCustomerPaymentFromInputs();
            });

            // Submit customer payment via AJAX
            $(document).on('submit', '#form-customer-due-payment', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-submit-customer-payment');
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
                        $('#customerPaymentModal').removeClass('open');
                        $('#customerDetailDrawer').removeClass('open');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'বাকি আদায় সফল হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        reloadCustomerTable();
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'বাকি আদায় সংরক্ষণে সমস্যা হয়েছে';
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
