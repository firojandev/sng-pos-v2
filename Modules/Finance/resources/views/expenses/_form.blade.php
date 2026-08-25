<div class="field" style="margin-top:0;">
    <label class="bn">শিরোনাম</label><label class="en" style="display:none;">Title</label>
    <input type="text" name="title" value="{{ old('title', $expense->title) }}" placeholder="যেমন দোকান ভাড়া" required>
    @error('title') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">ক্যাটাগরি</label><label class="en" style="display:none;">Category</label>
    <select name="expense_category_id">
        <option value="">-- নির্বাচন করুন --</option>
        @foreach ($expenseCategories as $category)
            <option value="{{ $category->id }}" {{ (int) old('expense_category_id', $expense->expense_category_id) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('expense_category_id') <div class="field-error">{{ $message }}</div> @enderror
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
    <label class="bn">পেমেন্ট পদ্ধতি</label><label class="en" style="display:none;">Payment Method</label>
    <input type="text" name="payment_method" value="{{ old('payment_method', $expense->payment_method) }}" placeholder="যেমন নগদ, ব্যাংক, বিকাশ">
    @error('payment_method') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
    <textarea name="note" placeholder="ঐচ্ছিক নোট">{{ old('note', $expense->note) }}</textarea>
    @error('note') <div class="field-error">{{ $message }}</div> @enderror
</div>
