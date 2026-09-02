<x-core::layout
    title="গুদাম"
    title-en="Warehouses"
    subtitle="আপনার শাখার গুদামসমূহ পরিচালনা করুন"
    subtitle-en="Manage your branch warehouses"
    active="branches"
>
    <x-shop::tabbar active="warehouses" />

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="min-width:210px;">
                <x-core::select
                    name="filter_branch"
                    id="filter-branch"
                    size="sm"
                    icon="git-branch"
                    :options="['' => 'সকল শাখা (All Branches)'] + ($filterBranchOptions ?? [])"
                    no-margin
                />
            </div>
            <div style="min-width:170px;">
                <x-core::select
                    name="filter_status"
                    id="filter-status"
                    size="sm"
                    icon="activity"
                    :options="[
                        '' => 'সকল অবস্থা (All Status)',
                        'active' => 'সক্রিয় (Active)',
                        'inactive' => 'নিষ্ক্রিয় (Inactive)',
                    ]"
                    no-margin
                />
            </div>
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reset-filters"
                title="ফিল্টার রিসেট / Reset Filters"
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
            id="btn-open-create-warehouse-modal"
        >
            <span class="bn">নতুন গুদাম</span>
            <span class="en" style="display:none;">New Warehouse</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'warehouses-data-table']) !!}
        </div>
    </div>

    {{-- Create Warehouse Modal --}}
    <div class="modal-backdrop" id="createWarehouseModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="package" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন গুদাম যোগ করুন</span>
                        <span class="en" style="display:none;">Create New Warehouse</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('warehouses.store') }}" id="create_warehouse_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::select
                        name="branch_id"
                        id="create_warehouse_branch_id"
                        label="শাখা"
                        label-en="Branch"
                        :options="$branchOptions"
                        :required="true"
                    />

                    <x-core::input
                        name="name"
                        id="create_warehouse_name"
                        label="গুদামের নাম"
                        label-en="Warehouse Name"
                        placeholder="যেমন: প্রধান গুদাম"
                        placeholder-en="e.g. Main Warehouse"
                        :required="true"
                    />

                    <x-core::textarea
                        name="address"
                        id="create_warehouse_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="গুদামের সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full warehouse address"
                        rows="2"
                    />

                    <x-core::select
                        name="status"
                        id="create_warehouse_status"
                        label="অবস্থা"
                        label-en="Status"
                        :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
                        value="active"
                        :required="true"
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
                        id="btn-save-create-warehouse"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save Warehouse</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Warehouse Modal --}}
    <div class="modal-backdrop" id="editWarehouseModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">গুদাম সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Warehouse</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_warehouse_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::select
                        name="branch_id"
                        id="edit_warehouse_branch_id"
                        label="শাখা"
                        label-en="Branch"
                        :options="$branchOptions"
                        :required="true"
                    />

                    <x-core::input
                        name="name"
                        id="edit_warehouse_name"
                        label="গুদামের নাম"
                        label-en="Warehouse Name"
                        placeholder="যেমন: প্রধান গুদাম"
                        placeholder-en="e.g. Main Warehouse"
                        :required="true"
                    />

                    <x-core::textarea
                        name="address"
                        id="edit_warehouse_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="গুদামের সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full warehouse address"
                        rows="2"
                    />

                    <x-core::select
                        name="status"
                        id="edit_warehouse_status"
                        label="অবস্থা"
                        label-en="Status"
                        :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
                        :required="true"
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
                        id="btn-update-warehouse"
                    >
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update Warehouse</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        (function () {
            function initWarehouseModals() {
                if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                    setTimeout(initWarehouseModals, 30);
                    return;
                }

                var $ = window.jQuery;

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

                function reloadWarehouseTable() {
                    if (window.LaravelDataTables && window.LaravelDataTables['warehouses-data-table']) {
                        window.LaravelDataTables['warehouses-data-table'].ajax.reload(null, false);
                    } else if ($.fn.DataTable.isDataTable('#warehouses-data-table')) {
                        $('#warehouses-data-table').DataTable().ajax.reload(null, false);
                    }
                }

                // Open Create Warehouse Modal
                $(document).on('click', '#btn-open-create-warehouse-modal', function (e) {
                    e.preventDefault();
                    var $form = $('#create_warehouse_form');
                    $form[0].reset();
                    clearFormErrors($form);
                    $('#create_warehouse_status').val('active');
                    openModal('createWarehouseModal');
                });

                // Open Edit Warehouse Modal via AJAX
                $(document).on('click', '.btn-edit-warehouse', function (e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    if (!url) return;

                    var $form = $('#edit_warehouse_form');
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
                            $('#edit_warehouse_branch_id').val(data.branch_id || '');
                            $('#edit_warehouse_name').val(data.name || '');
                            $('#edit_warehouse_address').val(data.address || '');
                            $('#edit_warehouse_status').val(data.status || 'active');
                            openModal('editWarehouseModal');
                        },
                        error: function () {
                            window.location.href = url;
                        }
                    });
                });

                // Submit Create Warehouse Form
                $('#create_warehouse_form').on('submit', function (e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btn-save-create-warehouse');
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
                            closeModal('createWarehouseModal');
                            $form[0].reset();
                            reloadWarehouseTable();
                            if (typeof window.toast === 'function') {
                                window.toast(response.message || 'গুদাম সফলভাবে যোগ করা হয়েছে', 'Warehouse created successfully');
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
                                    } else if (typeof window.Swal !== 'undefined') {
                                        window.Swal.fire({
                                            icon: 'error',
                                            title: 'ত্রুটি',
                                            text: xhr.responseJSON.message,
                                        });
                                    }
                                }
                            } else {
                                if (typeof window.toast === 'function') {
                                    window.toast('গুদাম যোগ করতে সমস্যা হয়েছে', 'Failed to create warehouse');
                                }
                            }
                        }
                    });
                });

                // Submit Edit Warehouse Form
                $('#edit_warehouse_form').on('submit', function (e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btn-update-warehouse');
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
                            closeModal('editWarehouseModal');
                            reloadWarehouseTable();
                            if (typeof window.toast === 'function') {
                                window.toast(response.message || 'গুদাম হালনাগাদ করা হয়েছে', 'Warehouse updated successfully');
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
                                    window.toast('গুদাম হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update warehouse');
                                }
                            }
                        }
                    });
                });

                // Table Filter Handlers
                $(document).on('change', '#filter-branch, #filter-status', function () {
                    reloadWarehouseTable();
                });

                $(document).on('click', '#btn-reset-filters', function (e) {
                    e.preventDefault();
                    $('#filter-branch').val('');
                    $('#filter-status').val('');
                    reloadWarehouseTable();
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
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWarehouseModals);
            } else {
                initWarehouseModals();
            }
        })();
        </script>
    @endpush
</x-core::layout>
