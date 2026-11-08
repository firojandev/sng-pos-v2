<x-core::layout title="খতিয়ান" title-en="General Ledger" subtitle="{{ $from->format('d M, Y') }} – {{ $to->format('d M, Y') }}" subtitle-en="{{ $from->format('d M, Y') }} – {{ $to->format('d M, Y') }}" active="accounting-reports">
    <x-accounting::tabbar active="general-ledger" />
    <x-accounting::filters :shops="$shops" :dates="['from' => ['থেকে', 'From', $from->toDateString()], 'to' => ['পর্যন্ত', 'To', $to->toDateString()]]">
        <x-slot:extra>
            <div style="width:280px; flex-shrink:0;">
                <x-core::select size="sm" name="account_id" label="অ্যাকাউন্ট" label-en="Account"
                    :options="$accounts->mapWithKeys(fn ($a) => [$a->id => $a->code.' — '.$a->name])->all()"
                    :value="$account?->id" placeholder="-- অ্যাকাউন্ট নির্বাচন করুন --" placeholder-en="-- Choose an account --" />
            </div>
        </x-slot:extra>
    </x-accounting::filters>

    @if ($report)
        <div class="table-container table-teal">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                            <th><span class="bn">এন্ট্রি</span><span class="en" style="display:none;">Entry</span></th>
                            <th><span class="bn">বিবরণ</span><span class="en" style="display:none;">Details</span></th>
                            <th class="table-cell-right"><span class="bn">ডেবিট</span><span class="en" style="display:none;">Debit</span></th>
                            <th class="table-cell-right"><span class="bn">ক্রেডিট</span><span class="en" style="display:none;">Credit</span></th>
                            <th class="table-cell-right"><span class="bn">ব্যালেন্স</span><span class="en" style="display:none;">Balance</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="font-weight:600;"><span class="bn">প্রারম্ভিক ব্যালেন্স</span><span class="en" style="display:none;">Opening Balance</span></td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:600;">{{ number_format($report['opening'], 2) }}</td>
                        </tr>
                        @forelse ($report['lines'] as $row)
                            <tr>
                                <td>{{ $row['line']->entry->entry_date->format('d M, Y') }}</td>
                                <td><a href="{{ route('journal-entries.show', $row['line']->entry) }}" style="font-family:var(--font-mono, monospace);">{{ $row['line']->entry->number }}</a></td>
                                <td style="font-size:12.5px;">
                                    {{ $row['line']->entry->narration }}
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $row['line']->shop?->name }} @if ($row['line']->party) · {{ $row['line']->party->name }} @endif {{ $row['line']->memo ? '· '.$row['line']->memo : '' }}</div>
                                </td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ (float) $row['line']->debit ? number_format((float) $row['line']->debit, 2) : '' }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ (float) $row['line']->credit ? number_format((float) $row['line']->credit, 2) : '' }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-core::table.empty icon="file-text" title="এই সময়ে কোনো লেনদেন নেই" title-en="No movements in this period" /></td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align:right; font-weight:700;"><span class="bn">সমাপনী ব্যালেন্স</span><span class="en" style="display:none;">Closing Balance</span></td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format($report['closing'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <x-core::table.empty icon="file-text" title="একটি অ্যাকাউন্ট নির্বাচন করুন" title-en="Choose an account" />
    @endif
</x-core::layout>
