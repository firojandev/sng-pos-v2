@props(['active' => null])

@php
$user = auth()->user();
$isSuperAdmin = $user?->isSuperAdmin() ?? false;

$superAdminGroups = [
    [
        'label' => null,
        'items' => [
            [
                'key' => 'shops',
                'route' => 'shops.index',
                'bn' => 'দোকানসমূহ',
                'en' => 'Shops',
                'icon' => '<path d="M3 9.5 12 4l9 5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 9v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'plans',
                'route' => 'plans.index',
                'bn' => 'প্ল্যান',
                'en' => 'Plans',
                'icon' => '<path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
            ],
        ],
    ],
];

$navGroups = [
    [
        'label' => null,
        'gated' => false,
        'items' => [
            [
                'key' => 'dashboard',
                'route' => 'dashboard',
                'bn' => 'ড্যাশবোর্ড',
                'en' => 'Dashboard',
                'icon' => '<path d="M4 11.5 12 4l8 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 10v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-9" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>',
            ],
        ],
    ],
    [
        'label' => ['bn' => 'বিক্রয় ও ক্রয়', 'en' => 'Sales & Purchase'],
        'gated' => true,
        'items' => [
            [
                'key' => 'sales',
                'route' => 'sales.index',
                'bn' => 'বিক্রয়',
                'en' => 'Sales',
                'icon' => '<path d="M4 4h2l2.2 11.5a2 2 0 0 0 2 1.6h6.6a2 2 0 0 0 2-1.6L20 8H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9.5" cy="20" r="1.4" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="20" r="1.4" stroke="currentColor" stroke-width="1.6"/>',
            ],
            [
                'key' => 'purchase',
                'route' => 'purchase.index',
                'bn' => 'ক্রয়',
                'en' => 'Purchase',
                'icon' => '<path d="M3 7h18l-1.5 10.5a2 2 0 0 1-2 1.5H6.5a2 2 0 0 1-2-1.5L3 7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 7V5.5A3.5 3.5 0 0 1 11.5 2h1A3.5 3.5 0 0 1 16 5.5V7" stroke="currentColor" stroke-width="1.7"/>',
            ],
            [
                'key' => 'cashbox',
                'route' => 'cashbox.index',
                'bn' => 'ক্যাশবক্স',
                'en' => 'Cashbox',
                'icon' => '<rect x="2.5" y="6" width="19" height="13" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12.5" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M2.5 9.5h3M18.5 15.5h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
            ],
            [
                'key' => 'quick-sale',
                'route' => 'quick-sale.create',
                'bn' => 'দ্রুত বেচা',
                'en' => 'Quick Sale',
                'icon' => '<circle cx="12" cy="12" r="9.2" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5v9M8.7 15.3c0 1.2 1.2 2.1 3.3 2.1s3.3-.9 3.3-2.1c0-3-6.6-1.2-6.6-4.1 0-1.2 1.2-2.1 3.3-2.1s3.3.9 3.3 2.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            ],
            [
                'key' => 'purchase-ledger',
                'permission' => 'purchase',
                'route' => 'purchase.ledger',
                'bn' => 'কেনার খাতা',
                'en' => 'Purchase Ledger',
                'icon' => '<path d="M4 4h16v16H4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 9h8M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
            ],
            [
                'key' => 'sales-ledger',
                'permission' => 'sales',
                'route' => 'sales.ledger',
                'bn' => 'বেচার খাতা',
                'en' => 'Sales Ledger',
                'icon' => '<path d="M4 4h16v16H4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 9h8M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
            ],
            [
                'key' => 'purchase-returns',
                'permission' => 'purchase',
                'route' => 'purchase-returns.index',
                'bn' => 'ক্রয় ফেরত',
                'en' => 'Purchase Returns',
                'icon' => '<path d="M4 12a8 8 0 1 1 2.3 5.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 17v-5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'sale-returns',
                'permission' => 'sales',
                'route' => 'sale-returns.index',
                'bn' => 'বিক্রয় ফেরত',
                'en' => 'Sale Returns',
                'icon' => '<path d="M4 12a8 8 0 1 1 2.3 5.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 17v-5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'due-ledger',
                'permission' => 'customers',
                'route' => 'due-ledger.index',
                'bn' => 'বাকির খাতা',
                'en' => 'Due Ledger',
                'icon' => '<circle cx="12" cy="12" r="9.2" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="16.5" r="1" fill="currentColor"/>',
            ],
        ],
    ],
    [
        'label' => ['bn' => 'ইনভেন্টরি', 'en' => 'Inventory'],
        'gated' => true,
        'items' => [
            [
                'key' => 'stock',
                'route' => 'stock.index',
                'bn' => 'স্টক',
                'en' => 'Stock',
                'icon' => '<path d="M3 8l9-5 9 5-9 5-9-5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M3 8v8l9 5 9-5V8" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'stock-transfers',
                'permission' => 'stock',
                'route' => 'stock-transfers.index',
                'bn' => 'স্টক ট্রান্সফার',
                'en' => 'Stock Transfer',
                'icon' => '<path d="M4 7h11M15 7l-3-3M15 7l-3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 17H9M9 17l3-3M9 17l3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'products',
                'route' => 'products.index',
                'bn' => 'পণ্য ব্যবস্থাপনা',
                'en' => 'Product Management',
                'icon' => '<rect x="3" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="4" width="8" height="8" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="14" width="8" height="6" rx="1.6" stroke="currentColor" stroke-width="1.6"/>',
            ],
        ],
    ],
    [
        'label' => ['bn' => 'পার্টি', 'en' => 'Parties'],
        'gated' => true,
        'items' => [
            [
                'key' => 'customers',
                'route' => 'customers.index',
                'bn' => 'গ্রাহক',
                'en' => 'Customers',
                'icon' => '<circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 19c.7-3 2.8-4.6 5.5-4.6s4.8 1.6 5.5 4.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2.3" stroke="currentColor" stroke-width="1.5"/><path d="M15 19c.3-1.7 1.3-2.9 2.8-3.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            ],
            [
                'key' => 'suppliers',
                'route' => 'suppliers.index',
                'bn' => 'সরবরাহকারী',
                'en' => 'Suppliers',
                'icon' => '<rect x="2" y="8" width="12" height="8" rx="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M14 11h3.5L20 14v2h-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="7" cy="18.5" r="1.6" stroke="currentColor" stroke-width="1.4"/><circle cx="17" cy="18.5" r="1.6" stroke="currentColor" stroke-width="1.4"/>',
            ],
        ],
    ],
    [
        'label' => ['bn' => 'হিসাব', 'en' => 'Accounts'],
        'gated' => true,
        'items' => [
            [
                'key' => 'income',
                'route' => 'income.index',
                'bn' => 'আয়',
                'en' => 'Income',
                'icon' => '<path d="M4 16l6-6 4 4 6-7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 7h5v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'expense',
                'route' => 'expense.index',
                'bn' => 'ব্যয়',
                'en' => 'Expense',
                'icon' => '<path d="M4 8l6 6 4-4 6 7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 17h5v-5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'tax',
                'route' => 'tax.index',
                'bn' => 'ট্যাক্স ও ভ্যাট',
                'en' => 'Tax & VAT',
                'icon' => '<circle cx="7.5" cy="7.5" r="2.2" stroke="currentColor" stroke-width="1.6"/><circle cx="16.5" cy="16.5" r="2.2" stroke="currentColor" stroke-width="1.6"/><path d="M18 6 6 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
            ],
        ],
    ],
    [
        'label' => ['bn' => 'প্রশাসন', 'en' => 'Administration'],
        'gated' => true,
        'items' => [
            [
                'key' => 'branches',
                'route' => 'branches.index',
                'bn' => 'শাখা ও গুদাম',
                'en' => 'Branches & Warehouses',
                'icon' => '<path d="M3 9.5 12 4l9 5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 9v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'employees',
                'route' => 'employees.index',
                'bn' => 'কর্মচারী',
                'en' => 'Employees',
                'icon' => '<path d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="7" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M19 20v-2a3.6 3.6 0 0 0-2.5-3.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M14.5 3.6a3.2 3.2 0 0 1 0 6.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            ],
            [
                'key' => 'users',
                'route' => 'users.index',
                'bn' => 'ইউজার',
                'en' => 'Users',
                'icon' => '<circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.6"/><path d="M4.5 20c1-4 3.8-6 7.5-6s6.5 2 7.5 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
            ],
            [
                'key' => 'audit-log',
                'permission' => 'audit',
                'route' => 'audit-log.index',
                'bn' => 'অ্যাক্টিভিটি লগ',
                'en' => 'Audit Log',
                'icon' => '<path d="M4 19V9m6 10V5m6 14v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
            ],
        ],
    ],
    [
        'label' => ['bn' => 'অন্যান্য', 'en' => 'Other'],
        'gated' => false,
        'items' => [
            [
                'key' => 'reports',
                'route' => 'reports.index',
                'bn' => 'রিপোর্ট',
                'en' => 'Reports',
                'icon' => '<path d="M4 19V9m6 10V5m6 14v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
                'gated' => true,
            ],
            [
                'key' => 'settings',
                'route' => 'settings.index',
                'bn' => 'সেটিংস',
                'en' => 'Settings',
                'icon' => '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M19 12a7 7 0 0 0-.14-1.4l2-1.5-2-3.5-2.3.9a7 7 0 0 0-2.4-1.4L14 2h-4l-.16 2.1a7 7 0 0 0-2.4 1.4l-2.3-.9-2 3.5 2 1.5A7 7 0 0 0 5 12a7 7 0 0 0 .14 1.4l-2 1.5 2 3.5 2.3-.9a7 7 0 0 0 2.4 1.4L10 22h4l.16-2.1a7 7 0 0 0 2.4-1.4l2.3.9 2-3.5-2-1.5c.09-.46.14-.93.14-1.4Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
                'gated' => false,
            ],
            [
                'key' => 'subscription',
                'route' => 'subscription.show',
                'bn' => 'সাবস্ক্রিপশন',
                'en' => 'Subscription',
                'icon' => '<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9.5h18" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 14.5h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
                'gated' => false,
            ],
        ],
    ],
];

$isNavItemVisible = function (array $item, bool $groupGated, $user) {
    $gated = $item['gated'] ?? $groupGated;
    if (! $gated) {
        return true;
    }
    $permissionKey = $item['permission'] ?? $item['key'];
    return $user && $user->shop && $user->shop->hasFeature($permissionKey) && $user->can("{$permissionKey}.view");
};
@endphp

<aside class="sidebar" id="sidebar">
    <div class="side-head">
        <div class="mark">ম</div>
        <div class="nm">
            <span class="brand-title">মাস্টার<span class="brand-accent">পস</span></span>
            <span class="brand-tagline bn">ব্যবসা ব্যবস্থাপনা</span>
            <span class="brand-tagline en" style="display:none;">Business Management</span>
        </div>
        <button class="side-close" onclick="toggleSidebar(false)" aria-label="Close sidebar">&times;</button>
    </div>

    <div class="side-nav-wrapper">
        @if ($isSuperAdmin)
            @foreach ($superAdminGroups as $group)
                <div class="nav-group">
                    @if ($group['label'])
                        <div class="nav-group-label bn">{{ $group['label']['bn'] }}</div>
                        <div class="nav-group-label en" style="display:none;">{{ $group['label']['en'] }}</div>
                    @endif

                    @foreach ($group['items'] as $item)
                        <a href="{{ route($item['route']) }}" class="nav-item {{ $active === $item['key'] ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none">{!! $item['icon'] !!}</svg>
                            <span class="bn">{{ $item['bn'] }}</span>
                            <span class="en" style="display:none;">{{ $item['en'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        @else
            @foreach ($navGroups as $group)
                @php
                    $visibleItems = array_filter($group['items'], fn ($item) => $isNavItemVisible($item, $group['gated'], $user));
                @endphp
                @if (count($visibleItems))
                    <div class="nav-group">
                        @if ($group['label'])
                            <div class="nav-group-label bn">{{ $group['label']['bn'] }}</div>
                            <div class="nav-group-label en" style="display:none;">{{ $group['label']['en'] }}</div>
                        @endif

                        @foreach ($visibleItems as $item)
                            <a href="{{ route($item['route']) }}" class="nav-item {{ $active === $item['key'] ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none">{!! $item['icon'] !!}</svg>
                                <span class="bn">{{ $item['bn'] }}</span>
                                <span class="en" style="display:none;">{{ $item['en'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    <div class="side-foot">
        <div class="side-user-card">
            <div class="av">{{ mb_substr($user->name ?? '?', 0, 1) }}</div>
            <div class="user-info">
                <div class="nm" title="{{ $user->name ?? '' }}">{{ $user->name ?? 'User' }}</div>
                <div class="role" title="{{ $isSuperAdmin ? 'Super Admin' : ($user->shop->name ?? '') }}">
                    {{ $isSuperAdmin ? 'Super Admin' : ($user->shop->name ?? 'Staff') }}
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="লগ আউট / Logout">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
