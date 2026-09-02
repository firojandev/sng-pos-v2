<x-core::layout
    title="শাখা"
    title-en="Branches"
    subtitle="আপনার ব্যবসার শাখাসমূহ পরিচালনা করুন"
    subtitle-en="Manage your business branches"
    active="branches"
>
    <x-shop::tabbar active="branches" />

    <div class="section-row" style="margin-bottom:16px; margin-top:16px;">
        <div class="filters"></div>
        <x-core::button
            type="button"
            variant="solid"
            color="primary"
            size="sm"
            icon="plus"
            id="btn-open-create-branch-modal"
        >
            <span class="bn">নতুন শাখা</span>
            <span class="en" style="display:none;">New Branch</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'branches-data-table']) !!}
        </div>
    </div>

    {{-- Create Branch Modal --}}
    <div class="modal-backdrop" id="createBranchModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="building" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন শাখা যোগ করুন</span>
                        <span class="en" style="display:none;">Create New Branch</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('branches.store') }}" id="create_branch_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="create_branch_name"
                        label="শাখার নাম"
                        label-en="Branch Name"
                        placeholder="যেমন: রাজশাহী শাখা"
                        placeholder-en="e.g. Rajshahi Branch"
                        :required="true"
                    />

                    <x-core::input
                        name="phone"
                        id="create_branch_phone"
                        label="মোবাইল / ফোন নম্বর"
                        label-en="Phone Number"
                        placeholder="+8801XXXXXXXXX"
                        placeholder-en="+8801XXXXXXXXX"
                    />

                    <x-core::textarea
                        name="address"
                        id="create_branch_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="শাখার সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full branch address"
                        rows="2"
                    />

                    <x-core::select
                        name="status"
                        id="create_branch_status"
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
                        id="btn-save-create-branch"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save Branch</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Branch Modal --}}
    <div class="modal-backdrop" id="editBranchModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">শাখা সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Branch</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_branch_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="edit_branch_name"
                        label="শাখার নাম"
                        label-en="Branch Name"
                        placeholder="যেমন: রাজশাহী শাখা"
                        placeholder-en="e.g. Rajshahi Branch"
                        :required="true"
                    />

                    <x-core::input
                        name="phone"
                        id="edit_branch_phone"
                        label="মোবাইল / ফোন নম্বর"
                        label-en="Phone Number"
                        placeholder="+8801XXXXXXXXX"
                        placeholder-en="+8801XXXXXXXXX"
                    />

                    <x-core::textarea
                        name="address"
                        id="edit_branch_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="শাখার সম্পূর্ণ ঠিকানা"
                        placeholder-en="Full branch address"
                        rows="2"
                    />

                    <x-core::select
                        name="status"
                        id="edit_branch_status"
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
                        id="btn-update-branch"
                    >
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update Branch</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        (function () {
            function initBranchModals() {
                if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                    setTimeout(initBranchModals, 30);
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

                function reloadBranchTable() {
                    if (window.LaravelDataTables && window.LaravelDataTables['branches-data-table']) {
                        window.LaravelDataTables['branches-data-table'].ajax.reload(null, false);
                    } else if ($.fn.DataTable.isDataTable('#branches-data-table')) {
                        $('#branches-data-table').DataTable().ajax.reload(null, false);
                    }
                }

                // Open Create Branch Modal
                $(document).on('click', '#btn-open-create-branch-modal', function (e) {
                    e.preventDefault();
                    var $form = $('#create_branch_form');
                    $form[0].reset();
                    clearFormErrors($form);
                    $('#create_branch_status').val('active');
                    openModal('createBranchModal');
                });

                // Open Edit Branch Modal via AJAX
                $(document).on('click', '.btn-edit-branch', function (e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    if (!url) return;

                    var $form = $('#edit_branch_form');
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
                            $('#edit_branch_name').val(data.name || '');
                            $('#edit_branch_phone').val(data.phone || '');
                            $('#edit_branch_address').val(data.address || '');
                            $('#edit_branch_status').val(data.status || 'active');
                            openModal('editBranchModal');
                        },
                        error: function () {
                            window.location.href = url;
                        }
                    });
                });

                // Submit Create Branch Form
                $('#create_branch_form').on('submit', function (e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btn-save-create-branch');
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
                            closeModal('createBranchModal');
                            $form[0].reset();
                            reloadBranchTable();
                            if (typeof window.toast === 'function') {
                                window.toast(response.message || 'শাখা সফলভাবে যোগ করা হয়েছে', 'Branch created successfully');
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
                                    window.toast('শাখা যোগ করতে সমস্যা হয়েছে', 'Failed to create branch');
                                }
                            }
                        }
                    });
                });

                // Submit Edit Branch Form
                $('#edit_branch_form').on('submit', function (e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $btn = $('#btn-update-branch');
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
                            closeModal('editBranchModal');
                            reloadBranchTable();
                            if (typeof window.toast === 'function') {
                                window.toast(response.message || 'শাখা হালনাগাদ করা হয়েছে', 'Branch updated successfully');
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
                                    window.toast('শাখা হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update branch');
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
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initBranchModals);
            } else {
                initBranchModals();
            }
        })();
        </script>
    @endpush
</x-core::layout>
