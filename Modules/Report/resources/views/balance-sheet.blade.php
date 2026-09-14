<x-core::layout
    title="ব্যালেন্স শীট"
    title-en="Balance Sheet"
    subtitle="সম্পদ, দায় ও মালিকানা স্বত্বের হিসাব বিবরণী"
    subtitle-en="A statement of assets, liabilities, and owner's equity"
    active="report-balance-sheet"
>
    <x-report::tabbar active="balance-sheet" />

    <div class="report-printable-area">
        @include('report::partials._snapshot-print-header', [
            'reportTitle' => 'ব্যালেন্স শীট',
            'reportTitleEn' => 'Balance Sheet',
            'printPermission' => 'report-balance-sheet.print',
        ])

        @unless ($asOf->isToday())
            <div style="margin-bottom:16px; padding:10px 14px; border-radius:8px; background:var(--gold-100); color:var(--gold-ink); font-size:12px;">
                <span class="bn">দ্রষ্টব্য: ধার, দেনা, জামানত, গ্রাহকের পাওনা ও সরবরাহকারীর দেনা তাদের বর্তমান অবস্থা অনুযায়ী দেখানো হয়েছে, নির্বাচিত তারিখের প্রকৃত অবস্থা নয় — এই আইটেমগুলোর ইতিহাস রক্ষণাবেক্ষণ করা হয় না।</span>
                <span class="en" style="display:none;">Note: Lend, Debts, Security Money, Customer Receivable, and Supplier Due reflect their current recorded status, not necessarily their exact status on the selected date — status history isn't tracked for these items.</span>
            </div>
        @endunless

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start;">
            <div class="table-container table-teal">
                <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border);">
                    <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                        <span class="bn">সম্পদ</span>
                        <span class="en" style="display:none;">Assets</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="app-table">
                        <tbody>
                            <tr>
                                <td class="cell-main"><span class="bn">ক্যাশ ও ব্যাংক ব্যালেন্স</span><span class="en" style="display:none;">Cash & Bank Balance</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($cashAndBank, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main"><span class="bn">গ্রাহকের কাছে পাওনা</span><span class="en" style="display:none;">Accounts Receivable (Customers)</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($receivable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main"><span class="bn">ধার (বকেয়া)</span><span class="en" style="display:none;">Lend Receivable</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($lendReceivable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main"><span class="bn">প্রদত্ত জামানত (ফেরতযোগ্য)</span><span class="en" style="display:none;">Security Money Paid</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($securityMoneyPaid, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main"><span class="bn">মজুদ পণ্যের মূল্য</span><span class="en" style="display:none;">Inventory / Stock</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($stockValue, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main"><span class="bn">স্থায়ী সম্পদ</span><span class="en" style="display:none;">Fixed Assets</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($fixedAssets, 2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid var(--border);">
                                <td class="cell-main"><span class="bn">মোট সম্পদ</span><span class="en" style="display:none;">Total Assets</span></td>
                                <td style="text-align:right; font-weight:800; font-size:15px; color:var(--green-ink, #059669);">৳{{ number_format($totalAssets, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-container table-teal">
                <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border);">
                    <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                        <span class="bn">দায় ও মালিকানা স্বত্ব</span>
                        <span class="en" style="display:none;">Liabilities & Equity</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="app-table">
                        <tbody>
                            <tr>
                                <td class="cell-main"><span class="bn">সরবরাহকারীকে দেনা</span><span class="en" style="display:none;">Accounts Payable (Suppliers)</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($payable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main"><span class="bn">দেনা (অপরিশোধিত)</span><span class="en" style="display:none;">Debts Payable</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($debtsPayable, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main"><span class="bn">গৃহীত জামানত</span><span class="en" style="display:none;">Security Money Received</span></td>
                                <td style="text-align:right; font-weight:700;">৳{{ number_format($securityMoneyReceived, 2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid var(--border);">
                                <td class="cell-main"><span class="bn">মোট দায়</span><span class="en" style="display:none;">Total Liabilities</span></td>
                                <td style="text-align:right; font-weight:800; font-size:15px; color:var(--red-ink, #dc2626);">৳{{ number_format($totalLiabilities, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">মালিকানা স্বত্ব (হিসাবকৃত)</span>
                                    <span class="en" style="display:none;">Owner's Equity (calculated)</span>
                                </td>
                                <td style="text-align:right; font-weight:700; color:{{ $equity >= 0 ? 'var(--green-ink, #059669)' : 'var(--red-ink, #dc2626)' }};">৳{{ number_format($equity, 2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid var(--border);">
                                <td class="cell-main"><span class="bn">মোট দায় + মালিকানা স্বত্ব</span><span class="en" style="display:none;">Total Liabilities + Equity</span></td>
                                <td style="text-align:right; font-weight:800; font-size:15px;">৳{{ number_format($totalLiabilities + $equity, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="margin-top:10px; font-size:11.5px; color:var(--ink-500); text-align:center;">
            <span class="bn">মালিকানা স্বত্ব একটি হিসাবকৃত মান (মোট সম্পদ − মোট দায়), যেহেতু আলাদা মূলধন হিসাব রক্ষণাবেক্ষণ করা হয় না।</span>
            <span class="en" style="display:none;">Owner's Equity is a calculated figure (Total Assets − Total Liabilities), since no separate capital ledger is maintained.</span>
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
</x-core::layout>
