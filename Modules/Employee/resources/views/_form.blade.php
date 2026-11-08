@php
    $userOptions = ['' => '-- কোনো অ্যাকাউন্ট সংযুক্ত নেই --'];
    if (isset($users)) {
        foreach ($users as $u) {
            $userOptions[$u->id] = $u->name . ' (' . $u->email . ')';
        }
    }
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::input
        name="employee_code"
        label="কর্মচারী কোড"
        label-en="Employee Code"
        value="{{ old('employee_code', $employee->employee_code) }}"
        placeholder="EMP-0001"
        placeholder-en="EMP-0001"
        size="sm"
        :required="$employee->exists"
    />

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
        @include('employee::partials.named-select', [
            'field' => 'designation', 'idPrefix' => 'employee', 'options' => $designationOptions, 'required' => true,
            'selectedId' => old('designation_id', $employee->designation_id), 'newValue' => old('designation', $employee->designation_id ? null : $employee->designation),
            'label' => 'পদবি', 'labelEn' => 'Designation', 'placeholder' => 'নতুন পদবির নাম', 'placeholderEn' => 'New designation name',
        ])

        @include('employee::partials.named-select', [
            'field' => 'department', 'idPrefix' => 'employee', 'options' => $departmentOptions, 'required' => false,
            'selectedId' => old('department_id', $employee->department_id), 'newValue' => old('department', $employee->department_id ? null : $employee->department),
            'label' => 'বিভাগ', 'labelEn' => 'Department', 'placeholder' => 'নতুন বিভাগের নাম', 'placeholderEn' => 'New department name',
        ])
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

    @if (! $employee->user_id)
        @include('employee::partials.login-account', ['idPrefix' => 'employee'])
    @endif

    <x-core::select
        name="user_id"
        label="সংযুক্ত ইউজার লগইন অ্যাকাউন্ট (ঐচ্ছিক)"
        label-en="Linked User Login Account (Optional)"
        size="sm"
        :options="$userOptions"
        :value="old('user_id', $employee->user_id)"
    />

    <p data-linked-user-note hidden style="font-size:12px; color:var(--ink-400); margin:-8px 0 0;">
        <span class="bn">নতুন লগইন অ্যাকাউন্টটি তৈরি হয়ে এখানে নিজে থেকেই সংযুক্ত হবে।</span>
        <span class="en" style="display:none;">The new login account is created and linked here automatically.</span>
    </p>

    <div>
        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
        <x-core::status-toggle
            name="status"
            :value="old('status', $employee->status ?? 'active')"
        />
    </div>
</div>

@include('employee::partials.form-scripts')
