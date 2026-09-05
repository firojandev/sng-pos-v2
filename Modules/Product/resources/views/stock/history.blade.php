<x-core::layout
    title="স্টকের ইতিহাস"
    title-en="Stock History"
    subtitle="প্রতিটি ক্রয়, বিক্রয় ও সমন্বয়ে স্টকের পরিবর্তন দেখুন"
    subtitle-en="View every stock change from purchases, sales and manual adjustments"
    active="stock"
>
    <div class="cash-page-head">
        <a href="{{ route('stock.index') }}" class="back" title="Back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 18l-6-6 6-6" stroke="#1C2B27" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <div class="ttl bn">স্টকের ইতিহাস</div>
        <div class="ttl en" style="display:none;">Stock History</div>
    </div>

    <form method="GET" action="{{ route('stock.history') }}" class="section-row">
        @if (request('product_id'))
            <input type="hidden" name="product_id" value="{{ request('product_id') }}">
        @endif
        <div class="filters">
            <div class="search-inline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#8B978F" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="#8B978F" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="পণ্যের নাম দিয়ে খুঁজুন">
            </div>
            <select name="type" onchange="this.form.submit()">
                <option value="all" @selected($type === 'all')>সব ধরন</option>
                <option value="in" @selected($type === 'in')>বৃদ্ধি (ইন)</option>
                <option value="out" @selected($type === 'out')>হ্রাস (আউট)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">
            <span class="bn">খুঁজুন</span><span class="en">Search</span>
        </button>
    </form>

    @if (isset($product) && $product)
        <div style="margin-bottom:14px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span style="font-size:13px; color:var(--ink-600);"><span class="bn">নির্দিষ্ট পণ্য:</span><span class="en" style="display:none;">Filtered Product:</span></span>
            <x-core::badge color="teal" size="sm" icon="package">
                {{ $product->name }} (SKU: {{ $product->sku }})
            </x-core::badge>
            <x-core::button size="xs" variant="secondary" :href="route('stock.history')" icon="x" title="সকল পণ্য দেখুন / Show all products">
                <span class="bn">সব দেখুন</span><span class="en" style="display:none;">Show All</span>
            </x-core::button>
        </div>
    @endif

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">তারিখ ও সময়</th><th class="en" style="display:none;">Date &amp; Time</th>
                            <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                            <th class="bn">ব্যাচ</th><th class="en" style="display:none;">Batch</th>
                            <th class="bn">ধরন</th><th class="en" style="display:none;">Type</th>
                            <th class="bn">পরিমাণ</th><th class="en" style="display:none;">Quantity</th>
                            <th class="bn">আগে &rarr; পরে</th><th class="en" style="display:none;">Before &rarr; After</th>
                            <th class="bn">রেফারেন্স</th><th class="en" style="display:none;">Reference</th>
                            <th class="bn">দ্বারা</th><th class="en" style="display:none;">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            @php $label = $movement->typeLabel(); @endphp
                            <tr>
                                <td>{{ $movement->created_at->format('d M, Y, h:i A') }}</td>
                                <td class="cell-main">{{ $movement->product->name ?? '—' }}</td>
                                <td>{{ $movement->batch->batch_no ?? '—' }}</td>
                                <td>
                                    @if (str_starts_with($movement->type, 'purchase') || $movement->type === 'adjustment_increase')
                                        <span class="badge b-green bn">{{ $label['bn'] }}</span><span class="badge b-green en" style="display:none;">{{ $label['en'] }}</span>
                                    @elseif (str_starts_with($movement->type, 'sale') || $movement->type === 'adjustment_decrease')
                                        <span class="badge b-red bn">{{ $label['bn'] }}</span><span class="badge b-red en" style="display:none;">{{ $label['en'] }}</span>
                                    @else
                                        <span class="badge b-grey bn">{{ $label['bn'] }}</span><span class="badge b-grey en" style="display:none;">{{ $label['en'] }}</span>
                                    @endif
                                </td>
                                <td style="font-weight:700; color:{{ $movement->quantity_change >= 0 ? 'var(--green-600)' : 'var(--red-600)' }};">
                                    {{ $movement->quantity_change >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($movement->quantity_change, 2), '0'), '.') }}
                                </td>
                                <td>{{ rtrim(rtrim(number_format($movement->quantity_before, 2), '0'), '.') }} &rarr; {{ rtrim(rtrim(number_format($movement->quantity_after, 2), '0'), '.') }}</td>
                                <td>
                                    @if ($movement->reference_type === \Modules\Purchase\Models\Purchase::class && $movement->reference)
                                        <span class="bn">ক্রয় </span><span class="en" style="display:none;">Purchase </span>#{{ $movement->reference->invoice_no }}
                                    @elseif ($movement->reference_type === \Modules\Sales\Models\Sale::class && $movement->reference)
                                        <span class="bn">বিক্রয় </span><span class="en" style="display:none;">Sale </span>#{{ $movement->reference->invoice_no }}
                                    @elseif ($movement->reference_type === \Modules\Product\Models\StockAdjustment::class)
                                        {{ $movement->note ?: '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $movement->creator->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-core::table.empty
                                        icon="history"
                                        title="কোনো স্টক পরিবর্তন নেই"
                                        title-en="No stock history found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
