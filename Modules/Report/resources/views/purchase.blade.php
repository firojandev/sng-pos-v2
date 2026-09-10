<x-core::layout
    title="ক্রয় রিপোর্ট"
    title-en="Purchase Report"
    subtitle="নির্বাচিত সময়ের ক্রয় সারসংক্ষেপ ও তালিকা"
    subtitle-en="Purchase summary and list for the selected period"
    active="report-purchase"
>
    <x-report::tabbar active="purchase" />

    <div class="report-printable-area">
        @include('report::partials._date-range-filter', [
            'reportTitle' => 'ক্রয় রিপোর্ট',
            'reportTitleEn' => 'Purchase Report',
        ])

        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
            <x-core::stat-card icon="truck" color="teal" :value="'৳' . number_format($totals['total'], 2)" label="মোট ক্রয়" label-en="Total Purchase" />
            <x-core::stat-card icon="map-pin" color="blue" :value="'৳' . number_format($totals['transportation_cost'], 2)" label="পরিবহন খরচ" label-en="Transportation Cost" />
            <x-core::stat-card icon="alert-circle" color="gold" :value="'৳' . number_format($totals['due'], 2)" label="মোট বাকি" label-en="Total Due" />
            <x-core::stat-card icon="receipt" color="grey" :value="$totals['count']" label="মোট ইনভয়েস" label-en="Total Invoices" />
        </div>

        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">ক্রয় তালিকা</span>
                    <span class="en" style="display:none;">Purchase List</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট {{ $purchases->count() }} টি রেকর্ড</span>
                    <span class="en" style="display:none;">Total {{ $purchases->count() }} records</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="bn">ইনভয়েস</th><th class="en" style="display:none;">Invoice</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">সরবরাহকারী</th><th class="en" style="display:none;">Supplier</th>
                            <th class="bn" style="text-align:right;">মোট</th><th class="en" style="display:none; text-align:right;">Total</th>
                            <th class="bn" style="text-align:right;">পরিবহন খরচ</th><th class="en" style="display:none; text-align:right;">Transport Cost</th>
                            <th class="bn" style="text-align:right;">বাকি</th><th class="en" style="display:none; text-align:right;">Due</th>
                            <th class="bn" style="text-align:center;">অবস্থা</th><th class="en" style="display:none; text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            <tr>
                                <td class="cell-main">{{ $purchase->invoice_no }}</td>
                                <td>{{ $purchase->purchase_date?->format('d M, Y') }}</td>
                                <td>{{ $purchase->supplier?->name ?? '—' }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $purchase->total, 2) }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $purchase->transportation_cost, 2) }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $purchase->due_amount, 2) }}</td>
                                <td style="text-align:center;">
                                    <x-core::badge
                                        :color="$purchase->payment_status === 'paid' ? 'green' : ($purchase->payment_status === 'due' ? 'gold' : 'grey')"
                                        size="xs"
                                    >
                                        {{ $purchase->payment_status }}
                                    </x-core::badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-core::table.empty icon="truck" title="কোনো ক্রয় পাওয়া যায়নি" title-en="No purchases found" />
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
