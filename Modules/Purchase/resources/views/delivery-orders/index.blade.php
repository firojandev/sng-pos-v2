<x-core::layout
    title="পারচেজ ডেলিভারি অর্ডার"
    title-en="Purchase Delivery Orders"
    subtitle="পণ্য ক্রয় থেকে ডেলিভারি গ্রহণ ও ইনভেন্টরি স্টক পরিচালনা করুন"
    subtitle-en="Manage purchase delivery orders, shipments, and inventory updates"
    active="purchase-delivery-orders"
>
    <div class="cash-page-head">
        <div>
            <div class="ttl bn">ডেলিভারি অর্ডার তালিকা</div>
            <div class="ttl en" style="display:none;">Delivery Orders List</div>
        </div>

        <div class="actions">
            <div class="total-pill">
                <span class="bn">মোট অর্ডার: </span><span class="en" style="display:none;">Total Orders: </span>
                <b>{{ $summary['total_count'] }}</b>
            </div>
            <div class="total-pill" style="background:var(--blue-100); border-color:var(--blue-ic-bg); color:var(--blue-ink);">
                <span class="bn">অপেক্ষমান: </span><span class="en" style="display:none;">Pending: </span>
                <b>{{ $summary['pending_count'] }}</b>
            </div>
            <div class="total-pill" style="background:var(--green-100); border-color:var(--green-ic-bg); color:var(--green-ink);">
                <span class="bn">সম্পূর্ণ গৃহীত: </span><span class="en" style="display:none;">Received: </span>
                <b>{{ $summary['received_count'] }}</b>
            </div>
            <x-core::button size="sm" color="primary" href="{{ route('purchase-delivery-orders.create') }}" icon="plus">
                <span class="bn">নতুন ডেলিভারি অর্ডার</span><span class="en" style="display:none;">New Delivery Order</span>
            </x-core::button>
        </div>
    </div>

    <form method="GET" action="{{ route('purchase-delivery-orders.index') }}" class="section-row" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px; overflow-x:auto; padding-bottom:4px;">
        <div class="filters" style="display:flex; align-items:center; gap:8px; flex-wrap:nowrap; flex:1;">
            <div style="min-width:200px; max-width:260px; flex:1;">
                <x-core::input
                    size="sm"
                    name="q"
                    value="{{ $search }}"
                    placeholder="অর্ডার নং বা সরবরাহকারী..."
                    placeholder-en="Search order or supplier..."
                    icon="search"
                    :no-margin="true"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::select
                    size="sm"
                    name="status"
                    value="{{ $status }}"
                    onchange="this.form.submit()"
                    :no-margin="true"
                >
                    <option value="all">সকল অবস্থা</option>
                    @foreach (\Modules\Purchase\Models\PurchaseDeliveryOrder::statusLabels() as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label['bn'] }}</option>
                    @endforeach
                </x-core::select>
            </div>

            <div style="width:160px; flex-shrink:0;">
                <x-core::select
                    size="sm"
                    name="supplier_id"
                    value="{{ $supplierId }}"
                    onchange="this.form.submit()"
                    :no-margin="true"
                >
                    <option value="">সকল সরবরাহকারী</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" @selected((string) $supplierId === (string) $s->id)>{{ $s->name }}</option>
                    @endforeach
                </x-core::select>
            </div>

            <div style="width:130px; flex-shrink:0;">
                <x-core::input
                    size="sm"
                    type="date"
                    name="from"
                    value="{{ $from }}"
                    :no-margin="true"
                />
            </div>

            <div style="width:130px; flex-shrink:0;">
                <x-core::input
                    size="sm"
                    type="date"
                    name="to"
                    value="{{ $to }}"
                    :no-margin="true"
                />
            </div>

            <x-core::button size="sm" variant="secondary" type="submit" icon="funnel" style="flex-shrink:0;">
                <span class="bn">ফিল্টার</span><span class="en" style="display:none;">Filter</span>
            </x-core::button>

            <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.index') }}" icon="rotate-ccw" style="flex-shrink:0;" title="ফিল্টার রিসেট / Reset Filters">
                <span class="bn">রিসেট</span><span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>
    </form>

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">অর্ডার নং</th><th class="en" style="display:none;">Order No</th>
                            <th class="bn">সরবরাহকারী</th><th class="en" style="display:none;">Supplier</th>
                            <th class="bn">গুদাম</th><th class="en" style="display:none;">Warehouse</th>
                            <th class="bn">পণ্য ও অগ্রগতি</th><th class="en" style="display:none;">Items & Progress</th>
                            <th class="bn">মোট টাকা</th><th class="en" style="display:none;">Total Amount</th>
                            <th class="bn">অর্ডার তারিখ</th><th class="en" style="display:none;">Order Date</th>
                            <th class="bn">অবস্থা</th><th class="en" style="display:none;">Status</th>
                            <th style="text-align:right;">কর্ম</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $statusLabel = $order->statusLabel();
                                $pct = $order->fulfillmentPercentage();
                            @endphp
                            <tr style="cursor:pointer;" onclick="window.location='{{ route('purchase-delivery-orders.show', $order) }}'">
                                <td class="cell-main">
                                    <span style="font-weight:600; color:var(--ink-900);">#{{ $order->order_no }}</span>
                                    @if($order->receipts->count() > 0)
                                         <div style="font-size:11px; color:var(--ink-400);">{{ $order->receipts->count() }}টি চালান গৃহীত</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="cell-main">{{ $order->supplier->name ?? 'সাধারণ সরবরাহকারী' }}</div>
                                    @if ($order->supplier?->phone)
                                        <div class="cell-sub">{{ $order->supplier->phone }}</div>
                                    @endif
                                </td>
                                <td>{{ $order->warehouse->name ?? '—' }}</td>
                                <td style="min-width:140px;">
                                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                        <span>{{ rtrim(rtrim(number_format($order->totalReceivedQuantity(), 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($order->totalOrderedQuantity(), 2), '0'), '.') }}</span>
                                        <span style="font-weight:600;">{{ $pct }}%</span>
                                    </div>
                                    <div style="background:var(--paper-line); border-radius:999px; height:6px; overflow:hidden;">
                                        <div style="background:{{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#3b82f6' : 'var(--ink-400)') }}; width:{{ $pct }}%; height:100%;"></div>
                                    </div>
                                </td>
                                <td><b>৳{{ number_format($order->total_amount, 2) }}</b></td>
                                <td>{{ optional($order->order_date)->format('d M, Y') }}</td>
                                <td>
                                    @if ($order->status === 'received')
                                        <x-core::badge color="emerald" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                                    @elseif ($order->status === 'partial_received')
                                        <x-core::badge color="blue" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                                    @elseif ($order->status === 'cancelled')
                                        <x-core::badge color="danger" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                                    @else
                                        <x-core::badge color="amber" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation();" style="text-align:right;">
                                    <div style="display:inline-flex; gap:6px; align-items:center; justify-content:flex-end;">
                                        <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.show', $order) }}" title="বিবরণ দেখুন">
                                            <span class="bn">বিবরণ</span><span class="en" style="display:none;">View</span>
                                        </x-core::button>

                                        @if ($order->canBeReceived())
                                            <x-core::button size="sm" color="primary" href="{{ route('purchase-delivery-orders.receive', $order) }}" title="পণ্য ডেলিভারি গ্রহণ">
                                                <span class="bn">গ্রহণ</span><span class="en" style="display:none;">Receive</span>
                                            </x-core::button>
                                        @endif

                                        @if ($order->canBeEdited())
                                            <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.edit', $order) }}" icon="edit" title="সম্পাদনা" />
                                        @endif

                                        @if ($order->canBeCancelled())
                                            <form method="POST" action="{{ route('purchase-delivery-orders.cancel', $order) }}" class="inline-block delete-form" data-title="অর্ডারটি বাতিল করতে চান?" data-text="এই ডেলিভারি অর্ডারটি বাতিল করা হবে।">
                                                @csrf
                                                <x-core::button size="sm" color="danger" type="submit" icon="trash-2" title="অর্ডার বাতিল" />
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-core::table.empty
                                        icon="truck"
                                        title="কোনো ডেলিভারি অর্ডার নেই"
                                        title-en="No purchase delivery orders found"
                                        description="নতুন ডেলিভারি অর্ডার তৈরি করতে উপরের বোতামে ক্লিক করুন"
                                        description-en="Click the button above to create a new purchase delivery order"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div style="margin-top:16px;">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-core::layout>
