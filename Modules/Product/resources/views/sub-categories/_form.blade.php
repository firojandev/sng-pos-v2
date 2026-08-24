<div class="field" style="margin-top:0;">
    <label class="bn">মূল ক্যাটাগরি</label><label class="en" style="display:none;">Parent Category</label>
    <select name="category_id" required>
        <option value="">-- নির্বাচন করুন --</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" {{ (int) old('category_id', $subCategory->category_id) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
    <input type="text" name="name" value="{{ old('name', $subCategory->name) }}" placeholder="যেমন মোবাইল ফোন" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">বিবরণ</label><label class="en" style="display:none;">Description</label>
    <textarea name="description" placeholder="ঐচ্ছিক বিবরণ">{{ old('description', $subCategory->description) }}</textarea>
</div>
