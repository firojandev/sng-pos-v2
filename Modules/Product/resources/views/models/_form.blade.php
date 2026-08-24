<div class="field" style="margin-top:0;">
    <label class="bn">ব্র্যান্ড</label><label class="en" style="display:none;">Brand</label>
    <select name="brand_id" required>
        <option value="">-- নির্বাচন করুন --</option>
        @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" {{ (int) old('brand_id', $model->brand_id) === $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
        @endforeach
    </select>
    @error('brand_id') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">মডেলের নাম</label><label class="en" style="display:none;">Model Name</label>
    <input type="text" name="name" value="{{ old('name', $model->name) }}" placeholder="যেমন গ্যালাক্সি এস২৪" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>
