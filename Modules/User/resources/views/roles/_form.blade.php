@php
    $currentPermissions = old('permissions', $rolePermissions);
    $standardActions = [
        'view' => ['bn' => 'দেখা', 'en' => 'View'],
        'create' => ['bn' => 'তৈরি', 'en' => 'Create'],
        'edit' => ['bn' => 'সম্পাদনা', 'en' => 'Edit'],
        'delete' => ['bn' => 'মুছে ফেলা', 'en' => 'Delete'],
    ];
    $allActionLabels = \Modules\Core\Support\Permissions::actionLabels();

    $featureMeta = [
        'sales' => ['icon' => 'shopping-cart', 'desc_bn' => 'পণ্য বিক্রয় ও ইনভয়েস', 'desc_en' => 'Sales & Invoicing'],
        'quick-sale' => ['icon' => 'sparkles', 'desc_bn' => 'কাউন্টারে দ্রুত বিক্রয়', 'desc_en' => 'Fast Counter POS'],
        'customers' => ['icon' => 'users', 'desc_bn' => 'গ্রাহক তথ্য ও বাকির খাতা', 'desc_en' => 'Customers & Due Ledger'],
        'cashbox' => ['icon' => 'wallet', 'desc_bn' => 'দৈনিক ক্যাশ ও লেনদেন', 'desc_en' => 'Cash Register & Flow'],
        'products' => ['icon' => 'box', 'desc_bn' => 'পণ্য ক্যাটালগ ও মূল্যতালিকা', 'desc_en' => 'Catalog & Pricing'],
        'stock' => ['icon' => 'layers', 'desc_bn' => 'মজুদ পরিমাণ ও সমন্বয়', 'desc_en' => 'Inventory & Adjustments'],
        'purchase' => ['icon' => 'truck', 'desc_bn' => 'পণ্য ক্রয় ও চালান গ্রহণ', 'desc_en' => 'Purchase & Receiving'],
        'suppliers' => ['icon' => 'truck', 'desc_bn' => 'সরবরাহকারী ও দেনার হিসাব', 'desc_en' => 'Suppliers & Payables'],
        'branches' => ['icon' => 'store', 'desc_bn' => 'আউটলেট ও গুদাম ব্যবস্থাপনা', 'desc_en' => 'Outlets & Warehouses'],
        'income' => ['icon' => 'trending-up', 'desc_bn' => 'অন্যান্য আয় ও প্রাপ্তি', 'desc_en' => 'Non-operating Income'],
        'expense' => ['icon' => 'trending-down', 'desc_bn' => 'দোকানের খরচ ও বিল পরিশোধ', 'desc_en' => 'Shop Expenses & Bills'],
        'accounts' => ['icon' => 'credit-card', 'desc_bn' => 'ব্যাংক ও ক্যাশ অ্যাকাউন্ট', 'desc_en' => 'Bank & Cash Accounts'],
        'account-transfers' => ['icon' => 'refresh', 'desc_bn' => 'হিসাবের মধ্যকার স্থানান্তর', 'desc_en' => 'Inter-account Transfers'],
        'tax' => ['icon' => 'percent', 'desc_bn' => 'ট্যাক্স ও ভ্যাট কনফিগারেশন', 'desc_en' => 'VAT & Tax Settings'],
        'reports' => ['icon' => 'bar-chart', 'desc_bn' => 'ব্যবসায়িক প্রতিবেদন ও হিসাব', 'desc_en' => 'Business Analytics & Reports'],
        'audit' => ['icon' => 'activity', 'desc_bn' => 'সিস্টেম অ্যাক্টিভিটি লগ', 'desc_en' => 'System Audit Logs'],
        'employees' => ['icon' => 'user-check', 'desc_bn' => 'কর্মচারী ও বেতন হিসাব', 'desc_en' => 'Staff & Payroll'],
        'users' => ['icon' => 'shield', 'desc_bn' => 'ইউজার অ্যাকাউন্ট ও পারমিশন', 'desc_en' => 'Users & Access Rights'],
    ];

    $domainDefinitions = [
        'sales_customer' => [
            'title_bn' => 'বিক্রয় ও গ্রাহক সেবা',
            'title_en' => 'Sales & Customers',
            'subtitle_bn' => 'পজ বিক্রয়, দ্রুত সেল, গ্রাহক ও ক্যাশবক্স পরিচালনা',
            'subtitle_en' => 'Point of sale, quick selling, customer profiles and cash register',
            'icon' => 'shopping-cart',
            'badge_color' => 'primary',
            'keys' => ['sales', 'quick-sale', 'customers', 'cashbox'],
        ],
        'inventory_supply' => [
            'title_bn' => 'পণ্য ও ইনভেন্টরি',
            'title_en' => 'Inventory & Products',
            'subtitle_bn' => 'পণ্য তালিকা, স্টক সমন্বয়, ক্রয় ও সরবরাহকারী',
            'subtitle_en' => 'Product catalogue, stock adjustments, purchase and suppliers',
            'icon' => 'package',
            'badge_color' => 'blue',
            'keys' => ['products', 'stock', 'purchase', 'suppliers', 'branches'],
        ],
        'finance_accounts' => [
            'title_bn' => 'হিসাব ও অর্থায়ন',
            'title_en' => 'Finance & Accounts',
            'subtitle_bn' => 'আয়, ব্যয়, ব্যাংক হিসাব, ফান্ড স্থানান্তর ও ট্যাক্স',
            'subtitle_en' => 'Income, expenses, bank accounts, fund transfers and taxation',
            'icon' => 'dollar',
            'badge_color' => 'gold',
            'keys' => ['income', 'expense', 'accounts', 'account-transfers', 'tax'],
        ],
        'admin_governance' => [
            'title_bn' => 'প্রশাসন ও অডিট',
            'title_en' => 'Administration & Security',
            'subtitle_bn' => 'কর্মচারী, ইউজার ও রোল, রিপোর্ট ও অ্যাক্টিভিটি লগ',
            'subtitle_en' => 'Staff, user accounts, business reports and activity audit',
            'icon' => 'shield',
            'badge_color' => 'red',
            'keys' => ['employees', 'users', 'reports', 'audit'],
        ],
    ];

    $groupedFeatures = [];
    $assignedFeatureKeys = [];

    foreach ($domainDefinitions as $domKey => $domain) {
        $domFeatures = [];
        foreach ($domain['keys'] as $fKey) {
            if (isset($features[$fKey])) {
                $domFeatures[$fKey] = $features[$fKey];
                $assignedFeatureKeys[] = $fKey;
            }
        }
        if (!empty($domFeatures)) {
            $groupedFeatures[$domKey] = [
                'meta' => $domain,
                'features' => $domFeatures,
            ];
        }
    }

    $leftoverKeys = array_diff(array_keys($features), $assignedFeatureKeys);
    if (!empty($leftoverKeys)) {
        $leftoverFeatures = [];
        foreach ($leftoverKeys as $fKey) {
            $leftoverFeatures[$fKey] = $features[$fKey];
        }
        $groupedFeatures['other'] = [
            'meta' => [
                'title_bn' => 'অন্যান্য ফিচার',
                'title_en' => 'Other Features',
                'subtitle_bn' => 'অন্যান্য অতিরিক্ত ফিচার ও পারমিশন',
                'subtitle_en' => 'Additional features and permissions',
                'icon' => 'sliders',
                'badge_color' => 'secondary',
            ],
            'features' => $leftoverFeatures,
        ];
    }
