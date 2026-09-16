<x-core::layout
    title="ব্যালেন্স শীট"
    title-en="Balance Sheet"
    subtitle="সম্পদ, দায় ও হিসাবকৃত মূলধনের আনুষ্ঠানিক বিবরণী"
    subtitle-en="A formal financial statement of assets, liabilities, and calculated equity"
    active="report-balance-sheet"
>
    <x-report::tabbar active="balance-sheet" />

    @php
        $isBalanced = $isBalanced ?? ($variance < 0.01);
    @endphp

    <div class="report-printable-area">
        @include('report::partials._snapshot-print-header', [
            'reportTitle' => 'ব্যালেন্স শীট (Balance Sheet)',
            'reportTitleEn' => 'Balance Sheet Report',
            'printPermission' => 'report-balance-sheet.print',
        ])

        @unless ($asOf->isToday())
            <div style="margin-bottom:16px; padding:10px 14px; border-radius:8px; background:var(--gold-100); color:var(--gold-ink); font-size:12px; display:flex; align-items:center; gap:8px;">
                <x-core::icon name="info" :size="16" />
                <div>
                    <span class="bn">দ্রষ্টব্য: ধার, দেনা, জামানত, গ্রাহকের পাওনা ও সরবরাহকারীর দেনা তাদের বর্তমান অবস্থা অনুযায়ী দেখানো হয়েছে, নির্বাচিত তারিখের প্রকৃত অবস্থা নয় — এই আইটেমগুলোর ইতিহাস রক্ষণাবেক্ষণ করা হয় না।</span>
                    <span class="en" style="display:none;">Note: Lend, Debts, Security Money, Customer Receivable, and Supplier Due reflect their current recorded status, not necessarily their exact status on the selected date — status history isn't tracked for these items.</span>
                </div>
            </div>
        @endunless

        @if (! $isBalanced)
            <div style="margin-bottom:16px; padding:12px 16px; border-radius:8px; background:var(--red-100); color:var(--red-600); font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px;">
                <x-core::icon name="alert-triangle" :size="18" />
                <div>
                    <span class="bn">হিসাবের অসঙ্গতি সতর্কতা: সর্বমোট সম্পদ এবং (মোট দায় + হিসাবকৃত মূলধন)-এর মধ্যে ৳{{ number_format($variance, 2) }} ব্যবধান সনাক্ত হয়েছে।</span>
                    <span class="en" style="display:none;">Accounting Discrepancy Warning: A variance of ৳{{ number_format($variance, 2) }} was detected between Total Assets and (Total Liabilities + Equity).</span>
                </div>
            </div>
        @endif

        {{-- Two-Column Formal Balance Sheet --}}
        <div class="report-snapshot-grid fin-snapshot-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start; margin-bottom:16px;">
            {{-- Left Column: Assets --}}
            <div class="table-container table-teal">
                <div class="panel-head" style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                        <span class="bn">সম্পদ (Assets)</span>
                        <span class="en" style="display:none;">Assets</span>
                    </div>
                    <div style="flex-shrink:0;">
                        <x-core::badge :color="$totalAssets < 0 ? 'danger' : 'green'" size="sm">
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
                            {{-- 1. Current Assets --}}
                            <tr style="background:var(--paper);">
                                <td colspan="2" style="font-weight:800; font-size:11.5px; color:var(--ink-700); text-transform:uppercase; letter-spacing:0.5px; padding:6px 12px;">
                                    <span class="bn">১. চলতি সম্পদ (Current Assets)</span>
                                    <span class="en" style="display:none;">1. Current Assets</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">ক্যাশ ও ব্যাংক ব্যালেন্স</span>
                                    <span class="en" style="display:none;">Cash & Bank Balance</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap; color:{{ $cashAndBank < 0 ? 'var(--red-600)' : 'inherit' }};">৳{{ number_format($cashAndBank, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">মজুদ পণ্যের মূল্য</span>
                                    <span class="en" style="display:none;">Inventory / Stock Value</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($stockValue, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">গ্রাহকের কাছে পাওনা</span>
                                    <span class="en" style="display:none;">Accounts Receivable (Customers)</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap; color:{{ $receivable < 0 ? 'var(--red-600)' : 'inherit' }};">৳{{ number_format($receivable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">প্রদত্ত ধার (বকেয়া)</span>
                                    <span class="en" style="display:none;">Loan Receivable (Lend)</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap; color:{{ $lendReceivable < 0 ? 'var(--red-600)' : 'inherit' }};">৳{{ number_format($lendReceivable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">প্রদত্ত জামানত (ফেরতযোগ্য)</span>
                                    <span class="en" style="display:none;">Security Money Paid</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap; color:{{ $securityMoneyPaid < 0 ? 'var(--red-600)' : 'inherit' }};">৳{{ number_format($securityMoneyPaid, 2) }}</td>
                            </tr>
                            <tr style="border-top:1px solid var(--border); background:var(--paper);">
                                <td class="cell-main" style="font-weight:700; padding-left:18px; color:var(--ink-800);">
                                    <span class="bn">মোট চলতি সম্পদ</span>
                                    <span class="en" style="display:none;">Total Current Assets</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap; color:{{ $currentAssets < 0 ? 'var(--red-600)' : 'var(--ink-800)' }};">৳{{ number_format($currentAssets, 2) }}</td>
                            </tr>

                            {{-- 2. Non-Current Assets --}}
                            <tr style="background:var(--paper);">
                                <td colspan="2" style="font-weight:800; font-size:11.5px; color:var(--ink-700); text-transform:uppercase; letter-spacing:0.5px; padding:6px 12px;">
                                    <span class="bn">২. স্থায়ী / অচলতি সম্পদ (Non-Current Assets)</span>
                                    <span class="en" style="display:none;">2. Non-Current Assets</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">স্থায়ী সম্পদ (অবচয় বাদে নিট মূল্য)</span>
                                    <span class="en" style="display:none;">Fixed Assets (Net of Depreciation)</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($fixedAssets, 2) }}</td>
                            </tr>
                            <tr style="border-top:1px solid var(--border); background:var(--paper);">
                                <td class="cell-main" style="font-weight:700; padding-left:18px; color:var(--ink-800);">
                                    <span class="bn">মোট অচলতি সম্পদ</span>
                                    <span class="en" style="display:none;">Total Non-Current Assets</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap; color:{{ $nonCurrentAssets < 0 ? 'var(--red-600)' : 'var(--ink-800)' }};">৳{{ number_format($nonCurrentAssets, 2) }}</td>
                            </tr>

                            {{-- Total Assets --}}
                            <tr style="border-top:2px solid var(--border); background:var(--paper-line);">
                                <td class="cell-main" style="font-weight:800; font-size:14px; color:var(--ink-900);">
                                    <span class="bn">সর্বমোট সম্পদ (Total Assets)</span>
                                    <span class="en" style="display:none;">TOTAL ASSETS</span>
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:15px; color:{{ $totalAssets < 0 ? 'var(--red-600)' : 'var(--green-ink, #059669)' }}; white-space:nowrap;">
                                    ৳{{ number_format($totalAssets, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right Column: Liabilities & Equity --}}
            <div class="table-container table-teal">
                <div class="panel-head" style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                        <span class="bn">দায় ও মূলধন (Liabilities & Equity)</span>
                        <span class="en" style="display:none;">Liabilities & Equity</span>
                    </div>
                    <div style="flex-shrink:0;">
                        <x-core::badge color="danger" size="sm">
                            ৳{{ number_format($totalLiabilities + $equity, 2) }}
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
                            {{-- 1. Current Liabilities --}}
                            <tr style="background:var(--paper);">
                                <td colspan="2" style="font-weight:800; font-size:11.5px; color:var(--ink-700); text-transform:uppercase; letter-spacing:0.5px; padding:6px 12px;">
                                    <span class="bn">১. চলতি দায় (Current Liabilities)</span>
                                    <span class="en" style="display:none;">1. Current Liabilities</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">সরবরাহকারীকে দেনা</span>
                                    <span class="en" style="display:none;">Accounts Payable (Suppliers)</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($payable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">ঋণ ও দেনা (অপরিশোধিত)</span>
                                    <span class="en" style="display:none;">Loans & Debts Payable</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($debtsPayable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px;">
                                    <span class="bn">গৃহীত জামানত (ফেরতযোগ্য)</span>
                                    <span class="en" style="display:none;">Security Money Received</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">৳{{ number_format($securityMoneyReceived, 2) }}</td>
                            </tr>
                            <tr style="border-top:1px solid var(--border); background:var(--paper);">
                                <td class="cell-main" style="font-weight:700; padding-left:18px; color:var(--ink-800);">
                                    <span class="bn">মোট চলতি দায়</span>
                                    <span class="en" style="display:none;">Total Current Liabilities</span>
                                </td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap; color:var(--ink-800);">৳{{ number_format($currentLiabilities, 2) }}</td>
                            </tr>

                            {{-- Subtotal Liabilities --}}
                            <tr style="border-top:1px solid var(--border); background:var(--paper);">
                                <td class="cell-main" style="font-weight:800; color:var(--ink-900);">
                                    <span class="bn">সর্বমোট দায় (Total Liabilities)</span>
                                    <span class="en" style="display:none;">TOTAL LIABILITIES</span>
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:14px; color:var(--red-ink, #dc2626); white-space:nowrap;">
                                    ৳{{ number_format($totalLiabilities, 2) }}
                                </td>
                            </tr>

                            {{-- 2. Equity --}}
                            <tr style="background:var(--paper);">
                                <td colspan="2" style="font-weight:800; font-size:11.5px; color:var(--ink-700); text-transform:uppercase; letter-spacing:0.5px; padding:6px 12px;">
                                    <span class="bn">২. মূলধন / ইকুইটি (Equity)</span>
                                    <span class="en" style="display:none;">2. Equity / Net Assets</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-main" style="padding-left:18px; font-weight:700;">
                                    <div style="font-weight:700; color:var(--ink-900);">
                                        <span class="bn">হিসাবকৃত মূলধন / নিট সম্পদ</span>
                                        <span class="en" style="display:none;">Calculated Equity / Net Assets</span>
                                    </div>
                                    <div style="font-size:10.5px; color:var(--ink-500); margin-top:1px;">
                                        <span class="bn">মোট সম্পদ − মোট দায় (হিসাবকৃত)</span>
                                        <span class="en" style="display:none;">Total Assets − Total Liabilities (calculated)</span>
                                    </div>
                                </td>
                                <td style="text-align:right; font-weight:700; color:{{ $equity >= 0 ? 'var(--green-ink, #059669)' : 'var(--red-600)' }}; font-size:14px; white-space:nowrap;">
                                    ৳{{ number_format($equity, 2) }}
                                </td>
                            </tr>

                            {{-- Total Liabilities + Equity --}}
                            <tr style="border-top:2px solid var(--border); background:var(--paper-line);">
                                <td class="cell-main" style="font-weight:800; font-size:14px; color:var(--ink-900);">
                                    <span class="bn">মোট দায় + হিসাবকৃত মূলধন</span>
                                    <span class="en" style="display:none;">TOTAL LIABILITIES + EQUITY</span>
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:15px; color:{{ ($totalLiabilities + $equity) < 0 ? 'var(--red-600)' : 'var(--ink-900)' }}; white-space:nowrap;">
                                    ৳{{ number_format($totalLiabilities + $equity, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Formal Accounting Equation Card --}}
        <div class="table-container fin-analysis-card" style="padding:14px 18px; margin-bottom:16px; background:var(--card); border:1px solid var(--border); border-radius:8px;">
            <div class="fin-analysis-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:10px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="fin-analysis-icon" style="width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:{{ $isBalanced ? 'var(--green-100)' : 'var(--red-100)' }}; color:{{ $isBalanced ? 'var(--green-ink, #059669)' : 'var(--red-600)' }}; flex-shrink:0;">
                        <x-core::icon :name="$isBalanced ? 'check-circle' : 'alert-triangle'" :size="20" />
                    </div>
                    <div>
                        <div class="fin-analysis-title" style="font-size:15px; font-weight:800; color:var(--ink-900);">
                            <span class="bn">হিসাববিজ্ঞান সমীকরণ (Fundamental Accounting Equation)</span>
                            <span class="en" style="display:none;">Fundamental Accounting Equation</span>
                        </div>
                        <div class="fin-analysis-sub" style="font-size:11.5px; color:var(--ink-600); margin-top:2px;">
                            <span class="bn">সম্পদ = দায় + মূলধন / নিট সম্পদ</span>
                            <span class="en" style="display:none;">Assets = Liabilities + Calculated Equity</span>
                        </div>
                    </div>
                </div>

                <div style="text-align:right;">
                    <x-core::badge :color="$isBalanced ? 'green' : 'danger'" size="sm">
                        <span class="bn">{{ $isBalanced ? '✓ সমীকরণ ব্যালেন্সড' : '⚠️ অমিল সনাক্ত' }}</span>
                        <span class="en" style="display:none;">{{ $isBalanced ? '✓ Balanced' : '⚠️ Unbalanced' }}</span>
                    </x-core::badge>
                </div>
            </div>

            <div class="fin-equation-box" style="background:var(--paper); padding:10px 14px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap; font-size:12.5px; font-weight:700;">
                <div style="color:{{ $totalAssets < 0 ? 'var(--red-600)' : 'var(--green-ink, #059669)' }}; display:flex; align-items:center; gap:6px;">
                    <span><span class="bn">মোট সম্পদ:</span><span class="en" style="display:none;">Total Assets:</span> ৳{{ number_format($totalAssets, 2) }}</span>
                </div>
                <div style="color:var(--ink-400); font-size:15px; font-weight:800;">=</div>
                <div style="color:{{ $totalLiabilities < 0 ? 'var(--green-ink, #059669)' : 'var(--red-600)' }}; display:flex; align-items:center; gap:6px;">
                    <span><span class="bn">মোট দায়:</span><span class="en" style="display:none;">Total Liabilities:</span> ৳{{ number_format($totalLiabilities, 2) }}</span>
                </div>
                <div style="color:var(--ink-400); font-size:15px; font-weight:800;">+</div>
                <div style="color:{{ $equity >= 0 ? 'var(--green-ink, #059669)' : 'var(--red-600)' }}; display:flex; align-items:center; gap:6px;">
                    <span><span class="bn">হিসাবকৃত মূলধন:</span><span class="en" style="display:none;">Calculated Equity:</span> ৳{{ number_format($equity, 2) }}</span>
                </div>
            </div>

            <div class="fin-disclosure-text" style="margin-top:10px; font-size:11.5px; color:var(--ink-500); line-height:1.5;">
                <span class="bn">📌 <b>প্রকাশ্য দ্রষ্টব্য:</b> প্রতিষ্ঠানটিতে বর্তমানে আলাদা মূলধন বা ইকুইটি খতিয়ান (Capital/Equity Ledger) সংরক্ষণ করা হয় না। বিধায় মোট সম্পদ থেকে মোট দায় বাদ দিয়ে হিসাবকৃত মূলধন (নিট সম্পদ) নির্ধারণ করা হয়েছে।</span>
                <span class="en" style="display:none;">📌 <b>Mandatory Disclosure:</b> Equity is calculated as Total Assets minus Total Liabilities because the system does not currently maintain a separate capital/equity ledger.</span>
            </div>
        </div>

        @can('report-balance-sheet.print')
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
        .report-snapshot-grid,
        .fin-snapshot-grid {
            grid-template-columns: 1fr !important;
        }
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm 8mm 8mm 8mm;
        }

        .report-snapshot-grid,
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
        .report-snapshot-grid > .table-container,
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
        .report-snapshot-grid .panel-head,
        .fin-snapshot-grid .panel-head {
            padding: 4px 8px !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            font-size: 11px !important;
        }
        .report-snapshot-grid .panel-head .app-badge,
        .fin-snapshot-grid .panel-head .app-badge {
            font-size: 9px !important;
            padding: 1px 5px !important;
        }
        .report-snapshot-grid .table-responsive,
        .fin-snapshot-grid .table-responsive {
            flex: 1 !important;
            overflow: visible !important;
            width: 100% !important;
        }
        .report-snapshot-grid .app-table,
        .fin-snapshot-grid .app-table {
            width: 100% !important;
            table-layout: auto !important;
            font-size: 8.5px !important;
            border-collapse: collapse !important;
            box-sizing: border-box !important;
        }
        .report-snapshot-grid .app-table col.col-name,
        .fin-snapshot-grid .app-table col.col-name,
        .report-snapshot-grid .app-table td:first-child:not([colspan]),
        .fin-snapshot-grid .app-table td:first-child:not([colspan]) {
            width: 60% !important;
            max-width: 60% !important;
            padding: 2.5px 5px !important;
            box-sizing: border-box !important;
            word-break: break-word !important;
        }
        .report-snapshot-grid .app-table col.col-amount,
        .fin-snapshot-grid .app-table col.col-amount,
        .report-snapshot-grid .app-table td:last-child:not([colspan]),
        .fin-snapshot-grid .app-table td:last-child:not([colspan]) {
            width: 40% !important;
            max-width: 40% !important;
            text-align: right !important;
            white-space: nowrap !important;
            padding: 2.5px 5px !important;
            box-sizing: border-box !important;
        }
        .report-snapshot-grid .app-table td[colspan],
        .fin-snapshot-grid .app-table td[colspan] {
            width: 100% !important;
            padding: 3px 5px !important;
            font-size: 8.5px !important;
            background: #f8fafc !important;
            box-sizing: border-box !important;
        }
        .report-snapshot-grid .app-table tr:last-child td,
        .fin-snapshot-grid .app-table tr:last-child td {
            font-size: 9.5px !important;
            padding: 3.5px 5px !important;
            background: #f1f5f9 !important;
            border-top: 2px solid #cbd5e1 !important;
            box-sizing: border-box !important;
        }

        /* Accounting Equation Card in Balance Sheet */
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
        .fin-equation-box {
            padding: 4px 6px !important;
            font-size: 8.5px !important;
            gap: 6px !important;
            border-radius: 4px !important;
            box-sizing: border-box !important;
        }
        .fin-disclosure-text {
            font-size: 7.5px !important;
            margin-top: 4px !important;
            line-height: 1.3 !important;
        }

        .report-print-footer {
            margin-top: 6px !important;
            padding-top: 4px !important;
            font-size: 8px !important;
            page-break-inside: avoid !important;
        }
    }
    </style>
    @endpush
</x-core::layout>
