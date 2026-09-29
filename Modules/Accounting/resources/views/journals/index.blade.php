<x-core::layout title="জার্নাল এন্ট্রি" title-en="Journal Entries" subtitle="লেজারে পোস্ট করা সকল এন্ট্রি" subtitle-en="Every entry posted to the ledger" active="journal-entries">
    <x-accounting::tabbar active="journals" />

    <x-accounting::filters :shops="$shops" :dates="['from' => ['থেকে', 'From', request('from')], 'to' => ['পর্যন্ত', 'To', request('to')]]">
        <x-slot:extra>
            <div style="flex:1; min-width:200px;">
                <x-core::input size="sm" name="search" label="খুঁজুন" label-en="Search" :value="request('search')" placeholder="নম্বর, বিবরণ বা রেফারেন্স" placeholder-en="Number, narration or reference" />
            </div>
        </x-slot:extra>
    </x-accounting::filters>

    <div class="section-row" style="margin-bottom:12px;">
        <div class="filters"></div>
        @can('accounting.create')
            <x-core::button as="a" href="{{ route('journal-entries.create') }}" size="sm" variant="solid" color="primary" icon="plus">
                <span class="bn">নতুন জার্নাল এন্ট্রি</span><span class="en" style="display:none;">New Journal Entry</span>
            </x-core::button>
        @endcan
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                        <th><span class="bn">নম্বর</span><span class="en" style="display:none;">Number</span></th>
                        <th><span class="bn">বিবরণ</span><span class="en" style="display:none;">Narration</span></th>
                        <th class="table-cell-right"><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Amount</span></th>
                        <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->entry_date->format('d M, Y') }}</td>
                            <td style="font-family:var(--font-mono, monospace); font-weight:600;">
                                {{ $entry->number }}
                                @if ($entry->isReversed())
                                    <x-core::badge color="grey" size="xs" label="রিভার্সড" label-en="Reversed" />
                                @elseif ($entry->reversal_of_id)
                                    <x-core::badge color="gold" size="xs" label="রিভার্সাল" label-en="Reversal" />
                                @endif
                            </td>
                            <td>
                                {{ $entry->narration }}
                                @if ($entry->reference)
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $entry->reference }}</div>
                                @endif
                            </td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ number_format($entry->total(), 2) }}</td>
                            <td class="table-cell-right">
                                <x-core::button :href="route('journal-entries.show', $entry)" size="sm" variant="soft" color="teal" icon="eye" icon-only title="দেখুন / View" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-core::table.empty icon="file-text" title="কোনো জার্নাল এন্ট্রি নেই" title-en="No journal entries" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px;">{{ $entries->links() }}</div>
</x-core::layout>
