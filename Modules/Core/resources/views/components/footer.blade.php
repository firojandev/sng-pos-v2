@php
    $siteTitle = $siteTitle ?? \Modules\Core\Models\Setting::getSiteTitle();
    $siteTitleBn = $siteTitleBn ?? ($siteTitle === 'SNGPOS' ? 'এসএনজিপস' : $siteTitle);
    $showTermsAndPolicy = \Modules\Core\Models\Setting::isTermsAndPolicyEnabled();
    $showCreditText = \Modules\Core\Models\Setting::isCreditTextEnabled();
    $creditText = $showCreditText ? \Modules\Core\Models\Setting::getCreditText() : '';
@endphp
<footer class="app-footer">
    <div class="copy">
        <span class="bn">&copy; {{ now()->year }} <b>{{ $siteTitleBn }}</b> &middot; সর্বস্বত্ব সংরক্ষিত</span>
        <span class="en" style="display:none;">&copy; {{ now()->year }} <b>{{ $siteTitle }}</b> &middot; All
            rights reserved</span>
    </div>
    <div class="meta">
        <span class="ver">v1.0.0</span>
        <a href="{{ route('home') }}#faq"><span class="bn">সহায়তা</span><span class="en" style="display:none;">Support</span></a>
        @if ($showTermsAndPolicy)
            <a href="{{ route('privacy-policy') }}"><span class="bn">গোপনীয়তা নীতি</span><span class="en" style="display:none;">Privacy Policy</span></a>
            <a href="{{ route('terms') }}"><span class="bn">ব্যবহারের শর্তাবলী</span><span class="en" style="display:none;">Terms & Conditions</span></a>
        @endif
    </div>
    @if ($showCreditText && !empty($creditText))
        <div class="credit-text" style="text-align:right; font-size:11px; color:var(--ink-400); margin-top:4px; font-weight:400; opacity:0.8;">
            {{ $creditText }}
        </div>
    @endif
</footer>
