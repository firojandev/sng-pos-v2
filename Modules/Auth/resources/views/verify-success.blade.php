<x-core::auth-layout
    title="ইমেইল ভেরিফিকেশন সফল"
    title-en="Email Verification Successful"
    card-title="ইমেইল ভেরিফিকেশন সম্পন্ন"
    card-title-en="Email Verified Successfully"
    card-subtitle="আপনার অ্যাকাউন্ট ও দোকান সফলভাবে সক্রিয় হয়েছে"
    card-subtitle-en="Your account and shop have been activated successfully"
    max-width="500px"
>
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 58px; height: 58px; margin: 0 auto 16px; border-radius: 50%; background: var(--green-100); color: var(--green-ink); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 8px rgba(34, 197, 94, 0.1);">
            <x-core::icon name="check-circle" size="30" />
        </div>
        <div style="font-size: 16px; font-weight: 700; color: var(--ink-900); line-height: 1.4;">
            <span class="bn">অভিনন্দন! আপনার ইমেইল সফলভাবে ভেরিফাই হয়েছে</span>
            <span class="en" style="display:none;">Congratulations! Your email has been verified</span>
        </div>
    </div>

    <div style="font-size: 13.5px; color: var(--ink-700); line-height: 1.65; margin-bottom: 22px; background: var(--paper); border: 1px solid var(--border); border-radius: 10px; padding: 16px 18px; text-align: center;">
        <p style="margin: 0 0 8px;">
            @if (!empty($email))
                <span class="bn">আপনার ইমেইল <strong style="color: var(--ink-900);">{{ $email }}</strong> সফলভাবে যাচাই করা হয়েছে। আপনার দোকান ও অ্যাকাউন্ট এখন সক্রিয়।</span>
                <span class="en" style="display:none;">Your email <strong style="color: var(--ink-900);">{{ $email }}</strong> has been verified. Your shop and account are now active.</span>
            @else
                <span class="bn">আপনার ইমেইল ঠিকানাটি সফলভাবে যাচাই করা হয়েছে। আপনার দোকান ও অ্যাকাউন্ট এখন সক্রিয়।</span>
                <span class="en" style="display:none;">Your email address has been verified. Your shop and account are now active.</span>
            @endif
        </p>
        <p style="margin: 0; font-size: 12.5px; color: var(--ink-600);">
            <span class="bn">নিরাপত্তার স্বার্থে এখন আপনার ইউজারনেম, ইমেইল বা ফোন এবং পাসওয়ার্ড দিয়ে লগইন করুন।</span>
            <span class="en" style="display:none;">For security, please sign in with your username, email or phone and password.</span>
        </p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 10px;">
        <x-core::button
            href="{{ route('login') }}"
            color="primary"
            size="sm"
            block
            icon="log-in"
            style="padding: 10px 16px; font-size: 13.5px;"
        >
            <span class="bn">লগইন করুন</span>
            <span class="en" style="display:none;">Sign In</span>
        </x-core::button>
    </div>

    <div style="margin-top: 20px; text-align: center; font-size: 12px; color: var(--ink-400);">
        <span class="bn">ভেরিফিকেশন লিংকটি একবার ব্যবহারযোগ্য এবং এর কার্যকারিতা শেষ হয়েছে।</span>
        <span class="en" style="display:none;">This verification link was single-use and is now expired.</span>
    </div>
</x-core::auth-layout>
