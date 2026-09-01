<div class="field" style="margin-top:0;">
    <label class="bn">শাখার নাম</label><label class="en" style="display:none;">Branch Name</label>
    <input type="text" name="name" value="{{ old('name', $branch->name) }}" placeholder="যেমন রাজশাহী শাখা" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">মোবাইল</label><label class="en" style="display:none;">Phone</label>
    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" placeholder="+8801XXXXXXXXX">
</div>

<div class="field">
    <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
    <textarea name="address" placeholder="শাখার ঠিকানা">{{ old('address', $branch->address) }}</textarea>
</div>

<div class="field">
    <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
    <div class="seg">
        <button type="button" class="{{ old('status', $branch->status ?? 'active') === 'active' ? 'active' : '' }}" onclick="setSegValue(this, 'branch-status', 'active')">
            <span class="bn">সক্রিয়</span><span class="en">Active</span>
        </button>
        <button type="button" class="{{ old('status', $branch->status ?? 'active') === 'inactive' ? 'active' : '' }}" onclick="setSegValue(this, 'branch-status', 'inactive')">
            <span class="bn">নিষ্ক্রিয়</span><span class="en">Inactive</span>
        </button>
    </div>
    <input type="hidden" name="status" id="branch-status" value="{{ old('status', $branch->status ?? 'active') }}">
</div>

<script>
function setSegValue(btn, inputId, value) {
    document.getElementById(inputId).value = value;
    btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
}
</script>
