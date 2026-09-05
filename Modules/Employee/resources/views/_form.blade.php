@php
    $userOptions = ['' => '-- কোনো অ্যাকাউন্ট সংযুক্ত নেই --'];
    if (isset($users)) {
        foreach ($users as $u) {
            $userOptions[$u->id] = $u->name . ' (' . $u->email . ')';
        }
    }
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <x-core::input
            name="name"
            label="পুরো নাম"
            label-en="Full Name"
            value="{{ old('name', $employee->name) }}"
            placeholder="কর্মচারীর পুরো নাম"
            placeholder-en="Full name"
            size="sm"
            :required="true"
        />

        <x-core::input
            name="phone"
            label="মোবাইল নম্বর"
            label-en="Phone Number"
            value="{{ old('phone', $employee->phone) }}"
            placeholder="01XXXXXXXXX"
            placeholder-en="01XXXXXXXXX"
            size="sm"
            :required="true"
        />
    </div>

    <x-core::input
        name="email"
        type="email"
        label="ইমেইল অ্যাড্রেস"
        label-en="Email Address"
        value="{{ old('email', $employee->email) }}"
        placeholder="employee@example.com"
        placeholder-en="employee@example.com"
        size="sm"
    />

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <x-core::input
            name="designation"
            label="পদবি"
            label-en="Designation"
            value="{{ old('designation', $employee->designation) }}"
            placeholder="যেমন: বিক্রয়কর্মী / ম্যানেজার"
            placeholder-en="e.g. Sales / Manager"
            size="sm"
            :required="true"
        />

        <x-core::input
            name="department"
            label="বিভাগ"
            label-en="Department"
            value="{{ old('department', $employee->department) }}"
            placeholder="যেমন: বিক্রয় / হিসাব"
            placeholder-en="e.g. Sales / Accounts"
            size="sm"
        />
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <x-core::input
            name="salary"
            type="number"
            step="0.01"
            min="0"
            label="মাসিক বেতন (৳)"
            label-en="Monthly Salary (৳)"
            value="{{ old('salary', $employee->salary) }}"
            placeholder="0.00"
            prefix="৳"
            size="sm"
            :required="true"
        />

        <x-core::input
            name="joining_date"
            type="date"
            label="যোগদানের তারিখ"
            label-en="Joining Date"
            value="{{ old('joining_date', optional($employee->joining_date)->format('Y-m-d')) }}"
            size="sm"
        />
    </div>

    <x-core::textarea
        name="address"
        label="ঠিকানা"
        label-en="Address"
        placeholder="কর্মচারীর বর্তমান ঠিকানা"
        placeholder-en="Employee address"
        rows="2"
        size="sm"
        value="{{ old('address', $employee->address) }}"
    />

    <x-core::select
        name="user_id"
        label="সংযুক্ত ইউজার লগইন অ্যাকাউন্ট (ঐচ্ছিক)"
        label-en="Linked User Login Account (Optional)"
        size="sm"
        :options="$userOptions"
        :value="old('user_id', $employee->user_id)"
    />

    <div>
        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
        <x-core::status-toggle
            name="status"
            :value="old('status', $employee->status ?? 'active')"
        />
    </div>
</div>
