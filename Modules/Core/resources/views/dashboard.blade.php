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

    @if (collect([
    $canViewSales,
    $canViewPurchase,
    $canViewExpense,
    $canViewProductProfit,
    $canViewTotalProfit,
    $canViewStockValue,
    $canViewStockQty,
    $canViewReceivable,
    $canViewPayable,
    $canViewCash,
    $canViewBank,
    $canViewMfs,
])->contains(true))
    <div class="section-row">
        @if ($canViewBalance)
            <div class="total-pill {{ $balance < 0 ? 'pill-red' : 'pill-green' }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="2.5" y="6" width="19" height="13" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12.5" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
                <span class="bn">মোট ব্যালেন্স: </span><span class="en" style="display:none;">Total Balance: </span>
                <b>{{ $balance < 0 ? '-৳' . number_format(abs($balance), 2) : '৳' . number_format($balance, 2) }}</b>
            </div>
        @endif

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
    @endif

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
        @if ($canViewSales)
            <x-core::stat-card
                icon="shopping-cart"
                color="teal"
                :value="'৳' . number_format($saleTotal, 2)"
                :label="$rangeLabel['bn'] . ' বিক্রি'"
                :label-en="$rangeLabel['en'] . ' Sale'"
                subtext="মোট বিক্রির পরিমাণ"
                subtext-en="Total sales volume"
            />
        @endif
        @if ($canViewPurchase)
            <x-core::stat-card
                icon="shopping-bag"
                color="blue"
                :value="'৳' . number_format($purchaseTotal, 2)"
                :label="$rangeLabel['bn'] . ' ক্রয়'"
                :label-en="$rangeLabel['en'] . ' Purchase'"
                subtext="মোট ক্রয়ের পরিমাণ"
                subtext-en="Total purchases"
            />
        @endif
        @if ($canViewExpense)
            <x-core::stat-card
                icon="trending-up"
                color="gold"
                :value="'৳' . number_format($expenseTotal, 2)"
                :label="$rangeLabel['bn'] . ' খরচ'"
                :label-en="$rangeLabel['en'] . ' Expense'"
                subtext="দোকানের মোট খরচ"
                subtext-en="Total shop expenses"
            />
        @endif

        @if ($canViewProductProfit)
            <x-core::stat-card
                icon="tag"
                color="teal"
                :value="($productProfit < 0 ? '-৳' : '৳') . number_format(abs($productProfit), 2)"
                :value-color="$productProfit < 0 ? 'red' : 'teal'"
                :label="$rangeLabel['bn'] . ' পণ্য লাভ'"
                :label-en="$rangeLabel['en'] . ' Product Profit'"
                subtext="পণ্য বিক্রি হতে মোট লাভ"
                subtext-en="Gross profit from sales"
            />
        @endif
        @if ($canViewTotalProfit)
            <x-core::stat-card
                icon="coins"
                :color="$totalProfit < 0 ? 'red' : 'green'"
                :value="($totalProfit < 0 ? '-৳' : '৳') . number_format(abs($totalProfit), 2)"
                :value-color="$totalProfit < 0 ? 'red' : 'green'"
                :label="$rangeLabel['bn'] . ' মোট লাভ'"
                :label-en="$rangeLabel['en'] . ' Total Profit'"
                subtext="খরচ বাদে প্রকৃত নিট লাভ"
                subtext-en="Net profit after expenses"
            />
        @endif
        @if ($canViewStockValue)
            <x-core::stat-card
                icon="banknote"
                color="gold"
                :value="'৳' . number_format($totalStockValue, 2)"
                label="মোট মজুদ মূল্য"
                label-en="Stock Valuation"
                subtext="ক্রয়মূল্য অনুসারে মজুদ"
                subtext-en="At purchase cost"
            />
        @endif

        @if ($canViewStockQty)
            <x-core::stat-card
                icon="package"
                color="blue"
                :value="number_format($totalStockQty, 2)"
                label="মোট মজুদ"
                label-en="Total Stock"
                subtext="সকল পণ্যের মজুদ একক"
                subtext-en="Across all batches"
            />
        @endif
        @if ($canViewReceivable)
            <x-core::stat-card
                icon="wallet"
                color="green"
                :value="'৳' . number_format($totalReceivable, 2)"
                value-color="green"
                label="মোট পাবো"
                label-en="Total Receivable"
                subtext="গ্রাহকের কাছে বাকি পাওনা"
                subtext-en="Customer outstanding due"
            />
        @endif
        @if ($canViewPayable)
            <x-core::stat-card
                icon="credit-card"
                color="red"
                :value="'৳' . number_format($totalPayable, 2)"
                value-color="red"
                label="মোট দিবো"
                label-en="Total Due"
                subtext="সরবরাহকারীর পরিশোধ্য দেনা"
                subtext-en="Supplier outstanding due"
            />
        @endif

        @if ($canViewCash)
            <x-core::stat-card
                icon="cash"
                color="green"
                :value="($totalCash < 0 ? '-৳' : '৳') . number_format(abs($totalCash), 2)"
                :value-color="$totalCash < 0 ? 'red' : 'green'"
                label="ক্যাশ ব্যালেন্স"
                label-en="Cash Balance"
                subtext="নগদ ক্যাশ বাক্স"
                subtext-en="Physical cash in hand"
            />
        @endif
        @if ($canViewBank)
            <x-core::stat-card
                icon="landmark"
                color="blue"
                :value="($totalBank < 0 ? '-৳' : '৳') . number_format(abs($totalBank), 2)"
                :value-color="$totalBank < 0 ? 'red' : 'blue'"
                label="ব্যাংক ব্যালেন্স"
                label-en="Bank Balance"
                subtext="সকল ব্যাংক অ্যাকাউন্ট"
                subtext-en="Across all bank accounts"
            />
        @endif
        @if ($canViewMfs)
            <x-core::stat-card
                icon="smartphone"
                color="gold"
                :value="($totalMfs < 0 ? '-৳' : '৳') . number_format(abs($totalMfs), 2)"
                :value-color="$totalMfs < 0 ? 'red' : 'gold'"
                label="মোবাইল ব্যাংকিং (MFS)"
                label-en="MFS Balance"
                subtext="বিকাশ / নগদ / রকেট"
                subtext-en="bKash, Nagad, Rocket, etc."
            />
        @endif
    </div>
</x-core::layout>
