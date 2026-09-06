<x-core::auth-layout
    title="লগইন"
    title-en="Login"
    card-title="মাস্টারপস-এ লগইন করুন"
    card-title-en="Sign in to MasterPOS"
    card-subtitle="আপনার হিসাব পরিচালনা করতে লগইন করুন"
    card-subtitle-en="Sign in to manage your business account"
>
    @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
        <div class="auth-error">
            <x-core::icon name="alert-triangle" size="14" />
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <x-core::input
            type="text"
            name="email"
            label="ইমেইল, ইউজারনেম বা ফোন"
            label-en="Email, Username or Phone"
            placeholder="user@example.com / username / 017xxxxxxxx"
            placeholder-en="Email, username or phone number"
            icon="user"
            :value="old('email', old('login'))"
            required
            autofocus
            no-margin
        />

        <x-core::input
            type="password"
            name="password"
            label="পাসওয়ার্ড বা পিন"
            label-en="Password or PIN"
            placeholder="••••••••"
            placeholder-en="••••••••"
            icon="lock"
            password-toggle
            required
        />

        <div style="font-size:11.5px; color:var(--ink-500); margin-top:6px; margin-bottom:4px; line-height:1.4;">
            <span class="bn">পাসওয়ার্ড, ৪-সংখ্যার ইউজার পিন অথবা ৬-সংখ্যার সাপোর্ট পিন দিয়ে লগইন করা যাবে।</span>
            <span class="en" style="display:none;">Sign in using your password, 4-digit user PIN, or 6-digit support PIN.</span>
        </div>

        <div class="auth-row">
            <x-core::checkbox
                name="remember"
                label="মনে রাখুন"
                label-en="Remember me"
            />
        </div>

        <x-core::button
            type="submit"
            color="primary"
            block
            size="md"
            style="margin-top: 20px; padding: 12px 0; font-size: 13.5px;"
        >
            <span class="bn">লগইন করুন</span>
            <span class="en">Sign In</span>
        </x-core::button>
    </form>
</x-core::auth-layout>

