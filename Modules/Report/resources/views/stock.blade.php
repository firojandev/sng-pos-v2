<x-core::layout
    title="স্টক রিপোর্ট"
    title-en="Stock Report"
    subtitle="বর্তমান মজুদ ও নির্বাচিত সময়ের স্টক মুভমেন্ট সারসংক্ষেপ"
    subtitle-en="Current on-hand stock and stock movement summary for the selected period"
    active="report-stock"
>
    <x-report::tabbar active="stock" />

    <div class="report-printable-area">
        @include('report::partials._date-range-filter', [
            'reportTitle' => 'স্টক রিপোর্ট',
            'reportTitleEn' => 'Stock Report',
        ])

        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
            <x-core::stat-card icon="box" color="teal" :value="number_format($totals['qty_on_hand'], 2)" label="মোট মজুদ (পরিমাণ)" label-en="Total On-Hand Qty" />
            <x-core::stat-card icon="dollar-sign" color="green" :value="'৳' . number_format($totals['stock_value'], 2)" label="মোট মজুদ মূল্য" label-en="Total Stock Value" />
        </div>

        @php
            $typeLabels = \Modules\Product\Models\StockMovement::typeLabels();
        @endphp
        @if ($movementSummary->isNotEmpty())
            <div class="panel" style="margin-top:0; margin-bottom:16px;">
                <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border);">
                    <span class="panel-title bn" style="font-weight:700;">নির্বাচিত সময়ে স্টক মুভমেন্ট</span>
                    <span class="panel-title en" style="display:none; font-weight:700;">Stock Movement in Period</span>
                </div>
                <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:10px; padding:14px 16px;">
                    @foreach ($movementSummary as $type => $row)
                        <x-core::badge color="grey" size="sm" variant="soft">
                            {{ $typeLabels[$type]['bn'] ?? $type }} ({{ $row->movement_count }}): {{ number_format((float) $row->quantity_change, 2) }}
                        </x-core::badge>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">বর্তমান মজুদ তালিকা</span>
                    <span class="en" style="display:none;">Current Stock List</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট {{ $onHand->count() }} টি পণ্য</span>
                    <span class="en" style="display:none;">Total {{ $onHand->count() }} products</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                            <th class="bn">SKU</th><th class="en" style="display:none;">SKU</th>
                            <th class="bn" style="text-align:right;">মজুদ পরিমাণ</th><th class="en" style="display:none; text-align:right;">Stock Qty</th>
                            <th class="bn" style="text-align:right;">মজুদ মূল্য</th><th class="en" style="display:none; text-align:right;">Stock Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($onHand as $row)
                            <tr>
                                <td class="cell-main">{{ $row->name }}</td>
                                <td>{{ $row->sku ?? '—' }}</td>
                                <td style="text-align:right;">{{ number_format((float) $row->qty_on_hand, 2) }}</td>
                                <td style="text-align:right;">৳{{ number_format((float) $row->stock_value, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-core::table.empty icon="box" title="কোনো মজুদ পাওয়া যায়নি" title-en="No stock found" />
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
