<div class="field-row">
    <div class="field" style="margin-top:0;">
        <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
        <input type="text" name="name" value="{{ old('name', $employee->name) }}" placeholder="কর্মচারীর নাম" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field" style="margin-top:0;">
        <label class="bn">ফোন</label><label class="en" style="display:none;">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="01xxxxxxxxx" required>
        @error('phone') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">ইমেইল</label><label class="en" style="display:none;">Email</label>
    <input type="email" name="email" value="{{ old('email', $employee->email) }}" placeholder="employee@example.com">
    @error('email') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">পদবি</label><label class="en" style="display:none;">Designation</label>
        <input type="text" name="designation" value="{{ old('designation', $employee->designation) }}" placeholder="যেমন বিক্রয়কর্মী" required>
        @error('designation') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">বিভাগ</label><label class="en" style="display:none;">Department</label>
        <input type="text" name="department" value="{{ old('department', $employee->department) }}" placeholder="যেমন বিক্রয়">
    </div>
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">বেতন (৳)</label><label class="en" style="display:none;">Salary (৳)</label>
        <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $employee->salary) }}" placeholder="0" required>
        @error('salary') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">যোগদানের তারিখ</label><label class="en" style="display:none;">Joining Date</label>
        <input type="date" name="joining_date" value="{{ old('joining_date', optional($employee->joining_date)->format('Y-m-d')) }}">
    </div>
</div>

<div class="field">
    <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
    <textarea name="address" placeholder="ঠিকানা লিখুন">{{ old('address', $employee->address) }}</textarea>
</div>

<div class="field">
    <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
    <x-core::status-toggle
        name="status"
        :value="old('status', $employee->status ?? 'active')"
    />
</div>
