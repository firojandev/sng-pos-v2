<x-core::layout
    title="ডেলিভারি অর্ডার বিবরণ"
    title-en="Purchase Delivery Order Details"
    subtitle="অর্ডারের সামগ্রিক অগ্রগতি, আইটেম ও গৃহীত ডেলিভারি ইতিহাস"
    subtitle-en="Order fulfillment progress, items, and received shipments"
    active="purchase-delivery-orders"
>
    @php
        $statusLabel = $order->statusLabel();
        $pct = $order->fulfillmentPercentage();
    @endphp

    <div class="cash-page-head">
        <div style="display:flex; align-items:center; gap:12px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="ttl bn">ডেলিভারি অর্ডার: #{{ $order->order_no }}</div>
                    @if ($order->status === 'received')
                        <x-core::badge color="emerald" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                    @elseif ($order->status === 'partial_received')
                        <x-core::badge color="blue" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                    @elseif ($order->status === 'cancelled')
                        <x-core::badge color="danger" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                    @else
                        <x-core::badge color="amber" dot>{{ $statusLabel['bn'] }}</x-core::badge>
                    @endif
                </div>
                <div style="font-size:12px; color:#64748b; margin-top:2px;">তৈরি করেছেন: {{ $order->creator->name ?? '—' }} | {{ $order->created_at->format('d M, Y h:i A') }}</div>
            </div>
        </div>

        <div class="actions">
            <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.index') }}" icon="arrow-left">
                <span class="bn">তালিকায় ফিরে যান</span>
            </x-core::button>

            <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.print', $order) }}" target="_blank" icon="printer">
                <span class="bn">প্রিন্ট / পিডিএফ</span>
            </x-core::button>

            @if ($order->canBeReceived())
                <x-core::button size="sm" color="primary" href="{{ route('purchase-delivery-orders.receive', $order) }}" icon="package-check">
                    <span class="bn">পণ্য ডেলিভারি গ্রহণ</span>
                </x-core::button>
            @endif

            @if ($order->canBeEdited())
                <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.edit', $order) }}" icon="edit">
                    <span class="bn">সম্পাদনা</span>
                </x-core::button>
            @endif

            @if ($order->canBeCancelled())
                <form method="POST" action="{{ route('purchase-delivery-orders.cancel', $order) }}" class="inline-block delete-form" data-title="অর্ডারটি বাতিল করতে চান?" data-text="এই ডেলিভারি অর্ডারটি সম্পূর্ণ বাতিল করা হবে।">
                    @csrf
                    <x-core::button size="sm" color="danger" type="submit" icon="trash-2">
                        <span class="bn">বাতিল</span>
                    </x-core::button>
                </form>
            @endif
        </div>
    </div>

    <!-- 4 Info Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:16px; margin-bottom:24px;">
        <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; box-shadow:var(--shadow-card);">
            <div style="font-size:12px; font-weight:600; color:var(--ink-400); margin-bottom:6px;" class="bn">সরবরাহকারী</div>
            <div style="font-size:15px; font-weight:700; color:var(--ink-900);">{{ $order->supplier->name ?? 'সাধারণ সরবরাহকারী' }}</div>
            @if ($order->supplier?->phone)
                <div style="font-size:13px; color:var(--ink-600); margin-top:2px;">{{ $order->supplier->phone }}</div>
            @endif
            @if ($order->supplier?->address)
                <div style="font-size:12px; color:var(--ink-400); margin-top:2px;">{{ $order->supplier->address }}</div>
            @endif
        </div>

        <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; box-shadow:var(--shadow-card);">
            <div style="font-size:12px; font-weight:600; color:var(--ink-400); margin-bottom:6px;" class="bn">গন্তব্য গুদাম</div>
            <div style="font-size:15px; font-weight:700; color:var(--ink-900);">{{ $order->warehouse->name ?? '—' }}</div>
            @if ($order->warehouse?->branch)
                <div style="font-size:13px; color:var(--ink-600); margin-top:2px;">শাখা: {{ $order->warehouse->branch->name }}</div>
            @endif
        </div>

        <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; box-shadow:var(--shadow-card);">
            <div style="font-size:12px; font-weight:600; color:var(--ink-400); margin-bottom:6px;" class="bn">তারিখ ও ডেলিভারি ম্যান</div>
            <div style="font-size:13px; color:var(--ink-900);"><b>অর্ডারের তারিখ:</b> {{ optional($order->order_date)->format('d M, Y') }}</div>
            <div style="font-size:13px; color:var(--ink-900); margin-top:2px;"><b>প্রত্যাশিত ডেলিভারি:</b> {{ optional($order->expected_delivery_date)->format('d M, Y') ?? 'নির্দিষ্ট নয়' }}</div>
            @if($order->delivery_person_name)
                <div style="font-size:13px; color:var(--ink-600); margin-top:2px;"><b>ডেলিভারি ম্যান:</b> {{ $order->delivery_person_name }} ({{ $order->delivery_person_phone }})</div>
            @endif
        </div>

        <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; box-shadow:var(--shadow-card);">
            <div style="font-size:12px; font-weight:600; color:var(--ink-400); margin-bottom:6px;" class="bn">আর্থিক তথ্য</div>
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:2px;">
                <span style="color:var(--ink-700);">সাবটোটাল:</span>
                <b style="color:var(--ink-900);">৳{{ number_format($order->subtotal, 2) }}</b>
            </div>
            @if($order->discount > 0)
                <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:2px; color:#ef4444;">
                    <span>ছাড়:</span>
                    <b>-৳{{ number_format($order->discount, 2) }}</b>
                </div>
            @endif
            @if($order->delivery_charge > 0)
                <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:2px;">
                    <span style="color:var(--ink-700);">ডেলিভারি চার্জ:</span>
                    <b style="color:var(--ink-900);">৳{{ number_format($order->delivery_charge, 2) }}</b>
                </div>
            @endif
            <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700; color:#10b981; border-top:1px solid var(--border); padding-top:4px; margin-top:4px;">
                <span>সর্বমোট:</span>
                <span>৳{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Fulfillment Progress -->
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px; margin-bottom:24px; box-shadow:var(--shadow-card);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div style="font-weight:700; font-size:15px; color:var(--ink-900);" class="bn">
                ডেলিভারি পূর্ণতার অগ্রগতি
            </div>
            <div style="font-weight:700; font-size:15px; color:var(--ink-900);">
                {{ $pct }}% সম্পন্ন
                <span style="font-size:13px; font-weight:400; color:var(--ink-400);">({{ rtrim(rtrim(number_format($order->totalReceivedQuantity(), 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($order->totalOrderedQuantity(), 2), '0'), '.') }} ইউনিট)</span>
            </div>
        </div>
        <div style="background:var(--paper-line); border-radius:999px; height:10px; overflow:hidden;">
            <div style="background:{{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#3b82f6' : 'var(--ink-400)') }}; width:{{ $pct }}%; height:100%; transition:width 0.3s ease;"></div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="panel" style="margin-top:0; margin-bottom:24px;">
        <div class="panel-head" style="padding:16px 20px; border-bottom:1px solid var(--border);">
            <div style="font-weight:700; font-size:15px; color:var(--ink-900);" class="bn">অর্ডারের পণ্য তালিকা</div>
        </div>
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">পণ্য</th>
                            <th class="bn">একক</th>
                            <th class="bn" style="text-align:right;">অর্ডার পরিমাণ</th>
                            <th class="bn" style="text-align:right;">গৃহীত পরিমাণ</th>
                            <th class="bn" style="text-align:right;">বাকি পরিমাণ</th>
                            <th class="bn" style="text-align:right;">ক্রয়মূল্য (একক)</th>
                            <th class="bn" style="text-align:right;">লাইন মোট</th>
                            <th class="bn" style="text-align:center;">অবস্থা</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            @php
                                $ordered = (float) $item->ordered_quantity;
                                $received = (float) $item->received_quantity;
                                $pending = $item->pendingQuantity();
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:var(--ink-900);">{{ $item->product->name }}</div>
                                    <div style="font-size:12px; color:var(--ink-400);">SKU: {{ $item->product->sku ?? '—' }}</div>
                                </td>
                                <td>{{ $item->unit->name ?? 'মূল একক' }}</td>
                                <td style="text-align:right; font-weight:600;">{{ rtrim(rtrim(number_format($ordered, 2), '0'), '.') }}</td>
                                <td style="text-align:right; font-weight:600; color:#10b981;">{{ rtrim(rtrim(number_format($received, 2), '0'), '.') }}</td>
                                <td style="text-align:right; font-weight:600; color:{{ $pending > 0 ? '#ef4444' : 'var(--ink-400)' }};">
                                    {{ rtrim(rtrim(number_format($pending, 2), '0'), '.') }}
                                </td>
                                <td style="text-align:right;">৳{{ number_format($item->purchase_price, 2) }}</td>
                                <td style="text-align:right; font-weight:600;">৳{{ number_format($item->subtotal, 2) }}</td>
                                <td style="text-align:center;">
                                    @if ($item->isFulfilled())
                                        <x-core::badge color="emerald" size="sm">পূর্ণ গৃহীত</x-core::badge>
                                    @elseif ($received > 0)
                                        <x-core::badge color="blue" size="sm">আংশিক</x-core::badge>
                                    @else
                                        <x-core::badge color="amber" size="sm">অপেক্ষমান</x-core::badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Receipts / Deliveries History -->
    <div class="panel" style="margin-top:0;">
        <div class="panel-head" style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:700; font-size:15px; color:var(--ink-900);" class="bn">
                গৃহীত চালান ও স্টক আপডেটের ইতিহাস (Goods Receipts)
            </div>
            @if ($order->canBeReceived())
                <x-core::button size="sm" color="primary" href="{{ route('purchase-delivery-orders.receive', $order) }}" icon="plus">
                    <span class="bn">নতুন চালান গ্রহণ করুন</span>
                </x-core::button>
            @endif
        </div>
        <div class="panel-body">
            @if ($order->receipts->count() > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="bn">চালান / রসিদ নং</th>
                                <th class="bn">সরবরাহকারী চালান নং</th>
                                <th class="bn">তারিখ</th>
                                <th class="bn">গ্রহণকারী কর্মকর্তা</th>
                                <th class="bn">গৃহীত আইটেম ও ব্যাচ</th>
                                <th class="bn">চালান মূল্য</th>
                                <th class="bn">কেনার খাতা ইনভয়েস</th>
                                <th style="text-align:right;" class="bn">কর্ম</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->receipts as $receipt)
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:var(--ink-900);">#{{ $receipt->receipt_no }}</div>
                                        @if($receipt->vehicle_no)
                                            <div style="font-size:11px; color:var(--ink-400);">গাড়ি: {{ $receipt->vehicle_no }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $receipt->challan_no ?? '—' }}</td>
                                    <td>{{ optional($receipt->delivery_date)->format('d M, Y') }}</td>
                                    <td>{{ $receipt->receiver->name ?? '—' }}</td>
                                    <td>
                                        @foreach ($receipt->items as $rItem)
                                            <div style="font-size:12px; margin-bottom:2px;">
                                                <b>{{ $rItem->product->name }}</b>: {{ rtrim(rtrim(number_format($rItem->received_quantity, 2), '0'), '.') }} একক (ব্যাচ: {{ $rItem->batch_no }})
                                            </div>
                                        @endforeach
                                    </td>
                                    <td><b>৳{{ number_format($receipt->total_amount, 2) }}</b></td>
                                    <td>
                                        @if ($receipt->purchase)
                                            <a href="{{ route('purchase.ledger', ['q' => $receipt->purchase->invoice_no]) }}" style="font-weight:600; color:#2563eb; text-decoration:underline;">
                                                #{{ $receipt->purchase->invoice_no }}
                                            </a>
                                        @else
                                            <span style="color:var(--ink-400);">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-receipts.print', $receipt) }}" target="_blank" icon="printer" title="প্রিন্ট চালান" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center; padding:36px; color:var(--ink-400);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--ink-400)" stroke-width="1.5" style="margin:0 auto 10px;"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <div style="font-weight:600; font-size:15px; color:var(--ink-700); margin-bottom:4px;" class="bn">এখনও কোনো পণ্য চালান গ্রহণ করা হয়নি</div>
                    <div style="font-size:13px; color:var(--ink-400); margin-bottom:16px;" class="bn">সরবরাহকারীর পণ্য গুদামে পৌঁছালে "পণ্য ডেলিভারি গ্রহণ" বোতামে ক্লিক করে স্টক গ্রহণ করুন।</div>
                    @if ($order->canBeReceived())
                        <x-core::button size="sm" color="primary" href="{{ route('purchase-delivery-orders.receive', $order) }}" icon="package-check">
                            <span class="bn">পণ্য ডেলিভারি গ্রহণ শুরু করুন</span>
                        </x-core::button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-core::layout>
