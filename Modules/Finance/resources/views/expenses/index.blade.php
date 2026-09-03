<x-core::layout
    title="ব্যয়"
    title-en="Expense"
    subtitle="দোকানের ব্যয় পরিচালনা করুন"
    subtitle-en="Manage your shop's expenses"
    active="expense"
>
    <x-finance::tabbar active="expense" />

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="min-width:200px;">
                <select name="filter_expense_category" id="filter-expense-category" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none; width:100%;">
                    <option value="" data-text-bn="সকল ক্যাটাগরি" data-text-en="All Categories">সকল ক্যাটাগরি</option>
                    @foreach ($expenseCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:200px;">
                <select name="filter_expense_account" id="filter-expense-account" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none; width:100%;">
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
        <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-expense-modal">
            <span class="bn">নতুন ব্যয়</span><span class="en" style="display:none;">New Expense</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'expenses-data-table']) !!}
        </div>
    </div>

    {{-- Create Expense Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createExpenseModal" style="z-index:999;">
        <div class="modal-box" style="width:540px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="trending-down" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ব্যয় যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Expense</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
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
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="field" style="margin-top:0;">
                            <label class="bn">ক্যাটাগরি</label>
                            <label class="en" style="display:none;">Category</label>
                            <select name="expense_category_id" id="create_expense_category_id">
                                <option value="" data-text-bn="-- নির্বাচন করুন --" data-text-en="-- Select --">-- নির্বাচন করুন --</option>
                                @foreach ($expenseCategories as $category)
                                    <option value="{{ $category->id }}" {{ (int) old('expense_category_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="field" style="margin-top:0;">
                            <label class="bn">সাব-ক্যাটাগরি</label>
                            <label class="en" style="display:none;">Sub-category</label>
                            <select name="expense_sub_category_id" id="create_expense_sub_category_id">
                                <option value="" data-text-bn="-- নির্বাচন করুন (ঐচ্ছিক) --" data-text-en="-- Select (Optional) --">-- নির্বাচন করুন (ঐচ্ছিক) --</option>
                            </select>
                            @error('expense_sub_category_id') <div class="field-error">{{ $message }}</div> @enderror
                        </div>
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
                            :required="true"
                        />

                        <x-core::input
                            name="expense_date"
                            id="create_expense_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            :value="now()->format('Y-m-d')"
                            :required="true"
                        />
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">পেমেন্ট অ্যাকাউন্ট</label>
                        <label class="en" style="display:none;">Payment Account</label>
                        <select name="account_id" id="create_expense_account_id">
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
                        id="create_expense_note"
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
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ব্যয় সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Expense</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
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
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="field" style="margin-top:0;">
                            <label class="bn">ক্যাটাগরি</label>
                            <label class="en" style="display:none;">Category</label>
                            <select name="expense_category_id" id="edit_expense_category_id">
                                <option value="" data-text-bn="-- নির্বাচন করুন --" data-text-en="-- Select --">-- নির্বাচন করুন --</option>
                                @foreach ($expenseCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="field" style="margin-top:0;">
                            <label class="bn">সাব-ক্যাটাগরি</label>
                            <label class="en" style="display:none;">Sub-category</label>
                            <select name="expense_sub_category_id" id="edit_expense_sub_category_id">
                                <option value="" data-text-bn="-- নির্বাচন করুন (ঐচ্ছিক) --" data-text-en="-- Select (Optional) --">-- নির্বাচন করুন (ঐচ্ছিক) --</option>
                            </select>
                            @error('expense_sub_category_id') <div class="field-error">{{ $message }}</div> @enderror
                        </div>
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
                            :required="true"
                        />

                        <x-core::input
                            name="expense_date"
                            id="edit_expense_date"
                            type="date"
                            label="তারিখ"
                            label-en="Date"
                            :required="true"
                        />
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">পেমেন্ট অ্যাকাউন্ট</label>
                        <label class="en" style="display:none;">Payment Account</label>
                        <select name="account_id" id="edit_expense_account_id">
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
                        id="edit_expense_note"
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
                var html = '<option value="" data-text-bn="-- নির্বাচন করুন (ঐচ্ছিক) --" data-text-en="-- Select (Optional) --">-- নির্বাচন করুন (ঐচ্ছিক) --</option>';

                $.each(options, function (_, sub) {
                    var isSel = String(sub.id) === String(selectedSubId) ? ' selected' : '';
                    html += '<option value="' + sub.id + '"' + isSel + '>' + $('<div>').text(sub.name).html() + '</option>';
                });

                $subSelect.html(html);
                if (window.updateSelectOptionsLang) {
                    window.updateSelectOptionsLang($subSelect);
                }
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

            // Dependent subcategory changes
            $('#create_expense_category_id').on('change', function () {
                populateSubCategories($(this), $('#create_expense_sub_category_id'), '');
            });

            $('#edit_expense_category_id').on('change', function () {
                populateSubCategories($(this), $('#edit_expense_sub_category_id'), '');
            });

            // Filter changes
            $(document).on('change', '#filter-expense-category, #filter-expense-account', function () {
                reloadExpenseTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-expense-category').val('');
                $('#filter-expense-account').val('');
                reloadExpenseTable();
            });

            // Open Create Modal
            $('#btn-open-create-expense-modal').on('click', function () {
                var $form = $('#create_expense_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#create_expense_date').val(new Date().toISOString().split('T')[0]);
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
                var note = $btn.data('note') || '';

                if (action) {
                    $form.attr('action', action);
                }
                $('#edit_expense_title').val(title);
                $('#edit_expense_amount').val(amount);
                $('#edit_expense_date').val(expenseDate);
                $('#edit_expense_category_id').val(categoryId);
                populateSubCategories($('#edit_expense_category_id'), $('#edit_expense_sub_category_id'), subCategoryId);
                $('#edit_expense_sub_category_id').val(subCategoryId);
                $('#edit_expense_account_id').val(accountId);
                $('#edit_expense_note').val(note);

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
