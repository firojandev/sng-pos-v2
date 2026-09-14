<x-core::auth-layout
    title="ইমেইল ভেরিফিকেশন"
    title-en="Email Verification"
    card-title="ইমেইল ভেরিফাই করুন"
    card-title-en="Verify Your Email"
    card-subtitle="দোকান সক্রিয় করতে আপনার ইমেইল যাচাই করা আবশ্যক"
    card-subtitle-en="Please verify your email address to activate your shop"
    max-width="540px"
>
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; background: var(--teal-100, #ccfbf1); color: var(--teal-800, #0d9488); display: flex; align-items: center; justify-content: center;">
            <x-core::icon name="mail" size="28" />
        </div>
        <div style="font-size: 15px; font-weight: 700; color: var(--ink-900);">
            <span class="bn">ভেরিফিকেশন লিংক পাঠানো হয়েছে</span>
            <span class="en" style="display:none;">Verification Link Sent</span>
        </div>
    </div>

    @if (session('status'))
        <div style="background: var(--green-100, #dcfce7); border: 1px solid var(--green-ic-bg, #86efac); color: var(--green-ink, #15803d); padding: 12px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
            <x-core::icon name="check-circle" size="16" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('warning'))
        <div style="background: var(--gold-100, #fef3c7); border: 1px solid var(--gold-ink, #d97706); color: var(--gold-ink, #92400e); padding: 12px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
            <x-core::icon name="alert-triangle" size="16" />
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <div style="font-size: 13.5px; color: var(--ink-700); line-height: 1.6; margin-bottom: 20px; background: var(--paper); border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px;">
        <p style="margin: 0 0 8px;">
            <span class="bn">আপনার দোকানে প্রবেশ ও ড্যাশবোর্ড ব্যবহারের পূর্বে ইমেইল ঠিকানাটি ভেরিফাই করা প্রয়োজন।</span>
            <span class="en" style="display:none;">You must verify your email address before accessing your shop and dashboard.</span>
        </p>
        <p style="margin: 0;">
            <span class="bn">আমরা আপনার নিবন্ধিত ইমেইল <strong>{{ $email ?? (auth()->user()?->email ?? 'আপনার ইমেইল') }}</strong> ঠিকানায় একটি ভেরিফিকেশন লিংক পাঠিয়েছি। অনুগ্রহ করে আপনার ইনবক্স চেক করুন এবং লিংকে ক্লিক করুন।</span>
            <span class="en" style="display:none;">We sent a verification link to <strong>{{ $email ?? (auth()->user()?->email ?? 'your email') }}</strong>. Please check your inbox and click the link.</span>
        </p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 22px;">
        <form method="POST" action="{{ route('verification.send') }}" style="margin: 0;">
            @csrf
            <x-core::button
                type="submit"
                color="primary"
                size="sm"
                block
                icon="send"
            >
                <span class="bn">পুনরায় ভেরিফিকেশন ইমেইল পাঠান</span>
                <span class="en" style="display:none;">Resend Verification Email</span>
            </x-core::button>
        </form>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; padding-top: 14px; border-top: 1px solid var(--border);">
            <a href="{{ route('dashboard') }}" style="font-size: 12.5px; color: var(--teal-800); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                <x-core::icon name="refresh-cw" size="13" />
                <span class="bn">ভেরিফাই করেছি, এগিয়ে যান</span>
                <span class="en" style="display:none;">I have verified, proceed</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <x-core::button
                    type="submit"
                    variant="soft"
                    color="secondary"
                    size="sm"
                    icon="log-out"
                >
                    <span class="bn">লগআউট</span>
                    <span class="en" style="display:none;">Log Out</span>
                </x-core::button>
            </form>
        </div>
    </div>
</x-core::auth-layout>
