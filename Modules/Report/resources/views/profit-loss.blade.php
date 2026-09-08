<x-core::layout
    title="লাভ-ক্ষতি রিপোর্ট"
    title-en="Profit & Loss Report"
    subtitle="নির্বাচিত সময়ের আয়-ব্যয় ও নিট লাভ-ক্ষতি সারসংক্ষেপ"
    subtitle-en="Income, expense, and net profit/loss summary for the selected period"
    active="report-profit-loss"
>
    @include('report::partials._date-range-filter')

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <tbody>
                    <tr>
                        <td class="cell-main">
                            <span class="bn">বিক্রয় (আয়)</span>
                            <span class="en" style="display:none;">Sales (Revenue)</span>
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
</x-core::layout>
