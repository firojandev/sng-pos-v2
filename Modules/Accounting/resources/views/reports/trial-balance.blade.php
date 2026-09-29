<x-core::layout title="রেওয়ামিল" title-en="Trial Balance" subtitle="{{ $asOf->format('d M, Y') }} পর্যন্ত" subtitle-en="As of {{ $asOf->format('d M, Y') }}" active="accounting-reports">
    <x-accounting::tabbar active="trial-balance" />
    <x-accounting::filters :shops="$shops" :dates="['as_of' => ['তারিখ পর্যন্ত', 'As Of', $asOf->toDateString()]]" />

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                        <th class="table-cell-right"><span class="bn">ডেবিট</span><span class="en" style="display:none;">Debit</span></th>
                        <th class="table-cell-right"><span class="bn">ক্রেডিট</span><span class="en" style="display:none;">Credit</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            <td><a href="{{ route('accounting-reports.general-ledger', ['account_id' => $row['account']->id, 'shop_id' => request('shop_id')]) }}">{{ $row['account']->code }} — {{ $row['account']->name }}</a></td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $row['debit'] ? number_format($row['debit'], 2) : '' }}</td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $row['credit'] ? number_format($row['credit'], 2) : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-core::table.empty icon="file-text" title="লেজারে কোনো এন্ট্রি নেই" title-en="No entries in the ledger" /></td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align:right; font-weight:700;"><span class="bn">মোট</span><span class="en" style="display:none;">Total</span></td>
                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format($report['debit'], 2) }}</td>
                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format($report['credit'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-core::layout>
