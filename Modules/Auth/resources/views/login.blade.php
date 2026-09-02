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
            type="email"
            name="email"
            label="ইমেইল"
            label-en="Email"
            placeholder="you@example.com"
            placeholder-en="you@example.com"
            icon="mail"
            required
            autofocus
            no-margin
        />

        <x-core::input
            type="password"
            name="password"
            label="পাসওয়ার্ড"
            label-en="Password"
            placeholder="••••••••"
            placeholder-en="••••••••"
            icon="lock"
            password-toggle
            required
        />

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

