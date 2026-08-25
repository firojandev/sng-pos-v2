<div class="field" style="margin-top:0;">
    <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
    <input type="text" name="name" value="{{ old('name', $expenseCategory->name) }}" placeholder="যেমন দোকান ভাড়া" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">বিবরণ</label><label class="en" style="display:none;">Description</label>
    <textarea name="description" placeholder="ঐচ্ছিক বিবরণ">{{ old('description', $expenseCategory->description) }}</textarea>
</div>
