<x-core::auth-layout
    title="পাসওয়ার্ড সেট করুন"
    title-en="Set Your Password"
    card-subtitle="আপনার অ্যাকাউন্টের জন্য একটি পাসওয়ার্ড বেছে নিন"
    card-subtitle-en="Choose a password for your account"
>
    @if ($errors->has('email') || $errors->has('token'))
        <div class="auth-error">
            <x-core::icon name="alert-triangle" size="14" />
            <span>{{ $errors->first('email') ?: $errors->first('token') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.setup.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-core::input
            type="email"
            name="email"
            label="ইমেইল"
            label-en="Email"
            icon="mail"
            :value="old('email', $email)"
            :error="false"
            readonly
            required
            no-margin
        />

        <x-core::input
            type="password"
            name="password"
            label="নতুন পাসওয়ার্ড"
            label-en="New Password"
            placeholder="কমপক্ষে ৮ অক্ষর"
            placeholder-en="At least 8 characters"
            icon="lock"
            password-toggle
            required
            autofocus
        />

        <x-core::input
            type="password"
            name="password_confirmation"
            label="পাসওয়ার্ড নিশ্চিত করুন"
            label-en="Confirm Password"
            placeholder="••••••••"
            placeholder-en="••••••••"
            icon="lock"
            password-toggle
            required
        />

        <x-core::button
            type="submit"
            color="primary"
            block
            size="md"
            style="margin-top: 20px; padding: 12px 0; font-size: 13.5px;"
        >
            <span class="bn">পাসওয়ার্ড সংরক্ষণ করুন</span>
            <span class="en">Save Password</span>
        </x-core::button>
    </form>
</x-core::auth-layout>
