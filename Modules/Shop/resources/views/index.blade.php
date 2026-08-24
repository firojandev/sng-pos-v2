<x-core::layout
    title="দোকানসমূহ"
    title-en="Shops"
    subtitle="সিস্টেমে নিবন্ধিত সকল দোকান পরিচালনা করুন"
    subtitle-en="Manage all shops registered on the platform"
    active="shops"
>
    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('shops.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন দোকান</span><span class="en">New Shop</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">দোকানের নাম</th><th class="en" style="display:none;">Shop Name</th>
                            <th class="bn">স্লাগ</th><th class="en" style="display:none;">Slug</th>
                            <th class="bn">ফোন</th><th class="en" style="display:none;">Phone</th>
                            <th class="bn">এডমিন সংখ্যা</th><th class="en" style="display:none;">Admins</th>
                            <th class="bn">সক্রিয় ফিচার</th><th class="en" style="display:none;">Enabled Features</th>
                            <th class="bn">অবস্থা</th><th class="en" style="display:none;">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shops as $shop)
                            <tr>
                                <td class="cell-main">{{ $shop->name }}</td>
                                <td>{{ $shop->slug }}</td>
                                <td>{{ $shop->phone ?? '—' }}</td>
                                <td>{{ $shop->admins_count }}</td>
                                <td>{{ count($shop->enabled_features ?? []) }}</td>
                                <td>
                                    @if ($shop->status === 'active')
                                        <span class="badge b-green bn">সক্রিয়</span><span class="badge b-green en" style="display:none;">Active</span>
                                    @else
                                        <span class="badge b-grey bn">নিষ্ক্রিয়</span><span class="badge b-grey en" style="display:none;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('shops.edit', $shop) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('shops.destroy', $shop) }}" onsubmit="return confirm('এই দোকান ও এর সকল তথ্য মুছে ফেলতে চান?');">
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
                            <tr><td colspan="7"><div class="helper" style="margin-top:0;">কোনো দোকান নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $shops->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
