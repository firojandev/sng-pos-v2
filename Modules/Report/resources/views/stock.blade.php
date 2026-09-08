<x-core::layout
    title="স্টক রিপোর্ট"
    title-en="Stock Report"
    subtitle="বর্তমান মজুদ ও নির্বাচিত সময়ের স্টক মুভমেন্ট সারসংক্ষেপ"
    subtitle-en="Current on-hand stock and stock movement summary for the selected period"
    active="report-stock"
>
    @include('report::partials._date-range-filter')

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card icon="box" color="teal" :value="number_format($totals['qty_on_hand'], 2)" label="মোট মজুদ (পরিমাণ)" label-en="Total On-Hand Qty" />
        <x-core::stat-card icon="dollar-sign" color="green" :value="'৳' . number_format($totals['stock_value'], 2)" label="মোট মজুদ মূল্য" label-en="Total Stock Value" />
    </div>

    @php
        $typeLabels = \Modules\Product\Models\StockMovement::typeLabels();
    @endphp
    @if ($movementSummary->isNotEmpty())
        <div class="panel" style="margin-top:0; margin-bottom:16px;">
            <div class="panel-head">
                <span class="panel-title bn">নির্বাচিত সময়ে স্টক মুভমেন্ট</span>
                <span class="panel-title en" style="display:none;">Stock Movement in Period</span>
            </div>
            <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:10px;">
                @foreach ($movementSummary as $type => $row)
                    <x-core::badge color="grey" size="sm" variant="soft">
                        {{ $typeLabels[$type]['bn'] ?? $type }} ({{ $row->movement_count }}): {{ number_format((float) $row->quantity_change, 2) }}
                    </x-core::badge>
                @endforeach
            </div>
        </div>
    @endif

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="bn">পণ্য</th>
                        <th class="bn">SKU</th>
                        <th class="bn">মজুদ পরিমাণ</th>
                        <th class="bn">মজুদ মূল্য</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($onHand as $row)
                        <tr>
                            <td class="cell-main">{{ $row->name }}</td>
                            <td>{{ $row->sku ?? '—' }}</td>
                            <td>{{ number_format((float) $row->qty_on_hand, 2) }}</td>
                            <td>৳{{ number_format((float) $row->stock_value, 2) }}</td>
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
</x-core::layout>
