<x-core::layout
    title="সরবরাহকারী"
    title-en="Suppliers"
    subtitle="দোকানের সরবরাহকারীদের তথ্য পরিচালনা করুন"
    subtitle-en="Manage your shop's supplier records"
    active="suppliers"
>
    <div class="section-row" style="margin-bottom:16px;">
        <div class="filters"></div>
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

            $('.modal-backdrop').on('click', function (e) {
                if ($(e.target).hasClass('modal-backdrop')) {
                    $(this).removeClass('open');
                }
            });
        });
        </script>
    @endpush
</x-core::layout>
