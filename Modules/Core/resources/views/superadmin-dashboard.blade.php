<x-core::layout
    title="সুপার অ্যাডমিন ড্যাশবোর্ড"
    title-en="Super Admin Dashboard"
    subtitle="প্ল্যাটফর্ম সারসংক্ষেপ, সাবস্ক্রিপশন আয় ও দোকান পর্যবেক্ষণ"
    subtitle-en="Platform overview, subscription revenue & shop monitoring"
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
        $rangeLabel = $rangeLabels[$range] ?? $rangeLabels['month'];
    @endphp

    {{-- System Status & Quick Header Bar --}}
    <div class="superadmin-top-bar" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:18px; padding:14px 18px; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-card);">
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:6px;">
                <span class="bn" style="font-size:12px; font-weight:700; color:var(--ink-700);">পাবলিক রেজিস্ট্রেশন:</span>
                <span class="en" style="display:none; font-size:12px; font-weight:700; color:var(--ink-700);">Public Registration:</span>
                <x-core::badge :color="$registrationEnabled ? 'teal' : 'red'" size="sm" :dot="true">
                    <span class="bn">{{ $registrationEnabled ? 'চালু' : 'বন্ধ' }}</span>
                    <span class="en" style="display:none;">{{ $registrationEnabled ? 'Enabled' : 'Disabled' }}</span>
                </x-core::badge>
            </div>
            <div style="width:1px; height:18px; background:var(--border); margin:0 4px;"></div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span class="bn" style="font-size:12px; font-weight:700; color:var(--ink-700);">ল্যান্ডিং পেজ:</span>
                <span class="en" style="display:none; font-size:12px; font-weight:700; color:var(--ink-700);">Landing Page:</span>
                <x-core::badge :color="$landingPageEnabled ? 'blue' : 'grey'" size="sm" :dot="true">
                    <span class="bn">{{ $landingPageEnabled ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
                    <span class="en" style="display:none;">{{ $landingPageEnabled ? 'Live' : 'Off' }}</span>
                </x-core::badge>
            </div>
            <div style="width:1px; height:18px; background:var(--border); margin:0 4px;"></div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span class="bn" style="font-size:12px; font-weight:700; color:var(--ink-700);">সক্রিয় প্ল্যান:</span>
                <span class="en" style="display:none; font-size:12px; font-weight:700; color:var(--ink-700);">Active Plans:</span>
                <x-core::badge color="gold" size="sm">
                    {{ $totalPlans }}
                </x-core::badge>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <x-core::button as="a" href="{{ route('shops.create') }}" color="primary" size="sm" icon="plus">
                <span class="bn">নতুন দোকান তৈরি</span>
                <span class="en" style="display:none;">Create Shop</span>
            </x-core::button>
            <x-core::button as="a" href="{{ route('plans.index') }}" variant="secondary" size="sm" icon="credit-card">
                <span class="bn">প্ল্যানসমূহ</span>
                <span class="en" style="display:none;">Plans</span>
            </x-core::button>
            <x-core::button as="a" href="{{ route('system-settings.index') }}" variant="secondary" size="sm" icon="settings">
                <span class="bn">সেটিংস</span>
                <span class="en" style="display:none;">Settings</span>
            </x-core::button>
        </div>
    </div>

    {{-- Filter Range Selector Row --}}
    <div class="section-row" style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="font-weight:700; font-size:13px; color:var(--ink-700);">
                <span class="bn">পরিসংখ্যান সময়কাল:</span>
                <span class="en" style="display:none;">Stats Period:</span>
            </div>
            <div class="range-tabs">
                @foreach ($rangeLabels as $key => $labels)
                    <a href="{{ route('dashboard', ['range' => $key]) }}" class="{{ $range === $key ? 'active' : '' }}">
                        <span class="bn">{{ $labels['bn'] }}</span>
                        <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <x-core::button href="{{ route('dashboard', ['range' => $range]) }}" variant="outline" size="sm" icon="refresh-cw">
            <span class="bn">রিফ্রেশ</span>
            <span class="en" style="display:none;">Refresh</span>
        </x-core::button>
    </div>

    {{-- Top 6 KPI Stat Cards Grid --}}
    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); margin-bottom:20px;">
        {{-- Card 1: Subscription Revenue --}}
        <x-core::stat-card
            icon="wallet"
            color="teal"
            :value="'৳' . number_format($revenuePeriod, 2)"
            :label="($range === 'all' ? 'সর্বমোট' : $rangeLabel['bn']) . ' সাবস্ক্রিপশন আয়'"
            :label-en="($range === 'all' ? 'All-Time' : $rangeLabel['en']) . ' Subscription Revenue'"
            :subtext="'সর্বমোট সংগৃহীত: ৳' . number_format($revenueAllTime, 2)"
            :subtext-en="'All-time collected: ৳' . number_format($revenueAllTime, 2)"
        />

        {{-- Card 2: Total Shops --}}
        <x-core::stat-card
            icon="shopping-bag"
            color="blue"
            :value="number_format($totalShops)"
            label="মোট নিবন্ধিত দোকান"
            label-en="Total Registered Shops"
            :subtext="'সক্রিয়: ' . $activeShops . ' | নিষ্ক্রিয়: ' . $inactiveShops"
            :subtext-en="'Active: ' . $activeShops . ' | Inactive: ' . $inactiveShops"
        />

        {{-- Card 3: New Registrations --}}
        <x-core::stat-card
            icon="plus-circle"
            color="gold"
            :value="number_format($newShopsPeriod)"
            :label="($range === 'all' ? 'সর্বমোট' : $rangeLabel['bn']) . ' নতুন নিবন্ধন'"
            :label-en="($range === 'all' ? 'All-Time' : $rangeLabel['en']) . ' New Signups'"
            subtext="নির্বাচিত সময়ে নতুন যুক্ত দোকান"
            subtext-en="Shops registered in period"
        />

        {{-- Card 4: Active Subscriptions --}}
        <x-core::stat-card
            icon="check-circle"
            color="green"
            value-color="green"
            :value="number_format($activeSubscriptions)"
            label="সক্রিয় সাবস্ক্রিপশন"
            label-en="Active Subscriptions"
            :subtext="'ট্রায়াল চলমান: ' . $trialSubscriptions . ' টি দোকান'"
            :subtext-en="'In Trial: ' . $trialSubscriptions . ' shops'"
        />

        {{-- Card 5: Expired / Past Due --}}
        <x-core::stat-card
            icon="alert-triangle"
            color="red"
            value-color="red"
            :value="number_format($expiredSubscriptions)"
            label="মেয়াদোত্তীর্ণ বা বকেয়া"
            label-en="Expired / Past Due"
            subtext="নবায়ন প্রয়োজন এমন দোকান"
            subtext-en="Shops needing renewal"
        />

        {{-- Card 6: Platform Users --}}
        <x-core::stat-card
            icon="users"
            color="blue"
            :value="number_format($totalUsers)"
            label="প্ল্যাটফর্ম ব্যবহারকারী"
            label-en="Platform Users"
            subtext="সকল দোকানের স্টাফ ও এডমিন"
            subtext-en="Across all shops & admins"
        />
    </div>

    {{-- Visual Analytics Row: Charts (Growth & Revenue Trend + Subscription Health & Plans) --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:20px;" class="analytics-grid">
        {{-- Growth & Revenue Chart Card --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div>
                    <div class="panel-title">
                        <span class="bn">মাসিক প্রবৃদ্ধি ও আয় বিশ্লেষণ</span>
                        <span class="en" style="display:none;">Monthly Growth & Revenue Trend</span>
                    </div>
                    <div style="font-size:12px; color:var(--ink-400); margin-top:2px;">
                        <span class="bn">বিগত ৬ মাসের সাবস্ক্রিপশন আয় এবং নতুন দোকান নিবন্ধনের চিত্র</span>
                        <span class="en" style="display:none;">Last 6 months subscription revenue and new shop signups</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:12px; font-size:11.5px; font-weight:700;">
                    <div style="display:flex; align-items:center; gap:5px;">
                        <span style="display:inline-block; width:10px; height:10px; border-radius:3px; background:var(--teal-800);"></span>
                        <span class="bn">আয় (৳)</span>
                        <span class="en" style="display:none;">Revenue (৳)</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:var(--blue-500);"></span>
                        <span class="bn">নতুন দোকান</span>
                        <span class="en" style="display:none;">New Shops</span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding:16px 20px 18px;">
                <div style="position:relative; width:100%; height:250px;">
                    <canvas id="superadminGrowthChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Subscription Health & Plan Distribution --}}
        <div class="panel" style="margin-top:0; display:flex; flex-direction:column;">
            <div class="panel-head">
                <div class="panel-title">
                    <span class="bn">প্ল্যান ও সাবস্ক্রিপশন অবস্থা</span>
                    <span class="en" style="display:none;">Plans & Subscription Status</span>
                </div>
                <x-core::badge color="teal" size="sm">
                    {{ $totalShops }} <span class="bn">দোকান</span><span class="en" style="display:none;">Shops</span>
                </x-core::badge>
            </div>
            <div class="panel-body" style="padding:16px 18px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                {{-- Donut Chart Canvas --}}
                <div style="position:relative; width:100%; height:140px; margin-bottom:14px;">
                    <canvas id="superadminDonutChart"></canvas>
                </div>

                {{-- Plan Distribution List with Progress Bars --}}
                <div style="border-top:1px solid var(--border); padding-top:12px;">
                    <div style="font-size:12px; font-weight:700; color:var(--ink-700); margin-bottom:10px;">
                        <span class="bn">প্ল্যানভিত্তিক সক্রিয় দোকান:</span>
                        <span class="en" style="display:none;">Active Subscribers by Plan:</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @forelse ($plans as $plan)
                            @php
                                $percent = $totalShops > 0 ? round(($plan->subscriptions_count / $totalShops) * 100) : 0;
                            @endphp
                            <div>
                                <div style="display:flex; align-items:center; justify-content:space-between; font-size:12px; margin-bottom:3px;">
                                    <span style="font-weight:600; color:var(--ink-900);">{{ $plan->name }}</span>
                                    <span style="color:var(--ink-600); font-weight:700;">
                                        {{ $plan->subscriptions_count }} <span class="bn">দোকান</span><span class="en" style="display:none;">shops</span> ({{ $percent }}%)
                                    </span>
                                </div>
                                <div style="height:6px; background:var(--paper-line); border-radius:99px; overflow:hidden;">
                                    <div style="height:100%; width:{{ min(100, max(4, $percent)) }}%; background:var(--teal-800); border-radius:99px;"></div>
                                </div>
                            </div>
                        @empty
                            <div style="font-size:12px; color:var(--ink-400); text-align:center; padding:10px 0;">
                                <span class="bn">কোনো প্ল্যান পাওয়া যায়নি</span>
                                <span class="en" style="display:none;">No plans configured</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Operations Row: Expiring Soon Alert & Recent Payments --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:20px;" class="two-col-grid">
        {{-- Expiring Soon Subscriptions Card --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:28px; height:28px; border-radius:8px; background:var(--red-100); color:var(--red-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="clock" size="16" />
                    </div>
                    <div>
                        <div class="panel-title">
                            <span class="bn">শীঘ্রই মেয়াদ শেষ হবে (আগামী ১৪ দিন)</span>
                            <span class="en" style="display:none;">Expiring Soon (Next 14 Days)</span>
                        </div>
                    </div>
                </div>
                <x-core::badge :color="$expiringSubscriptions->isNotEmpty() ? 'red' : 'green'" size="sm">
                    {{ $expiringSubscriptions->count() }} <span class="bn">টি</span><span class="en" style="display:none;">items</span>
                </x-core::badge>
            </div>
            <div class="panel-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="app-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th><span class="bn">দোকানের নাম</span><span class="en" style="display:none;">Shop</span></th>
                                <th><span class="bn">প্ল্যান</span><span class="en" style="display:none;">Plan</span></th>
                                <th><span class="bn">মেয়াদোত্তীর্ণের তারিখ</span><span class="en" style="display:none;">Expires On</span></th>
                                <th><span class="bn">অবশিষ্ট দিন</span><span class="en" style="display:none;">Remaining</span></th>
                                <th style="text-align:right;"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringSubscriptions as $sub)
                                @php
                                    $expDate = $sub->ends_at ?? $sub->trial_ends_at;
                                    $daysRemaining = $expDate ? now()->diffInDays($expDate, false) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:var(--ink-900);">
                                            {{ $sub->shop?->name ?? 'N/A' }}
                                        </div>
                                        <div style="font-size:11px; color:var(--ink-400);">
                                            {{ $sub->shop?->store_code ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <x-core::badge color="blue" size="sm">
                                            {{ $sub->plan?->name ?? 'Custom' }}
                                        </x-core::badge>
                                    </td>
                                    <td style="font-size:12px; color:var(--ink-700);">
                                        {{ $expDate ? $expDate->format('d M, Y') : '-' }}
                                    </td>
                                    <td>
                                        @if ($daysRemaining < 0)
                                            <x-core::badge color="red" size="sm">
                                                <span class="bn">মেয়াদ শেষ</span>
                                                <span class="en" style="display:none;">Expired</span>
                                            </x-core::badge>
                                        @elseif ($daysRemaining <= 3)
                                            <x-core::badge color="red" size="sm" :dot="true">
                                                {{ $daysRemaining }} <span class="bn">দিন বাকি</span><span class="en" style="display:none;">d left</span>
                                            </x-core::badge>
                                        @else
                                            <x-core::badge color="gold" size="sm" :dot="true">
                                                {{ $daysRemaining }} <span class="bn">দিন বাকি</span><span class="en" style="display:none;">d left</span>
                                            </x-core::badge>
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        @if ($sub->shop)
                                            <x-core::button
                                                as="a"
                                                href="{{ route('shops.edit', $sub->shop) }}"
                                                variant="outline"
                                                size="sm"
                                                icon="edit"
                                            >
                                                <span class="bn">রিনিউ</span>
                                                <span class="en" style="display:none;">Renew</span>
                                            </x-core::button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-core::table.empty
                                            icon="shield-check"
                                            title="শীঘ্রই মেয়াদ শেষ হবে এমন কোনো সাবস্ক্রিপশন নেই"
                                            title-en="No subscriptions expiring soon"
                                            description="সকল সক্রিয় সাবস্ক্রিপশন স্বাভাবিক সময়সীমার মধ্যে আছে"
                                            description-en="All active subscriptions are healthy"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Subscription Payments Card --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:28px; height:28px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="credit-card" size="16" />
                    </div>
                    <div>
                        <div class="panel-title">
                            <span class="bn">সাম্প্রতিক সাবস্ক্রিপশন পেমেন্ট</span>
                            <span class="en" style="display:none;">Recent Subscription Payments</span>
                        </div>
                    </div>
                </div>
                <x-core::button as="a" href="{{ route('shops.index') }}" variant="ghost" size="sm">
                    <span class="bn">সব দেখুন</span>
                    <span class="en" style="display:none;">View All</span>
                </x-core::button>
            </div>
            <div class="panel-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="app-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th><span class="bn">দোকান</span><span class="en" style="display:none;">Shop</span></th>
                                <th><span class="bn">প্ল্যান</span><span class="en" style="display:none;">Plan</span></th>
                                <th><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Amount</span></th>
                                <th><span class="bn">মাধ্যম</span><span class="en" style="display:none;">Method</span></th>
                                <th style="text-align:right;"><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPayments as $payment)
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:var(--ink-900);">
                                            {{ $payment->subscription?->shop?->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size:12px; font-weight:600; color:var(--ink-700);">
                                            {{ $payment->subscription?->plan?->name ?? 'Subscription' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; color:var(--green-ink);">
                                            ৳{{ number_format($payment->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <x-core::badge color="teal" size="sm">
                                            {{ ucfirst($payment->method ?? 'Cash') }}
                                        </x-core::badge>
                                    </td>
                                    <td style="text-align:right; font-size:12px; color:var(--ink-600);">
                                        {{ $payment->paid_at ? $payment->paid_at->format('d M, Y') : $payment->created_at?->format('d M, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-core::table.empty
                                            icon="banknote"
                                            title="কোনো সাম্প্রতিক পেমেন্ট নেই"
                                            title-en="No recent payments"
                                            description="সাবস্ক্রিপশন ফি জমা হলে এখানে প্রদর্শিত হবে"
                                            description-en="Subscription payment history will appear here"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row: Recently Registered Shops & Recent System Activity (Audit Logs) --}}
    <div style="display:grid; grid-template-columns:3fr 2fr; gap:18px; margin-bottom:24px;" class="analytics-grid">
        {{-- Recently Registered Shops --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:28px; height:28px; border-radius:8px; background:var(--blue-100); color:var(--blue-ink); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="store" size="16" />
                    </div>
                    <div>
                        <div class="panel-title">
                            <span class="bn">সাম্প্রতিক নিবন্ধিত দোকানসমূহ</span>
                            <span class="en" style="display:none;">Recently Registered Shops</span>
                        </div>
                    </div>
                </div>
                <x-core::button as="a" href="{{ route('shops.index') }}" variant="secondary" size="sm">
                    <span class="bn">সকল দোকান ({{ $totalShops }})</span>
                    <span class="en" style="display:none;">All Shops ({{ $totalShops }})</span>
                </x-core::button>
            </div>
            <div class="panel-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="app-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th><span class="bn">দোকানের নাম</span><span class="en" style="display:none;">Shop Name</span></th>
                                <th><span class="bn">কোড</span><span class="en" style="display:none;">Code</span></th>
                                <th><span class="bn">যোগাযোগ</span><span class="en" style="display:none;">Contact</span></th>
                                <th><span class="bn">প্ল্যান</span><span class="en" style="display:none;">Plan</span></th>
                                <th><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                                <th style="text-align:right;"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentShops as $shop)
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:var(--ink-900);">
                                            {{ $shop->name }}
                                        </div>
                                        <div style="font-size:11px; color:var(--ink-400);">
                                            {{ $shop->created_at?->format('d M, Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-family:'Plus Jakarta Sans',sans-serif; font-size:12px; font-weight:600; color:var(--ink-700);">
                                            {{ $shop->store_code ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:12px; color:var(--ink-600);">
                                            {{ $shop->phone ?: ($shop->email ?: '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <x-core::badge color="blue" size="sm">
                                            {{ $shop->activeSubscription?->plan?->name ?? 'Default' }}
                                        </x-core::badge>
                                    </td>
                                    <td>
                                        <x-core::badge :color="$shop->status === 'active' ? 'green' : 'red'" size="sm" :dot="true">
                                            <span class="bn">{{ $shop->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
                                            <span class="en" style="display:none;">{{ ucfirst($shop->status) }}</span>
                                        </x-core::badge>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; align-items:center; gap:6px;">
                                            <x-core::button
                                                as="a"
                                                href="{{ route('shops.edit', $shop) }}"
                                                variant="outline"
                                                size="sm"
                                                icon="edit"
                                                title="দোকান সম্পাদনা"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-core::table.empty
                                            icon="store"
                                            title="কোনো দোকান পাওয়া যায়নি"
                                            title-en="No shops found"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent System Activity (Audit Logs) --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:28px; height:28px; border-radius:8px; background:var(--gold-100); color:var(--gold-ink); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="activity" size="16" />
                    </div>
                    <div>
                        <div class="panel-title">
                            <span class="bn">সাম্প্রতিক সিস্টেম অ্যাক্টিভিটি</span>
                            <span class="en" style="display:none;">Recent Audit Activity</span>
                        </div>
                    </div>
                </div>
                <x-core::button as="a" href="{{ route('audit-log.index') }}" variant="ghost" size="sm">
                    <span class="bn">লগ দেখুন</span>
                    <span class="en" style="display:none;">View Logs</span>
                </x-core::button>
            </div>
            <div class="panel-body" style="padding:14px 18px;">
                <div style="display:flex; flex-direction:column; gap:12px;">
                    @forelse ($recentAuditLogs as $log)
                        @php
                            $meta = $log->modelMeta();
                            $actMeta = $log->actionLabel();
                        @endphp
                        <div style="display:flex; align-items:flex-start; gap:10px; padding-bottom:10px; border-bottom:1px solid var(--border);">
                            <div style="width:30px; height:30px; border-radius:8px; background:var(--paper-line); display:flex; align-items:center; justify-content:center; flex-shrink:0; color:var(--ink-700);">
                                <x-core::icon :name="$meta['icon'] ?? 'file-text'" size="15" />
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:2px;">
                                    <span style="font-weight:700; font-size:12.5px; color:var(--ink-900);">
                                        <span class="bn">{{ $meta['bn'] }}</span>
                                        <span class="en" style="display:none;">{{ $meta['en'] }}</span>
                                    </span>
                                    <x-core::badge :color="$actMeta['color'] ?? 'grey'" size="xs">
                                        <span class="bn">{{ $actMeta['bn'] }}</span>
                                        <span class="en" style="display:none;">{{ $actMeta['en'] }}</span>
                                    </x-core::badge>
                                </div>
                                <div style="font-size:11.5px; color:var(--ink-600); display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    @if ($log->shop)
                                        <span style="font-weight:600; color:var(--teal-800);">{{ $log->shop->name }}</span>
                                    @endif
                                    @if ($log->user)
                                        <span>• {{ $log->user->name }}</span>
                                    @endif
                                    <span>• {{ $log->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:20px 0; color:var(--ink-400); font-size:12px;">
                            <span class="bn">কোনো অডিট লগ রেকর্ড নেই</span>
                            <span class="en" style="display:none;">No recent audit activity</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media (max-width: 1024px) {
                .analytics-grid, .two-col-grid {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            $(function () {
                const isDark = $('html').attr('data-theme') === 'dark';
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
                const textColor = isDark ? '#94a3b8' : '#64748b';

                // 1. Growth & Revenue Trend Dual-Chart
                const growthCtx = document.getElementById('superadminGrowthChart');
                if (growthCtx) {
                    new Chart(growthCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [
                                {
                                    label: 'রেভিনিউ / Revenue (৳)',
                                    data: @json($chartRevenueData),
                                    backgroundColor: 'rgba(13, 148, 136, 0.75)',
                                    hoverBackgroundColor: 'rgba(13, 148, 136, 0.95)',
                                    borderRadius: 6,
                                    yAxisID: 'yRevenue',
                                    order: 2,
                                },
                                {
                                    label: 'নতুন দোকান / New Shops',
                                    data: @json($chartShopsData),
                                    type: 'line',
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                                    pointBackgroundColor: '#3b82f6',
                                    pointBorderColor: '#ffffff',
                                    pointHoverRadius: 6,
                                    pointRadius: 4,
                                    borderWidth: 2.5,
                                    tension: 0.35,
                                    fill: false,
                                    yAxisID: 'yShops',
                                    order: 1,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#1e293b' : '#0f172a',
                                    titleColor: '#ffffff',
                                    bodyColor: '#e2e8f0',
                                    borderColor: isDark ? '#334155' : '#1e293b',
                                    borderWidth: 1,
                                    padding: 10,
                                    callbacks: {
                                        label: function (ctx) {
                                            if (ctx.dataset.yAxisID === 'yRevenue') {
                                                return ' আয়: ৳' + Number(ctx.raw).toLocaleString();
                                            }
                                            return ' নতুন দোকান: ' + ctx.raw;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: gridColor },
                                    ticks: { color: textColor, font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 } }
                                },
                                yRevenue: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    grid: { color: gridColor },
                                    ticks: {
                                        color: textColor,
                                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 10.5 },
                                        callback: function (val) {
                                            return '৳' + val.toLocaleString();
                                        }
                                    }
                                },
                                yShops: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    ticks: {
                                        color: textColor,
                                        stepSize: 1,
                                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 10.5 }
                                    }
                                }
                            }
                        }
                    });
                }

                // 2. Subscription Status Donut Chart
                const donutCtx = document.getElementById('superadminDonutChart');
                if (donutCtx) {
                    const statusData = @json($statusCounts);
                    new Chart(donutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['সক্রিয় (Active)', 'ট্রায়াল (Trialing)', 'বকেয়া (Past Due)', 'মেয়াদোত্তীর্ণ (Expired)'],
                            datasets: [{
                                data: [
                                    statusData.active || 0,
                                    statusData.trialing || 0,
                                    statusData.past_due || 0,
                                    statusData.expired || 0
                                ],
                                backgroundColor: [
                                    '#0d9488', // Teal
                                    '#f59e0b', // Amber/Gold
                                    '#ea580c', // Orange
                                    '#ef4444'  // Red
                                ],
                                borderWidth: 2,
                                borderColor: isDark ? '#111827' : '#ffffff',
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        font: { size: 10, family: "'Noto Sans Bengali', sans-serif" },
                                        color: textColor,
                                        padding: 8
                                    }
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#1e293b' : '#0f172a',
                                    titleColor: '#ffffff',
                                    bodyColor: '#e2e8f0',
                                    padding: 8
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-core::layout>
