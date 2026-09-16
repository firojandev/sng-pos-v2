<x-core::auth-layout
    title="ভেরিফিকেশন লিংক মেয়াদোত্তীর্ণ"
    title-en="Verification Link Expired"
    card-title="SNG Pos"
    card-title-en="SNG Pos"
    cardSubtitle=""
    cardSubtitleEn=""
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
                <span class="bn">এই ভেরিফিকেশন লিংকটি ইতিমধ্যে ব্যবহার করা হয়েছে। নিরাপত্তার জন্য প্রতিটি লিংক <strong>শুধুমাত্র একবার ব্যবহার করা যায়</strong>।</span>
                <span class="en" style="display:none;">This verification link has already been used. For security, each link can be <strong>used only once</strong>.</span>
            </p>
            <p style="margin: 0; font-size: 12.5px; color: var(--ink-600);">
                <span class="bn">আপনার ইমেইল ইতিমধ্যে ভেরিফাই করা হয়ে থাকলে, অনুগ্রহ করে লগইন করুন।</span>
                <span class="en" style="display:none;">If your email has already been verified, please sign in to your account.</span>
            </p>

        @elseif (isset($reason) && $reason === 'expired_signature')
            <p style="margin: 0 0 8px;">
                <span class="bn">এই ভেরিফিকেশন লিংকটির মেয়াদ শেষ হয়ে গেছে। নিরাপত্তার জন্য লিংকটি <strong>৬০ মিনিটের জন্য বৈধ</strong> ছিল।</span>
                <span class="en" style="display:none;">This verification link has expired. For security, the link was <strong>valid for 60 minutes</strong>.</span>
            </p>
            <p style="margin: 0; font-size: 12.5px; color: var(--ink-600);">
                <span class="bn">অনুগ্রহ করে লগইন পেজ থেকে একটি নতুন ভেরিফিকেশন লিংক অনুরোধ করুন।</span>
                <span class="en" style="display:none;">Please go to the login page and request a new verification link.</span>
            </p>

        @else
            <p style="margin: 0 0 8px;">
                <span class="bn">এই ভেরিফিকেশন লিংকটি আর ব্যবহার করা যাচ্ছে না। এটি হয়তো মেয়াদ শেষ হয়ে গেছে অথবা ইতিমধ্যে ব্যবহার করা হয়েছে।</span>
                <span class="en" style="display:none;">This verification link is no longer available. It may have expired or already been used.</span>
            </p>
            <p style="margin: 0; font-size: 12.5px; color: var(--ink-600);">
                <span class="bn">আপনার ইমেইল ইতিমধ্যে ভেরিফাই করা থাকলে লগইন করুন। অন্যথায়, লগইন পেজ থেকে একটি নতুন ভেরিফিকেশন লিংক অনুরোধ করুন।</span>
                <span class="en" style="display:none;">If your email is already verified, please sign in. Otherwise, go to the login page and request a new verification link.</span>
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
            <span class="bn">লগইন পেজে যান</span>
            <span class="en" style="display:none;">Goto Login Page</span>
        </x-core::button>
    </div>
</x-core::auth-layout>
