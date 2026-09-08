<x-core::layout
    title="বিক্রয় রিপোর্ট"
    title-en="Sales Report"
    subtitle="নির্বাচিত সময়ের বিক্রয় সারসংক্ষেপ ও তালিকা"
    subtitle-en="Sales summary and list for the selected period"
    active="report-sales"
>
    @include('report::partials._date-range-filter')

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card icon="shopping-cart" color="teal" :value="'৳' . number_format($totals['total'], 2)" label="মোট বিক্রয়" label-en="Total Sales" />
        <x-core::stat-card icon="trending-up" color="green" :value="'৳' . number_format($totals['profit'], 2)" label="মোট মুনাফা" label-en="Total Profit" />
        <x-core::stat-card icon="alert-circle" color="gold" :value="'৳' . number_format($totals['due'], 2)" label="মোট বাকি" label-en="Total Due" />
        <x-core::stat-card icon="receipt" color="grey" :value="$totals['count']" label="মোট ইনভয়েস" label-en="Total Invoices" />
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="bn">ইনভয়েস</th>
                        <th class="bn">তারিখ</th>
                        <th class="bn">গ্রাহক</th>
                        <th class="bn">মোট</th>
                        <th class="bn">মুনাফা</th>
                        <th class="bn">বাকি</th>
                        <th class="bn">অবস্থা</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="cell-main">{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->sale_date?->format('d M, Y') }}</td>
                            <td>{{ $sale->customer?->name ?? '—' }}</td>
                            <td>৳{{ number_format((float) $sale->total, 2) }}</td>
                            <td>৳{{ number_format((float) $sale->profit, 2) }}</td>
                            <td>৳{{ number_format((float) $sale->due_amount, 2) }}</td>
                            <td>
                                <x-core::badge
                                    :color="$sale->payment_status === 'paid' ? 'green' : ($sale->payment_status === 'due' ? 'gold' : 'grey')"
                                    size="xs"
                                >
                                    {{ $sale->payment_status }}
                                </x-core::badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-core::table.empty icon="shopping-cart" title="কোনো বিক্রয় পাওয়া যায়নি" title-en="No sales found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
