<x-core::layout title="উদ্বৃত্তপত্র" title-en="Balance Sheet" subtitle="{{ $asOf->format('d M, Y') }} পর্যন্ত" subtitle-en="As of {{ $asOf->format('d M, Y') }}" active="accounting-reports">
    <x-accounting::tabbar active="balance-sheet" />
    <x-accounting::filters :shops="$shops" :dates="['as_of' => ['তারিখ পর্যন্ত', 'As Of', $asOf->toDateString()]]" />

    @unless ($report['is_balanced'])
        <div style="border:1px solid var(--red-200, #fecaca); background:var(--red-50, #fef2f2); color:var(--red-600); border-radius:10px; padding:10px 12px; margin-bottom:12px; font-size:12.5px;">
            <span class="bn">সম্পদ ও (দায় + ইকুইটি) সমান নয়।</span><span class="en" style="display:none;">Assets do not equal liabilities plus equity.</span>
        </div>
    @endunless

    <div class="table-container table-teal" style="max-width:760px;">
        <div class="table-responsive">
            <table class="app-table">
                <tbody>
                    @include('accounting::reports._section', ['title' => 'সম্পদ / Assets', 'rows' => $report['assets'], 'totalLabel' => 'মোট সম্পদ / Total Assets', 'total' => $report['total_assets']])
                    @include('accounting::reports._section', ['title' => 'দায় / Liabilities', 'rows' => $report['liabilities'], 'totalLabel' => 'মোট দায় / Total Liabilities', 'total' => $report['total_liabilities']])
                    @include('accounting::reports._section', [
                        'title' => 'ইকুইটি / Equity',
                        'rows' => $report['equity'],
                        'extraRows' => ['চলতি মুনাফা (এখনো বন্ধ হয়নি) / Current Profit (not yet closed)' => $report['unclosed_profit']],
                        'totalLabel' => 'মোট ইকুইটি / Total Equity',
                        'total' => $report['total_equity'],
                    ])
                    <tr>
                        <td style="font-weight:800;">দায় + ইকুইটি / Liabilities + Equity</td>
                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:800;">{{ number_format($report['total_liabilities'] + $report['total_equity'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
