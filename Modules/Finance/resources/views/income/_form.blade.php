<div class="field" style="margin-top:0;">
    <label class="bn">উৎস</label><label class="en" style="display:none;">Source</label>
    <input type="text" name="source" value="{{ old('source', $income->source) }}" placeholder="যেমন বিবিধ আয়" required>
    @error('source') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">পরিমাণ (৳)</label><label class="en" style="display:none;">Amount (৳)</label>
        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $income->amount) }}" placeholder="0" required>
        @error('amount') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">তারিখ</label><label class="en" style="display:none;">Date</label>
        <input type="date" name="income_date" value="{{ old('income_date', optional($income->income_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        @error('income_date') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">পেমেন্ট পদ্ধতি</label><label class="en" style="display:none;">Payment Method</label>
    <input type="text" name="payment_method" value="{{ old('payment_method', $income->payment_method) }}" placeholder="যেমন নগদ, ব্যাংক, বিকাশ">
    @error('payment_method') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
    <textarea name="note" placeholder="ঐচ্ছিক নোট">{{ old('note', $income->note) }}</textarea>
    @error('note') <div class="field-error">{{ $message }}</div> @enderror
</div>
