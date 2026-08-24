<div class="field" style="margin-top:0;">
    <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
    <input type="text" name="name" value="{{ old('name', $category->name) }}" placeholder="যেমন ইলেকট্রনিক্স" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">বিবরণ</label><label class="en" style="display:none;">Description</label>
    <textarea name="description" placeholder="ঐচ্ছিক বিবরণ">{{ old('description', $category->description) }}</textarea>
</div>
