<x-core::layout
    title="ধার"
    title-en="Lend"
    subtitle="দোকান থেকে ধার দেওয়া অর্থের তালিকা পরিচালনা করুন"
    subtitle-en="Manage money you've lent out"
    active="lend"
>
    <x-financemanagement::tabbar active="lend" />

    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="trending-down"
                color="red"
                value-color="red"
                :value="'৳' . number_format($metrics['totalDue'], 2)"
                label="বাকি (ফেরত পাওয়া বাকি)"
                label-en="Due (Not Yet Received)"
            />
            <x-core::stat-card
                icon="check-circle"
                color="green"
                value-color="green"
                :value="'৳' . number_format($metrics['totalReceived'], 2)"
                label="ফেরত পাওয়া"
                label-en="Received"
            />
            <x-core::stat-card
                icon="receipt"
                color="teal"
                :value="number_format($metrics['totalCount'])"
                label="মোট রেকর্ড সংখ্যা"
                label-en="Total Records"
            />
        </div>
    @endif

    @php
        $defaultAcc = $accounts->firstWhere('is_default', true) ?? $accounts->first();
        $defaultAccId = $defaultAcc ? $defaultAcc->id : '';
    @endphp
    <input type="hidden" id="default-lend-account-id" value="{{ $defaultAccId }}">

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:8px;">
            <div style="width:170px;">
                <x-core::select id="filter-lend-status" name="filter_lend_status" size="sm" :no-margin="true">
                    <option value="">সকল অবস্থা (All Status)</option>
                    <option value="due">বাকি (Due)</option>
                    <option value="received">ফেরত পাওয়া (Received)</option>
                </x-core::select>
            </div>
        </div>

        @can('lend.create')
            <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-lend-modal">
                <span class="bn">নতুন ধার</span>
                <span class="en" style="display:none;">New Lend</span>
            </x-core::button>
        @endcan
    </div>

    <div class="table-container">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'lends-data-table']) !!}
        </div>
    </div>

    {{-- Create Lend Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createLendModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--red-100); color:var(--red-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="trending-down" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ধার যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Lend</span>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>
            <form method="POST" action="{{ route('lend.store') }}" id="create_lend_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="borrower_name"
                        id="create_lend_borrower_name"
                        label="গ্রহীতার নাম"
                        label-en="Borrower Name"
                        size="sm"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="amount"
                            id="create_lend_amount"
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

                        <x-core::input
                            name="date"
                            id="create_lend_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            :value="now()->format('Y-m-d')"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::select
                            name="status"
                            id="create_lend_status"
                            label="অবস্থা"
                            label-en="Status"
                            size="sm"
                            :required="true"
                        >
                            <option value="due" selected>বাকি (Due)</option>
                            <option value="received">ফেরত পাওয়া (Received)</option>
                        </x-core::select>

                        <x-core::select
                            name="account_id"
                            id="create_lend_account_id"
                            label="অ্যাকাউন্ট"
                            label-en="Account"
                            size="sm"
                        >
                            <option value="">-- ডিফল্ট অ্যাকাউন্ট --</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }})</option>
                            @endforeach
                        </x-core::select>
                    </div>

                    <x-core::textarea
                        name="note"
                        id="create_lend_note"
                        label="নোট"
                        label-en="Note"
                        placeholder="ঐচ্ছিক নোট লিখুন..."
                        rows="2"
                        size="sm"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-save-create-lend">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Lend Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editLendModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--red-100); color:var(--red-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ধার সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Lend</span>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>
            <form method="POST" action="" id="edit_lend_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="borrower_name"
                        id="edit_lend_borrower_name"
                        label="গ্রহীতার নাম"
                        label-en="Borrower Name"
                        size="sm"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="amount"
                            id="edit_lend_amount"
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

                        <x-core::input
                            name="date"
                            id="edit_lend_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::select
                            name="status"
                            id="edit_lend_status"
                            label="অবস্থা"
                            label-en="Status"
                            size="sm"
                            :required="true"
                        >
                            <option value="due">বাকি (Due)</option>
                            <option value="received">ফেরত পাওয়া (Received)</option>
                        </x-core::select>

                        <x-core::select
                            name="account_id"
                            id="edit_lend_account_id"
                            label="অ্যাকাউন্ট"
                            label-en="Account"
                            size="sm"
                        >
                            <option value="">-- ডিফল্ট অ্যাকাউন্ট --</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }})</option>
                            @endforeach
                        </x-core::select>
                    </div>

                    <x-core::textarea
                        name="note"
                        id="edit_lend_note"
                        label="নোট"
                        label-en="Note"
                        rows="2"
                        size="sm"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-update-lend">
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

            function reloadLendTable() {
                var tableId = 'lends-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            $(document).on('change', '#filter-lend-status', function () {
                reloadLendTable();
            });

            $('#btn-open-create-lend-modal').on('click', function () {
                var $form = $('#create_lend_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#create_lend_date').val(new Date().toISOString().split('T')[0]);
                var defaultAcc = $('#default-lend-account-id').val();
                if (defaultAcc) { $('#create_lend_account_id').val(defaultAcc); }
                $('#createLendModal').addClass('open');
                setTimeout(function () {
                    $('#create_lend_borrower_name').focus();
                }, 100);
            });

            $(document).on('click', '.btn-edit-lend', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var $form = $('#edit_lend_form');
                clearFormErrors($form);

                var action = $btn.data('action');
                var url = $btn.data('url');

                if (action) {
                    $form.attr('action', action);
                }
                $('#edit_lend_borrower_name').val($btn.data('borrower-name'));
                $('#edit_lend_amount').val($btn.data('amount'));
                $('#edit_lend_date').val($btn.data('date'));
                $('#edit_lend_status').val($btn.data('status'));
                $('#edit_lend_account_id').val($btn.data('account-id'));
                $('#edit_lend_note').val($btn.data('note') || '');

                if (url) {
                    $.getJSON(url, function (data) {
                        if (data) {
                            if (data.borrower_name !== undefined) $('#edit_lend_borrower_name').val(data.borrower_name);
                            if (data.amount !== undefined) $('#edit_lend_amount').val(data.amount);
                            if (data.date) $('#edit_lend_date').val(data.date);
                            if (data.status) $('#edit_lend_status').val(data.status);
                            if (data.account_id) $('#edit_lend_account_id').val(data.account_id);
                            if (data.note !== undefined) $('#edit_lend_note').val(data.note || '');
                        }
                    });
                }

                $('#editLendModal').addClass('open');
                setTimeout(function () {
                    $('#edit_lend_borrower_name').focus();
                }, 100);
            });

            $('#create_lend_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-lend');

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
                        $('#createLendModal').removeClass('open');
                        $form[0].reset();
                        reloadLendTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'ধারের তথ্য সফলভাবে যোগ করা হয়েছে', 'Lend created successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else if (typeof window.toast === 'function') {
                            window.toast('ধার যোগ করতে সমস্যা হয়েছে', 'Failed to create lend');
                        }
                    }
                });
            });

            $('#edit_lend_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-update-lend');

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
                        $('#editLendModal').removeClass('open');
                        reloadLendTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'ধারের তথ্য হালনাগাদ করা হয়েছে', 'Lend updated successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else if (typeof window.toast === 'function') {
                            window.toast('ধার হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update lend');
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
