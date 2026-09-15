<x-core::auth-layout
    title="ভেরিফিকেশন লিংক মেয়াদোত্তীর্ণ"
    title-en="Verification Link Expired"
    card-title="ভেরিফিকেশন লিংক আর কার্যকর নয়"
    card-title-en="Verification Link Expired"
    card-subtitle="এই লিংকটি ইতিমধ্যে ব্যবহার করা হয়েছে অথবা এর মেয়াদ শেষ"
    card-subtitle-en="This link has already been used or has expired"
    max-width="500px"
>
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 58px; height: 58px; margin: 0 auto 16px; border-radius: 50%; background: var(--gold-100); color: var(--gold-ink); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 8px rgba(234, 179, 8, 0.1);">
            <x-core::icon name="alert-triangle" size="30" />
        </div>
        <div style="font-size: 16px; font-weight: 700; color: var(--ink-900); line-height: 1.4;">
            <span class="bn">লিংকটির মেয়াদ শেষ হয়ে গেছে</span>
            <span class="en" style="display:none;">This Link Has Expired</span>
        </div>
    </div>

    <div style="font-size: 13.5px; color: var(--ink-700); line-height: 1.65; margin-bottom: 22px; background: var(--paper); border: 1px solid var(--border); border-radius: 10px; padding: 16px 18px; text-align: center;">
        @if (isset($reason) && $reason === 'already_used')
            <p style="margin: 0 0 8px;">
                <span class="bn">এই ভেরিফিকেশন লিংকটি ইতিমধ্যে ব্যবহার করা হয়েছে। প্রতিটি লিংক শুধুমাত্র <strong>একবার ব্যবহারযোগ্য</strong>।</span>
                <span class="en" style="display:none;">This verification link has already been used. Each link is <strong>single-use only</strong>.</span>
            </p>
            <p style="margin: 0; font-size: 12.5px; color: var(--ink-600);">
                <span class="bn">আপনার ইমেইল ইতিমধ্যে সফলভাবে ভেরিফাই করা থাকলে, অনুগ্রহ করে সরাসরি লগইন করুন।</span>
                <span class="en" style="display:none;">If your email has already been verified, please sign in directly.</span>
            </p>
        @elseif (isset($reason) && $reason === 'expired_signature')
            <p style="margin: 0 0 8px;">
                <span class="bn">নিরাপত্তার স্বার্থে ভেরিফিকেশন লিংকটির নির্ধারিত সময়সীমা (৬০ মিনিট) পার হয়ে গেছে।</span>
                <span class="en" style="display:none;">For security reasons, this verification link has exceeded its 60-minute time limit.</span>
            </p>
            <p style="margin: 0; font-size: 12.5px; color: var(--ink-600);">
                <span class="bn">অনুগ্রহ করে লগইন পেজে গিয়ে পুনরায় নতুন ভেরিফিকেশন লিংক অনুরোধ করুন অথবা অ্যাকাউন্ট সক্রিয় থাকলে লগইন করুন।</span>
                <span class="en" style="display:none;">Please go to the login page to request a new verification link, or log in if your account is active.</span>
            </p>
        @else
            <p style="margin: 0 0 8px;">
                <span class="bn">এই ভেরিফিকেশন লিংকটির মেয়াদ শেষ হয়ে গেছে অথবা এটি ইতিমধ্যে ব্যবহার করা হয়েছে।</span>
                <span class="en" style="display:none;">This verification link has expired or has already been used.</span>
            </p>
            <p style="margin: 0; font-size: 12.5px; color: var(--ink-600);">
                <span class="bn">আপনার ইমেইল যাচাই করা থাকলে লগইন করুন, অথবা নতুন লিংকের জন্য লগইন পাতায় যান।</span>
                <span class="en" style="display:none;">If your email is verified, please log in, or visit the login page for a new link.</span>
            </p>
        @endif
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

    @if (\Modules\Core\Models\Setting::isRegistrationEnabled())
        <div style="margin-top: 18px; text-align: center; font-size: 12.5px; color: var(--ink-600);">
            <span class="bn">নতুন দোকান খুলতে চান?</span>
            <span class="en" style="display:none;">Want to create a new shop?</span>
            <a href="{{ route('register') }}" style="color: var(--teal-800); font-weight: 700; text-decoration: none; margin-left: 4px;">
                <span class="bn">রেজিস্ট্রেশন করুন</span>
                <span class="en" style="display:none;">Register</span>
            </a>
        </div>
    @endif
</x-core::auth-layout>
