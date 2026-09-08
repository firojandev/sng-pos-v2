<x-core::layout
    title="আয় রিপোর্ট"
    title-en="Income Report"
    subtitle="নির্বাচিত সময়ের আয়ের তালিকা ও সারসংক্ষেপ"
    subtitle-en="Income list and summary for the selected period"
    active="report-income"
>
    @include('report::partials._date-range-filter')

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card icon="trending-up" color="green" :value="'৳' . number_format($totals['amount'], 2)" label="মোট আয়" label-en="Total Income" />
        <x-core::stat-card icon="receipt" color="grey" :value="$totals['count']" label="মোট এন্ট্রি" label-en="Total Entries" />
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="bn">তারিখ</th>
                        <th class="bn">উৎস</th>
                        <th class="bn">পরিশোধ পদ্ধতি</th>
                        <th class="bn">পরিমাণ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incomes as $income)
                        <tr>
                            <td>{{ $income->income_date?->format('d M, Y') }}</td>
                            <td class="cell-main">{{ $income->source }}</td>
                            <td>{{ $income->payment_method ?? '—' }}</td>
                            <td>৳{{ number_format((float) $income->amount, 2) }}</td>
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
</x-core::layout>
