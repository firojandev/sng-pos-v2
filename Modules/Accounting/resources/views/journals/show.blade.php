<x-core::layout title="জার্নাল এন্ট্রি {{ $entry->number }}" title-en="Journal Entry {{ $entry->number }}" subtitle="{{ $entry->narration }}" subtitle-en="{{ $entry->narration }}" active="journal-entries">
    <x-accounting::tabbar active="journals" />

    <div class="panel" style="margin-top:16px;">
        <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div class="panel-title">
                {{ $entry->number }} · {{ $entry->entry_date->format('d M, Y') }}
                @if ($entry->isReversed())
                    <x-core::badge color="grey" size="xs" label="রিভার্সড" label-en="Reversed" />
                @endif
            </div>
            <div style="display:flex; gap:8px;">
                @if ($entry->reversalOf)
                    <x-core::button :href="route('journal-entries.show', $entry->reversalOf)" size="sm" variant="secondary">
                        <span class="bn">মূল এন্ট্রি</span><span class="en" style="display:none;">Original Entry</span>
                    </x-core::button>
                @endif
                @if ($entry->reversal)
                    <x-core::button :href="route('journal-entries.show', $entry->reversal)" size="sm" variant="secondary">
                        <span class="bn">রিভার্সাল এন্ট্রি</span><span class="en" style="display:none;">Reversal Entry</span>
                    </x-core::button>
                @endif
                @can('accounting.delete')
                    @if (! $entry->isReversed() && ! $entry->reversal_of_id)
                        <form method="POST" action="{{ route('journal-entries.reverse', $entry) }}" class="delete-form" data-title="এন্ট্রি রিভার্স করবেন?" data-text="বিপরীত একটি এন্ট্রি পোস্ট হবে; মূল এন্ট্রি মুছে যাবে না।">
                            @csrf
                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="rotate-ccw">
                                <span class="bn">রিভার্স করুন</span><span class="en" style="display:none;">Reverse</span>
                            </x-core::button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
        <div class="panel-body" style="font-size:12.5px; color:var(--ink-600); padding-bottom:0;">
            @if ($entry->reference) <span class="bn">রেফারেন্স</span><span class="en" style="display:none;">Reference</span>: {{ $entry->reference }} · @endif
            <span class="bn">পোস্ট করেছেন</span><span class="en" style="display:none;">Posted by</span>: {{ $entry->creator?->name ?? '—' }}
        </div>
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                        <th><span class="bn">দোকান / পক্ষ</span><span class="en" style="display:none;">Shop / Party</span></th>
                        <th><span class="bn">নোট</span><span class="en" style="display:none;">Memo</span></th>
                        <th class="table-cell-right"><span class="bn">ডেবিট</span><span class="en" style="display:none;">Debit</span></th>
                        <th class="table-cell-right"><span class="bn">ক্রেডিট</span><span class="en" style="display:none;">Credit</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entry->lines as $line)
                        <tr>
                            <td>{{ $line->account?->code }} — {{ $line->account?->name }}</td>
                            <td style="font-size:12.5px;">{{ $line->shop?->name }} @if ($line->party) · {{ $line->party->name }} @endif</td>
                            <td style="font-size:12.5px;">{{ $line->memo }}</td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ (float) $line->debit ? number_format((float) $line->debit, 2) : '' }}</td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ (float) $line->credit ? number_format((float) $line->credit, 2) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right; font-weight:700;"><span class="bn">মোট</span><span class="en" style="display:none;">Total</span></td>
                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format((float) $entry->lines->sum('debit'), 2) }}</td>
                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format((float) $entry->lines->sum('credit'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-core::layout>
