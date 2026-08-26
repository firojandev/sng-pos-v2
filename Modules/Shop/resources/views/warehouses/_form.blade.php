<div class="field" style="margin-top:0;">
    <label class="bn">শাখা</label><label class="en" style="display:none;">Branch</label>
    <select name="branch_id" required>
        <option value="">-- নির্বাচন করুন --</option>
        @foreach ($branches as $branchOption)
            <option value="{{ $branchOption->id }}" {{ (string) old('branch_id', $warehouse->branch_id) === (string) $branchOption->id ? 'selected' : '' }}>{{ $branchOption->name }}</option>
        @endforeach
    </select>
    @error('branch_id') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">গুদামের নাম</label><label class="en" style="display:none;">Warehouse Name</label>
    <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" placeholder="যেমন প্রধান গুদাম" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
    <textarea name="address" placeholder="গুদামের ঠিকানা">{{ old('address', $warehouse->address) }}</textarea>
</div>

<div class="field">
    <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
    <div class="seg">
        <button type="button" class="{{ old('status', $warehouse->status ?? 'active') === 'active' ? 'active' : '' }}" onclick="setSegValue(this, 'warehouse-status', 'active')">
            <span class="bn">সক্রিয়</span><span class="en">Active</span>
        </button>
        <button type="button" class="{{ old('status', $warehouse->status ?? 'active') === 'inactive' ? 'active' : '' }}" onclick="setSegValue(this, 'warehouse-status', 'inactive')">
            <span class="bn">নিষ্ক্রিয়</span><span class="en">Inactive</span>
        </button>
    </div>
    <input type="hidden" name="status" id="warehouse-status" value="{{ old('status', $warehouse->status ?? 'active') }}">
</div>

<script>
function setSegValue(btn, inputId, value) {
    document.getElementById(inputId).value = value;
    btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
}
</script>
