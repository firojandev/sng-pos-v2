<x-core::layout title="লাভ-ক্ষতি" title-en="Profit & Loss" subtitle="{{ $from->format('d M, Y') }} – {{ $to->format('d M, Y') }}" subtitle-en="{{ $from->format('d M, Y') }} – {{ $to->format('d M, Y') }}" active="accounting-reports">
    <x-accounting::tabbar active="profit-loss" />
    <x-accounting::filters :shops="$shops" :dates="['from' => ['থেকে', 'From', $from->toDateString()], 'to' => ['পর্যন্ত', 'To', $to->toDateString()]]" />

    <div class="table-container table-teal" style="max-width:760px;">
        <div class="table-responsive">
            <table class="app-table">
                <tbody>
                    @include('accounting::reports._section', ['title' => 'আয় / Income', 'rows' => $report['income'], 'totalLabel' => 'মোট আয় / Total Income', 'total' => $report['total_income']])
                    @include('accounting::reports._section', ['title' => 'ব্যয় / Expenses', 'rows' => $report['expenses'], 'totalLabel' => 'মোট ব্যয় / Total Expenses', 'total' => $report['total_expenses']])
                    <tr>
                        <td style="font-weight:800; font-size:14px;">{{ $report['net'] >= 0 ? 'নিট লাভ / Net Profit' : 'নিট ক্ষতি / Net Loss' }}</td>
                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:800; font-size:14px; color:{{ $report['net'] >= 0 ? 'var(--green-600, #16a34a)' : 'var(--red-600)' }};">{{ number_format(abs($report['net']), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
