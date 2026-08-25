@php
    $isEdit = $user->exists;
    $currentRole = $isEdit ? $user->roles->first()?->name : null;
@endphp

<div class="field">
    <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
    <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="ইউজারের নাম" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">ইমেইল</label><label class="en" style="display:none;">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="user@example.com" required>
    @error('email') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">পাসওয়ার্ড</label><label class="en" style="display:none;">Password</label>
        <input type="password" name="password" placeholder="{{ $isEdit ? '••••••••' : '' }}" {{ $isEdit ? '' : 'required' }}>
        @error('password') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">পাসওয়ার্ড নিশ্চিত করুন</label><label class="en" style="display:none;">Confirm Password</label>
        <input type="password" name="password_confirmation" placeholder="{{ $isEdit ? '••••••••' : '' }}">
    </div>
</div>

@if ($isEdit)
    <div class="helper">
        <span class="bn">পাসওয়ার্ড খালি রাখলে বর্তমান পাসওয়ার্ড অপরিবর্তিত থাকবে।</span>
        <span class="en" style="display:none;">Leave password blank to keep the current password unchanged.</span>
    </div>
@endif

<div class="field">
    <label class="bn">রোল</label><label class="en" style="display:none;">Role</label>
    <select name="role" required>
        <option value="">-- নির্বাচন করুন --</option>
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" {{ old('role', $currentRole) === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
    </select>
    @error('role') <div class="field-error">{{ $message }}</div> @enderror
    <div class="helper" style="margin-top:6px;">
        <span class="bn">কোনো উপযুক্ত রোল না পেলে আগে "রোল ও পারমিশন" ট্যাব থেকে একটি রোল তৈরি করুন।</span>
        <span class="en" style="display:none;">If no suitable role exists, create one first from the "Roles & Permissions" tab.</span>
    </div>
</div>
