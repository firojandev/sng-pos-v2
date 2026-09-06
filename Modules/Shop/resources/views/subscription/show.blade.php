<x-core::layout
    title="সাবস্ক্রিপশন ও প্ল্যান"
    title-en="Subscription & Plan"
    subtitle="আপনার দোকানের সক্রিয় প্ল্যান, রিসোর্স সীমা ও ব্যবহার দেখুন"
    subtitle-en="View your shop plan, resource limits, and feature quota"
    active="subscription"
>
    @if ($subscription && ! $subscription->isUsable())
        <div class="panel" style="margin-top:0; max-width:840px; border-color:var(--red-600); background:var(--red-50);">
            <div class="panel-body">
                <div class="badge b-red bn" style="margin-bottom:8px;">সাবস্ক্রিপশন নিষ্ক্রিয় (Inactive)</div>
                <div class="badge b-red en" style="display:none; margin-bottom:8px;">Subscription Inactive</div>
                <p class="bn" style="font-size:13.5px; color:var(--red-800); margin:0;">আপনার সাবস্ক্রিপশন বর্তমানে সক্রিয় নেই বা ট্রায়াল সময় শেষ হয়েছে। অ্যাপের সমস্ত ফিচার আনলক করতে অনুগ্রহ করে প্ল্যান নবায়ন করুন অথবা প্রশাসকের সাথে যোগাযোগ করুন।</p>
                <p class="en" style="display:none; font-size:13.5px; color:var(--red-800); margin:0;">Your subscription is currently inactive or the trial period has ended. Please renew your plan or contact support to continue using all features.</p>
            </div>
        </div>
    @endif

    <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:20px; max-width:1100px; align-items:start;">
        {{-- Left: Current Plan & Resource Limits --}}
        <div style="display:flex; flex-direction:column; gap:20px;">
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="sparkles" size="18" style="color:var(--teal-800);" />
                        <span class="bn">বর্তমান সাবস্ক্রিপশন প্ল্যান</span>
                        <span class="en" style="display:none;">Current Subscription Plan</span>
                    </div>
                </div>
                <div class="panel-body">
                    @if ($subscription && $subscription->plan)
                        @php $label = $subscription->statusLabel(); @endphp
                        <div class="tx-row strong">
                            <span class="lbl bn">প্ল্যান নাম</span>
                            <span class="lbl en" style="display:none;">Plan Name</span>
                            <span class="val" style="color:var(--teal-900); font-weight:800; font-size:15px;">
                                {{ $subscription->plan->name }}
                                <span style="font-size:12px; font-weight:600; color:var(--ink-500);">
                                    (&middot; ৳{{ number_format($subscription->plan->price, 0) }}/{{ $subscription->plan->billing_interval?->value ?? 'মাস' }})
                                </span>
                            </span>
                        </div>
                        <div class="tx-row">
                            <span class="lbl bn">সাবস্ক্রিপশন অবস্থা</span>
                            <span class="lbl en" style="display:none;">Status</span>
                            <span class="val">
                                @if ($subscription->isUsable())
                                    <span class="badge b-green bn">{{ $label['bn'] }}</span>
                                    <span class="badge b-green en" style="display:none;">{{ $label['en'] }}</span>
                                @else
                                    <span class="badge b-red bn">{{ $label['bn'] }}</span>
                                    <span class="badge b-red en" style="display:none;">{{ $label['en'] }}</span>
                                @endif
                            </span>
                        </div>
                        @if ($subscription->onTrial())
                            <div class="tx-row">
                                <span class="lbl bn">ট্রায়াল অবশিষ্ট দিন</span>
                                <span class="lbl en" style="display:none;">Trial Remaining</span>
                                <span class="val" style="color:var(--gold-800); font-weight:700;">
                                    {{ $subscription->trialDaysRemaining() }} দিন ({{ $subscription->trial_ends_at?->format('d M, Y') }})
                                </span>
                            </div>
                        @endif
                        @if ($subscription->ends_at)
                            <div class="tx-row">
                                <span class="lbl bn">মেয়াদ শেষ</span>
                                <span class="lbl en" style="display:none;">Expires On</span>
                                <span class="val" style="font-weight:600;">
                                    {{ $subscription->ends_at->format('d M, Y') }}
                                    <span style="font-size:11.5px; color:var(--ink-500);">({{ $subscription->daysRemaining() }} দিন বাকি)</span>
                                </span>
                            </div>
                        @endif

                        {{-- Quota / Limits --}}
                        <div class="drawer-title" style="font-size:14px; margin:22px 0 12px; color:var(--teal-800); font-weight:700;">
                            <span class="bn">রিসোর্স কোটা ও ব্যবহার (Quota Limits)</span>
                            <span class="en" style="display:none;">Resource Quotas & Usage</span>
                        </div>

                        @php
                            $quotaItems = [
                                'users' => ['bn' => 'ইউজার / স্টাফ', 'en' => 'Users / Staff', 'icon' => 'user'],
                                'branches' => ['bn' => 'দোকানের শাখা', 'en' => 'Branches', 'icon' => 'building'],
                                'warehouses' => ['bn' => 'গুদাম / ওয়্যারহাউজ', 'en' => 'Warehouses', 'icon' => 'warehouse'],
                                'products' => ['bn' => 'পণ্য সংখ্যা', 'en' => 'Products', 'icon' => 'box'],
                            ];
                        @endphp

                        <div style="display:flex; flex-direction:column; gap:12px;">
                            @foreach ($quotaItems as $key => $meta)
                                @php
                                    $used = $usage[$key] ?? 0;
                                    $isUnlimited = $shop->isUnlimitedUsage($key);
                                    $limit = $isUnlimited ? null : ($shop->subscription()?->getPlan()?->features()->where('slug', $key)->first()?->pivot?->value ?? $subscription->plan->{'max_' . $key} ?? null);
                                    $percent = ($limit && $limit > 0) ? min(100, round(($used / $limit) * 100)) : ($isUnlimited ? 15 : 0);
                                @endphp
                                <div style="background:var(--paper); padding:10px 14px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px;">
                                        <span style="font-weight:700; color:var(--ink-800); display:flex; align-items:center; gap:6px;">
                                            <x-core::icon :name="$meta['icon']" size="14" style="color:var(--teal-700);" />
                                            <span class="bn">{{ $meta['bn'] }}</span>
                                            <span class="en" style="display:none;">{{ $meta['en'] }}</span>
                                        </span>
                                        <span style="font-weight:800; color:{{ $percent >= 90 ? 'var(--red-600)' : 'var(--teal-800)' }};">
                                            {{ $used }} / {{ $isUnlimited ? '∞ (সীমাহীন)' : $limit }}
                                        </span>
                                    </div>
                                    @if (! $isUnlimited && $limit)
                                        <div style="width:100%; height:6px; background:var(--border); border-radius:99px; overflow:hidden;">
                                            <div style="width:{{ $percent }}%; height:100%; background:{{ $percent >= 90 ? 'var(--red-600)' : 'var(--teal-700)' }}; border-radius:99px; transition:width .3s ease;"></div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="helper" style="margin-top:0;">
                            <span class="bn">কোনো সাবস্ক্রিপশন তথ্য পাওয়া যায়নি।</span>
                            <span class="en" style="display:none;">No subscription information available.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payment History --}}
            @if ($subscription && $subscription->payments->isNotEmpty())
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="cash" size="18" style="color:var(--teal-800);" />
                            <span class="bn">পেমেন্ট ইতিহাস (Payment History)</span>
                            <span class="en" style="display:none;">Payment History</span>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <x-core::table variant="flush" size="sm">
                            <x-slot:header>
                                <x-core::table.th>তারিখ</x-core::table.th>
                                <x-core::table.th>মেথড</x-core::table.th>
                                <x-core::table.th align="right">পরিমাণ</x-core::table.th>
                            </x-slot:header>
                            @foreach ($subscription->payments as $payment)
                                <x-core::table.tr>
                                    <x-core::table.td>{{ $payment->paid_at->format('d M, Y') }}</x-core::table.td>
                                    <x-core::table.td muted>{{ ucfirst($payment->method ?? 'Cash') }}</x-core::table.td>
                                    <x-core::table.td align="right" bold>৳ {{ number_format($payment->amount, 2) }}</x-core::table.td>
                                </x-core::table.tr>
                            @endforeach
                        </x-core::table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: Features & Modules Included in Plan --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                    <x-core::icon name="shield" size="18" style="color:var(--teal-800);" />
                    <span class="bn">প্ল্যানে অন্তর্ভুক্ত ফিচারসমূহ</span>
                    <span class="en" style="display:none;">Features & Modules Included</span>
                </div>
            </div>
            <div class="panel-body">
                <p style="font-size:12px; color:var(--ink-500); margin-top:0; margin-bottom:14px;">
                    আপনার বর্তমান প্ল্যানে অনুমোদিত সমস্ত মডিউল ও পারমিশন নিচে তালিকাভুক্ত করা হয়েছে:
                </p>

                <div style="display:grid; grid-template-columns:1fr; gap:8px;">
                    @php
                        $featureNames = [
                            'sales' => ['bn' => 'বিক্রয় ও ইনভয়েস', 'en' => 'Sales & Invoicing'],
                            'purchase' => ['bn' => 'ক্রয় ব্যবস্থাপনা', 'en' => 'Purchase Management'],
                            'cashbox' => ['bn' => 'ক্যাশবক্স ও লেনদেন খাতা', 'en' => 'Cashbox & Drawer'],
                            'quick-sale' => ['bn' => 'দ্রুত বেচা (POS Checkout)', 'en' => 'Quick Sale POS'],
                            'stock' => ['bn' => 'রিয়েলটাইম স্টক ট্র্যাকিং', 'en' => 'Realtime Stock'],
                            'customers' => ['bn' => 'গ্রাহক ও বাকি খাতা', 'en' => 'Customer Due Ledger'],
                            'suppliers' => ['bn' => 'সরবরাহকারী তালিকা', 'en' => 'Supplier Directory'],
                            'income' => ['bn' => 'অন্যান্য আয় ব্যবস্থাপনা', 'en' => 'Income Tracking'],
                            'expense' => ['bn' => 'দৈনন্দিন ব্যয় ট্র্যাকিং', 'en' => 'Expense Tracking'],
                            'tax' => ['bn' => 'ট্যাক্স ও ভ্যাট হিসাব', 'en' => 'Tax & VAT Calculations'],
                            'reports' => ['bn' => 'অ্যানালিটিক্স ও রিপোর্টস', 'en' => 'Reports & Analytics'],
                            'audit' => ['bn' => 'ইউজার অ্যাক্টিভিটি অডিট লগ', 'en' => 'Audit Activity Trail'],
                            'employees' => ['bn' => 'কর্মচারী ও বেতন', 'en' => 'Employees & Payroll'],
                        ];
                    @endphp

                    @foreach ($featureNames as $slug => $labels)
                        @php $hasAccess = $shop ? $shop->hasFeature($slug) : false; @endphp
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; border-radius:var(--radius-sm); background:{{ $hasAccess ? 'var(--paper)' : 'rgba(0,0,0,.02)' }}; border:1px solid {{ $hasAccess ? 'var(--border)' : 'transparent' }}; opacity:{{ $hasAccess ? '1' : '.5' }};">
                            <span style="font-size:12.5px; font-weight:600; color:var(--ink-800); display:flex; align-items:center; gap:8px;">
                                @if ($hasAccess)
                                    <x-core::icon name="check-circle" size="16" style="color:var(--teal-700);" />
                                @else
                                    <x-core::icon name="x-circle" size="16" style="color:var(--ink-400);" />
                                @endif
                                <span class="bn">{{ $labels['bn'] }}</span>
                                <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                            </span>
                            @if ($hasAccess)
                                <span class="badge b-teal" style="font-size:10.5px; padding:2px 6px;">সক্রিয় (Active)</span>
                            @else
                                <span class="badge b-dark" style="font-size:10.5px; padding:2px 6px; opacity:.7;">লকড (Locked)</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-core::layout>
