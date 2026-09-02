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
    <label class="bn">জমা অ্যাকাউন্ট</label><label class="en" style="display:none;">Deposit Account</label>
    <select name="account_id">
        <option value="">-- নির্বাচন করুন (ডিফল্ট অ্যাকাউন্ট) --</option>
        @foreach ($accounts as $acc)
            <option value="{{ $acc->id }}" {{ (int) old('account_id', $income->account_id ?? ($acc->is_default ? $acc->id : 0)) === $acc->id ? 'selected' : '' }}>
                {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
            </option>
        @endforeach
    </select>
    @error('account_id') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
    <textarea name="note" placeholder="ঐচ্ছিক নোট">{{ old('note', $income->note) }}</textarea>
    @error('note') <div class="field-error">{{ $message }}</div> @enderror
</div>
