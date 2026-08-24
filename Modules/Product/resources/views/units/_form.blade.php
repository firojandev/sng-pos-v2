<div class="field-row">
    <div class="field" style="margin-top:0;">
        <label class="bn">ইউনিটের নাম</label><label class="en" style="display:none;">Unit Name</label>
        <input type="text" name="name" value="{{ old('name', $unit->name) }}" placeholder="যেমন কিলোগ্রাম" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field" style="margin-top:0;">
        <label class="bn">সংক্ষিপ্ত কোড</label><label class="en" style="display:none;">Short Code</label>
        <input type="text" name="short_code" value="{{ old('short_code', $unit->short_code) }}" placeholder="যেমন Kg" required>
        @error('short_code') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>
