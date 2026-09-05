<x-core::layout
    title="ব্যয়"
    title-en="Expense"
    subtitle="দোকানের ব্যয় পরিচালনা করুন"
    subtitle-en="Manage your shop's expenses"
    active="expense"
>
    <x-finance::tabbar active="expense" />

    {{-- Summary KPI Stat Cards --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="trending-down"
                color="red"
                value-color="red"
                :value="'৳' . number_format($metrics['totalExpense'], 2)"
                label="মোট ব্যয়"
                label-en="Total Expense"
                :subtext="number_format($metrics['totalCount']) . ' টি লেনদেন সম্পন্ন'"
                :subtext-en="number_format($metrics['totalCount']) . ' Transactions'"
            />

            <x-core::stat-card
                icon="calendar"
                color="gold"
                value-color="gold"
                :value="'৳' . number_format($metrics['todayExpense'], 2)"
                label="আজকের ব্যয়"
                label-en="Today's Expense"
                subtext="আজকের মোট খরচ"
                subtext-en="Today's expense"
            />

            <x-core::stat-card
                icon="bar-chart-2"
                color="blue"
                value-color="blue"
                :value="'৳' . number_format($metrics['thisMonthExpense'], 2)"
                label="চলতি মাসের ব্যয়"
                label-en="This Month's Expense"
                :subtext="now()->format('F Y') . ' এর ব্যয়'"
                :subtext-en="now()->format('M Y') . ' volume'"
            />

            <x-core::stat-card
                icon="receipt"
                color="teal"
                :value="number_format($metrics['totalCount'])"
                label="মোট লেনদেন সংখ্যা"
                label-en="Total Transactions"
                subtext="সর্বমোট ব্যয়ের রেকর্ড"
                subtext-en="Total expense entries"
            />
        </div>
    @endif

    @php
        $categoryFilterOptions = ['' => 'সকল ক্যাটাগরি (All Categories)'];
        foreach ($expenseCategories as $cat) {
            $categoryFilterOptions[$cat->id] = $cat->name;
        }

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
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    id="filter-expense-category"
                    name="filter_expense_category"
                    size="sm"
                    :no-margin="true"
                    :options="$categoryFilterOptions"
                />
            </div>

            <div style="width:180px; flex-shrink:0;">
                <x-core::select
                    id="filter-expense-account"
                    name="filter_expense_account"
                    size="sm"
                    :no-margin="true"
                    :options="$accountFilterOptions"
                />
            </div>

            <div style="width:160px; flex-shrink:0;">
                <x-core::select
                    id="filter-expense-method"
                    name="filter_expense_method"
                    size="sm"
                    :no-margin="true"
                    :options="$methodFilterOptions"
                />
            </div>

            <div style="width:135px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-expense-date-from"
                    name="filter_expense_date_from"
                    size="sm"
                    :no-margin="true"
                    placeholder="হতে / From"
                    title="তারিখ হতে / Date From"
                />
            </div>

            <div style="width:135px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-expense-date-to"
                    name="filter_expense_date_to"
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

        <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-expense-modal">
            <span class="bn">নতুন ব্যয়</span>
            <span class="en" style="display:none;">New Expense</span>
        </x-core::button>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'expenses-data-table']) !!}
        </div>
    </div>

    {{-- Create Expense Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createExpenseModal" style="z-index:999;">
        <div class="modal-box" style="width:540px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--red-100); color:var(--red-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="trending-down" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ব্যয় যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Expense</span>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>
            <form method="POST" action="{{ route('expense.store') }}" id="create_expense_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="title"
                        id="create_expense_title"
                        label="শিরোনাম"
                        label-en="Title"
                        placeholder="যেমন: দোকান ভাড়া / বিদ্যুৎ বিল"
                        size="sm"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::select
                            name="expense_category_id"
                            id="create_expense_category_id"
                            label="ক্যাটাগরি"
                            label-en="Category"
                            size="sm"
                        >
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($expenseCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-core::select>

                        <x-core::select
                            name="expense_sub_category_id"
                            id="create_expense_sub_category_id"
                            label="সাব-ক্যাটাগরি"
                            label-en="Sub-category"
                            size="sm"
                        >
                            <option value="">-- নির্বাচন করুন (ঐচ্ছিক) --</option>
                        </x-core::select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="amount"
                            id="create_expense_amount"
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

                        <x-core::input
                            name="expense_date"
                            id="create_expense_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            :value="now()->format('Y-m-d')"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <div class="payment-account-grid" id="create_expense_payment_grid" style="display:grid; grid-template-columns:1fr; gap:12px;">
                        <div class="payment-method-wrapper">
                            <x-core::select
                                name="payment_method"
                                id="create_expense_payment_method"
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

                        <div class="account-select-wrapper" id="create_expense_account_wrapper" style="display:none;">
                            <x-core::select
                                name="account_id"
                                id="create_expense_account_id"
                                class="account-select-input"
                                label="পেমেন্ট অ্যাকাউন্ট"
                                label-en="Payment Account"
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
                        id="create_expense_note"
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
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-save-create-expense">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Expense Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editExpenseModal" style="z-index:999;">
        <div class="modal-box" style="width:540px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--red-100); color:var(--red-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ব্যয় সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Expense</span>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>
            <form method="POST" action="" id="edit_expense_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="title"
                        id="edit_expense_title"
                        label="শিরোনাম"
                        label-en="Title"
                        placeholder="যেমন: দোকান ভাড়া / বিদ্যুৎ বিল"
                        size="sm"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::select
                            name="expense_category_id"
                            id="edit_expense_category_id"
                            label="ক্যাটাগরি"
                            label-en="Category"
                            size="sm"
                        >
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($expenseCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-core::select>

                        <x-core::select
                            name="expense_sub_category_id"
                            id="edit_expense_sub_category_id"
                            label="সাব-ক্যাটাগরি"
                            label-en="Sub-category"
                            size="sm"
                        >
                            <option value="">-- নির্বাচন করুন (ঐচ্ছিক) --</option>
                        </x-core::select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="amount"
                            id="edit_expense_amount"
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

                        <x-core::input
                            name="expense_date"
                            id="edit_expense_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <div class="payment-account-grid" id="edit_expense_payment_grid" style="display:grid; grid-template-columns:1fr; gap:12px;">
                        <div class="payment-method-wrapper">
                            <x-core::select
                                name="payment_method"
                                id="edit_expense_payment_method"
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

                        <div class="account-select-wrapper" id="edit_expense_account_wrapper" style="display:none;">
                            <x-core::select
                                name="account_id"
                                id="edit_expense_account_id"
                                class="account-select-input"
                                label="পেমেন্ট অ্যাকাউন্ট"
                                label-en="Payment Account"
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
                        id="edit_expense_note"
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
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-update-expense">
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
            var EXPENSE_SUBCATS = @json($subCategoriesByCategory);

            function populateSubCategories($catSelect, $subSelect, selectedSubId) {
                var categoryId = $catSelect.val();
                var options = (EXPENSE_SUBCATS && EXPENSE_SUBCATS[categoryId]) ? EXPENSE_SUBCATS[categoryId] : [];
                var html = '<option value="">-- নির্বাচন করুন (ঐচ্ছিক) --</option>';

                $.each(options, function (_, sub) {
                    var isSel = String(sub.id) === String(selectedSubId) ? ' selected' : '';
                    html += '<option value="' + sub.id + '"' + isSel + '>' + $('<div>').text(sub.name).html() + '</option>';
                });

                $subSelect.html(html);
            }

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

            function reloadExpenseTable() {
                var tableId = 'expenses-data-table';
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

            // Dependent subcategory changes
            $('#create_expense_category_id').on('change', function () {
                populateSubCategories($(this), $('#create_expense_sub_category_id'), '');
            });

            $('#edit_expense_category_id').on('change', function () {
                populateSubCategories($(this), $('#edit_expense_sub_category_id'), '');
            });

            // Payment method change listeners
            $(document).on('change', '#create_expense_payment_method', function () {
                applyPaymentMethodRules($('#createExpenseModal'), $(this).val());
            });

            $(document).on('change', '#edit_expense_payment_method', function () {
                applyPaymentMethodRules($('#editExpenseModal'), $(this).val());
            });

            // Filter changes
            $(document).on('change', '#filter-expense-category, #filter-expense-account, #filter-expense-method, #filter-expense-date-from, #filter-expense-date-to', function () {
                reloadExpenseTable();
            });

            // Reset filters
            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-expense-category').val('');
                $('#filter-expense-account').val('');
                $('#filter-expense-method').val('');
                $('#filter-expense-date-from').val('');
                $('#filter-expense-date-to').val('');
                reloadExpenseTable();
            });

            // Open Create Modal
            $('#btn-open-create-expense-modal').on('click', function () {
                var $form = $('#create_expense_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#create_expense_date').val(new Date().toISOString().split('T')[0]);
                $('#create_expense_payment_method').val('cash');
                applyPaymentMethodRules($('#createExpenseModal'), 'cash');
                populateSubCategories($('#create_expense_category_id'), $('#create_expense_sub_category_id'), '');
                $('#createExpenseModal').addClass('open');
                setTimeout(function () {
                    $('#create_expense_title').focus();
                }, 100);
            });

            // Open Edit Modal
            $(document).on('click', '.btn-edit-expense', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var $form = $('#edit_expense_form');
                clearFormErrors($form);

                var action = $btn.data('action');
                var url = $btn.data('url');
                var id = $btn.data('id');
                var title = $btn.data('title');
                var amount = $btn.data('amount');
                var expenseDate = $btn.data('expense-date');
                var categoryId = $btn.data('category-id');
                var subCategoryId = $btn.data('subcategory-id');
                var accountId = $btn.data('account-id');
                var paymentMethod = $btn.data('payment-method') || 'cash';
                var note = $btn.data('note') || '';

                if (paymentMethod !== 'cash' && paymentMethod !== 'bank' && paymentMethod !== 'mfs') {
                    paymentMethod = 'cash';
                }

                if (action) {
                    $form.attr('action', action);
                }
                $('#edit_expense_title').val(title);
                $('#edit_expense_amount').val(amount);
                $('#edit_expense_date').val(expenseDate);
                $('#edit_expense_category_id').val(categoryId);
                populateSubCategories($('#edit_expense_category_id'), $('#edit_expense_sub_category_id'), subCategoryId);
                $('#edit_expense_sub_category_id').val(subCategoryId);
                $('#edit_expense_payment_method').val(paymentMethod);
                applyPaymentMethodRules($('#editExpenseModal'), paymentMethod, accountId);
                $('#edit_expense_note').val(note);

                // Fetch fresh details if URL provided
                if (url) {
                    $.getJSON(url, function (data) {
                        if (data) {
                            if (data.title) $('#edit_expense_title').val(data.title);
                            if (data.amount !== undefined) $('#edit_expense_amount').val(data.amount);
                            if (data.expense_date) $('#edit_expense_date').val(data.expense_date);
                            if (data.expense_category_id) {
                                $('#edit_expense_category_id').val(data.expense_category_id);
                                populateSubCategories($('#edit_expense_category_id'), $('#edit_expense_sub_category_id'), data.expense_sub_category_id);
                            }
                            if (data.expense_sub_category_id) {
                                $('#edit_expense_sub_category_id').val(data.expense_sub_category_id);
                            }
                            var pMethod = data.payment_method || 'cash';
                            if (pMethod !== 'cash' && pMethod !== 'bank' && pMethod !== 'mfs') {
                                pMethod = 'cash';
                            }
                            $('#edit_expense_payment_method').val(pMethod);
                            applyPaymentMethodRules($('#editExpenseModal'), pMethod, data.account_id);
                            if (data.note !== undefined) $('#edit_expense_note').val(data.note || '');
                        }
                    });
                }

                $('#editExpenseModal').addClass('open');
                setTimeout(function () {
                    $('#edit_expense_title').focus();
                }, 100);
            });

            // Submit Create Expense Form via AJAX
            $('#create_expense_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-expense');
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
                        $('#createExpenseModal').removeClass('open');
                        $form[0].reset();
                        reloadExpenseTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'ব্যয় সফলভাবে যোগ করা হয়েছে', 'Expense created successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('ব্যয় যোগ করতে সমস্যা হয়েছে', 'Failed to create expense');
                            }
                        }
                    }
                });
            });

            // Submit Edit Expense Form via AJAX
            $('#edit_expense_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-update-expense');
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
                        $('#editExpenseModal').removeClass('open');
                        reloadExpenseTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'ব্যয় হালনাগাদ করা হয়েছে', 'Expense updated successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('ব্যয় হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update expense');
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
