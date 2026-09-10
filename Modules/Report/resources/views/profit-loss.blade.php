<x-core::layout
    title="লাভ-ক্ষতি রিপোর্ট"
    title-en="Profit & Loss Report"
    subtitle="নির্বাচিত সময়ের আয়-ব্যয় ও নিট লাভ-ক্ষতি সারসংক্ষেপ"
    subtitle-en="Income, expense, and net profit/loss summary for the selected period"
    active="report-profit-loss"
>
    <x-report::tabbar active="profit-loss" />

    <div class="report-printable-area">
        @include('report::partials._date-range-filter', [
            'reportTitle' => 'লাভ-ক্ষতি রিপোর্ট',
            'reportTitleEn' => 'Profit & Loss Report',
        ])

        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">লাভ-ক্ষতির হিসাব বিবরণী</span>
                    <span class="en" style="display:none;">Statement of Profit & Loss</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="bn">খাত / বিবরণ</th><th class="en" style="display:none;">Particulars / Description</th>
                            <th class="bn" style="text-align:right;">পরিমাণ</th><th class="en" style="display:none; text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">বিক্রয় (মোট আয়)</span>
                                <span class="en" style="display:none;">Sales (Gross Revenue)</span>
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--teal-800);">৳{{ number_format($sales, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">পণ্য ক্রয়</span>
                                <span class="en" style="display:none;">Product Purchase</span>
                            </td>
                            <td style="text-align:right; font-weight:700;">৳{{ number_format($productPurchase, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">পরিবহন খরচ</span>
                                <span class="en" style="display:none;">Transport Fee</span>
                            </td>
                            <td style="text-align:right; font-weight:700;">৳{{ number_format($transportFee, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">বিক্রয় থেকে মুনাফা</span>
                                <span class="en" style="display:none;">Profit from Sales</span>
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--green-ink, #059669);">৳{{ number_format($profitFromSales, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">অন্যান্য আয়</span>
                                <span class="en" style="display:none;">Other Income</span>
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--green-ink, #059669);">৳{{ number_format($otherIncome, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">মোট ব্যয়</span>
                                <span class="en" style="display:none;">Total Expense</span>
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--red-ink, #dc2626);">৳{{ number_format($totalExpense, 2) }}</td>
                        </tr>
                        <tr style="border-top:2px solid var(--border);">
                            <td class="cell-main">
                                <span class="bn">নিট মুনাফা</span>
                                <span class="en" style="display:none;">Net Profit</span>
                            </td>
                            <td style="text-align:right; font-weight:800; font-size:15px; color:var(--green-ink, #059669);">৳{{ number_format($netProfit, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">মোট ক্ষতি</span>
                                <span class="en" style="display:none;">Total Loss</span>
                            </td>
                            <td style="text-align:right; font-weight:800; font-size:15px; color:var(--red-ink, #dc2626);">৳{{ number_format($totalLoss, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

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
    </div>
</x-core::layout>
