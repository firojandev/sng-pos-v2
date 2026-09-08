<x-core::layout
    title="ক্রয় রিপোর্ট"
    title-en="Purchase Report"
    subtitle="নির্বাচিত সময়ের ক্রয় সারসংক্ষেপ ও তালিকা"
    subtitle-en="Purchase summary and list for the selected period"
    active="report-purchase"
>
    @include('report::partials._date-range-filter')

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card icon="truck" color="teal" :value="'৳' . number_format($totals['total'], 2)" label="মোট ক্রয়" label-en="Total Purchase" />
        <x-core::stat-card icon="map-pin" color="blue" :value="'৳' . number_format($totals['transportation_cost'], 2)" label="পরিবহন খরচ" label-en="Transportation Cost" />
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
                        <th class="bn">সরবরাহকারী</th>
                        <th class="bn">মোট</th>
                        <th class="bn">পরিবহন খরচ</th>
                        <th class="bn">বাকি</th>
                        <th class="bn">অবস্থা</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td class="cell-main">{{ $purchase->invoice_no }}</td>
                            <td>{{ $purchase->purchase_date?->format('d M, Y') }}</td>
                            <td>{{ $purchase->supplier?->name ?? '—' }}</td>
                            <td>৳{{ number_format((float) $purchase->total, 2) }}</td>
                            <td>৳{{ number_format((float) $purchase->transportation_cost, 2) }}</td>
                            <td>৳{{ number_format((float) $purchase->due_amount, 2) }}</td>
                            <td>
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
</x-core::layout>
