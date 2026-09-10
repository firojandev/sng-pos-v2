<x-core::layout
    title="আয় রিপোর্ট"
    title-en="Income Report"
    subtitle="নির্বাচিত সময়ের আয়ের তালিকা ও সারসংক্ষেপ"
    subtitle-en="Income list and summary for the selected period"
    active="report-income"
>
    <x-report::tabbar active="income" />

    <div class="report-printable-area">
        @include('report::partials._date-range-filter', [
            'reportTitle' => 'আয় রিপোর্ট',
            'reportTitleEn' => 'Income Report',
        ])

        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
            <x-core::stat-card icon="trending-up" color="green" :value="'৳' . number_format($totals['amount'], 2)" label="মোট আয়" label-en="Total Income" />
            <x-core::stat-card icon="receipt" color="grey" :value="$totals['count']" label="মোট এন্ট্রি" label-en="Total Entries" />
        </div>

        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">আয়ের তালিকা</span>
                    <span class="en" style="display:none;">Income List</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট {{ $incomes->count() }} টি রেকর্ড</span>
                    <span class="en" style="display:none;">Total {{ $incomes->count() }} records</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">উৎস</th><th class="en" style="display:none;">Source</th>
                            <th class="bn">পরিশোধ পদ্ধতি</th><th class="en" style="display:none;">Payment Method</th>
                            <th class="bn" style="text-align:right;">পরিমাণ</th><th class="en" style="display:none; text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($incomes as $income)
                            <tr>
                                <td>{{ $income->income_date?->format('d M, Y') }}</td>
                                <td class="cell-main">{{ $income->source }}</td>
                                <td>{{ $income->payment_method ?? '—' }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $income->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-core::table.empty icon="trending-up" title="কোনো আয় পাওয়া যায়নি" title-en="No income found" />
                                </td>
                            </tr>
                        @endforelse
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
