<x-core::layout
    title="আর্থিক অবস্থান"
    title-en="Financial Position"
    subtitle="বর্তমান সম্পদ, দায় ও নিট আর্থিক অবস্থার বিশ্লেষণাত্মক সারসংক্ষেপ"
    subtitle-en="A management-friendly summary of assets, liabilities, and net financial position"
    active="report-financial-position"
>
    <x-report::tabbar active="financial-position" />

    @php
        $authUser = auth()->user();
        $authShop = $authUser?->shop;
        $isSolvent = $netPosition >= 0;
        $isBalanced = $isBalanced ?? ($variance < 0.01);
    @endphp

    <div class="report-printable-area">
        @include('report::partials._snapshot-print-header', [
            'reportTitle' => 'আর্থিক অবস্থান রিপোর্ট (Financial Position)',
            'reportTitleEn' => 'Financial Position Report',
            'printPermission' => 'report-financial-position.print',
        ])

        @unless ($asOf->isToday())
            <div class="fin-notice-bar" style="margin-bottom:16px; padding:10px 14px; border-radius:8px; background:var(--gold-100); color:var(--gold-ink); font-size:12px; display:flex; align-items:center; gap:8px;">
                <x-core::icon name="info" :size="16" />
                <div>
                    <span class="bn">দ্রষ্টব্য: ধার, দেনা, জামানত, গ্রাহকের পাওনা ও সরবরাহকারীর দেনা তাদের বর্তমান অবস্থা অনুযায়ী দেখানো হয়েছে, নির্বাচিত তারিখের প্রকৃত অবস্থা নয় — এই আইটেমগুলোর ইতিহাস রক্ষণাবেক্ষণ করা হয় না।</span>
                    <span class="en" style="display:none;">Note: Lend, Debts, Security Money, Customer Receivable, and Supplier Due reflect their current recorded status, not necessarily their exact status on the selected date — status history isn't tracked for these items.</span>
                </div>
            </div>
        @endunless

        @if (! $isBalanced)
            <div class="fin-alert-bar" style="margin-bottom:16px; padding:12px 16px; border-radius:8px; background:var(--red-100); color:var(--red-600); font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px;">
                <x-core::icon name="alert-triangle" :size="18" />
                <div>
                    <span class="bn">হিসাবের অমিল সতর্কতা: মোট সম্পদ এবং (মোট দায় + নিট অবস্থান)-এর মধ্যে ৳{{ number_format($variance, 2) }} ব্যবধান সনাক্ত হয়েছে।</span>
                    <span class="en" style="display:none;">Accounting Discrepancy Warning: A variance of ৳{{ number_format($variance, 2) }} was detected between Total Assets and (Total Liabilities + Net Position).</span>
                </div>
            </div>
        @endif

        {{-- Executive KPI Stat Cards --}}
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="layers"
                color="green"
                value-color="green"
                :value="'৳' . number_format($totalAssets, 2)"
                label="সর্বমোট সম্পদ"
                label-en="Total Assets"
                subtext="চলতি ও স্থায়ী সম্পদ"
                subtext-en="Current & fixed assets"
            />
            <x-core::stat-card
                icon="alert-circle"
                color="red"
                value-color="red"
                :value="'৳' . number_format($totalLiabilities, 2)"
                label="সর্বমোট দায় ও দেনা"
                label-en="Total Liabilities"
                subtext="বকেয়া দেনা, ঋণ ও জামানত"
                subtext-en="Payables, debts & deposits"
            />
            <x-core::stat-card
                icon="scale"
                :color="$isSolvent ? 'teal' : 'red'"
                :value-color="$isSolvent ? 'teal' : 'red'"
                :value="'৳' . number_format($netPosition, 2)"
                label="নিট অবস্থান"
                label-en="Net Position"
                :subtext="$isSolvent ? 'উদ্বৃত্ত সম্পদ' : 'ঘাটতি'"
                :subtext-en="$isSolvent ? 'Net Surplus' : 'Net Deficit'"
            />
            <x-core::stat-card
                icon="shield-check"
                :color="$isSolvent ? 'teal' : 'gold'"
                :value-color="$isSolvent ? 'teal' : 'gold'"
                :value="$solvencyMultiple !== null ? $solvencyMultiple . 'x' : ($totalAssets > 0 ? 'ঋণমুক্ত (Debt-free)' : '০x')"
                label="দায় পরিশোধ সক্ষমতা"
                label-en="Solvency Coverage"
                :subtext="$solvencyRatio !== null ? 'কভারেজ: ' . $solvencyRatio . '%' : 'দায় নেই'"
                :subtext-en="$solvencyRatio !== null ? 'Coverage: ' . $solvencyRatio . '%' : 'No Debt'"
            />
        </div>

        {{-- Two-Column Side-by-Side Breakdown: Assets vs Liabilities --}}
        <div class="fin-snapshot-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start; margin-bottom:16px;">
            {{-- Left Column: Assets Breakdown --}}
            <div class="table-container table-teal">
                <div class="panel-head" style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <div style="font-weight:700; font-size:14px; color:var(--ink-900); display:flex; align-items:center; gap:6px;">
                        <span class="bn">সম্পদ বিবরণী</span>
                        <span class="en" style="display:none;">Assets</span>
                    </div>
                    <div style="flex-shrink:0;">
                        <x-core::badge color="green" size="sm">
                            ৳{{ number_format($totalAssets, 2) }}
                        </x-core::badge>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="app-table">
                        <colgroup>
                            <col class="col-name" style="width:60%;">
                            <col class="col-amount" style="width:40%;">
                        </colgroup>
                        <tbody>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">ক্যাশ ও ব্যাংক ব্যালেন্স</span>
                                    <span class="en" style="display:none;">Cash & Bank Balance</span>
                                    @if ($authShop?->hasFeature('accounts') && $authUser?->can('accounts.view'))
                                        <a href="{{ route('accounts.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="হিসাব তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($cashAndBank, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">মজুদ পণ্যের মূল্য</span>
                                    <span class="en" style="display:none;">Inventory / Stock Value</span>
                                    @if ($authShop?->hasFeature('report-stock') && $authUser?->can('report-stock.view'))
                                        <a href="{{ route('reports.stock') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="স্টক রিপোর্ট">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($stockValue, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">গ্রাহকের কাছে পাওনা</span>
                                    <span class="en" style="display:none;">Customer Receivable</span>
                                    @if ($authShop?->hasFeature('customers') && $authUser?->can('customers.view'))
                                        <a href="{{ route('customers.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="গ্রাহক তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($receivable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">প্রদত্ত ঋণ / ধার (বকেয়া)</span>
                                    <span class="en" style="display:none;">Loan Given / Loan Receivable</span>
                                    @if ($authShop?->hasFeature('lend') && $authUser?->can('lend.view'))
                                        <a href="{{ route('lend.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="ধার তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($lendOutstanding, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">প্রদত্ত জামানত (ফেরতযোগ্য)</span>
                                    <span class="en" style="display:none;">Security Money Paid</span>
                                    @if ($authShop?->hasFeature('security-money') && $authUser?->can('security-money.view'))
                                        <a href="{{ route('security-money.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="জামানত তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($securityMoneyPaid, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">স্থায়ী সম্পদ (নেট বুক ভ্যালু)</span>
                                    <span class="en" style="display:none;">Fixed Assets (Net Value)</span>
                                    @if ($authShop?->hasFeature('assets') && $authUser?->can('assets.view'))
                                        <a href="{{ route('assets.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="সম্পদ তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($fixedAssets, 2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid var(--border); background:var(--paper-line);">
                                <td class="cell-main" style="font-weight:800; font-size:14px; color:var(--ink-900);">
                                    <span class="bn">সর্বমোট সম্পদ (Total Assets)</span>
                                    <span class="en" style="display:none;">Total Assets</span>
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:15px; color:var(--green-ink, #059669); white-space:nowrap;">
                                    ৳{{ number_format($totalAssets, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right Column: Liabilities Breakdown --}}
            <div class="table-container table-teal">
                <div class="panel-head" style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <div style="font-weight:700; font-size:14px; color:var(--ink-900); display:flex; align-items:center; gap:6px;">
                        <span class="bn">দায় ও দেনা বিবরণী</span>
                        <span class="en" style="display:none;">Liabilities & Dues</span>
                    </div>
                    <div style="flex-shrink:0;">
                        <x-core::badge color="danger" size="sm">
                            ৳{{ number_format($totalLiabilities, 2) }}
                        </x-core::badge>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="app-table">
                        <colgroup>
                            <col class="col-name" style="width:60%;">
                            <col class="col-amount" style="width:40%;">
                        </colgroup>
                        <tbody>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">সরবরাহকারীকে দেনা</span>
                                    <span class="en" style="display:none;">Supplier Payable</span>
                                    @if ($authShop?->hasFeature('suppliers') && $authUser?->can('suppliers.view'))
                                        <a href="{{ route('suppliers.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="সরবরাহকারী তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($payable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">ঋণ ও দেনা (অপরিশোধিত)</span>
                                    <span class="en" style="display:none;">Debts / Loans Payable</span>
                                    @if ($authShop?->hasFeature('debts') && $authUser?->can('debts.view'))
                                        <a href="{{ route('debts.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="দেনা তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($debtsPayable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">গৃহীত জামানত (ফেরতযোগ্য)</span>
                                    <span class="en" style="display:none;">Security Money Received</span>
                                    @if ($authShop?->hasFeature('security-money') && $authUser?->can('security-money.view'))
                                        <a href="{{ route('security-money.index') }}" class="no-print" style="margin-left:6px; color:var(--ink-400); text-decoration:none;" title="জামানত তালিকা">
                                            <x-core::icon name="external-link" :size="12" />
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($securityMoneyReceived, 2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid var(--border); background:var(--paper-line);">
                                <td class="cell-main" style="font-weight:800; font-size:14px; color:var(--ink-900);">
                                    <span class="bn">সর্বমোট দায় (Total Liabilities)</span>
                                    <span class="en" style="display:none;">Total Liabilities</span>
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:15px; color:var(--red-ink, #dc2626); white-space:nowrap;">
                                    ৳{{ number_format($totalLiabilities, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Management Financial Position Analysis & Equations Card --}}
        <div class="table-container fin-analysis-card" style="padding:16px 20px; margin-bottom:16px; background:var(--card); border:1px solid var(--border); border-radius:8px;">
            <div class="fin-analysis-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="fin-analysis-icon" style="width:38px; height:38px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:{{ $isSolvent ? 'var(--green-100)' : 'var(--red-100)' }}; color:{{ $isSolvent ? 'var(--green-ink, #059669)' : 'var(--red-ink, #dc2626)' }}; flex-shrink:0;">
                        <x-core::icon :name="$isSolvent ? 'shield-check' : 'alert-triangle'" :size="22" />
                    </div>
                    <div>
                        <div class="fin-analysis-title" style="font-size:15.5px; font-weight:800; color:var(--ink-900);">
                            <span class="bn">ব্যবস্থাপনা আর্থিক সমীকরণ ও বিশ্লেষণ (Financial Position Equations)</span>
                            <span class="en" style="display:none;">Management Financial Position Equations</span>
                        </div>
                        <div class="fin-analysis-sub" style="font-size:11.5px; color:var(--ink-600); margin-top:2px;">
                            <span class="bn">ব্যবসায়ের নিট আর্থিক অবস্থান এবং ভারসাম্য সমীকরণ সারসংক্ষেপ</span>
                            <span class="en" style="display:none;">Executive summary of net financial standing and balancing identities</span>
                        </div>
                    </div>
                </div>

                <div class="fin-net-badge" style="text-align:right;">
                    <div style="font-size:11.5px; color:var(--ink-500); font-weight:600;">
                        <span class="bn">নিট আর্থিক স্থিতি</span>
                        <span class="en" style="display:none;">Net Financial Position</span>
                    </div>
                    <div class="fin-net-val" style="font-size:22px; font-weight:800; color:{{ $isSolvent ? 'var(--green-ink, #059669)' : 'var(--red-ink, #dc2626)' }}; line-height:1.2;">
                        ৳{{ number_format($netPosition, 2) }}
                    </div>
                </div>
            </div>

            {{-- Dual Equations Container --}}
            <div class="fin-equations-container" style="display:flex; flex-direction:column; gap:8px; margin-bottom:12px;">
                {{-- Equation 1: Assets - Liabilities = Net Position --}}
                <div class="fin-equation-box" style="background:var(--paper); padding:10px 14px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; font-size:12.5px; font-weight:700;">
                    <span style="color:var(--ink-500); font-size:11px; margin-right:4px;">
                        <span class="bn">সমীকরণ ১:</span><span class="en" style="display:none;">Eq 1:</span>
                    </span>
                    <div style="color:var(--green-ink, #059669); display:flex; align-items:center; gap:4px;">
                        <span><span class="bn">মোট সম্পদ</span><span class="en" style="display:none;">Total Assets</span> (৳{{ number_format($totalAssets, 2) }})</span>
                    </div>
                    <div style="color:var(--ink-400); font-size:15px; font-weight:800;">&minus;</div>
                    <div style="color:var(--red-ink, #dc2626); display:flex; align-items:center; gap:4px;">
                        <span><span class="bn">মোট দায়</span><span class="en" style="display:none;">Total Liabilities</span> (৳{{ number_format($totalLiabilities, 2) }})</span>
                    </div>
                    <div style="color:var(--ink-400); font-size:15px; font-weight:800;">=</div>
                    <div style="color:{{ $isSolvent ? 'var(--green-ink, #059669)' : 'var(--red-ink, #dc2626)' }}; display:flex; align-items:center; gap:4px;">
                        <span><span class="bn">নিট অবস্থান</span><span class="en" style="display:none;">Net Position</span> (৳{{ number_format($netPosition, 2) }})</span>
                    </div>
                </div>

                {{-- Equation 2: Liabilities + Net Position = Assets --}}
                <div class="fin-equation-box" style="background:var(--paper); padding:10px 14px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; font-size:12.5px; font-weight:700;">
                    <span style="color:var(--ink-500); font-size:11px; margin-right:4px;">
                        <span class="bn">সমীকরণ ২:</span><span class="en" style="display:none;">Eq 2:</span>
                    </span>
                    <div style="color:var(--red-ink, #dc2626); display:flex; align-items:center; gap:4px;">
                        <span><span class="bn">মোট দায়</span><span class="en" style="display:none;">Total Liabilities</span> (৳{{ number_format($totalLiabilities, 2) }})</span>
                    </div>
                    <div style="color:var(--ink-400); font-size:15px; font-weight:800;">+</div>
                    <div style="color:{{ $isSolvent ? 'var(--green-ink, #059669)' : 'var(--red-ink, #dc2626)' }}; display:flex; align-items:center; gap:4px;">
                        <span><span class="bn">নিট অবস্থান</span><span class="en" style="display:none;">Net Position</span> (৳{{ number_format($netPosition, 2) }})</span>
                    </div>
                    <div style="color:var(--ink-400); font-size:15px; font-weight:800;">=</div>
                    <div style="color:var(--green-ink, #059669); display:flex; align-items:center; gap:4px;">
                        <span><span class="bn">মোট সম্পদ</span><span class="en" style="display:none;">Total Assets</span> (৳{{ number_format($totalAssets, 2) }})</span>
                    </div>
                </div>
            </div>

            {{-- Solvency Coverage & Management Commentary --}}
            <div class="fin-commentary-box" style="background:var(--paper); padding:10px 14px; border-radius:8px; border:1px solid var(--border); font-size:12px; color:var(--ink-700); line-height:1.6;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px; font-weight:700; color:var(--ink-900);">
                    <x-core::icon name="info" :size="15" style="color:var(--teal-800);" />
                    <span class="bn">দায় পরিশোধ সক্ষমতা বিশ্লেষণ (Solvency Coverage):</span>
                    <span class="en" style="display:none;">Solvency Coverage Analysis:</span>
                    <x-core::badge color="teal" size="sm">
                        {{ $solvencyMultiple !== null ? $solvencyMultiple . 'x' : 'ঋণমুক্ত (Debt-free)' }}
                        @if ($solvencyRatio !== null) ({{ $solvencyRatio }}%) @endif
                    </x-core::badge>
                </div>
                <div style="padding-left:23px;">
                    @if ($totalLiabilities > 0)
                        <span class="bn">
                            সূত্র: <b>মোট সম্পদ (৳{{ number_format($totalAssets, 2) }}) &divide; মোট দায় (৳{{ number_format($totalLiabilities, 2) }}) = {{ $solvencyMultiple }}x</b>।
                            অর্থাৎ আপনার ব্যবসায়ে প্রতি <b>৳১.০০ দায়ের বিপরীতে ৳{{ $solvencyMultiple }} টাকার সম্পদ</b> বিদ্যমান রয়েছে।
                        </span>
                        <span class="en" style="display:none;">
                            Formula: <b>Total Assets (৳{{ number_format($totalAssets, 2) }}) &divide; Total Liabilities (৳{{ number_format($totalLiabilities, 2) }}) = {{ $solvencyMultiple }}x</b>.
                            This means the business holds <b>৳{{ $solvencyMultiple }} in assets for every ৳1.00 of liabilities</b>.
                        </span>
                    @else
                        <span class="bn">প্রতিষ্ঠানের কোনো বকেয়া ঋণ বা দেনা নেই। ব্যবসায় শতভাগ দায়মুক্ত।</span>
                        <span class="en" style="display:none;">The business has no outstanding debts or liabilities (100% debt-free).</span>
                    @endif
                </div>
                <div style="padding-left:23px; margin-top:4px;">
                    @if ($isSolvent)
                        <span class="bn">💡 <b>সারসংক্ষেপ:</b> ব্যবসায় বিনিয়োগকৃত মূলধন ও মুনাফা নিরাপদ। সামগ্রিক দায় ও দেনা পরিশোধের পূর্ণ আর্থিক সক্ষমতা বজায় রয়েছে।</span>
                        <span class="en" style="display:none;">💡 <b>Summary:</b> Capital and profit reserves are secure. Full solvency and debt-coverage capabilities are maintained.</span>
                    @else
                        <span class="bn">⚠️ <b>সারসংক্ষেপ:</b> বর্তমানে মোট দায়ের পরিমাণ সম্পদের চেয়ে <b>৳{{ number_format(abs($netPosition), 2) }} বেশি (ঘাটতি)</b>। সরবরাহকারীর দেনা ও ঋণ পরিশোধের জন্য কার্যকর নগদ প্রবাহ নিশ্চিত করার পরামর্শ দেওয়া হচ্ছে।</span>
                        <span class="en" style="display:none;">⚠️ <b>Summary:</b> Total liabilities exceed assets by <b>৳{{ number_format(abs($netPosition), 2) }} (deficit)</b>. Managing operational cash flow and addressing debt obligations is recommended.</span>
                    @endif
                </div>
            </div>
        </div>

        @can('report-financial-position.print')
        <div class="report-print-footer" style="display:none;">
            <div>
                <span class="bn">এটি একটি কম্পিউটার প্রস্তুতকৃত রিপোর্ট &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
                <span class="en" style="display:none;">Computer generated report &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
            </div>
            <div>
                <span class="bn">মুদ্রণ সময়: {{ now()->format('d M Y, h:i A') }}</span>
                <span class="en" style="display:none;">Printed: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        @endcan
    </div>

    @push('styles')
    <style>
    @media (max-width: 768px) {
        .fin-snapshot-grid {
            grid-template-columns: 1fr !important;
        }
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm 8mm 8mm 8mm;
        }

        /* Notices & Alerts in Print */
        .fin-notice-bar,
        .fin-alert-bar {
            margin-bottom: 6px !important;
            padding: 4px 8px !important;
            font-size: 8.5px !important;
            line-height: 1.3 !important;
            border-radius: 4px !important;
            page-break-inside: avoid !important;
        }
        .fin-notice-bar svg,
        .fin-alert-bar svg {
            width: 12px !important;
            height: 12px !important;
        }

        /* Stat Cards Row */
        .stat-grid {
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            gap: 6px !important;
            margin-bottom: 8px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            page-break-inside: avoid !important;
        }
        .stat-grid > .stat-card {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            padding: 4px 6px !important;
            gap: 4px !important;
            border: 1px solid #cbd5e1 !important;
            background: #f8fafc !important;
            border-radius: 4px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            page-break-inside: avoid !important;
        }
        .stat-grid .stat-card .ic {
            width: 22px !important;
            height: 22px !important;
            min-width: 22px !important;
            max-width: 22px !important;
            border-radius: 4px !important;
        }
        .stat-grid .stat-card .ic svg {
            width: 12px !important;
            height: 12px !important;
        }
        .stat-grid .stat-card .val {
            font-size: 11px !important;
            line-height: 1.15 !important;
        }
        .stat-grid .stat-card .lbl {
            font-size: 8px !important;
            line-height: 1.15 !important;
        }
        .stat-grid .stat-card .stat-card-subtext {
            font-size: 7.5px !important;
            line-height: 1.15 !important;
        }

        /* Two-Column Side-by-Side Snapshot: Assets vs Liabilities & Dues */
        .fin-snapshot-grid {
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            gap: 8px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            margin-bottom: 8px !important;
            page-break-inside: avoid !important;
        }
        .fin-snapshot-grid > .table-container {
            flex: 1 1 0 !important;
            width: 50% !important;
            max-width: 50% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            border-radius: 4px !important;
            overflow: visible !important;
        }
        .fin-snapshot-grid .panel-head {
            padding: 4px 8px !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            font-size: 11px !important;
        }
        .fin-snapshot-grid .panel-head .app-badge {
            font-size: 9px !important;
            padding: 1px 5px !important;
        }
        .fin-snapshot-grid .table-responsive {
            flex: 1 !important;
            overflow: visible !important;
            width: 100% !important;
        }
        .fin-snapshot-grid .app-table {
            width: 100% !important;
            table-layout: auto !important;
            font-size: 9px !important;
            border-collapse: collapse !important;
            box-sizing: border-box !important;
        }
        .fin-snapshot-grid .app-table col.col-name,
        .fin-snapshot-grid .app-table td:first-child:not([colspan]) {
            width: 60% !important;
            max-width: 60% !important;
            box-sizing: border-box !important;
            word-break: break-word !important;
            padding: 3px 6px !important;
        }
        .fin-snapshot-grid .app-table col.col-amount,
        .fin-snapshot-grid .app-table td:last-child:not([colspan]) {
            width: 40% !important;
            max-width: 40% !important;
            text-align: right !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
            padding: 3px 6px !important;
        }
        .fin-snapshot-grid .app-table td {
            border: 1px solid #e2e8f0 !important;
            padding: 3px 6px !important;
            line-height: 1.25 !important;
            box-sizing: border-box !important;
        }
        .fin-snapshot-grid .app-table tr:last-child td {
            font-size: 10px !important;
            padding: 4px 6px !important;
            background: #f1f5f9 !important;
            border-top: 2px solid #cbd5e1 !important;
        }

        /* Management Financial Position Analysis & Equations Card */
        .fin-analysis-card {
            padding: 8px 10px !important;
            margin-bottom: 6px !important;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
        }
        .fin-analysis-header {
            margin-bottom: 6px !important;
            gap: 6px !important;
        }
        .fin-analysis-icon {
            width: 24px !important;
            height: 24px !important;
            border-radius: 4px !important;
        }
        .fin-analysis-icon svg {
            width: 14px !important;
            height: 14px !important;
        }
        .fin-analysis-title {
            font-size: 11.5px !important;
            line-height: 1.2 !important;
        }
        .fin-analysis-sub {
            font-size: 8px !important;
            margin-top: 1px !important;
        }
        .fin-net-val {
            font-size: 14px !important;
            line-height: 1.15 !important;
        }
        .fin-equations-container {
            display: flex !important;
            flex-direction: row !important;
            gap: 6px !important;
            margin-bottom: 6px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .fin-equation-box {
            flex: 1 1 0 !important;
            width: 50% !important;
            padding: 4px 6px !important;
            font-size: 8.5px !important;
            line-height: 1.2 !important;
            gap: 4px !important;
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            border-radius: 4px !important;
            justify-content: center !important;
            white-space: normal !important;
            box-sizing: border-box !important;
        }
        .fin-commentary-box {
            padding: 5px 8px !important;
            font-size: 8.5px !important;
            line-height: 1.35 !important;
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 4px !important;
        }
        .fin-commentary-box svg {
            width: 12px !important;
            height: 12px !important;
        }
        .fin-commentary-box .app-badge {
            font-size: 8px !important;
            padding: 1px 4px !important;
        }

        /* Print Footer */
        .report-print-footer {
            margin-top: 8px !important;
            padding-top: 6px !important;
            font-size: 8px !important;
            page-break-inside: avoid !important;
        }
    }
    </style>
    @endpush
</x-core::layout>
