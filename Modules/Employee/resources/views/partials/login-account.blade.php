{{-- "Also create a login": the role, and a password unless the employee has an email to send the set-password link to. --}}
@if ($canCreateLogin ?? false)
    @php
        $loginRoleSelectOptions = ['' => $loginRoleRequired ? '-- রোল নির্বাচন করুন (Select role) --' : '-- কোনো রোল নেই (No role) --'] + $loginRoleOptions;
    @endphp

    <div data-login-block style="border:1px solid var(--border); background:var(--paper); border-radius:10px; padding:12px;">
        <x-core::checkbox
            name="create_user"
            :id="$idPrefix.'_create_user'"
            size="sm"
            label="লগইন অ্যাকাউন্টও তৈরি করুন"
            label-en="Also create a login account"
        />

        <div data-login-fields hidden>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <x-core::select
                    name="user_role"
                    :id="$idPrefix.'_user_role'"
                    label="ইউজার রোল"
                    label-en="User Role"
                    size="sm"
                    :required="$loginRoleRequired"
                    :options="$loginRoleSelectOptions"
                    :value="old('user_role', '')"
                />

                <x-core::input
                    type="password"
                    name="user_password"
                    :id="$idPrefix.'_user_password'"
                    label="পাসওয়ার্ড"
                    label-en="Password"
                    placeholder="কমপক্ষে ৮ অক্ষর"
                    placeholder-en="At least 8 characters"
                    size="sm"
                    password-toggle
                    autocomplete="new-password"
                />
            </div>

            <p data-login-hint="invite" style="font-size:12px; color:var(--ink-600); margin:8px 0 0;">
                <span class="bn">পাসওয়ার্ড ফাঁকা রাখুন: কর্মচারীর ইমেইলে পাসওয়ার্ড সেট করার লিংক যাবে, সেখান থেকে তিনি নিজেই পাসওয়ার্ড দিয়ে লগইন করবেন।</span>
                <span class="en" style="display:none;">Leave the password blank: a set-password link is emailed to the employee, who chooses their own password and signs in.</span>
            </p>
            <p data-login-hint="password" style="font-size:12px; color:var(--ink-600); margin:8px 0 0;">
                <span class="bn">ইমেইল দেওয়া নেই, তাই পাসওয়ার্ড দিতে হবে। ইমেইল দিলে কর্মচারী নিজেই পাসওয়ার্ড সেট করতে পারবেন।</span>
                <span class="en" style="display:none;">No email is entered, so a password is required. With an email, the employee sets their own.</span>
            </p>
            <p style="font-size:12px; color:var(--ink-400); margin:4px 0 0;">
                <span class="bn">নাম, মোবাইল ও ইমেইল কর্মচারীর তথ্য থেকে নেওয়া হবে এবং নতুন অ্যাকাউন্টটি এই কর্মচারীর সাথে নিজে থেকেই সংযুক্ত হবে।</span>
                <span class="en" style="display:none;">The name, phone and email come from the employee, and the new account is linked to them automatically.</span>
            </p>
        </div>
    </div>
@endif
