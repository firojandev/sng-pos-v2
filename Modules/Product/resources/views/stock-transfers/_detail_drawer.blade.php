@php $label = $transfer->statusLabel(); @endphp

<div class="drawer-head" style="padding:20px 24px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="width:42px; height:42px; border-radius:10px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <x-core::icon name="arrow-left-right" size="20" />
        </div>
        <div>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <div class="drawer-title" style="font-size:17px; font-weight:700; color:var(--ink-900); font-family:var(--font-mono, monospace);">
                    {{ $transfer->transfer_no ?: ('#TR-'.str_pad((string) $transfer->id, 4, '0', STR_PAD_LEFT)) }}
                </div>
                @if ($transfer->status === 'received')
                    <x-core::badge color="green" size="xs" :dot="true">{{ $label['bn'] }}</x-core::badge>
                @elseif ($transfer->status === 'cancelled')
                    <x-core::badge color="red" size="xs" :dot="true">{{ $label['bn'] }}</x-core::badge>
                @elseif ($transfer->status === 'dispatched')
                    <x-core::badge color="blue" size="xs" :dot="true">{{ $label['bn'] }}</x-core::badge>
                @elseif ($transfer->status === 'approved')
                    <x-core::badge color="teal" size="xs" :dot="true">{{ $label['bn'] }}</x-core::badge>
                @else
                    <x-core::badge color="gold" size="xs" :dot="true">{{ $label['bn'] }}</x-core::badge>
                @endif
            </div>
            <div style="font-size:12px; color:var(--ink-500); margin-top:2px;">
                <span class="bn">অনুরোধের তারিখ:</span>
                <span class="en" style="display:none;">Requested Date:</span>
                {{ $transfer->created_at->format('d M, Y, h:i A') }}
            </div>
        </div>
    </div>
    <button type="button" class="drawer-x" style="cursor:pointer; background:transparent; border:none; color:var(--ink-400); font-size:24px; line-height:1;" title="Close">&times;</button>
</div>

