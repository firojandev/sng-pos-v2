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
                label="মোট সম্পদ"
                label-en="Total Assets"
                :subtext="number_format($metrics['totalCount']) . ' টি সম্পদ'"
                :subtext-en="number_format($metrics['totalCount']) . ' Assets'"
            />
            <x-core::stat-card
                icon="trending-down"
                color="red"
                value-color="red"
                :value="'৳' . number_format($metrics['totalDepreciation'] ?? 0, 2)"
                label="মোট অবচয়"
                label-en="Total Depreciation"
            />
            <x-core::stat-card
                icon="check-circle"
                color="green"
                value-color="green"
                :value="'৳' . number_format($metrics['netAssets'] ?? $metrics['totalAssets'], 2)"
                label="বর্তমান নিট মূল্য"
                label-en="Net Asset Value"
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
        <div class="modal-box" style="width:580px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
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

                    <div style="display:grid; grid-template-columns: 1fr 1.15fr 1.05fr; gap:10px; align-items:flex-start;">
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

                        <x-core::form-group
                            id="create_asset_depreciation"
                            label="অবচয়"
                            label-en="Depreciation"
                        >
                            <div class="input-group-joined" style="display:flex; align-items:stretch; width:100%;">
                                <div style="flex:1; min-width:0;">
                                    <x-core::input
                                        name="depreciation"
                                        id="create_asset_depreciation"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        size="sm"
                                        :stepper="false"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-right-radius:0 !important; border-bottom-right-radius:0 !important;"
                                    />
                                </div>
                                <div style="width:96px; flex-shrink:0; margin-left:-1px;">
                                    <x-core::select
                                        name="depreciation_type"
                                        id="create_asset_depreciation_type"
                                        size="sm"
                                        value="flat"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-left-radius:0 !important; border-bottom-left-radius:0 !important; background-color:var(--paper); cursor:pointer; font-weight:500;"
                                        :options="[
                                            'flat' => ['bn' => 'ফ্ল্যাট (৳)', 'en' => 'Flat (৳)'],
                                            'percentage' => ['bn' => 'শতাংশ (%)', 'en' => 'Percent (%)'],
                                        ]"
                                    />
                                </div>
                            </div>
                        </x-core::form-group>

                        <x-core::form-group
                            id="create_asset_validity"
                            label="মেয়াদ"
                            label-en="Validity"
                        >
                            <div class="input-group-joined" style="display:flex; align-items:stretch; width:100%;">
                                <div style="flex:1; min-width:0;">
                                    <x-core::input
                                        name="validity"
                                        id="create_asset_validity"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        placeholder="যেমন: ৫"
                                        size="sm"
                                        :stepper="false"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-right-radius:0 !important; border-bottom-right-radius:0 !important;"
                                    />
                                </div>
                                <div style="width:82px; flex-shrink:0; margin-left:-1px;">
                                    <x-core::select
                                        name="validity_unit"
                                        id="create_asset_validity_unit"
                                        size="sm"
                                        value="year"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-left-radius:0 !important; border-bottom-left-radius:0 !important; background-color:var(--paper); cursor:pointer; font-weight:500;"
                                        :options="[
                                            'year' => ['bn' => 'বছর', 'en' => 'Years'],
                                            'month' => ['bn' => 'মাস', 'en' => 'Months'],
                                            'day' => ['bn' => 'দিন', 'en' => 'Days'],
                                        ]"
                                    />
                                </div>
                            </div>
                        </x-core::form-group>
                    </div>

                    <div id="create_asset_net_preview" style="display:none; padding:8px 12px; border-radius:8px; background:var(--paper-line); border:1px solid var(--border); font-size:12.5px; align-items:center; justify-content:space-between;">
                        <span style="color:var(--ink-600); font-weight:600;">
                            <span class="bn">বর্তমান নিট মূল্য:</span>
                            <span class="en" style="display:none;">Current Net Value:</span>
                        </span>
                        <span id="create_asset_net_val" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--green-ink); font-size:13.5px;">৳0.00</span>
                    </div>

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
        <div class="modal-box" style="width:580px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
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

                    <div style="display:grid; grid-template-columns: 1fr 1.15fr 1.05fr; gap:10px; align-items:flex-start;">
                        <x-core::input
                            name="amount"
                            id="edit_asset_amount"
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

                        <x-core::form-group
                            id="edit_asset_depreciation"
                            label="অবচয়"
                            label-en="Depreciation"
                        >
                            <div class="input-group-joined" style="display:flex; align-items:stretch; width:100%;">
                                <div style="flex:1; min-width:0;">
                                    <x-core::input
                                        name="depreciation"
                                        id="edit_asset_depreciation"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        size="sm"
                                        :stepper="false"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-right-radius:0 !important; border-bottom-right-radius:0 !important;"
                                    />
                                </div>
                                <div style="width:96px; flex-shrink:0; margin-left:-1px;">
                                    <x-core::select
                                        name="depreciation_type"
                                        id="edit_asset_depreciation_type"
                                        size="sm"
                                        value="flat"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-left-radius:0 !important; border-bottom-left-radius:0 !important; background-color:var(--paper); cursor:pointer; font-weight:500;"
                                        :options="[
                                            'flat' => ['bn' => 'ফ্ল্যাট (৳)', 'en' => 'Flat (৳)'],
                                            'percentage' => ['bn' => 'শতাংশ (%)', 'en' => 'Percent (%)'],
                                        ]"
                                    />
                                </div>
                            </div>
                        </x-core::form-group>

                        <x-core::form-group
                            id="edit_asset_validity"
                            label="মেয়াদ"
                            label-en="Validity"
                        >
                            <div class="input-group-joined" style="display:flex; align-items:stretch; width:100%;">
                                <div style="flex:1; min-width:0;">
                                    <x-core::input
                                        name="validity"
                                        id="edit_asset_validity"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        placeholder="যেমন: ৫"
                                        size="sm"
                                        :stepper="false"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-right-radius:0 !important; border-bottom-right-radius:0 !important;"
                                    />
                                </div>
                                <div style="width:82px; flex-shrink:0; margin-left:-1px;">
                                    <x-core::select
                                        name="validity_unit"
                                        id="edit_asset_validity_unit"
                                        size="sm"
                                        value="year"
                                        :no-margin="true"
                                        :error="false"
                                        style="border-top-left-radius:0 !important; border-bottom-left-radius:0 !important; background-color:var(--paper); cursor:pointer; font-weight:500;"
                                        :options="[
                                            'year' => ['bn' => 'বছর', 'en' => 'Years'],
                                            'month' => ['bn' => 'মাস', 'en' => 'Months'],
                                            'day' => ['bn' => 'দিন', 'en' => 'Days'],
                                        ]"
                                    />
                                </div>
                            </div>
                        </x-core::form-group>
                    </div>

                    <div id="edit_asset_net_preview" style="display:none; padding:8px 12px; border-radius:8px; background:var(--paper-line); border:1px solid var(--border); font-size:12.5px; align-items:center; justify-content:space-between;">
                        <span style="color:var(--ink-600); font-weight:600;">
                            <span class="bn">বর্তমান নিট মূল্য:</span>
                            <span class="en" style="display:none;">Current Net Value:</span>
                        </span>
                        <span id="edit_asset_net_val" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--green-ink); font-size:13.5px;">৳0.00</span>
                    </div>

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

            function updateCreateNetPreview() {
                var amount = parseFloat($('#create_asset_amount').val()) || 0;
                var type = $('#create_asset_depreciation_type').val() || 'flat';
                var depreciation = parseFloat($('#create_asset_depreciation').val()) || 0;
                var $depInput = $('#create_asset_depreciation');
                var $preview = $('#create_asset_net_preview');

                var depAmount = 0;
                if (type === 'percentage') {
                    depAmount = (amount * depreciation) / 100;
                    $depInput.attr('max', '100');
                } else {
                    depAmount = depreciation;
                    $depInput.removeAttr('max');
                }

                if (amount > 0 || depreciation > 0) {
                    var net = Math.max(0, amount - depAmount);
                    $preview.css('display', 'flex');
                    var subtext = type === 'percentage'
                        ? '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (অবচয়: ৳' + depAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' / ' + depreciation + '%)'
                        : '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (অবচয়: ৳' + depAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
                    $('#create_asset_net_val').text(subtext);
                } else {
                    $preview.hide();
                }
            }

            function updateEditNetPreview() {
                var amount = parseFloat($('#edit_asset_amount').val()) || 0;
                var type = $('#edit_asset_depreciation_type').val() || 'flat';
                var depreciation = parseFloat($('#edit_asset_depreciation').val()) || 0;
                var $depInput = $('#edit_asset_depreciation');
                var $preview = $('#edit_asset_net_preview');

                var depAmount = 0;
                if (type === 'percentage') {
                    depAmount = (amount * depreciation) / 100;
                    $depInput.attr('max', '100');
                } else {
                    depAmount = depreciation;
                    $depInput.removeAttr('max');
                }

                if (amount > 0 || depreciation > 0) {
                    var net = Math.max(0, amount - depAmount);
                    $preview.css('display', 'flex');
                    var subtext = type === 'percentage'
                        ? '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (অবচয়: ৳' + depAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' / ' + depreciation + '%)'
                        : '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (অবচয়: ৳' + depAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
                    $('#edit_asset_net_val').text(subtext);
                } else {
                    $preview.hide();
                }
            }

            $(document).on('input', '#create_asset_amount, #create_asset_depreciation', updateCreateNetPreview);
            $(document).on('change', '#create_asset_depreciation_type', updateCreateNetPreview);
            $(document).on('input', '#edit_asset_amount, #edit_asset_depreciation', updateEditNetPreview);
            $(document).on('change', '#edit_asset_depreciation_type', updateEditNetPreview);

            $('#btn-open-create-asset-modal').on('click', function () {
                var $form = $('#create_asset_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#create_asset_depreciation_type').val('flat');
                $('#create_asset_depreciation').val('');
                $('#create_asset_validity').val('');
                $('#create_asset_validity_unit').val('year');
                updateCreateNetPreview();
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
                $('#edit_asset_depreciation_type').val($btn.data('depreciation-type') || 'flat');
                $('#edit_asset_depreciation').val($btn.data('depreciation') !== undefined ? $btn.data('depreciation') : '0');
                var valVal = $btn.data('validity') !== undefined ? $btn.data('validity') : ($btn.data('useful-life') !== undefined ? $btn.data('useful-life') : '');
                var valUnit = $btn.data('validity-unit') || $btn.data('useful-life-unit') || 'year';
                $('#edit_asset_validity').val(valVal);
                $('#edit_asset_validity_unit').val(valUnit);
                $('#edit_asset_note').val($btn.data('note') || '');
                updateEditNetPreview();

                if (url) {
                    $.getJSON(url, function (data) {
                        if (data) {
                            if (data.name !== undefined) $('#edit_asset_name').val(data.name);
                            if (data.amount !== undefined) $('#edit_asset_amount').val(data.amount);
                            if (data.depreciation_type !== undefined) $('#edit_asset_depreciation_type').val(data.depreciation_type);
                            if (data.depreciation !== undefined) $('#edit_asset_depreciation').val(data.depreciation);
                            var v = data.validity !== undefined ? data.validity : (data.useful_life !== undefined ? data.useful_life : '');
                            var vu = data.validity_unit || data.useful_life_unit || 'year';
                            $('#edit_asset_validity').val(v || '');
                            $('#edit_asset_validity_unit').val(vu);
                            if (data.note !== undefined) $('#edit_asset_note').val(data.note || '');
                            updateEditNetPreview();
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
