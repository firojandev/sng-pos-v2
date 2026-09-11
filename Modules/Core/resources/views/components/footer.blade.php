@php
    $siteTitle = $siteTitle ?? \Modules\Core\Models\Setting::getSiteTitle();
    $siteTitleBn = $siteTitleBn ?? ($siteTitle === 'SNGPOS' ? 'এসএনজিপস' : $siteTitle);
@endphp
<footer class="app-footer">
    <div class="copy">
        <span class="bn">&copy; {{ now()->year }} <b>{{ $siteTitleBn }}</b> &middot; সর্বস্বত্ব সংরক্ষিত</span>
        <span class="en" style="display:none;">&copy; {{ now()->year }} <b>{{ $siteTitle }}</b> &middot; All
            rights reserved</span>
    </div>
    <div class="meta">
        <span class="ver">v2.0.0</span>
        <a href="#"><span class="bn">সহায়তা</span><span class="en" style="display:none;">Support</span></a>
        <a href="#"><span class="bn">গোপনীয়তা নীতি</span><span class="en" style="display:none;">Privacy
                Policy</span></a>
    </div>
</footer>
