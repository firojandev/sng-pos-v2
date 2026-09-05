<div class="modal-backdrop" id="stockHistoryModal" style="z-index:1000;">
    <div class="modal-box" id="stockHistoryModalContent" style="width:880px; max-width:96vw; max-height:92vh; overflow-y:auto; padding:24px; border-radius:18px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
        {{-- Modal Header --}}
        <div class="modal-head" style="margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:46px; height:46px; border-radius:12px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.04);">
                    <x-core::icon name="history" size="24" />
                </div>
                <div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <h3 class="modal-title" style="font-size:18px; font-weight:700; color:var(--ink-900); margin:0; line-height:1.3;">
                            <span class="bn">স্টকের ইতিহাস: {{ $product->name }}</span>
                            <span class="en" style="display:none;">Stock History: {{ $product->name }}</span>
                        </h3>
                        @if ($totalStock <= 0)
                            <x-core::badge color="red" size="xs" :dot="true" label="স্টক আউট (০)" label-en="Out of Stock (0)" />
                        @elseif ($product->alert_qty > 0 && $totalStock <= $product->alert_qty)
                            <x-core::badge color="gold" size="xs" :dot="true" label="কম স্টক ({{ rtrim(rtrim(number_format($totalStock, 2), '0'), '.') }})" label-en="Low Stock ({{ rtrim(rtrim(number_format($totalStock, 2), '0'), '.') }})" />
                        @else
                            <x-core::badge color="green" size="xs" :dot="true" label="বর্তমান মজুদ: {{ rtrim(rtrim(number_format($totalStock, 2), '0'), '.') }}" label-en="Current Stock: {{ rtrim(rtrim(number_format($totalStock, 2), '0'), '.') }}" />
                        @endif
                    </div>

                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-size:12px; color:var(--ink-500); margin-top:6px;">
                        <span style="display:inline-flex; align-items:center; gap:4px; background:var(--paper-line); padding:2px 8px; border-radius:6px; font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); border:1px solid var(--border);">
                            <x-core::icon name="barcode" size="12" /> SKU: {{ $product->sku }}
                        </span>
                        @if ($product->category)
                            <span style="display:inline-flex; align-items:center; gap:4px; color:var(--ink-700);">
                                <x-core::icon name="folder" size="12" style="color:var(--ink-400);" /> {{ $product->category->name }}
                            </span>
                        @endif
                        @if ($product->brand)
                            <span style="display:inline-flex; align-items:center; gap:4px; color:var(--ink-700);">
                                <x-core::icon name="tag" size="12" style="color:var(--ink-400);" /> {{ $product->brand->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <button type="button" class="drawer-x" onclick="closeModal('stockHistoryModal')" style="cursor:pointer; background:transparent; border:none; color:var(--ink-400); font-size:24px; line-height:1;" title="Close">&times;</button>
        </div>

        {{-- Summary Metric Badges --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:10px; margin-bottom:18px;">
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 14px;">
                <div style="font-size:11px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">
                    <span class="bn">বর্তমান মজুদ</span><span class="en" style="display:none;">Current Stock</span>
                </div>
                <div style="font-size:18px; font-weight:800; color:var(--teal-800); font-family:var(--font-mono, monospace); margin-top:3px;">
                    {{ rtrim(rtrim(number_format($totalStock, 2), '0'), '.') }}
                </div>
            </div>

            <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 14px;">
                <div style="font-size:11px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">
                    <span class="bn">মোট আগমন (ইন)</span><span class="en" style="display:none;">Total Inflow</span>
                </div>
                <div style="font-size:18px; font-weight:800; color:var(--green-600); font-family:var(--font-mono, monospace); margin-top:3px;">
                    +{{ rtrim(rtrim(number_format($movements->where('quantity_change', '>', 0)->sum('quantity_change'), 2), '0'), '.') }}
                </div>
            </div>

            <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 14px;">
                <div style="font-size:11px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">
                    <span class="bn">মোট নির্গমন (আউট)</span><span class="en" style="display:none;">Total Outflow</span>
                </div>
                <div style="font-size:18px; font-weight:800; color:var(--red-600); font-family:var(--font-mono, monospace); margin-top:3px;">
                    {{ rtrim(rtrim(number_format($movements->where('quantity_change', '<', 0)->sum('quantity_change'), 2), '0'), '.') }}
                </div>
            </div>

            <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 14px;">
                <div style="font-size:11px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">
                    <span class="bn">মোট লেনদেন</span><span class="en" style="display:none;">Total Records</span>
                </div>
                <div style="font-size:18px; font-weight:800; color:var(--ink-800); font-family:var(--font-mono, monospace); margin-top:3px;">
                    {{ $movements->total() }}
                </div>
            </div>
        </div>

        {{-- Stock Movements Table --}}
        <div class="table-wrap" style="border:1px solid var(--border); border-radius:12px; overflow:hidden; background:var(--card);">
            <table class="app-table" style="width:100%; border-collapse:collapse; font-size:12.5px;">
                <thead>
                    <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                        <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700);"><span class="bn">তারিখ ও সময়</span><span class="en" style="display:none;">Date & Time</span></th>
                        <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700);"><span class="bn">ব্যাচ</span><span class="en" style="display:none;">Batch</span></th>
                        <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700);"><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                        <th style="padding:10px 12px; text-align:right; font-weight:700; color:var(--ink-700);"><span class="bn">পরিবর্তন</span><span class="en" style="display:none;">Change</span></th>
                        <th style="padding:10px 12px; text-align:center; font-weight:700; color:var(--ink-700);"><span class="bn">আগে &rarr; পরে</span><span class="en" style="display:none;">Before &rarr; After</span></th>
                        <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700);"><span class="bn">রেফারেন্স</span><span class="en" style="display:none;">Reference</span></th>
                        <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700);"><span class="bn">দ্বারা</span><span class="en" style="display:none;">By</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        @php $label = $movement->typeLabel(); @endphp
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 12px; white-space:nowrap; color:var(--ink-600);">
                                {{ $movement->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td style="padding:10px 12px;">
                                <span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-800); font-size:12px;">
                                    {{ $movement->batch->batch_no ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;">
                                @if (str_starts_with($movement->type, 'purchase') || $movement->type === 'adjustment_increase' || $movement->type === 'transfer_in')
                                    <x-core::badge color="green" size="xs">{{ $label['bn'] }}</x-core::badge>
                                @elseif (str_starts_with($movement->type, 'sale') || $movement->type === 'adjustment_decrease' || $movement->type === 'transfer_out')
                                    <x-core::badge color="red" size="xs">{{ $label['bn'] }}</x-core::badge>
                                @else
                                    <x-core::badge color="grey" size="xs">{{ $label['bn'] }}</x-core::badge>
                                @endif
                            </td>
                            <td style="padding:10px 12px; text-align:right;">
                                <span style="font-weight:700; font-family:var(--font-mono, monospace); color:{{ $movement->quantity_change >= 0 ? 'var(--green-600)' : 'var(--red-600)' }};">
                                    {{ $movement->quantity_change >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($movement->quantity_change, 2), '0'), '.') }}
                                </span>
                            </td>
                            <td style="padding:10px 12px; text-align:center; font-family:var(--font-mono, monospace); font-size:12px; color:var(--ink-600);">
                                {{ rtrim(rtrim(number_format($movement->quantity_before, 2), '0'), '.') }} &rarr; {{ rtrim(rtrim(number_format($movement->quantity_after, 2), '0'), '.') }}
                            </td>
                            <td style="padding:10px 12px; color:var(--ink-700);">
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
                            <td style="padding:10px 12px; color:var(--ink-600);">
                                {{ $movement->creator->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:24px; text-align:center;">
                                <x-core::table.empty
                                    icon="history"
                                    title="কোনো স্টক পরিবর্তন নেই"
                                    title-en="No stock movements recorded for this product"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal Footer --}}
        <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <div style="font-size:12px; color:var(--ink-500);">
                @if ($movements->total() > 15)
                    <span class="bn">সর্বশেষ ১৫টি রেকর্ড দেখানো হচ্ছে</span>
                    <span class="en" style="display:none;">Showing latest 15 records</span>
                @endif
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <x-core::button size="sm" variant="secondary" type="button" onclick="closeModal('stockHistoryModal')">
                    <span class="bn">বন্ধ করুন</span>
                    <span class="en" style="display:none;">Close</span>
                </x-core::button>

                <x-core::button size="sm" color="primary" icon="external-link" :href="route('stock.history', ['product_id' => $product->id])">
                    <span class="bn">সম্পূর্ণ ইতিহাস দেখুন</span>
                    <span class="en" style="display:none;">View Full History</span>
                </x-core::button>
            </div>
        </div>
    </div>
</div>
