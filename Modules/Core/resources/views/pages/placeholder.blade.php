<x-core::layout
    :title="$title"
    :title-en="$titleEn"
    :subtitle="$subtitle"
    :subtitle-en="$subtitleEn"
    :active="$active"
>
    <div class="panel" style="margin-top:0;">
        <div class="panel-body" style="text-align:center; padding:60px 20px;">
            <div style="width:56px; height:56px; border-radius:14px; background:var(--teal-100); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M12 3l9 16H3L12 3Z" stroke="var(--teal-800)" stroke-width="1.7" stroke-linejoin="round"/></svg>
            </div>
            <div class="panel-title bn">এই পাতাটি শীঘ্রই আসছে</div>
            <div class="panel-title en" style="display:none;">This page is coming soon</div>
            <p class="bn" style="color:var(--ink-600); font-size:12.5px; margin-top:8px;">{{ $subtitle }} — কার্যকারিতা পরবর্তী ধাপে যুক্ত করা হবে।</p>
            <p class="en" style="display:none; color:var(--ink-600); font-size:12.5px; margin-top:8px;">{{ $subtitleEn }} — functionality will be wired up in the next step.</p>
        </div>
    </div>
</x-core::layout>
