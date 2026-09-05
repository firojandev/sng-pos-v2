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

        <x-core::button href="{{ route('dashboard', ['range' => $range]) }}" variant="outline" size="sm" icon="refresh-cw">
            <span class="bn">রিফ্রেশ</span><span class="en" style="display:none;">Refresh</span>
        </x-core::button>
    </div>

    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <x-core::stat-card
            icon="shopping-cart"
            color="teal"
            :value="'৳' . number_format($saleTotal, 2)"
            :label="$rangeLabel['bn'] . ' বিক্রি'"
            :label-en="$rangeLabel['en'] . ' Sale'"
        />
        <x-core::stat-card
            icon="shopping-bag"
            color="blue"
            :value="'৳' . number_format($purchaseTotal, 2)"
            :label="$rangeLabel['bn'] . ' ক্রয়'"
            :label-en="$rangeLabel['en'] . ' Purchase'"
        />
        <x-core::stat-card
            icon="trending-up"
            color="gold"
            :value="'৳' . number_format($expenseTotal, 2)"
            :label="$rangeLabel['bn'] . ' খরচ'"
            :label-en="$rangeLabel['en'] . ' Expense'"
        />
        <x-core::stat-card
            icon="package"
            color="green"
            :value="number_format($totalStockQty, 2)"
            label="মোট মজুদ"
            label-en="Total Stock"
        />
        <x-core::stat-card
            icon="wallet"
            color="green"
            :value="'৳' . number_format($totalReceivable, 2)"
            label="মোট পাবো"
            label-en="Total Receivable"
        />
        <x-core::stat-card
            icon="credit-card"
            color="red"
            :value="'৳' . number_format($totalPayable, 2)"
            value-color="red"
            label="মোট দিবো"
            label-en="Total Due"
        />
    </div>
</x-core::layout>
