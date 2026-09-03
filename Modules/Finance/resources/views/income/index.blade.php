<x-core::layout
    title="আয়"
    title-en="Income"
    subtitle="দোকানের আয় পরিচালনা করুন"
    subtitle-en="Manage your shop's income"
    active="income"
>
    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="min-width:220px;">
                <select name="filter_income_account" id="filter-income-account" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none; width:100%;">
                    <option value="" data-text-bn="সকল অ্যাকাউন্ট" data-text-en="All Accounts">সকল অ্যাকাউন্ট</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->display_name }}</option>
                    @endforeach
                </select>
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
        <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-income-modal">
            <span class="bn">নতুন আয়</span><span class="en" style="display:none;">New Income</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'incomes-data-table']) !!}
        </div>
    </div>

    {{-- Create Income Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createIncomeModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="trending-up" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন আয় যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Income</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('income.store') }}" id="create_income_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="source"
                        id="create_income_source"
                        label="উৎস"
                        label-en="Source"
                        placeholder="যেমন: বিবিধ আয় / সার্ভিস চার্জ"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="amount"
                            id="create_income_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            label="পরিমাণ (৳)"
                            label-en="Amount (৳)"
                            placeholder="0.00"
                            prefix="৳"
                            :required="true"
                        />

                        <x-core::input
                            name="income_date"
                            id="create_income_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            :value="now()->format('Y-m-d')"
                            :required="true"
                        />
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">জমা অ্যাকাউন্ট</label>
                        <label class="en" style="display:none;">Deposit Account</label>
                        <select name="account_id" id="create_income_account_id">
                            <option value="" data-text-bn="-- নির্বাচন করুন (ডিফল্ট অ্যাকাউন্ট) --" data-text-en="-- Select (Default Account) --">-- নির্বাচন করুন (ডিফল্ট অ্যাকাউন্ট) --</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (int) old('account_id', $acc->is_default ? $acc->id : 0) === $acc->id ? 'selected' : '' }}>
                                    {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <x-core::textarea
                        name="note"
                        id="create_income_note"
                        label="নোট"
                        label-en="Note"
                        placeholder="ঐচ্ছিক নোট"
                        rows="2"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-save-create-income">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Income Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editIncomeModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">আয় সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Income</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_income_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="source"
                        id="edit_income_source"
                        label="উৎস"
                        label-en="Source"
                        placeholder="যেমন: বিবিধ আয় / সার্ভিস চার্জ"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="amount"
                            id="edit_income_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            label="পরিমাণ (৳)"
                            label-en="Amount (৳)"
                            placeholder="0.00"
                            prefix="৳"
                            :required="true"
                        />

                        <x-core::input
                            name="income_date"
                            id="edit_income_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            :required="true"
                        />
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">জমা অ্যাকাউন্ট</label>
                        <label class="en" style="display:none;">Deposit Account</label>
                        <select name="account_id" id="edit_income_account_id">
                            <option value="" data-text-bn="-- নির্বাচন করুন (ডিফল্ট অ্যাকাউন্ট) --" data-text-en="-- Select (Default Account) --">-- নির্বাচন করুন (ডিফল্ট অ্যাকাউন্ট) --</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <x-core::textarea
                        name="note"
                        id="edit_income_note"
                        label="নোট"
                        label-en="Note"
                        placeholder="ঐচ্ছিক নোট"
                        rows="2"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-update-income">
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

            function reloadIncomeTable() {
                var tableId = 'incomes-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filter changes
            $(document).on('change', '#filter-income-account', function () {
                reloadIncomeTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-income-account').val('');
                reloadIncomeTable();
            });

            // Open Create Modal
            $('#btn-open-create-income-modal').on('click', function () {
                var $form = $('#create_income_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#create_income_date').val(new Date().toISOString().split('T')[0]);
                $('#createIncomeModal').addClass('open');
                setTimeout(function () {
                    $('#create_income_source').focus();
                }, 100);
            });

            // Open Edit Modal
            $(document).on('click', '.btn-edit-income', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var $form = $('#edit_income_form');
                clearFormErrors($form);

                var action = $btn.data('action');
                var url = $btn.data('url');
                var id = $btn.data('id');
                var source = $btn.data('source');
                var amount = $btn.data('amount');
                var incomeDate = $btn.data('income-date');
                var accountId = $btn.data('account-id');
                var note = $btn.data('note') || '';

                if (action) {
                    $form.attr('action', action);
                }
                $('#edit_income_source').val(source);
                $('#edit_income_amount').val(amount);
                $('#edit_income_date').val(incomeDate);
                $('#edit_income_account_id').val(accountId);
                $('#edit_income_note').val(note);

                $('#editIncomeModal').addClass('open');
                setTimeout(function () {
                    $('#edit_income_source').focus();
                }, 100);
            });

            // Submit Create Income Form via AJAX
            $('#create_income_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-income');
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
                        $('#createIncomeModal').removeClass('open');
                        $form[0].reset();
                        reloadIncomeTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'আয় সফলভাবে যোগ করা হয়েছে', 'Income created successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('আয় যোগ করতে সমস্যা হয়েছে', 'Failed to create income');
                            }
                        }
                    }
                });
            });

            // Submit Edit Income Form via AJAX
            $('#edit_income_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-update-income');
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
                        $('#editIncomeModal').removeClass('open');
                        reloadIncomeTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'আয় হালনাগাদ করা হয়েছে', 'Income updated successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('আয় হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update income');
                            }
                        }
                    }
                });
            });

            // Close Modal Handlers
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
