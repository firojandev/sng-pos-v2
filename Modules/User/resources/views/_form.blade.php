@php
    $isEdit = $user->exists;
    $currentRole = $isEdit ? $user->roles->first()?->name : null;

    $roleOptions = ['' => '-- রোল নির্বাচন করুন --'];
    foreach ($roles as $role) {
        $roleOptions[$role->name] = $role->name;
    }
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::input
        name="name"
        label="পুরো নাম"
        label-en="Full Name"
        value="{{ old('name', $user->name) }}"
        placeholder="ইউজারের নাম লিখুন"
        placeholder-en="Enter full name"
        size="sm"
        :required="true"
    />

    @php
        $isOwner = $isEdit && $user->isShopOwner();
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <x-core::input
            name="username"
            label="ইউজারনেম (ঐচ্ছিক)"
            label-en="Username (Optional)"
            value="{{ old('username', $user->username) }}"
            placeholder="যেমন: sakib101"
            placeholder-en="e.g. sakib101"
            size="sm"
        />

        @if ($isOwner)
            <x-core::input
                name="phone"
                type="text"
                label="ফোন নম্বর (মালিকের নম্বর অপরিবর্তনযোগ্য)"
                label-en="Phone Number (Locked for Shop Owner)"
                value="{{ $user->phone }}"
                size="sm"
                :readonly="true"
                helper="দোকানের মালিকের ফোন নম্বর পরিবর্তন করা যাবে না।"
                helper-en="Shop owner phone number cannot be modified."
            />
        @else
            <x-core::input
                name="phone"
                type="text"
                label="ফোন নম্বর (ঐচ্ছিক)"
                label-en="Phone Number (Optional)"
                value="{{ old('phone', $user->phone) }}"
                placeholder="যেমন: 017xxxxxxxx"
                placeholder-en="e.g. 017xxxxxxxx"
                size="sm"
            />
        @endif
    </div>

    <x-core::input
        name="email"
        type="email"
        label="ইমেইল অ্যাড্রেস (ঐচ্ছিক)"
        label-en="Email Address (Optional)"
        value="{{ old('email', $user->email) }}"
        placeholder="user@example.com"
        placeholder-en="user@example.com"
        size="sm"
    />

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <x-core::input
            name="password"
            type="password"
            label="{{ $isEdit ? 'নতুন পাসওয়ার্ড (ঐচ্ছিক)' : 'পাসওয়ার্ড' }}"
            label-en="{{ $isEdit ? 'New Password (Optional)' : 'Password' }}"
            placeholder="{{ $isEdit ? '••••••••' : 'কমপক্ষে ৮ অক্ষর' }}"
            size="sm"
            :required="!$isEdit"
        />

        <x-core::input
            name="password_confirmation"
            type="password"
            label="পাসওয়ার্ড নিশ্চিত করুন"
            label-en="Confirm Password"
            placeholder="{{ $isEdit ? '••••••••' : 'পুনরায় লিখুন' }}"
            size="sm"
        />
    </div>

    @if ($isEdit)
        <div style="font-size:11.5px; color:var(--ink-500); margin-top:-6px;">
            <span class="bn">পাসওয়ার্ড পরিবর্তন করতে না চাইলে ঘরটি খালি রাখুন।</span>
            <span class="en" style="display:none;">Leave password blank to keep the current password unchanged.</span>
        </div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:start;">
        <x-core::input
            name="pin"
            type="text"
            maxlength="4"
            label="{{ $isEdit ? 'নতুন ইউজার পিন (৪ সংখ্যা)' : 'ইউজার পিন (৪ সংখ্যা)' }}"
            label-en="{{ $isEdit ? 'New User PIN (4 Digits)' : 'User PIN (4 Digits)' }}"
            value="{{ old('pin') }}"
            placeholder="{{ $isEdit ? 'পরিবর্তন করতে চাইলে ৪ ডিজিট দিন' : 'যেমন: 1234' }}"
            placeholder-en="{{ $isEdit ? 'Enter 4 digits to change' : 'e.g. 1234' }}"
            size="sm"
        />

        @if ($isEdit)
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:6px 12px; margin-top:20px;">
                <label class="form-label" style="font-size:11px; font-weight:700; color:var(--gold-ink); margin-bottom:2px; display:flex; align-items:center; gap:4px;">
                    <x-core::icon name="headphones" size="xs" />
                    <span class="bn">বর্তমান সাপোর্ট পিন (৬ ডিজিট)</span>
                    <span class="en" style="display:none;">Support PIN (6 Digits)</span>
                </label>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:13px; color:var(--ink-900); letter-spacing:1px;">
                        {{ $user->support_pin ?: '—' }}
                    </span>
                    <x-core::checkbox
                        name="regenerate_support_pin"
                        value="1"
                        size="sm"
                        label="নতুন কোড"
                        label-en="Regenerate"
                    />
                </div>
            </div>
        @else
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:8px 12px; margin-top:20px;">
                <div style="font-size:11px; font-weight:700; color:var(--gold-ink); display:flex; align-items:center; gap:5px;">
                    <x-core::icon name="headphones" size="xs" />
                    <span class="bn">সাপোর্ট পিন (৬ ডিজিট)</span>
                    <span class="en" style="display:none;">Support PIN (6 Digits)</span>
                </div>
                <div style="font-size:10.5px; color:var(--ink-500); margin-top:2px; line-height:1.3;">
                    <span class="bn">ইউজার তৈরিতে স্বয়ংক্রিয় ৬-ডিজিট সাপোর্ট কোড তৈরি হবে।</span>
                    <span class="en" style="display:none;">Auto 6-digit support code will be generated upon creation.</span>
                </div>
            </div>
        @endif
    </div>

    <x-core::select
        name="role"
        label="অ্যাক্সেস রোল"
        label-en="Access Role"
        size="sm"
        :required="true"
        :options="$roleOptions"
        :value="old('role', $currentRole)"
    />

    <div style="font-size:11.5px; color:var(--ink-500); background:var(--paper-line); padding:8px 12px; border-radius:8px; border:1px solid var(--border);">
        <span class="bn">কোনো উপযুক্ত রোল না পেলে আগে <strong>"রোল ও পারমিশন"</strong> ট্যাব থেকে একটি রোল তৈরি করুন।</span>
        <span class="en" style="display:none;">If no suitable role exists, create one first from the <strong>"Roles & Permissions"</strong> tab.</span>
    </div>
</div>
