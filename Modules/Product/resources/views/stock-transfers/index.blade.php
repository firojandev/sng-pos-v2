<x-core::layout
    title="স্টক ট্রান্সফার"
    title-en="Stock Transfer"
    subtitle="গুদামের মধ্যে পণ্য স্থানান্তর পরিচালনা করুন"
    subtitle-en="Manage stock transfers between warehouses"
    active="stock-transfers"
>
    <div class="section-row">
        <div class="filters">
            <form method="GET" action="{{ route('stock-transfers.index') }}">
                <select name="status" onchange="this.form.submit()">
                    <option value="all" @selected($status === 'all')>সব</option>
                    @foreach (\Modules\Product\Models\StockTransfer::statusLabels() as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label['bn'] }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <a class="btn btn-gold" href="{{ route('stock-transfers.create') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
            <span class="bn">নতুন ট্রান্সফার</span><span class="en">New Transfer</span>
        </a>
    </div>

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">ট্রান্সফার নং</th><th class="en" style="display:none;">Transfer No</th>
                            <th class="bn">থেকে</th><th class="en" style="display:none;">From</th>
                            <th class="bn">প্রতি</th><th class="en" style="display:none;">To</th>
                            <th class="bn">আইটেম</th><th class="en" style="display:none;">Items</th>
                            <th class="bn">অবস্থা</th><th class="en" style="display:none;">Status</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $transfer)
                            @php $label = $transfer->statusLabel(); @endphp
                            <tr style="cursor:pointer;" onclick="openModal('transferDetail-{{ $transfer->id }}')">
                                <td class="cell-main">#{{ $transfer->transfer_no }}</td>
                                <td>{{ $transfer->fromWarehouse->name ?? '—' }}</td>
                                <td>{{ $transfer->toWarehouse->name ?? '—' }}</td>
                                <td>{{ $transfer->items->count() }}</td>
                                <td>
                                    @if ($transfer->status === 'received')
                                        <span class="badge b-green bn">{{ $label['bn'] }}</span><span class="badge b-green en" style="display:none;">{{ $label['en'] }}</span>
                                    @elseif ($transfer->status === 'cancelled')
                                        <span class="badge b-red bn">{{ $label['bn'] }}</span><span class="badge b-red en" style="display:none;">{{ $label['en'] }}</span>
                                    @else
                                        <span class="badge b-gold bn">{{ $label['bn'] }}</span><span class="badge b-gold en" style="display:none;">{{ $label['en'] }}</span>
                                    @endif
                                </td>
                                <td>{{ $transfer->created_at->format('d M, Y') }}</td>
                                <td onclick="event.stopPropagation();">
                                    <div class="row-actions">
                                        <button type="button" class="act" title="Details" onclick="openModal('transferDetail-{{ $transfer->id }}')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="5.5" r="1.6" fill="#5C6B65"/><circle cx="12" cy="12" r="1.6" fill="#5C6B65"/><circle cx="12" cy="18.5" r="1.6" fill="#5C6B65"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-core::table.empty
                                        icon="arrow-left-right"
                                        title="কোনো স্টক ট্রান্সফার নেই"
                                        title-en="No stock transfers found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>

    @foreach ($transfers as $transfer)
        @php $label = $transfer->statusLabel(); @endphp
        <div class="drawer-backdrop" id="transferDetail-{{ $transfer->id }}">
            <div class="drawer">
                <div class="drawer-head">
                    <div class="drawer-title bn">ট্রান্সফার #{{ $transfer->transfer_no }}</div>
                    <div class="drawer-title en" style="display:none;">Transfer #{{ $transfer->transfer_no }}</div>
                    <button type="button" class="drawer-x" onclick="closeModal('transferDetail-{{ $transfer->id }}')">&times;</button>
                </div>

                <div class="tx-section">
                    <div class="tx-row">
                        <span class="lbl bn">থেকে</span><span class="lbl en" style="display:none;">From</span>
                        <span class="val">{{ $transfer->fromWarehouse->name ?? '—' }}</span>
                    </div>
                    <div class="tx-row">
                        <span class="lbl bn">প্রতি</span><span class="lbl en" style="display:none;">To</span>
                        <span class="val">{{ $transfer->toWarehouse->name ?? '—' }}</span>
                    </div>
                    <div class="tx-row strong">
                        <span class="lbl bn">অবস্থা</span><span class="lbl en" style="display:none;">Status</span>
                        <span class="val">{{ $label['bn'] }}</span>
                    </div>
                </div>

                <div class="tx-section">
                    <div class="tx-row">
                        <span class="lbl bn">অনুরোধ</span><span class="lbl en" style="display:none;">Requested</span>
                        <span class="val" style="font-weight:400;">{{ $transfer->requestedBy->name ?? '—' }} &middot; {{ $transfer->created_at->format('d M, h:i A') }}</span>
                    </div>
                    @if ($transfer->approved_at)
                        <div class="tx-row">
                            <span class="lbl bn">অনুমোদন</span><span class="lbl en" style="display:none;">Approved</span>
                            <span class="val" style="font-weight:400;">{{ $transfer->approvedBy->name ?? '—' }} &middot; {{ $transfer->approved_at->format('d M, h:i A') }}</span>
                        </div>
                    @endif
                    @if ($transfer->dispatched_at)
                        <div class="tx-row">
                            <span class="lbl bn">প্রেরণ</span><span class="lbl en" style="display:none;">Dispatched</span>
                            <span class="val" style="font-weight:400;">{{ $transfer->dispatchedBy->name ?? '—' }} &middot; {{ $transfer->dispatched_at->format('d M, h:i A') }}</span>
                        </div>
                    @endif
                    @if ($transfer->received_at)
                        <div class="tx-row">
                            <span class="lbl bn">গ্রহণ</span><span class="lbl en" style="display:none;">Received</span>
                            <span class="val" style="font-weight:400;">{{ $transfer->receivedBy->name ?? '—' }} &middot; {{ $transfer->received_at->format('d M, h:i A') }}</span>
                        </div>
                    @endif
                </div>

                <div class="drawer-title bn" style="font-size:14px; margin-bottom:10px;">আইটেমসমূহ</div>
                <div class="drawer-title en" style="display:none; font-size:14px; margin-bottom:10px;">Items</div>
                <div class="tx-section">
                    @foreach ($transfer->items as $item)
                        <div class="tx-item">
                            <div class="nm">{{ $item->product->name ?? '—' }}</div>
                            <div class="meta">
                                <span>Batch: {{ $item->batch_no }}</span>
                                <span>Qty: {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($transfer->note)
                    <div class="tx-section">
                        <div class="lbl bn" style="margin-bottom:6px;">নোট</div>
                        <div class="lbl en" style="display:none; margin-bottom:6px;">Notes</div>
                        <div class="val" style="font-weight:400;">{{ $transfer->note }}</div>
                    </div>
                @endif

                <div style="display:flex; gap:10px; margin-top:20px;">
                    @if ($transfer->status === 'pending')
                        <form method="POST" action="{{ route('stock-transfers.approve', $transfer) }}" style="flex:1;">
                            @csrf
                            <button type="submit" class="btn btn-teal" style="width:100%; justify-content:center;">
                                <span class="bn">অনুমোদন করুন</span><span class="en">Approve</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" style="flex:1;" onsubmit="return confirm('এই ট্রান্সফার বাতিল করতে চান?');">
                            @csrf
                            <button type="submit" class="btn btn-red" style="width:100%; justify-content:center;">
                                <span class="bn">বাতিল করুন</span><span class="en">Cancel</span>
                            </button>
                        </form>
                    @elseif ($transfer->status === 'approved')
                        <form method="POST" action="{{ route('stock-transfers.dispatch', $transfer) }}" style="flex:1;">
                            @csrf
                            <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center;">
                                <span class="bn">প্রেরণ করুন</span><span class="en">Dispatch</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" style="flex:1;" onsubmit="return confirm('এই ট্রান্সফার বাতিল করতে চান?');">
                            @csrf
                            <button type="submit" class="btn btn-red" style="width:100%; justify-content:center;">
                                <span class="bn">বাতিল করুন</span><span class="en">Cancel</span>
                            </button>
                        </form>
                    @elseif ($transfer->status === 'dispatched')
                        <form method="POST" action="{{ route('stock-transfers.receive', $transfer) }}" style="flex:1;">
                            @csrf
                            <button type="submit" class="btn btn-green" style="width:100%; justify-content:center;">
                                <span class="bn">গ্রহণ নিশ্চিত করুন</span><span class="en">Confirm Receipt</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</x-core::layout>
