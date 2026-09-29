<x-core::layout title="অবচয় সূচি" title-en="Depreciation Schedule" :subtitle="$asset->name" :subtitle-en="$asset->name" active="assets">
    @php $money = fn ($amount) => number_format((float) $amount, 2); @endphp

    <div class="panel" style="margin-top:16px;">
        <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:28px;">
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">ক্রয়মূল্য</span><span class="en" style="display:none;">Cost</span></div><div style="font-size:18px; font-weight:700;">{{ $money($asset->amount) }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">ক্রয়ের তারিখ</span><span class="en" style="display:none;">Purchased</span></div><div style="font-size:18px; font-weight:700;">{{ $asset->purchase_date?->format('d M, Y') ?? '—' }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">অবশিষ্ট মূল্য</span><span class="en" style="display:none;">Residual Value</span></div><div style="font-size:18px; font-weight:700;">{{ $money($asset->residual_value) }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">ব্যবহারকাল</span><span class="en" style="display:none;">Useful Life</span></div><div style="font-size:18px; font-weight:700;">{{ $asset->usefulLifeMonths() }} <span class="bn">মাস</span><span class="en" style="display:none;">months</span></div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">পদ্ধতি</span><span class="en" style="display:none;">Method</span></div><div style="font-size:18px; font-weight:700;"><span class="bn">সরলরেখা</span><span class="en" style="display:none;">Straight-line</span></div></div>
        </div>
    </div>

    <div class="table-container table-teal" style="margin-top:16px;">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">মাস</span><span class="en" style="display:none;">Month</span></th>
                        <th class="table-cell-right"><span class="bn">অবচয়</span><span class="en" style="display:none;">Depreciation</span></th>
                        <th class="table-cell-right"><span class="bn">পুঞ্জীভূত</span><span class="en" style="display:none;">Accumulated</span></th>
                        <th class="table-cell-right"><span class="bn">বই মূল্য</span><span class="en" style="display:none;">Book Value</span></th>
                        <th class="table-cell-center"><span class="bn">পোস্ট</span><span class="en" style="display:none;">Posted</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedule as $row)
                        <tr>
                            <td>{{ $row['period']->format('M Y') }}</td>
                            <td class="table-cell-right">{{ $money($row['amount']) }}</td>
                            <td class="table-cell-right">{{ $money($row['accumulated']) }}</td>
                            <td class="table-cell-right" style="font-weight:600;">{{ $money($row['book_value']) }}</td>
                            <td class="table-cell-center">
                                @if ($recorded->has($row['period']->format('Y-m')))
                                    <x-core::badge color="green" size="xs" label="হ্যাঁ" label-en="Yes" />
                                @else
                                    <span style="color:var(--ink-400);">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-core::table.empty icon="calendar" title="সূচি নেই" title-en="No schedule" description="ক্রয়ের তারিখ ও মেয়াদ দিন।" description-en="Set the purchase date and useful life." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
