<div class="field" style="margin-top:0;">
    <label class="bn">ব্র্যান্ডের নাম</label><label class="en" style="display:none;">Brand Name</label>
    <input type="text" name="name" value="{{ old('name', $brand->name) }}" placeholder="যেমন স্যামসাং" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">বিবরণ</label><label class="en" style="display:none;">Description</label>
    <textarea name="description" placeholder="ঐচ্ছিক বিবরণ">{{ old('description', $brand->description) }}</textarea>
</div>
