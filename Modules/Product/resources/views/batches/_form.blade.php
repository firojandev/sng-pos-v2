<div class="field" style="margin-top:0;">
    <label class="bn">পণ্য</label><label class="en" style="display:none;">Product</label>
    <select name="product_id" required>
        <option value="">-- নির্বাচন করুন --</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}" {{ (int) old('product_id', $batch->product_id) === $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->sku }})</option>
        @endforeach
    </select>
    @error('product_id') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">ব্যাচ নং</label><label class="en" style="display:none;">Batch No</label>
    <input type="text" name="batch_no" value="{{ old('batch_no', $batch->batch_no) }}" placeholder="যেমন BT-2026-001" required>
    @error('batch_no') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">উৎপাদন তারিখ</label><label class="en" style="display:none;">Mfg Date</label>
        <input type="date" name="mfg_date" value="{{ old('mfg_date', optional($batch->mfg_date)->format('Y-m-d')) }}">
    </div>
    <div class="field">
        <label class="bn">মেয়াদ শেষের তারিখ</label><label class="en" style="display:none;">Expiry Date</label>
        <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($batch->expiry_date)->format('Y-m-d')) }}">
        @error('expiry_date') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">পরিমাণ</label><label class="en" style="display:none;">Quantity</label>
    <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity', $batch->quantity) }}" required>
    @error('quantity') <div class="field-error">{{ $message }}</div> @enderror
</div>
