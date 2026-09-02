@php
    $subCategoriesByCategory = $expenseCategories->mapWithKeys(
        fn ($category) => [$category->id => $category->subCategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()]
    );
@endphp

<div class="field" style="margin-top:0;">
    <label class="bn">শিরোনাম</label><label class="en" style="display:none;">Title</label>
    <input type="text" name="title" value="{{ old('title', $expense->title) }}" placeholder="যেমন দোকান ভাড়া" required>
    @error('title') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">ক্যাটাগরি</label><label class="en" style="display:none;">Category</label>
        <select name="expense_category_id" id="f-expense-category">
            <option value="">-- নির্বাচন করুন --</option>
            @foreach ($expenseCategories as $category)
                <option value="{{ $category->id }}" {{ (int) old('expense_category_id', $expense->expense_category_id) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('expense_category_id') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">সাব-ক্যাটাগরি</label><label class="en" style="display:none;">Sub-category</label>
        <select name="expense_sub_category_id" id="f-expense-subcategory"></select>
        @error('expense_sub_category_id') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">পরিমাণ (৳)</label><label class="en" style="display:none;">Amount (৳)</label>
        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $expense->amount) }}" placeholder="0" required>
        @error('amount') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">তারিখ</label><label class="en" style="display:none;">Date</label>
        <input type="date" name="expense_date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        @error('expense_date') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">পেমেন্ট অ্যাকাউন্ট</label><label class="en" style="display:none;">Payment Account</label>
    <select name="account_id">
        <option value="">-- নির্বাচন করুন (ডিফল্ট অ্যাকাউন্ট) --</option>
        @foreach ($accounts as $acc)
            <option value="{{ $acc->id }}" {{ (int) old('account_id', $expense->account_id ?? ($acc->is_default ? $acc->id : 0)) === $acc->id ? 'selected' : '' }}>
                {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
            </option>
        @endforeach
    </select>
    @error('account_id') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
    <textarea name="note" placeholder="ঐচ্ছিক নোট">{{ old('note', $expense->note) }}</textarea>
    @error('note') <div class="field-error">{{ $message }}</div> @enderror
</div>

<script>
$(function () {
    var EXPENSE_SUBCATS_BY_CATEGORY = @json($subCategoriesByCategory);
    var SELECTED_EXPENSE_SUBCATEGORY = @json(old('expense_sub_category_id', $expense->expense_sub_category_id));

    function filterExpenseSubCategories() {
        var categoryId = $('#f-expense-category').val();
        var $subSelect = $('#f-expense-subcategory');
        var options = (EXPENSE_SUBCATS_BY_CATEGORY[categoryId] || []);
        var html = '<option value="">-- নির্বাচন করুন (ঐচ্ছিক) --</option>';

        $.each(options, function (_, sub) {
            var selected = String(sub.id) === String(SELECTED_EXPENSE_SUBCATEGORY) ? ' selected' : '';
            html += '<option value="' + sub.id + '"' + selected + '>' + $('<div>').text(sub.name).html() + '</option>';
        });

        $subSelect.html(html);
    }

    $('#f-expense-category').on('change', function () {
        SELECTED_EXPENSE_SUBCATEGORY = '';
        filterExpenseSubCategories();
    });

    filterExpenseSubCategories();
});
</script>
