<x-core::layout
    title="বিক্রয় রিপোর্ট"
    title-en="Sales Report"
    subtitle="নির্বাচিত সময়ের বিক্রয় সারসংক্ষেপ ও তালিকা"
    subtitle-en="Sales summary and list for the selected period"
    active="report-sales"
>
    <x-report::tabbar active="sales" />

    <div class="report-printable-area">
        @include('report::partials._date-range-filter', [
            'reportTitle' => 'বিক্রয় রিপোর্ট',
            'reportTitleEn' => 'Sales Report',
            'printPermission' => 'report-sales.print',
        ])

        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
            <x-core::stat-card icon="shopping-cart" color="teal" :value="'৳' . number_format($totals['total'], 2)" label="মোট বিক্রয়" label-en="Total Sales" />
            <x-core::stat-card icon="trending-up" color="green" :value="'৳' . number_format($totals['profit'], 2)" label="মোট মুনাফা" label-en="Total Profit" />
            <x-core::stat-card icon="alert-circle" color="gold" :value="'৳' . number_format($totals['due'], 2)" label="মোট বাকি" label-en="Total Due" />
            <x-core::stat-card icon="receipt" color="grey" :value="$totals['count']" label="মোট ইনভয়েস" label-en="Total Invoices" />
        </div>

        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">বিক্রয় তালিকা</span>
                    <span class="en" style="display:none;">Sales List</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট {{ $sales->count() }} টি রেকর্ড</span>
                    <span class="en" style="display:none;">Total {{ $sales->count() }} records</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="bn">ইনভয়েস</th><th class="en" style="display:none;">Invoice</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">গ্রাহক</th><th class="en" style="display:none;">Customer</th>
                            <th class="bn" style="text-align:right;">মোট</th><th class="en" style="display:none; text-align:right;">Total</th>
                            <th class="bn" style="text-align:right;">মুনাফা</th><th class="en" style="display:none; text-align:right;">Profit</th>
                            <th class="bn" style="text-align:right;">বাকি</th><th class="en" style="display:none; text-align:right;">Due</th>
                            <th class="bn" style="text-align:center;">অবস্থা</th><th class="en" style="display:none; text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="cell-main">{{ $sale->invoice_no }}</td>
                                <td>{{ $sale->sale_date?->format('d M, Y') }}</td>
                                <td>{{ $sale->customer?->name ?? '—' }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $sale->total, 2) }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $sale->profit, 2) }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $sale->due_amount, 2) }}</td>
                                <td style="text-align:center;">
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

        @can('report-sales.print')
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
