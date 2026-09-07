<x-core::layout
    title="স্টকের ইতিহাস"
    title-en="Stock History"
    subtitle="প্রতিটি ক্রয়, বিক্রয় ও সমন্বয়ে স্টকের পরিবর্তন দেখুন"
    subtitle-en="View every stock movement from purchases, sales and manual adjustments"
    active="stock"
>
    {{-- Top Tab Navigation --}}
    <div class="tabbar" style="margin-bottom:16px;">
        <a href="{{ route('stock.index') }}" class="tabbtn">
            <span class="bn">স্টক খাতা</span><span class="en" style="display:none;">Stock Ledger</span>
        </a>
        <a href="{{ route('stock.history') }}" class="tabbtn active">
            <span class="bn">স্টকের ইতিহাস</span><span class="en" style="display:none;">Stock History</span>
        </a>
        <a href="{{ route('stock-transfers.index') }}" class="tabbtn">
            <span class="bn">স্টক ট্রান্সফার</span><span class="en" style="display:none;">Stock Transfers</span>
        </a>
        <a href="{{ route('batches.index') }}" class="tabbtn">
            <span class="bn">ব্যাচসমূহ</span><span class="en" style="display:none;">Batches</span>
        </a>
    </div>

    {{-- Filter Toolbar --}}
    <form method="GET" action="{{ route('stock.history') }}" class="section-row" style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        @if (request('product_id'))
            <input type="hidden" name="product_id" value="{{ request('product_id') }}">
        @endif

        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
            <div style="width:240px; flex-shrink:0;">
                <x-core::input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="পণ্য বা SKU দিয়ে খুঁজুন..."
                    placeholder-en="Search by product or SKU..."
                    icon="search"
                    size="sm"
                    :no-margin="true"
                />
            </div>
            <div style="width:160px; flex-shrink:0;">
                <x-core::select
                    name="type"
                    size="sm"
                    :no-margin="true"
                    :value="$type"
                    :options="[
                        'all' => 'সব ধরন (All)',
                        'in' => 'বৃদ্ধি ইন (+ In)',
                        'out' => 'হ্রাস আউট (- Out)',
                    ]"
                    onchange="this.form.submit()"
                />
            </div>
            <x-core::button
                type="submit"
                variant="secondary"
                size="sm"
                icon="search"
            >
                <span class="bn">ফিল্টার</span>
                <span class="en" style="display:none;">Filter</span>
            </x-core::button>
            @if ($search !== '' || $type !== 'all' || request('product_id'))
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="rotate-ccw"
                    :href="route('stock.history')"
                    title="রিসেট / Reset"
                >
                    <span class="bn">রিসেট</span>
                    <span class="en" style="display:none;">Reset</span>
                </x-core::button>
            @endif
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <x-core::button
                :href="route('stock.index')"
                size="sm"
                variant="secondary"
                icon="arrow-left"
            >
                <span class="bn">স্টক খাতা</span>
                <span class="en" style="display:none;">Stock Ledger</span>
            </x-core::button>
        </div>
    </form>

    @if (isset($product) && $product)
        <div style="margin-bottom:16px; padding:10px 14px; background:var(--paper); border:1px solid var(--border); border-radius:10px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:13px; font-weight:600; color:var(--ink-700);">
                    <span class="bn">ফিল্টার করা পণ্য:</span>
                    <span class="en" style="display:none;">Filtered Product:</span>
                </span>
                <x-core::badge color="teal" size="sm" icon="package">
                    {{ $product->name }}@if ($product->sku) (SKU: {{ $product->sku }})@endif
                </x-core::badge>
            </div>
            <x-core::button size="xs" variant="secondary" :href="route('stock.history')" icon="x">
                <span class="bn">সকল পণ্য দেখুন</span>
                <span class="en" style="display:none;">Show All Products</span>
            </x-core::button>
        </div>
    @endif

    {{-- History Data Table --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="bn" style="width:160px;">তারিখ ও সময়</th><th class="en" style="display:none;">Date &amp; Time</th>
                        <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                        <th class="bn" style="width:130px;">ব্যাচ</th><th class="en" style="display:none;">Batch</th>
                        <th class="bn" style="width:140px;">ধরন</th><th class="en" style="display:none;">Type</th>
                        <th class="bn table-cell-right" style="width:110px;">পরিমাণ</th><th class="en" style="display:none;">Quantity</th>
                        <th class="bn table-cell-center" style="width:140px;">আগে &rarr; পরে</th><th class="en" style="display:none;">Before &rarr; After</th>
                        <th class="bn" style="width:180px;">রেফারেন্স</th><th class="en" style="display:none;">Reference</th>
                        <th class="bn" style="width:130px;">দ্বারা</th><th class="en" style="display:none;">By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        @php $label = $movement->typeLabel(); @endphp
                        <tr>
                            <td style="white-space:nowrap; color:var(--ink-600); font-size:12.5px;">
                                {{ $movement->created_at->format('d M, Y, h:i A') }}
                            </td>
                            <td>
                                <div style="font-weight:700; color:var(--ink-900); font-size:13px;">
                                    {{ $movement->product->name ?? '—' }}
                                </div>
                                @if (!empty($movement->product->sku))
                                    <div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400); margin-top:2px;">
                                        SKU: {{ $movement->product->sku }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-800); font-size:12.5px;">
                                    {{ $movement->batch->batch_no ?? '—' }}
                                </span>
                            </td>
                            <td>
                                @if (str_starts_with($movement->type, 'purchase') || $movement->type === 'adjustment_increase' || $movement->type === 'transfer_in')
                                    <x-core::badge color="green" size="xs">{{ $label['bn'] }}</x-core::badge>
                                @elseif (str_starts_with($movement->type, 'sale') || $movement->type === 'adjustment_decrease' || $movement->type === 'transfer_out')
                                    <x-core::badge color="red" size="xs">{{ $label['bn'] }}</x-core::badge>
                                @else
                                    <x-core::badge color="grey" size="xs">{{ $label['bn'] }}</x-core::badge>
                                @endif
                            </td>
                            <td class="table-cell-right">
                                <span style="font-weight:800; font-family:var(--font-mono, monospace); font-size:13px; color:{{ $movement->quantity_change >= 0 ? 'var(--green-600)' : 'var(--red-600)' }};">
                                    {{ $movement->quantity_change >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($movement->quantity_change, 2), '0'), '.') }}
                                </span>
                            </td>
                            <td class="table-cell-center" style="font-family:var(--font-mono, monospace); font-size:12px; color:var(--ink-600);">
                                {{ rtrim(rtrim(number_format($movement->quantity_before, 2), '0'), '.') }} &rarr; {{ rtrim(rtrim(number_format($movement->quantity_after, 2), '0'), '.') }}
                            </td>
                            <td style="color:var(--ink-700); font-size:12px;">
                                @if ($movement->reference_type === \Modules\Purchase\Models\Purchase::class && $movement->reference)
                                    <span class="bn">ক্রয় ইনভয়েস:</span><span class="en" style="display:none;">Purchase:</span> #{{ $movement->reference->invoice_no }}
                                @elseif ($movement->reference_type === \Modules\Sales\Models\Sale::class && $movement->reference)
                                    <span class="bn">বিক্রয় ইনভয়েস:</span><span class="en" style="display:none;">Sale:</span> #{{ $movement->reference->invoice_no }}
                                @elseif ($movement->reference_type === \Modules\Product\Models\StockAdjustment::class)
                                    {{ $movement->note ?: 'স্টক সমন্বয়' }}
                                @else
                                    {{ $movement->note ?: '—' }}
                                @endif
                            </td>
                            <td style="color:var(--ink-600); font-size:12px;">
                                {{ $movement->creator->name ?? '—' }}
                            </td>
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

        @if ($movements->hasPages())
            <div style="padding:14px 18px; border-top:1px solid var(--border);">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</x-core::layout>
