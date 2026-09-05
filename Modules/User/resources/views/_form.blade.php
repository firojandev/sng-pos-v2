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

    <x-core::input
        name="email"
        type="email"
        label="ইমেইল অ্যাড্রেস"
        label-en="Email Address"
        value="{{ old('email', $user->email) }}"
        placeholder="user@example.com"
        placeholder-en="user@example.com"
        size="sm"
        :required="true"
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
