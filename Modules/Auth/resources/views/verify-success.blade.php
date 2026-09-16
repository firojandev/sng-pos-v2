<x-core::auth-layout
    title="ইমেইল ভেরিফিকেশন সফল"
    title-en="Email Verification Successful"
    card-title="SNG Pos"
    card-title-en="SNG Pos"
    cardSubtitle=""
    cardSubtitleEn=""
    max-width="500px"
>
    <div style="text-align: center; margin: 50px 20px;">
        <div style="width: 58px; height: 58px; margin: 0 auto 16px; border-radius: 50%; background: var(--green-100); color: var(--green-ink); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 8px rgba(34, 197, 94, 0.1);">
            <x-core::icon name="check-circle" size="30" />
        </div>
        <div style="font-size: 16px; font-weight: 700; color: var(--ink-900); line-height: 1.4;">
            <span class="bn">অভিনন্দন! আপনার ইমেইল সফলভাবে ভেরিফাই হয়েছে</span>
            <span class="en" style="display:none;">Congratulations! Your email has been verified</span>
        </div>
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