<div class="drawer-body" style="padding:24px; display:flex; flex-direction:column; gap:20px;">
    {{-- Route Card --}}
    <div style="display:grid; grid-template-columns:1fr auto 1fr; align-items:center; gap:12px; background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:16px;">
        <div style="min-width:0;">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--ink-400); letter-spacing:0.5px;">
                <span class="bn">উৎস গুদাম (হতে)</span><span class="en" style="display:none;">From</span>
            </div>
            <div style="font-size:14px; font-weight:700; color:var(--ink-900); margin-top:4px;">
                {{ $transfer->fromWarehouse->name ?? '—' }}
            </div>
            @if ($transfer->fromWarehouse?->branch)
                <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                    {{ $transfer->fromWarehouse->branch->name }}
                </div>
            @endif
        </div>

        <div style="width:32px; height:32px; border-radius:50%; background:var(--card); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--teal-700); flex-shrink:0;">
            <x-core::icon name="arrow-right" size="16" />
        </div>

        <div style="min-width:0; text-align:right;">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--ink-400); letter-spacing:0.5px;">
                <span class="bn">গন্তব্য গুদাম (প্রতি)</span><span class="en" style="display:none;">To</span>
            </div>
            <div style="font-size:14px; font-weight:700; color:var(--ink-900); margin-top:4px;">
                {{ $transfer->toWarehouse->name ?? '—' }}
            </div>
            @if ($transfer->toWarehouse?->branch)
                <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                    {{ $transfer->toWarehouse->branch->name }}
                </div>
            @endif
        </div>
    </div>

    {{-- Audit Timeline & Stages --}}
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px;">
        <div style="font-size:13px; font-weight:700; color:var(--ink-800); margin-bottom:12px;">
            <span class="bn">কার্যক্রমের বিবরণ (Timeline)</span>
            <span class="en" style="display:none;">Activity Timeline</span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:10px 12px;">
                <div style="font-size:11px; color:var(--ink-500);">অনুরোধকারী</div>
                <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-top:2px;">{{ $transfer->requestedBy->name ?? '—' }}</div>
                <div style="font-size:10.5px; color:var(--ink-400); margin-top:2px;">{{ $transfer->created_at->format('d M, h:i A') }}</div>
            </div>

            @if ($transfer->approved_at)
                <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:10px 12px;">
                    <div style="font-size:11px; color:var(--teal-700); font-weight:600;">অনুমোদনকারী</div>
                    <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-top:2px;">{{ $transfer->approvedBy->name ?? '—' }}</div>
                    <div style="font-size:10.5px; color:var(--ink-400); margin-top:2px;">{{ $transfer->approved_at->format('d M, h:i A') }}</div>
                </div>
            @endif

            @if ($transfer->dispatched_at)
                <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:10px 12px;">
                    <div style="font-size:11px; color:var(--blue-ink); font-weight:600;">প্রেরণকারী</div>
                    <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-top:2px;">{{ $transfer->dispatchedBy->name ?? '—' }}</div>
                    <div style="font-size:10.5px; color:var(--ink-400); margin-top:2px;">{{ $transfer->dispatched_at->format('d M, h:i A') }}</div>
                </div>
            @endif

            @if ($transfer->received_at)
                <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:10px 12px;">
                    <div style="font-size:11px; color:var(--green-ink); font-weight:600;">গ্রহণকারী</div>
                    <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-top:2px;">{{ $transfer->receivedBy->name ?? '—' }}</div>
                    <div style="font-size:10.5px; color:var(--ink-400); margin-top:2px;">{{ $transfer->received_at->format('d M, h:i A') }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
            <div style="font-size:13.5px; font-weight:700; color:var(--ink-800);">
                <span class="bn">স্থানান্তরিত পণ্যের তালিকা</span>
                <span class="en" style="display:none;">Transfer Items</span>
            </div>
            <x-core::badge color="teal" size="xs">
                {{ $transfer->items->count() }} টি পণ্য &middot; মোট {{ rtrim(rtrim(number_format($transfer->items->sum('quantity'), 2), '0'), '.') }} একক
            </x-core::badge>
        </div>

        <div class="table-wrap" style="border:1px solid var(--border); border-radius:10px; overflow:hidden; background:var(--card);">
            <table class="app-table" style="width:100%; border-collapse:collapse; font-size:12.5px;">
                <thead>
                    <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                        <th style="padding:10px 14px; text-align:left; font-weight:700; color:var(--ink-700);"><span class="bn">পণ্য</span><span class="en" style="display:none;">Product</span></th>
                        <th style="padding:10px 14px; text-align:left; font-weight:700; color:var(--ink-700);"><span class="bn">ব্যাচ নং</span><span class="en" style="display:none;">Batch No</span></th>
                        <th style="padding:10px 14px; text-align:right; font-weight:700; color:var(--ink-700);"><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Quantity</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transfer->items as $item)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 14px;">
                                <div style="font-weight:700; color:var(--ink-900); font-size:13px;">{{ $item->product->name ?? '—' }}</div>
                                @if (isset($item->product->sku))
                                    <div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400); margin-top:2px;">
                                        SKU: {{ $item->product->sku }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding:10px 14px;">
                                <span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-800); font-size:12px;">
                                    {{ $item->batch_no }}
                                </span>
                            </td>
                            <td style="padding:10px 14px; text-align:right;">
                                <span style="font-weight:800; font-family:var(--font-mono, monospace); color:var(--teal-800); font-size:13px;">
                                    {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($transfer->note)
        <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px 14px;">
            <div style="font-size:11.5px; font-weight:700; color:var(--ink-500); margin-bottom:4px;">
                <span class="bn">মন্তব্য / নোট:</span><span class="en" style="display:none;">Note:</span>
            </div>
            <div style="font-size:12.5px; color:var(--ink-800); line-height:1.4;">{{ $transfer->note }}</div>
        </div>
    @endif
</div>

{{-- Drawer Actions Footer --}}
<div class="drawer-foot" style="padding:16px 24px; border-top:1px solid var(--border); background:var(--card); display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
    <x-core::button
        type="button"
        variant="secondary"
        size="sm"
        onclick="closeModal('transferDetailDrawer')"
    >
        <span class="bn">বন্ধ করুন</span>
        <span class="en" style="display:none;">Close</span>
    </x-core::button>

    @can('stock.transfer')
    <div style="display:flex; align-items:center; gap:8px;">
        @if ($transfer->status === 'pending')
            <form method="POST" action="{{ route('stock-transfers.approve', $transfer) }}" class="inline-drawer-action-form" style="display:inline;">
                @csrf
                <x-core::button
                    type="submit"
                    color="primary"
                    size="sm"
                    icon="check"
                >
                    <span class="bn">অনুমোদন করুন</span>
                    <span class="en" style="display:none;">Approve</span>
                </x-core::button>
            </form>
            <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" class="delete-form inline-drawer-action-form" data-title="ট্রান্সফার বাতিল করতে চান?" data-text="এই ট্রান্সফার বাতিল করা হবে। আপনি কি নিশ্চিত?" style="display:inline;">
                @csrf
                <x-core::button
                    type="submit"
                    color="danger"
                    size="sm"
                    icon="x"
                >
                    <span class="bn">বাতিল করুন</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>
            </form>
        @elseif ($transfer->status === 'approved')
            <form method="POST" action="{{ route('stock-transfers.dispatch', $transfer) }}" class="inline-drawer-action-form" style="display:inline;">
                @csrf
                <x-core::button
                    type="submit"
                    color="primary"
                    size="sm"
                    icon="arrow-right"
                >
                    <span class="bn">প্রেরণ করুন</span>
                    <span class="en" style="display:none;">Dispatch</span>
                </x-core::button>
            </form>
            <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" class="delete-form inline-drawer-action-form" data-title="ট্রান্সফার বাতিল করতে চান?" data-text="এই ট্রান্সফার বাতিল করা হবে। আপনি কি নিশ্চিত?" style="display:inline;">
                @csrf
                <x-core::button
                    type="submit"
                    color="danger"
                    size="sm"
                    icon="x"
                >
                    <span class="bn">বাতিল করুন</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>
            </form>
        @elseif ($transfer->status === 'dispatched')
            <form method="POST" action="{{ route('stock-transfers.receive', $transfer) }}" class="inline-drawer-action-form" style="display:inline;">
                @csrf
                <x-core::button
                    type="submit"
                    color="primary"
                    size="sm"
                    icon="check-circle"
                >
                    <span class="bn">গ্রহণ নিশ্চিত করুন</span>
                    <span class="en" style="display:none;">Confirm Receipt</span>
                </x-core::button>
            </form>
        @endif
    </div>
    @endcan
</div>
