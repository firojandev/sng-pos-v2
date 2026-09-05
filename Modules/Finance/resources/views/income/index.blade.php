<x-core::layout
    title="আয়"
    title-en="Income"
    subtitle="দোকানের আয় পরিচালনা করুন"
    subtitle-en="Manage your shop's income"
    active="income"
>
    {{-- Summary KPI Stat Cards --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="trending-up"
                color="teal"
                :value="'৳' . number_format($metrics['totalIncome'], 2)"
                label="মোট আয়"
                label-en="Total Income"
                :subtext="number_format($metrics['totalCount']) . ' টি লেনদেন সম্পন্ন'"
                :subtext-en="number_format($metrics['totalCount']) . ' Transactions'"
            />

            <x-core::stat-card
                icon="calendar"
                color="green"
                value-color="green"
                :value="'৳' . number_format($metrics['todayIncome'], 2)"
                label="আজকের আয়"
                label-en="Today's Income"
                subtext="আজকের মোট প্রাপ্তি"
                subtext-en="Today's collection"
            />

            <x-core::stat-card
                icon="bar-chart-2"
                color="blue"
                value-color="blue"
                :value="'৳' . number_format($metrics['thisMonthIncome'], 2)"
                label="চলতি মাসের আয়"
                label-en="This Month's Income"
                :subtext="now()->format('F Y') . ' এর আয়'"
                :subtext-en="now()->format('M Y') . ' volume'"
            />

            <x-core::stat-card
                icon="receipt"
                color="gold"
                :value="number_format($metrics['totalCount'])"
                label="মোট লেনদেন সংখ্যা"
                label-en="Total Transactions"
                subtext="সর্বমোট আয়ের রেকর্ড"
                subtext-en="Total income entries"
            />
        </div>
    @endif

    @php
        $accountFilterOptions = ['' => 'সকল অ্যাকাউন্ট (All Accounts)'];
        foreach ($accounts as $acc) {
            $accountFilterOptions[$acc->id] = $acc->display_name;
        }

        $methodFilterOptions = [
            '' => 'সকল মেথড (All Methods)',
            'cash' => 'নগদ (Cash)',
            'bank' => 'ব্যাংক (Bank)',
            'mfs' => 'মোবাইল ব্যাংকিং (MFS)',
        ];

        $defaultCashAcc = $accounts->firstWhere('type', 'cash');
        $defaultCashId = $defaultCashAcc ? $defaultCashAcc->id : '';
    @endphp
    <input type="hidden" id="default-cash-account-id" value="{{ $defaultCashId }}">

    {{-- Filter Toolbar & Action Button --}}
    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px; overflow-x:auto; max-width:100%; padding-bottom:2px;">
            <div style="width:200px; flex-shrink:0;">
                <x-core::select
                    id="filter-income-account"
                    name="filter_income_account"
                    size="sm"
                    :no-margin="true"
                    :options="$accountFilterOptions"
                />
            </div>

            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    id="filter-income-method"
                    name="filter_income_method"
                    size="sm"
                    :no-margin="true"
                    :options="$methodFilterOptions"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-income-date-from"
                    name="filter_income_date_from"
                    size="sm"
                    :no-margin="true"
                    placeholder="হতে / From"
                    title="তারিখ হতে / Date From"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-income-date-to"
                    name="filter_income_date_to"
                    size="sm"
                    :no-margin="true"
                    placeholder="পর্যন্ত / To"
                    title="তারিখ পর্যন্ত / Date To"
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

        <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-income-modal">
            <span class="bn">নতুন আয়</span>
            <span class="en" style="display:none;">New Income</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'incomes-data-table']) !!}
        </div>
    </div>

    {{-- Create Income Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createIncomeModal" style="z-index:999;">
        <div class="modal-box" style="width:540px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
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
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>
            <form method="POST" action="{{ route('income.store') }}" id="create_income_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="source"
                        id="create_income_source"
                        label="উৎস"
                        label-en="Source"
                        placeholder="যেমন: সার্ভিস চার্জ / বিবিধ আয়"
                        size="sm"
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
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="income_date"
                            id="create_income_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            :value="now()->format('Y-m-d')"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <div class="payment-account-grid" id="create_income_payment_grid" style="display:grid; grid-template-columns:1fr; gap:12px;">
                        <div class="payment-method-wrapper">
                            <x-core::select
                                name="payment_method"
                                id="create_income_payment_method"
                                label="পেমেন্ট মেথড"
                                label-en="Payment Method"
                                size="sm"
                                :required="true"
                            >
                                <option value="cash" selected>নগদ (Cash)</option>
                                <option value="bank">ব্যাংক (Bank)</option>
                                <option value="mfs">মোবাইল ব্যাংকিং (MFS)</option>
                            </x-core::select>
                        </div>

                        <div class="account-select-wrapper" id="create_income_account_wrapper" style="display:none;">
                            <x-core::select
                                name="account_id"
                                id="create_income_account_id"
                                class="account-select-input"
                                label="জমা অ্যাকাউন্ট"
                                label-en="Deposit Account"
                                size="sm"
                            >
                                <option value="">-- অ্যাকাউন্ট নির্বাচন করুন --</option>
                                @foreach ($accounts as $acc)
                                    <option
                                        value="{{ $acc->id }}"
                                        data-type="{{ $acc->type }}"
                                    >
                                        {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
                                    </option>
                                @endforeach
                            </x-core::select>
                        </div>
                    </div>

                    <x-core::textarea
                        name="note"
                        id="create_income_note"
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
        <div class="modal-box" style="width:540px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
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
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
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
                        placeholder="যেমন: সার্ভিস চার্জ / বিবিধ আয়"
                        size="sm"
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
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="income_date"
                            id="edit_income_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <div class="payment-account-grid" id="edit_income_payment_grid" style="display:grid; grid-template-columns:1fr; gap:12px;">
                        <div class="payment-method-wrapper">
                            <x-core::select
                                name="payment_method"
                                id="edit_income_payment_method"
                                label="পেমেন্ট মেথড"
                                label-en="Payment Method"
                                size="sm"
                                :required="true"
                            >
                                <option value="cash">নগদ (Cash)</option>
                                <option value="bank">ব্যাংক (Bank)</option>
                                <option value="mfs">মোবাইল ব্যাংকিং (MFS)</option>
                            </x-core::select>
                        </div>

                        <div class="account-select-wrapper" id="edit_income_account_wrapper" style="display:none;">
                            <x-core::select
                                name="account_id"
                                id="edit_income_account_id"
                                class="account-select-input"
                                label="জমা অ্যাকাউন্ট"
                                label-en="Deposit Account"
                                size="sm"
                            >
                                <option value="">-- অ্যাকাউন্ট নির্বাচন করুন --</option>
                                @foreach ($accounts as $acc)
                                    <option
                                        value="{{ $acc->id }}"
                                        data-type="{{ $acc->type }}"
                                    >
                                        {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
                                    </option>
                                @endforeach
                            </x-core::select>
                        </div>
                    </div>

                    <x-core::textarea
                        name="note"
                        id="edit_income_note"
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

            function applyPaymentMethodRules($scope, method, selectedAccountId) {
                var $grid = $scope.find('.payment-account-grid');
                var $accountWrapper = $scope.find('.account-select-wrapper');
                var $accountSelect = $scope.find('select[name="account_id"]');
                var defaultCashId = $('#default-cash-account-id').val();

                if (!method || method === 'cash') {
                    $accountWrapper.hide();
                    $grid.css('grid-template-columns', '1fr');
                    if (defaultCashId) {
                        $accountSelect.val(defaultCashId);
                    } else {
                        $accountSelect.val('');
                    }
                } else if (method === 'bank' || method === 'mfs') {
                    $accountWrapper.show();
                    $grid.css('grid-template-columns', '1fr 1fr');

                    var matchedFound = false;
                    $accountSelect.find('option').each(function () {
                        var $opt = $(this);
                        var optType = $opt.data('type');
                        var optVal = $opt.val();

                        if (!optVal) {
                            $opt.show().prop('disabled', false);
                            return;
                        }

                        if (optType === method) {
                            $opt.show().prop('disabled', false);
                            if (selectedAccountId && String(optVal) === String(selectedAccountId)) {
                                $opt.prop('selected', true);
                                matchedFound = true;
                            }
                        } else {
                            $opt.hide().prop('disabled', true);
                            if ($opt.is(':selected')) {
                                $opt.prop('selected', false);
                            }
                        }
                    });

                    // If no matching account is currently selected, pick the first valid one
                    if (!matchedFound) {
                        var $firstValid = $accountSelect.find('option:enabled[value!=""]:first');
                        if ($firstValid.length) {
                            $firstValid.prop('selected', true);
                        } else {
                            $accountSelect.val('');
                        }
                    }
                }
            }

            // Payment method change listeners
            $(document).on('change', '#create_income_payment_method', function () {
                applyPaymentMethodRules($('#createIncomeModal'), $(this).val());
            });

            $(document).on('change', '#edit_income_payment_method', function () {
                applyPaymentMethodRules($('#editIncomeModal'), $(this).val());
            });

            // Filter changes
            $(document).on('change', '#filter-income-account, #filter-income-method, #filter-income-date-from, #filter-income-date-to', function () {
                reloadIncomeTable();
            });

            // Reset filters
            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-income-account').val('');
                $('#filter-income-method').val('');
                $('#filter-income-date-from').val('');
                $('#filter-income-date-to').val('');
                reloadIncomeTable();
            });

            // Open Create Modal
            $('#btn-open-create-income-modal').on('click', function () {
                var $form = $('#create_income_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#create_income_date').val(new Date().toISOString().split('T')[0]);
                $('#create_income_payment_method').val('cash');
                applyPaymentMethodRules($('#createIncomeModal'), 'cash');
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
                var paymentMethod = $btn.data('payment-method') || 'cash';
                var note = $btn.data('note') || '';

                if (paymentMethod !== 'cash' && paymentMethod !== 'bank' && paymentMethod !== 'mfs') {
                    paymentMethod = 'cash';
                }

                if (action) {
                    $form.attr('action', action);
                }
                $('#edit_income_source').val(source);
                $('#edit_income_amount').val(amount);
                $('#edit_income_date').val(incomeDate);
                $('#edit_income_payment_method').val(paymentMethod);
                applyPaymentMethodRules($('#editIncomeModal'), paymentMethod, accountId);
                $('#edit_income_note').val(note);

                // Fetch fresh details if URL provided
                if (url) {
                    $.getJSON(url, function (data) {
                        if (data) {
                            if (data.source) $('#edit_income_source').val(data.source);
                            if (data.amount !== undefined) $('#edit_income_amount').val(data.amount);
                            if (data.income_date) $('#edit_income_date').val(data.income_date);
                            var pMethod = data.payment_method || 'cash';
                            if (pMethod !== 'cash' && pMethod !== 'bank' && pMethod !== 'mfs') {
                                pMethod = 'cash';
                            }
                            $('#edit_income_payment_method').val(pMethod);
                            applyPaymentMethodRules($('#editIncomeModal'), pMethod, data.account_id);
                            if (data.note !== undefined) $('#edit_income_note').val(data.note || '');
                        }
                    });
                }

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
