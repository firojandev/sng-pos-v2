<div class="field-row">
    <div class="field" style="margin-top:0;">
        <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
        <input type="text" name="name" value="{{ old('name', $customer->name) }}" placeholder="গ্রাহকের নাম" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field" style="margin-top:0;">
        <label class="bn">ফোন</label><label class="en" style="display:none;">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="01xxxxxxxxx">
        @error('phone') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">ইমেইল</label><label class="en" style="display:none;">Email</label>
    <input type="email" name="email" value="{{ old('email', $customer->email) }}" placeholder="customer@example.com">
    @error('email') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
    <textarea name="address" placeholder="ঠিকানা লিখুন">{{ old('address', $customer->address) }}</textarea>
</div>

<div class="field">
    <label class="bn">প্রারম্ভিক বাকি (৳)</label><label class="en" style="display:none;">Opening Due (৳)</label>
    <input type="number" step="0.01" min="0" name="opening_due" value="{{ old('opening_due', $customer->opening_due ?? 0) }}" placeholder="0">
    @error('opening_due') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
    <x-core::status-toggle
        name="status"
        :value="old('status', $customer->status ?? 'active')"
    />
</div>