@endphp

<style>
.role-top-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 16px;
    margin-bottom: 20px;
}
@media (max-width: 768px) {
    .role-top-grid {
        grid-template-columns: 1fr;
    }
}
.role-domain-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s ease;
}
.role-domain-card:hover {
    box-shadow: var(--shadow-card);
}
.role-domain-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    background: var(--card);
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 10px;
}
.module-row {
    border-bottom: 1px solid var(--border);
    transition: background 0.15s ease;
}
.module-row:hover {
    background: var(--paper-line);
}
.special-action-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 3px 8px !important;
    border-radius: 6px !important;
    background: var(--paper) !important;
    border: 1px solid var(--border) !important;
    cursor: pointer !important;
    user-select: none !important;
    transition: background 0.15s, border-color 0.15s, color 0.15s !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    color: var(--ink-700) !important;
    margin: 0 !important;
}
.special-action-pill:hover {
    background: var(--paper-line) !important;
    border-color: var(--ink-400) !important;
}
.special-action-pill.is-active,
.special-action-pill:has(input:checked) {
    background: var(--teal-50) !important;
    border-color: var(--teal-600) !important;
    color: var(--ink-900) !important;
}
.special-action-pill .form-check-label {
    font-size: 11px !important;
    font-weight: 500 !important;
    color: inherit !important;
    padding-left: 0 !important;
}
.special-action-pill .form-check-box {
    margin-top: 0 !important;
}
.form-check input:indeterminate ~ .form-check-box .indeterminate-icon {
    display: block !important;
}
.row-count-badge.is-all {
    background: var(--green-100) !important;
    border-color: var(--green-ic-bg) !important;
    color: var(--green-ink) !important;
    font-weight: 600 !important;
}
.row-count-badge.is-partial {
    background: var(--paper-line) !important;
    color: var(--ink-900) !important;
    font-weight: 600 !important;
}
.domain-count-badge.is-all {
    background: var(--green-100) !important;
    border-color: var(--green-ic-bg) !important;
    color: var(--green-ink) !important;
    font-weight: 700 !important;
}
.role-floating-footer {
    position: sticky;
    bottom: 16px;
    z-index: 40;
    margin-top: 24px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 20px;
    box-shadow: var(--shadow-card);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
</style>

{{-- TOP SECTION: Role Info & Selection Overview --}}
<div class="role-top-grid">
    {{-- Left: Role Name & Info --}}
    <div class="panel" style="margin-top:0; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:18px;">
        <div style="margin-bottom:12px;">
            <x-core::input
                size="sm"
                name="name"
                label="রোলের নাম"
                label-en="Role Name"
                icon="shield"
                :value="old('name', $role->name)"
                placeholder="যেমন: ক্যাশিয়ার, ম্যানেজার, হিসাবরক্ষক"
                required
                :readonly="$role->name === 'Admin'"
            />
            @if ($role->name === 'Admin')
                <div style="font-size:12px; color:var(--gold-ink); background:var(--gold-100); padding:6px 10px; border-radius:6px; margin-top:8px; display:flex; align-items:center; gap:6px;">
                    <x-core::icon name="info" size="xs" />
                    <span class="bn">ডিফল্ট এডমিন রোলের নাম পরিবর্তন করা যাবে না, তবে পারমিশন কাস্টমাইজ করতে পারেন।</span>
                    <span class="en" style="display:none;">Default Admin role name cannot be changed, but its permissions can be customized.</span>
                </div>
            @endif
        </div>
        <div style="font-size:12px; color:var(--ink-600); line-height:1.5;">
            <span class="bn">নির্দিষ্ট রোলের ইউজাররা সিস্টেমে কী কী সুবিধা ও মেনু দেখতে পারবে, তা নিচের মডিউলভিত্তিক চেকবক্স থেকে নির্ধারণ করুন।</span>
            <span class="en" style="display:none;">Specify exactly what actions and modules users assigned to this role can access using the matrix below.</span>
        </div>
    </div>

    {{-- Right: Selection Progress Meter --}}
    <div class="panel" style="margin-top:0; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:18px; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:13px; font-weight:600; color:var(--ink-700);">
                    <span class="bn">পারমিশন নির্বাচন অগ্রগতি</span>
                    <span class="en" style="display:none;">Permission Selection</span>
                </span>
                <x-core::badge size="xs" color="primary" id="metric-percent-badge">0%</x-core::badge>
            </div>
            <div style="display:flex; align-items:baseline; gap:6px; margin-bottom:10px;">
                <span id="metric-selected-count" style="font-size:24px; font-weight:700; color:var(--ink-900);">0</span>
                <span style="font-size:14px; color:var(--ink-400);">/</span>
                <span id="metric-total-count" style="font-size:16px; font-weight:600; color:var(--ink-600);">0</span>
                <span style="font-size:12px; color:var(--ink-600); margin-left:4px;">
                    <span class="bn">টি নির্বাচিত</span>
                    <span class="en" style="display:none;">selected</span>
                </span>
            </div>
            <div style="height:8px; background:var(--paper-line); border-radius:999px; overflow:hidden; margin-bottom:8px;">
                <div id="metric-progress-fill" style="height:100%; width:0%; background:var(--primary); transition:width 0.25s ease;"></div>
            </div>
        </div>
        <div style="display:flex; align-items:center; justify-content:space-between; font-size:12px; color:var(--ink-600);">
            <span id="metric-status-hint">
                <span class="bn">নির্বাচন শুরু করুন</span>
                <span class="en" style="display:none;">Start selecting</span>
            </span>
            <span style="font-size:11px; color:var(--ink-400);">
                <span class="bn">মোট {{ count($features) }}টি মডিউল সক্রিয়</span>
                <span class="en" style="display:none;">Total {{ count($features) }} active modules</span>
            </span>
        </div>
    </div>
</div>

{{-- QUICK PRESETS TOOLBAR --}}
<div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:14px 18px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="width:28px; height:28px; border-radius:6px; background:var(--paper); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--gold-600);">
                <x-core::icon name="sparkles" size="sm" />
            </div>
            <div>
                <div style="font-size:13px; font-weight:600; color:var(--ink-900);">
                    <span class="bn">কুইক পারমিশন টেমপ্লেট</span>
                    <span class="en" style="display:none;">Quick Role Presets</span>
                </div>
                <div style="font-size:11px; color:var(--ink-600);">
                    <span class="bn">এক ক্লিকে আপনার দোকানের সাধারণ রোলগুলোর পারমিশন সেট করুন</span>
                    <span class="en" style="display:none;">Quickly configure permissions for common business roles with one click</span>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
        <x-core::button type="button" size="sm" variant="soft" color="teal" class="btn-preset" data-preset="full" icon="shield-check">
            <span class="bn">সম্পূর্ণ এক্সেস (Full)</span>
            <span class="en" style="display:none;">Full Access</span>
        </x-core::button>
        <x-core::button type="button" size="sm" variant="soft" color="blue" class="btn-preset" data-preset="view-only" icon="eye">
            <span class="bn">শুধু দেখা (View Only)</span>
            <span class="en" style="display:none;">View Only</span>
        </x-core::button>
        <x-core::button type="button" size="sm" variant="secondary" class="btn-preset" data-preset="cashier" icon="shopping-cart">
            <span class="bn">ক্যাশিয়ার / বিক্রয়কর্মী</span>
            <span class="en" style="display:none;">Cashier / POS</span>
        </x-core::button>
        <x-core::button type="button" size="sm" variant="secondary" class="btn-preset" data-preset="inventory" icon="package">
            <span class="bn">স্টোরকিপার / ইনভেন্টরি</span>
            <span class="en" style="display:none;">Store / Inventory</span>
        </x-core::button>
        <x-core::button type="button" size="sm" variant="secondary" class="btn-preset" data-preset="accountant" icon="dollar">
            <span class="bn">হিসাবরক্ষক / ফিন্যান্স</span>
            <span class="en" style="display:none;">Accountant / Finance</span>
        </x-core::button>
        <x-core::button type="button" size="sm" variant="ghost" color="danger" class="btn-preset" data-preset="clear" icon="trash-2">
            <span class="bn">সব বাতিল</span>
            <span class="en" style="display:none;">Clear All</span>
        </x-core::button>
    </div>
</div>

{{-- SEARCH & BULK ACTIONS BAR --}}
<div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px 18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">
    {{-- Search --}}
    <div style="flex:1; min-width:240px; max-width:340px;">
        <x-core::input
            size="sm"
            id="module-search-input"
            icon="search"
            placeholder="মডিউল খুঁজুন... (Search module by name)"
            :no-margin="true"
        />
    </div>

    {{-- Global Column Selection Checkboxes --}}
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap; padding:6px 14px; background:var(--paper); border-radius:8px; border:1px solid var(--border);">
        <span style="font-size:12px; font-weight:600; color:var(--ink-700); display:flex; align-items:center; gap:4px;">
            <x-core::icon name="check-circle" size="xs" />
            <span class="bn">কলাম নির্বাচন:</span>
            <span class="en" style="display:none;">Columns:</span>
        </span>
        <x-core::checkbox size="sm" color="primary" class="check-global-col" data-col="view">
            <span class="bn" style="font-size:12px; font-weight:600;">দেখা</span>
            <span class="en" style="display:none; font-size:12px; font-weight:600;">View</span>
        </x-core::checkbox>
        <x-core::checkbox size="sm" color="primary" class="check-global-col" data-col="create">
            <span class="bn" style="font-size:12px; font-weight:600;">তৈরি</span>
            <span class="en" style="display:none; font-size:12px; font-weight:600;">Create</span>
        </x-core::checkbox>
        <x-core::checkbox size="sm" color="primary" class="check-global-col" data-col="edit">
            <span class="bn" style="font-size:12px; font-weight:600;">সম্পাদনা</span>
            <span class="en" style="display:none; font-size:12px; font-weight:600;">Edit</span>
        </x-core::checkbox>
        <x-core::checkbox size="sm" color="primary" class="check-global-col" data-col="delete">
            <span class="bn" style="font-size:12px; font-weight:600;">মুছে ফেলা</span>
            <span class="en" style="display:none; font-size:12px; font-weight:600;">Delete</span>
        </x-core::checkbox>
    </div>

    {{-- Master Select / Deselect --}}
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <x-core::button type="button" size="sm" variant="secondary" id="btn-select-all" icon="check">
            <span class="bn">সব নির্বাচন</span>
            <span class="en" style="display:none;">Select All</span>
        </x-core::button>
        <x-core::button type="button" size="sm" variant="secondary" id="btn-deselect-all" icon="x">
            <span class="bn">সব বাতিল</span>
            <span class="en" style="display:none;">Deselect All</span>
        </x-core::button>
    </div>
</div>

{{-- SEARCH EMPTY STATE --}}
<div id="search-empty-state" style="display:none; text-align:center; padding:36px 20px; background:var(--card); border:1px solid var(--border); border-radius:10px; margin-bottom:20px;">
    <x-core::table.empty
        icon="search"
        title="কোনো মডিউল খুঁজে পাওয়া যায়নি"
        title-en="No matching modules found"
    />
</div>

@if (count($features) === 0)
    <div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:36px 20px; text-align:center;">
        <x-core::table.empty
            icon="shield"
            title="আপনার দোকানের জন্য কোনো ফিচার সক্রিয় নেই"
            title-en="No features are enabled for your shop"
        />
    </div>
@else
    {{-- DOMAIN GROUPS & PERMISSION MATRIX --}}
    @foreach ($groupedFeatures as $domKey => $group)
        <div class="role-domain-card" data-domain-card="{{ $domKey }}">
            <div class="role-domain-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--paper); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--ink-900);">
                        <x-core::icon :name="$group['meta']['icon']" size="md" />
                    </div>
                    <div>
                        <div style="font-size:14px; font-weight:700; color:var(--ink-900); display:flex; align-items:center; gap:8px;">
                            <span class="bn">{{ $group['meta']['title_bn'] }}</span>
                            <span class="en" style="display:none;">{{ $group['meta']['title_en'] }}</span>
                            <x-core::badge size="xs" :color="$group['meta']['badge_color'] ?? 'primary'" variant="subtle">
                                {{ count($group['features']) }}
                            </x-core::badge>
                        </div>
                        <div style="font-size:11px; color:var(--ink-600); margin-top:2px;">
                            <span class="bn">{{ $group['meta']['subtitle_bn'] }}</span>
                            <span class="en" style="display:none;">{{ $group['meta']['subtitle_en'] }}</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="domain-count-badge" id="domain-count-{{ $domKey }}" style="font-size:12px; font-weight:600; color:var(--ink-600); background:var(--paper); padding:4px 8px; border-radius:6px; border:1px solid var(--border);">
                        0 / 0
                    </span>
                    <x-core::button type="button" size="sm" variant="ghost" color="secondary" class="btn-domain-toggle" data-domain="{{ $domKey }}">
                        <span class="bn">সেকশন নির্বাচন</span>
                        <span class="en" style="display:none;">Toggle Section</span>
                    </x-core::button>
                </div>
            </div>

            <div class="table-wrap" style="overflow-x:auto;">
                <table class="data-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                            <th style="padding:8px 10px; text-align:center; width:48px;">
                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:4px;">
                                    <span style="font-size:11px; font-weight:600;"><span class="bn">সব</span><span class="en" style="display:none;">All</span></span>
                                    <x-core::checkbox
                                        size="sm"
                                        color="primary"
                                        class="check-domain-all-rows"
                                        data-domain="{{ $domKey }}"
                                        title="Toggle all rows in this section"
                                    />
                                </div>
                            </th>
                            <th style="padding:10px 16px; text-align:left; font-size:12px; font-weight:600; color:var(--ink-700); min-width:220px;">
                                <span class="bn">ফিচার / মডিউল</span>
                                <span class="en" style="display:none;">Feature / Module</span>
                            </th>
                            @foreach ($standardActions as $colKey => $colLabels)
                                <th style="padding:8px 8px; text-align:center; font-size:12px; font-weight:600; color:var(--ink-700); width:80px;">
                                    <div style="display:inline-flex; flex-direction:column; align-items:center; gap:4px;">
                                        <span>
                                            <span class="bn">{{ $colLabels['bn'] }}</span>
                                            <span class="en" style="display:none;">{{ $colLabels['en'] }}</span>
                                        </span>
                                        <x-core::checkbox
                                            size="sm"
                                            color="primary"
                                            class="check-domain-col"
                                            data-col="{{ $colKey }}"
                                            data-domain="{{ $domKey }}"
                                            title="Toggle all {{ $colLabels['en'] }} in this section"
                                        />
                                    </div>
                                </th>
                            @endforeach
                            <th style="padding:10px 16px; text-align:left; font-size:12px; font-weight:600; color:var(--ink-700); min-width:260px;">
                                <span class="bn">বিশেষ অ্যাকশন ও সুবিধা</span>
                                <span class="en" style="display:none;">Special Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group['features'] as $key => $labels)
                            @php
                                $featureActions = \Modules\Core\Support\Permissions::actionsFor($key);
                                $specialActions = array_diff($featureActions, array_keys($standardActions));
                                $meta = $featureMeta[$key] ?? ['icon' => 'sliders', 'desc_bn' => '', 'desc_en' => ''];
                                $searchHaystack = strtolower($key . ' ' . $labels['bn'] . ' ' . $labels['en'] . ' ' . ($meta['desc_bn'] ?? '') . ' ' . ($meta['desc_en'] ?? ''));
                            @endphp
                            <tr class="module-row" data-module="{{ $key }}" data-search="{{ $searchHaystack }}" style="border-bottom:1px solid var(--border); transition:background 0.15s ease;">
                                <td style="padding:10px 10px; text-align:center; vertical-align:middle; width:48px;">
                                    <div style="display:inline-flex; justify-content:center; align-items:center;">
                                        <x-core::checkbox
                                            size="sm"
                                            color="primary"
                                            class="check-row-master"
                                            data-module="{{ $key }}"
                                            title="এই রোলের সব পারমিশন নির্বাচন করুন / Select all for this row"
                                        />
                                    </div>
                                </td>
                                <td style="padding:12px 16px; vertical-align:middle;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div style="width:30px; height:30px; border-radius:6px; background:var(--paper); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--ink-700); flex-shrink:0;">
                                                <x-core::icon :name="$meta['icon']" size="sm" />
                                            </div>
                                            <div>
                                                <div style="font-size:13px; font-weight:600; color:var(--ink-900);">
                                                    <span class="bn">{{ $labels['bn'] }}</span>
                                                    <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                                                </div>
                                                <div style="font-size:11px; color:var(--ink-400); margin-top:1px;">
                                                    <span class="bn">{{ $meta['desc_bn'] }}</span>
                                                    <span class="en" style="display:none;">{{ $meta['desc_en'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="row-count-badge" data-module="{{ $key }}" style="font-size:11px; color:var(--ink-600); font-weight:500; background:var(--paper); padding:2px 6px; border-radius:4px; border:1px solid var(--border); flex-shrink:0;">
                                            0 / 0
                                        </span>
                                    </div>
                                </td>
                                @foreach (['view', 'create', 'edit', 'delete'] as $act)
                                    <td style="padding:10px 8px; text-align:center; vertical-align:middle;">
                                        @if (in_array($act, $featureActions))
                                            @php
                                                $actPermVal = "{$key}.{$act}";
                                                $isActChecked = in_array($actPermVal, $currentPermissions);
                                            @endphp
                                            <div style="display:inline-flex; justify-content:center;">
                                                <x-core::checkbox
                                                    size="sm"
                                                    color="primary"
                                                    name="permissions[]"
                                                    :value="$actPermVal"
                                                    class="perm-checkbox perm-col-{{ $act }} perm-row-{{ $key }} perm-domain-{{ $domKey }}"
                                                    :checked="$isActChecked"
                                                />
                                            </div>
                                        @else
                                            <span style="color:var(--ink-400); font-size:13px; user-select:none;">&mdash;</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td style="padding:10px 16px; vertical-align:middle;">
                                    @if (count($specialActions) > 0)
                                        <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                                            @foreach ($specialActions as $act)
                                                @php
                                                    $permVal = "{$key}.{$act}";
                                                    $isChecked = in_array($permVal, $currentPermissions);
                                                @endphp
                                                <x-core::checkbox
                                                    size="sm"
                                                    color="primary"
                                                    name="permissions[]"
                                                    value="{{ $permVal }}"
                                                    class="special-action-pill {{ $isChecked ? 'is-active' : '' }}"
                                                    :checked="$isChecked"
                                                >
                                                    <span class="bn">{{ $allActionLabels[$act]['bn'] ?? $act }}</span>
                                                    <span class="en" style="display:none;">{{ $allActionLabels[$act]['en'] ?? $act }}</span>
                                                </x-core::checkbox>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color:var(--ink-400); font-size:12px; user-select:none;">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif

@error('permissions')
    <div class="field-error" style="color:var(--red-600); font-size:12px; margin-top:8px; background:var(--red-100); padding:8px 12px; border-radius:6px; border:1px solid var(--red-ic-bg);">
        {{ $message }}
    </div>
@enderror

{{-- STICKY FLOATING FOOTER ACTION BAR --}}
<div class="role-floating-footer">
    <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size:13px; font-weight:600; color:var(--ink-700);">
            <span class="bn">নির্বাচিত পারমিশন:</span>
            <span class="en" style="display:none;">Selected:</span>
        </span>
        <x-core::badge size="md" color="primary">
            <span id="floating-selected-count">0</span> / <span id="floating-total-count">0</span>
        </x-core::badge>
        <span id="floating-percent" style="font-size:12px; color:var(--ink-600); font-weight:500;">(0%)</span>
    </div>

    <div style="display:flex; align-items:center; gap:10px;">
        <x-core::button tag="a" href="{{ route('roles.index') }}" size="sm" variant="secondary">
            <span class="bn">বাতিল</span>
            <span class="en" style="display:none;">Cancel</span>
        </x-core::button>
        <x-core::button type="submit" size="sm" color="primary" icon="check">
            <span class="bn">{{ $role->exists ? 'হালনাগাদ করুন' : 'রোল সংরক্ষণ করুন' }}</span>
            <span class="en" style="display:none;">{{ $role->exists ? 'Update Role' : 'Save Role' }}</span>
        </x-core::button>
    </div>
</div>

<script>
$(function () {
    var $search = $('#module-search-input');
    var $emptyState = $('#search-empty-state');

    function updateMetrics() {
        var $allInputs = $('input[name="permissions[]"]');
        var total = $allInputs.length;
        var checked = $allInputs.filter(':checked').length;
        var percent = total > 0 ? Math.round((checked / total) * 100) : 0;

        $('#metric-selected-count').text(checked);
        $('#metric-total-count').text(total);
        $('#floating-selected-count').text(checked);
        $('#floating-total-count').text(total);
        $('#floating-percent').text('(' + percent + '%)');

        $('#metric-progress-fill').css('width', percent + '%');
        $('#metric-percent-badge').text(percent + '%');

        var hintBn = 'কাস্টম পারমিশন সক্রিয়';
        var hintEn = 'Custom permissions active';
        if (checked === 0) {
            hintBn = 'কোনো পারমিশন নির্বাচিত নয়';
            hintEn = 'No permissions selected';
        } else if (checked === total) {
            hintBn = 'সকল পারমিশন সক্রিয়';
            hintEn = 'All permissions active';
        }
        $('#metric-status-hint .bn').text(hintBn);
        $('#metric-status-hint .en').text(hintEn);

        // Synchronize Global Column Checkboxes (View, Create, Edit, Delete)
        ['view', 'create', 'edit', 'delete'].forEach(function (col) {
            var $colInputs = $('input[name="permissions[]"]').filter(function () {
                return ($(this).val() || '').endsWith('.' + col);
            });
            var colTotal = $colInputs.length;
            var colChecked = $colInputs.filter(':checked').length;
            var isAll = colTotal > 0 && colChecked === colTotal;
            var isPartial = !isAll && colChecked > 0;
            $('.check-global-col[data-col="' + col + '"]').find('input[type="checkbox"]')
                .prop('checked', isAll)
                .prop('indeterminate', isPartial);
        });

        // Synchronize Domain Cards, Section Checkboxes, and Table Header Column Checkboxes
        $('[data-domain-card]').each(function () {
            var domainKey = $(this).attr('data-domain-card');
            var $card = $(this);
            var $domInputs = $card.find('input[name="permissions[]"]');
            var domTotal = $domInputs.length;
            var domChecked = $domInputs.filter(':checked').length;
            var isDomAll = domTotal > 0 && domChecked === domTotal;
            var isDomPartial = !isDomAll && domChecked > 0;
            var $domBadge = $('#domain-count-' + domainKey);
            $domBadge.text(domChecked + ' / ' + domTotal);
            $domBadge.toggleClass('is-all', isDomAll);

            // Synchronize section all-rows checkbox
            $card.find('.check-domain-all-rows input[type="checkbox"]')
                .prop('checked', isDomAll)
                .prop('indeterminate', isDomPartial);

            ['view', 'create', 'edit', 'delete'].forEach(function (col) {
                var $colInputs = $card.find('input[name="permissions[]"]').filter(function () {
                    return ($(this).val() || '').endsWith('.' + col);
                });
                var cTotal = $colInputs.length;
                var cChecked = $colInputs.filter(':checked').length;
                var isColAll = cTotal > 0 && cChecked === cTotal;
                var isColPartial = !isColAll && cChecked > 0;
                $card.find('.check-domain-col[data-col="' + col + '"]').find('input[type="checkbox"]')
                    .prop('checked', isColAll)
                    .prop('indeterminate', isColPartial);
            });
        });

        // Synchronize Row Master Checkboxes and Row Count Badges
        $('tr.module-row').each(function () {
            var $row = $(this);
            var $rowInputs = $row.find('input[name="permissions[]"]');
            var rTotal = $rowInputs.length;
            var rChecked = $rowInputs.filter(':checked').length;
            var isRowAll = rTotal > 0 && rChecked === rTotal;
            var isRowPartial = !isRowAll && rChecked > 0;
            $row.find('.check-row-master input[type="checkbox"]')
                .prop('checked', isRowAll)
                .prop('indeterminate', isRowPartial);
            var $badge = $row.find('.row-count-badge');
            $badge.text(rChecked + ' / ' + rTotal);
            $badge.toggleClass('is-all', isRowAll).toggleClass('is-partial', isRowPartial);
        });

        $('.special-action-pill').each(function () {
            var isChecked = $(this).find('input[type="checkbox"]').is(':checked');
            $(this).toggleClass('is-active', isChecked);
        });
    }

    $(document).on('change', 'input[name="permissions[]"]', function () {
        updateMetrics();
    });

    // Global Column Checkbox Handler (View, Create, Edit, Delete across all modules)
    $(document).on('change', '.check-global-col input[type="checkbox"]', function () {
        var col = $(this).closest('.check-global-col').attr('data-col');
        var isChecked = $(this).is(':checked');
        var $colInputs = $('input[name="permissions[]"]').filter(function () {
            return ($(this).val() || '').endsWith('.' + col);
        });
        $colInputs.prop('checked', isChecked);
        updateMetrics();
    });

    // Domain Table Header Column Checkbox Handler (View, Create, Edit, Delete in section)
    $(document).on('change', '.check-domain-col input[type="checkbox"]', function () {
        var col = $(this).closest('.check-domain-col').attr('data-col');
        var domain = $(this).closest('.check-domain-col').attr('data-domain');
        var isChecked = $(this).is(':checked');
        var $colInputs = $('[data-domain-card="' + domain + '"]').find('input[name="permissions[]"]').filter(function () {
            return ($(this).val() || '').endsWith('.' + col);
        });
        $colInputs.prop('checked', isChecked);
        updateMetrics();
    });

    // Domain Section All-Rows Checkbox Handler
    $(document).on('change', '.check-domain-all-rows input[type="checkbox"]', function () {
        var domain = $(this).closest('.check-domain-all-rows').attr('data-domain');
        var isChecked = $(this).is(':checked');
        var $domInputs = $('[data-domain-card="' + domain + '"]').find('input[name="permissions[]"]');
        $domInputs.prop('checked', isChecked);
        updateMetrics();
    });

    // Row Master Checkbox Handler (Select all permissions for this specific row)
    $(document).on('change', '.check-row-master input[type="checkbox"]', function () {
        var module = $(this).closest('.check-row-master').attr('data-module');
        var isChecked = $(this).is(':checked');
        var $rowInputs = $('tr.module-row[data-module="' + module + '"]').find('input[name="permissions[]"]');
        $rowInputs.prop('checked', isChecked);
        updateMetrics();
    });

    $(document).on('click', '#btn-select-all', function (e) {
        e.preventDefault();
        $('input[name="permissions[]"]').prop('checked', true);
        updateMetrics();
    });

    $(document).on('click', '#btn-deselect-all', function (e) {
        e.preventDefault();
        $('input[name="permissions[]"]').prop('checked', false);
        updateMetrics();
    });

    $(document).on('click', '.btn-row-toggle', function (e) {
        e.preventDefault();
        var module = $(this).closest('.btn-row-toggle').attr('data-module');
        var $rowInputs = $('tr.module-row[data-module="' + module + '"]').find('input[name="permissions[]"]');
        var allChecked = $rowInputs.length > 0 && $rowInputs.filter(':checked').length === $rowInputs.length;
        $rowInputs.prop('checked', !allChecked);
        updateMetrics();
    });

    $(document).on('click', '.btn-domain-toggle', function (e) {
        e.preventDefault();
        var domain = $(this).closest('.btn-domain-toggle').attr('data-domain');
        var $domInputs = $('[data-domain-card="' + domain + '"]').find('input[name="permissions[]"]');
        var allChecked = $domInputs.length > 0 && $domInputs.filter(':checked').length === $domInputs.length;
        $domInputs.prop('checked', !allChecked);
        updateMetrics();
    });

    var presets = {
        'full': function () {
            $('input[name="permissions[]"]').prop('checked', true);
            updateMetrics();
        },
        'clear': function () {
            $('input[name="permissions[]"]').prop('checked', false);
            updateMetrics();
        },
        'view-only': function () {
            $('input[name="permissions[]"]').prop('checked', false);
            $('input[name="permissions[]"]').filter(function () {
                var val = $(this).val() || '';
                return val.endsWith('.view');
            }).prop('checked', true);
            updateMetrics();
        },
        'cashier': function () {
            $('input[name="permissions[]"]').prop('checked', false);
            var perms = [
                'sales.view', 'sales.create', 'sales.print',
                'quick-sale.view', 'quick-sale.create',
                'customers.view', 'customers.create', 'customers.payment',
                'cashbox.view', 'cashbox.cash-in',
                'stock.view',
                'products.view'
            ];
            $.each(perms, function (i, p) {
                $('input[name="permissions[]"][value="' + p + '"]').prop('checked', true);
            });
            updateMetrics();
        },
        'inventory': function () {
            $('input[name="permissions[]"]').prop('checked', false);
            var perms = [
                'products.view', 'products.create', 'products.edit',
                'stock.view', 'stock.create', 'stock.edit', 'stock.adjust', 'stock.transfer',
                'purchase.view', 'purchase.create', 'purchase.edit', 'purchase.receive', 'purchase.return', 'purchase.print',
                'suppliers.view', 'suppliers.create', 'suppliers.edit',
                'branches.view'
            ];
            $.each(perms, function (i, p) {
                $('input[name="permissions[]"][value="' + p + '"]').prop('checked', true);
            });
            updateMetrics();
        },
        'accountant': function () {
            $('input[name="permissions[]"]').prop('checked', false);
            var perms = [
                'income.view', 'income.create', 'income.edit',
                'expense.view', 'expense.create', 'expense.edit',
                'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.transfer',
                'account-transfers.view', 'account-transfers.create',
                'cashbox.view', 'cashbox.cash-in', 'cashbox.cash-out',
                'tax.view', 'tax.edit',
                'reports.view', 'reports.print'
            ];
            $.each(perms, function (i, p) {
                $('input[name="permissions[]"][value="' + p + '"]').prop('checked', true);
            });
            updateMetrics();
        }
    };

    $(document).on('click', '.btn-preset', function (e) {
        e.preventDefault();
        var preset = $(this).closest('.btn-preset').attr('data-preset') || $(this).data('preset');
        if (presets[preset]) {
            presets[preset]();
        }
    });

    $search.on('input keyup', function () {
        var query = $.trim($(this).val().toLowerCase());
        var visibleDomains = 0;

        $('[data-domain-card]').each(function () {
            var $card = $(this);
            var $rows = $card.find('.module-row');
            var matchedRows = 0;

            $rows.each(function () {
                var $row = $(this);
                var searchData = $row.attr('data-search') || '';
                if (!query || searchData.indexOf(query) !== -1) {
                    $row.show();
                    matchedRows++;
                } else {
                    $row.hide();
                }
            });

            if (matchedRows > 0) {
                $card.show();
                visibleDomains++;
            } else {
                $card.hide();
            }
        });

        if (visibleDomains === 0) {
            $emptyState.show();
        } else {
            $emptyState.hide();
        }
    });

    updateMetrics();
});
</script>
