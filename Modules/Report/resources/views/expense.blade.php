<x-core::layout
    title="ব্যয় রিপোর্ট"
    title-en="Expense Report"
    subtitle="নির্বাচিত সময়ের ব্যয়ের তালিকা ও সারসংক্ষেপ"
    subtitle-en="Expense list and summary for the selected period"
    active="report-expense"
>
    @include('report::partials._date-range-filter')

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card icon="trending-down" color="red" :value="'৳' . number_format($totals['amount'], 2)" label="মোট ব্যয়" label-en="Total Expense" />
        <x-core::stat-card icon="receipt" color="grey" :value="$totals['count']" label="মোট এন্ট্রি" label-en="Total Entries" />
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="bn">তারিখ</th>
                        <th class="bn">শিরোনাম</th>
                        <th class="bn">বিভাগ</th>
                        <th class="bn">পরিশোধ পদ্ধতি</th>
                        <th class="bn">পরিমাণ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date?->format('d M, Y') }}</td>
                            <td class="cell-main">{{ $expense->title }}</td>
                            <td>{{ $expense->category?->name ?? '—' }}</td>
                            <td>{{ $expense->payment_method ?? '—' }}</td>
                            <td>৳{{ number_format((float) $expense->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-core::table.empty icon="trending-down" title="কোনো ব্যয় পাওয়া যায়নি" title-en="No expense found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
