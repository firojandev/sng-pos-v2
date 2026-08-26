<x-core::layout
    title="গুদাম"
    title-en="Warehouses"
    subtitle="আপনার শাখার গুদামসমূহ পরিচালনা করুন"
    subtitle-en="Manage your branch warehouses"
    active="branches"
>
    <x-shop::tabbar active="warehouses" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('warehouses.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন গুদাম</span><span class="en">New Warehouse</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">গুদামের নাম</th><th class="en" style="display:none;">Warehouse</th>
                            <th class="bn">শাখা</th><th class="en" style="display:none;">Branch</th>
                            <th class="bn">ঠিকানা</th><th class="en" style="display:none;">Address</th>
                            <th class="bn">ব্যাচ সংখ্যা</th><th class="en" style="display:none;">Batches</th>
                            <th class="bn">অবস্থা</th><th class="en" style="display:none;">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warehouses as $warehouse)
                            <tr>
                                <td class="cell-main">{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->branch->name ?? '—' }}</td>
                                <td>{{ $warehouse->address ?: '—' }}</td>
                                <td>{{ $warehouse->batches_count }}</td>
                                <td>
                                    @if ($warehouse->status === 'active')
                                        <span class="badge b-green bn">সক্রিয়</span><span class="badge b-green en" style="display:none;">Active</span>
                                    @else
                                        <span class="badge b-grey bn">নিষ্ক্রিয়</span><span class="badge b-grey en" style="display:none;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('warehouses.edit', $warehouse) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" onsubmit="return confirm('এই গুদাম মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="helper" style="margin-top:0;">কোনো গুদাম নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $warehouses->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
