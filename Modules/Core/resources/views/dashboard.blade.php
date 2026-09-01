<x-core::layout
    title="ড্যাশবোর্ড"
    title-en="Dashboard"
    subtitle="আজ, {{ now()->format('d F Y') }} — আপনার ব্যবসার সারসংক্ষেপ"
    subtitle-en="Today, {{ now()->format('d F Y') }} — your business at a glance"
    active="dashboard"
>
    @php
        $rangeLabels = [
            'today' => ['bn' => 'আজকের', 'en' => "Today's"],
            'week' => ['bn' => 'এই সপ্তাহের', 'en' => "This week's"],
            'month' => ['bn' => 'এই মাসের', 'en' => "This month's"],
            'year' => ['bn' => 'এই বছরের', 'en' => "This year's"],
            'all' => ['bn' => 'সর্বমোট', 'en' => 'All-time'],
        ];
        $rangeLabel = $rangeLabels[$range];
    @endphp

    <div class="section-row">
        <div class="mobile-toggle" style="margin-left:auto;">
            <span class="bn">মোবাইল ভিউ</span><span class="en" style="display:none;">Mobile View</span>
            <label class="switch">
                <input type="checkbox" id="mobile-preview-input" onchange="setMobilePreview(this.checked)">
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <div class="section-row">
        <div class="total-pill pill-green">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="2.5" y="6" width="19" height="13" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12.5" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
            <span class="bn">ব্যালেন্স: </span><span class="en" style="display:none;">Balance: </span>
            <b>৳{{ number_format($balance, 2) }}</b>
        </div>

        <div class="range-tabs" style="margin-left:auto;">
            @foreach ($rangeLabels as $key => $labels)
                <a href="{{ route('dashboard', ['range' => $key]) }}" class="{{ $range === $key ? 'active' : '' }}">
                    <span class="bn">{{ $labels['bn'] }}</span><span class="en" style="display:none;">{{ $labels['en'] }}</span>
                </a>
            @endforeach
        </div>

        <a href="{{ route('dashboard', ['range' => $range]) }}" class="btn btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 0 1 13.7-5.7L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 0 1-13.7 5.7L4 15.5M4 20v-4.5h4.5" stroke="#1C2B27" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="bn">রিফ্রেশ</span><span class="en">Refresh</span>
        </a>
    </div>

    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="ic" style="background:var(--teal-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 4h2l2.2 11.5a2 2 0 0 0 2 1.6h6.6a2 2 0 0 0 2-1.6L20 8H7" stroke="var(--teal-800)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="val">৳{{ number_format($saleTotal, 2) }}</div>
            <div class="lbl bn">{{ $rangeLabel['bn'] }} বিক্রি</div>
            <div class="lbl en" style="display:none;">{{ $rangeLabel['en'] }} Sale</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--blue-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 7h18l-1.5 10.5a2 2 0 0 1-2 1.5H6.5a2 2 0 0 1-2-1.5L3 7Z" stroke="#8A611B" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 7V5.5A3.5 3.5 0 0 1 11.5 2h1A3.5 3.5 0 0 1 16 5.5V7" stroke="#8A611B" stroke-width="1.7"/></svg>
            </div>
            <div class="val">৳{{ number_format($purchaseTotal, 2) }}</div>
            <div class="lbl bn">{{ $rangeLabel['bn'] }} ক্রয়</div>
            <div class="lbl en" style="display:none;">{{ $rangeLabel['en'] }} Purchase</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--gold-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 8l6 6 4-4 6 7" stroke="#8A611B" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 17h5v-5" stroke="#8A611B" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="val">৳{{ number_format($expenseTotal, 2) }}</div>
            <div class="lbl bn">{{ $rangeLabel['bn'] }} খরচ</div>
            <div class="lbl en" style="display:none;">{{ $rangeLabel['en'] }} Expense</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--green-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 8l9-5 9 5-9 5-9-5Z" stroke="var(--green-600)" stroke-width="1.6" stroke-linejoin="round"/><path d="M3 8v8l9 5 9-5V8" stroke="var(--green-600)" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </div>
            <div class="val">{{ number_format($totalStockQty, 2) }}</div>
            <div class="lbl bn">মোট মজুদ</div>
            <div class="lbl en" style="display:none;">Total Stock</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--green-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="18" height="14" rx="2" stroke="var(--green-600)" stroke-width="1.7"/><circle cx="11" cy="12" r="3" stroke="var(--green-600)" stroke-width="1.5"/></svg>
            </div>
            <div class="val">৳{{ number_format($totalReceivable, 2) }}</div>
            <div class="lbl bn">মোট পাবো</div>
            <div class="lbl en" style="display:none;">Total Receivable</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--red-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2.5" y="6" width="19" height="13" rx="2" stroke="var(--red-600)" stroke-width="1.7"/><circle cx="12" cy="12.5" r="3" stroke="var(--red-600)" stroke-width="1.6"/></svg>
            </div>
            <div class="val">৳{{ number_format($totalPayable, 2) }}</div>
            <div class="lbl bn">মোট দিবো</div>
            <div class="lbl en" style="display:none;">Total Due</div>
        </div>
    </div>
</x-core::layout>
