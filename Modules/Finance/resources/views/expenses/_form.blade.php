@php
    $subCategoriesByCategory = $expenseCategories->mapWithKeys(
        fn ($category) => [$category->id => $category->subCategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()]
    );

    $defaultCashAcc = $accounts->firstWhere('type', 'cash');
    $defaultCashId = $defaultCashAcc ? $defaultCashAcc->id : '';
    $selectedMethod = old('payment_method', $expense->payment_method ?? 'cash');
    if (!in_array($selectedMethod, ['cash', 'bank', 'mfs'])) {
        $selectedMethod = 'cash';
    }
@endphp

<input type="hidden" id="form-default-cash-account-id" value="{{ $defaultCashId }}">

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::input
        name="title"
        label="শিরোনাম"
        label-en="Title"
        value="{{ old('title', $expense->title) }}"
        placeholder="যেমন: দোকান ভাড়া / বিদ্যুৎ বিল"
        size="sm"
        :required="true"
    />

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <x-core::select
            name="expense_category_id"
            id="f_expense_category"
            label="ক্যাটাগরি"
            label-en="Category"
            size="sm"
        >
            <option value="">-- নির্বাচন করুন --</option>
            @foreach ($expenseCategories as $category)
                <option value="{{ $category->id }}" {{ (int) old('expense_category_id', $expense->expense_category_id) === $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </x-core::select>

        <x-core::select
            name="expense_sub_category_id"
            id="f_expense_subcategory"
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
            type="number"
            step="0.01"
            min="0"
            prefix="৳"
            label="পরিমাণ (৳)"
            label-en="Amount (৳)"
            value="{{ old('amount', $expense->amount) }}"
            placeholder="0.00"
            size="sm"
            :required="true"
            :stepper="false"
        />

        <x-core::input
            name="expense_date"
            type="date"
            label="তারিখ"
            label-en="Date"
            value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            size="sm"
            :required="true"
        />
    </div>

    <div class="payment-account-grid" id="form_expense_payment_grid" style="display:grid; grid-template-columns:{{ $selectedMethod === 'cash' ? '1fr' : '1fr 1fr' }}; gap:12px;">
        <div class="payment-method-wrapper">
            <x-core::select
                name="payment_method"
                id="form_expense_payment_method"
                label="পেমেন্ট মেথড"
                label-en="Payment Method"
                size="sm"
                :required="true"
            >
                <option value="cash" {{ $selectedMethod === 'cash' ? 'selected' : '' }}>নগদ (Cash)</option>
                <option value="bank" {{ $selectedMethod === 'bank' ? 'selected' : '' }}>ব্যাংক (Bank)</option>
                <option value="mfs" {{ $selectedMethod === 'mfs' ? 'selected' : '' }}>মোবাইল ব্যাংকিং (MFS)</option>
            </x-core::select>
        </div>

        <div class="account-select-wrapper" id="form_expense_account_wrapper" style="display:{{ $selectedMethod === 'cash' ? 'none' : 'block' }};">
            <x-core::select
                name="account_id"
                id="form_expense_account_id"
                label="পেমেন্ট অ্যাকাউন্ট"
                label-en="Payment Account"
                size="sm"
            >
                <option value="">-- অ্যাকাউন্ট নির্বাচন করুন --</option>
                @foreach ($accounts as $acc)
                    <option
                        value="{{ $acc->id }}"
                        data-type="{{ $acc->type }}"
                        {{ (int) old('account_id', $expense->account_id ?? 0) === $acc->id ? 'selected' : '' }}
                    >
                        {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
                    </option>
                @endforeach
            </x-core::select>
        </div>
    </div>

    <x-core::textarea
        name="note"
        label="নোট"
        label-en="Note"
        placeholder="ঐচ্ছিক নোট লিখুন..."
        rows="2"
        size="sm"
        value="{{ old('note', $expense->note) }}"
    />
</div>

@push('scripts')
<script>
$(function () {
    var EXPENSE_SUBCATS_BY_CATEGORY = @json($subCategoriesByCategory);
    var SELECTED_EXPENSE_SUBCATEGORY = @json(old('expense_sub_category_id', $expense->expense_sub_category_id));

    function filterExpenseSubCategories() {
        var categoryId = $('#f_expense_category').val();
        var $subSelect = $('#f_expense_subcategory');
        var options = (EXPENSE_SUBCATS_BY_CATEGORY[categoryId] || []);
        var html = '<option value="">-- নির্বাচন করুন (ঐচ্ছিক) --</option>';

        $.each(options, function (_, sub) {
            var selected = String(sub.id) === String(SELECTED_EXPENSE_SUBCATEGORY) ? ' selected' : '';
            html += '<option value="' + sub.id + '"' + selected + '>' + $('<div>').text(sub.name).html() + '</option>';
        });

        $subSelect.html(html);
    }

    function applyFormPaymentRules(method, selectedAccountId) {
        var $grid = $('#form_expense_payment_grid');
        var $wrapper = $('#form_expense_account_wrapper');
        var $select = $('#form_expense_account_id');
        var defaultCashId = $('#form-default-cash-account-id').val();

        if (!method || method === 'cash') {
            $wrapper.hide();
            $grid.css('grid-template-columns', '1fr');
            if (defaultCashId) {
                $select.val(defaultCashId);
            }
        } else if (method === 'bank' || method === 'mfs') {
            $wrapper.show();
            $grid.css('grid-template-columns', '1fr 1fr');

            var matched = false;
            $select.find('option').each(function () {
                var $opt = $(this);
                var optType = $opt.data('type');
                var optVal = $opt.val();
                if (!optVal) return;

                if (optType === method) {
                    $opt.show().prop('disabled', false);
                    if (selectedAccountId && String(optVal) === String(selectedAccountId)) {
                        $opt.prop('selected', true);
                        matched = true;
                    }
                } else {
                    $opt.hide().prop('disabled', true);
                    if ($opt.is(':selected')) {
                        $opt.prop('selected', false);
                    }
                }
            });

            if (!matched) {
                var $firstValid = $select.find('option:enabled[value!=""]:first');
                if ($firstValid.length) {
                    $firstValid.prop('selected', true);
                } else {
                    $select.val('');
                }
            }
        }
    }

    $(document).on('change', '#f_expense_category', function () {
        SELECTED_EXPENSE_SUBCATEGORY = '';
        filterExpenseSubCategories();
    });

    $(document).on('change', '#form_expense_payment_method', function () {
        applyFormPaymentRules($(this).val());
    });

    filterExpenseSubCategories();
    applyFormPaymentRules('{{ $selectedMethod }}', '{{ old('account_id', $expense->account_id) }}');
});
</script>
@endpush
