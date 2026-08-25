<div class="field-row">
    <div class="field" style="margin-top:0;">
        <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" placeholder="সরবরাহকারীর নাম" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field" style="margin-top:0;">
        <label class="bn">ফোন</label><label class="en" style="display:none;">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" placeholder="01xxxxxxxxx">
        @error('phone') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">ইমেইল</label><label class="en" style="display:none;">Email</label>
    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" placeholder="supplier@example.com">
    @error('email') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
    <textarea name="address" placeholder="ঠিকানা লিখুন">{{ old('address', $supplier->address) }}</textarea>
</div>

<div class="field">
    <label class="bn">প্রারম্ভিক বাকি (৳)</label><label class="en" style="display:none;">Opening Due (৳)</label>
    <input type="number" step="0.01" min="0" name="opening_due" value="{{ old('opening_due', $supplier->opening_due ?? 0) }}" placeholder="0">
    @error('opening_due') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
    <div class="seg">
        <button type="button" class="status-btn {{ old('status', $supplier->status ?? 'active') === 'active' ? 'active' : '' }}" data-val="active" onclick="selSupplierStatus(this)">
            <span class="bn">সক্রিয়</span><span class="en">Active</span>
        </button>
        <button type="button" class="status-btn {{ old('status', $supplier->status ?? 'active') === 'inactive' ? 'active' : '' }}" data-val="inactive" onclick="selSupplierStatus(this)">
            <span class="bn">নিষ্ক্রিয়</span><span class="en">Inactive</span>
        </button>
    </div>
    <input type="hidden" name="status" id="status-input" value="{{ old('status', $supplier->status ?? 'active') }}">
</div>

<script>
    function selSupplierStatus(btn) {
        btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('status-input').value = btn.getAttribute('data-val');
    }
</script>
