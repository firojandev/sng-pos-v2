<x-core::layout
    title="সম্পদ"
    title-en="Assets"
    subtitle="দোকানের সম্পদের তালিকা পরিচালনা করুন"
    subtitle-en="Manage your shop's assets register"
    active="assets"
>
    <x-financemanagement::tabbar active="assets" />

    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="box"
                color="teal"
                value-color="teal"
                :value="'৳' . number_format($metrics['totalAssets'], 2)"
                label="মোট সম্পদের মূল্য"
                label-en="Total Asset Value"
                :subtext="number_format($metrics['totalCount']) . ' টি সম্পদ'"
                :subtext-en="number_format($metrics['totalCount']) . ' Assets'"
            />
        </div>
    @endif

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:flex-end;">
        @can('assets.create')
            <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-asset-modal">
                <span class="bn">নতুন সম্পদ</span>
                <span class="en" style="display:none;">New Asset</span>
            </x-core::button>
        @endcan
    </div>

    <div class="table-container">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'assets-data-table']) !!}
        </div>
    </div>

    {{-- Create Asset Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createAssetModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="box" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন সম্পদ যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Asset</span>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>
            <form method="POST" action="{{ route('assets.store') }}" id="create_asset_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="create_asset_name"
                        label="সম্পদের নাম"
                        label-en="Asset Name"
                        placeholder="যেমন: ফ্রিজ, দোকানের ফার্নিচার"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="amount"
                        id="create_asset_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        label="পরিমাণ (৳)"
                        label-en="Amount (৳)"
                        placeholder="0.00"
                        prefix="৳"
                        size="sm"
                        :required="true"
                        :stepper="false"
                    />

                    <x-core::textarea
                        name="note"
                        id="create_asset_note"
                        label="নোট"
                        label-en="Note"
                        placeholder="ঐচ্ছিক নোট লিখুন..."
                        rows="3"
                        size="sm"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-save-create-asset">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Asset Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editAssetModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">সম্পদ সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Asset</span>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>
            <form method="POST" action="" id="edit_asset_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="edit_asset_name"
                        label="সম্পদের নাম"
                        label-en="Asset Name"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="amount"
                        id="edit_asset_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        label="পরিমাণ (৳)"
                        label-en="Amount (৳)"
                        prefix="৳"
                        size="sm"
                        :required="true"
                        :stepper="false"
                    />

                    <x-core::textarea
                        name="note"
                        id="edit_asset_note"
                        label="নোট"
                        label-en="Note"
                        rows="3"
                        size="sm"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-update-asset">
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update</span>
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

            function reloadAssetTable() {
                var tableId = 'assets-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            $('#btn-open-create-asset-modal').on('click', function () {
                var $form = $('#create_asset_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#createAssetModal').addClass('open');
                setTimeout(function () {
                    $('#create_asset_name').focus();
                }, 100);
            });

            $(document).on('click', '.btn-edit-asset', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var $form = $('#edit_asset_form');
                clearFormErrors($form);

                var action = $btn.data('action');
                var url = $btn.data('url');

                if (action) {
                    $form.attr('action', action);
                }
                $('#edit_asset_name').val($btn.data('name'));
                $('#edit_asset_amount').val($btn.data('amount'));
                $('#edit_asset_note').val($btn.data('note') || '');

                if (url) {
                    $.getJSON(url, function (data) {
                        if (data) {
                            if (data.name !== undefined) $('#edit_asset_name').val(data.name);
                            if (data.amount !== undefined) $('#edit_asset_amount').val(data.amount);
                            if (data.note !== undefined) $('#edit_asset_note').val(data.note || '');
                        }
                    });
                }

                $('#editAssetModal').addClass('open');
                setTimeout(function () {
                    $('#edit_asset_name').focus();
                }, 100);
            });

            $('#create_asset_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-asset');

                clearFormErrors($form);
                $btn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (response) {
                        $btn.prop('disabled', false);
                        $('#createAssetModal').removeClass('open');
                        $form[0].reset();
                        reloadAssetTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'সম্পদ সফলভাবে যোগ করা হয়েছে', 'Asset created successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else if (typeof window.toast === 'function') {
                            window.toast('সম্পদ যোগ করতে সমস্যা হয়েছে', 'Failed to create asset');
                        }
                    }
                });
            });

            $('#edit_asset_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-update-asset');

                clearFormErrors($form);
                $btn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (response) {
                        $btn.prop('disabled', false);
                        $('#editAssetModal').removeClass('open');
                        reloadAssetTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'সম্পদ হালনাগাদ করা হয়েছে', 'Asset updated successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else if (typeof window.toast === 'function') {
                            window.toast('সম্পদ হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update asset');
                        }
                    }
                });
            });

            $(document).on('click', '.modal-close-btn', function () {
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
