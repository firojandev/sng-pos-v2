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
            label="ইউজারনেম"
            label-en="Username"
            placeholder="user@example.com/username/017xxxxxxxx"
            placeholder-en="Email/username/phone number"
            icon="user"
            :value="old('email', old('login'))"
            required
            autofocus
            no-margin
        />

        <x-core::input
            type="password"
            name="password"
            label="পাসওয়ার্ড/পিন"
            label-en="Password/PIN"
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

    @if (\Modules\Core\Models\Setting::isRegistrationEnabled())
        <div style="margin-top: 20px; text-align: center; font-size: 12.5px; color: var(--ink-600);">
            <span class="bn">নতুন দোকান চালু করতে চান?</span>
            <span class="en" style="display:none;">Want to open a new shop?</span>
            <a href="{{ route('register') }}" style="color: var(--teal-800); font-weight: 700; text-decoration: none; margin-left: 4px;">
                <span class="bn">ফ্রি অ্যাকাউন্ট তৈরি করুন</span>
                <span class="en" style="display:none;">Create Free Account</span>
            </a>
        </div>
    @endif
</x-core::auth-layout>

