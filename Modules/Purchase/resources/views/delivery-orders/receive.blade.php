<x-core::layout
    title="পণ্য ডেলিভারি গ্রহণ"
    title-en="Receive Purchase Delivery"
    subtitle="সরবরাহকারীর পণ্য চালান গ্রহণ করুন এবং গুদামের ইনভেন্টরি স্টক আপডেট করুন"
    subtitle-en="Receive delivery shipment and update warehouse inventory batches"
    active="purchase-delivery-orders"
>
    <div class="cash-page-head">
        <div>
            <div class="ttl bn">পণ্য ডেলিভারি গ্রহণ: #{{ $order->order_no }}</div>
            <div class="ttl en" style="display:none;">Receive Shipment: #{{ $order->order_no }}</div>
            <div style="font-size:13px; color:#64748b; margin-top:2px;">
                সরবরাহকারী: <b>{{ $order->supplier->name ?? 'সাধারণ সরবরাহকারী' }}</b> | গন্তব্য গুদাম: <b>{{ $order->warehouse->name }}</b>
            </div>
        </div>
        <div class="actions">
            <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.show', $order) }}" icon="arrow-left">
                <span class="bn">অর্ডারে ফিরে যান</span>
            </x-core::button>
        </div>
    </div>

    @if (session('error'))
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('purchase-delivery-orders.store-receive', $order) }}" id="receive-form">
        @csrf

        <!-- Delivery Metadata -->
        <div class="panel" style="margin-top:0; margin-bottom:20px;">
            <div class="panel-head" style="padding:14px 20px; border-bottom:1px solid var(--border);">
                <div style="font-weight:700; font-size:15px; color:var(--ink-900);" class="bn">চালান ও ডেলিভারির তথ্য</div>
            </div>
            <div class="panel-body">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">গ্রহণের তারিখ *</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date', now()->format('Y-m-d')) }}" required style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">সরবরাহকারী চালান / বিল নং</label>
                        <input type="text" name="challan_no" value="{{ old('challan_no') }}" placeholder="যেমন: CH-9082" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">যানবাহন নম্বর</label>
                        <input type="text" name="vehicle_no" value="{{ old('vehicle_no') }}" placeholder="যেমন: ঢাকা মেট্রো-ট-..." style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">ডেলিভারি ব্যক্তির নাম</label>
                        <input type="text" name="delivery_person_name" value="{{ old('delivery_person_name', $order->delivery_person_name) }}" placeholder="নাম" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">ডেলিভারি মোবাইল নম্বর</label>
                        <input type="text" name="delivery_person_phone" value="{{ old('delivery_person_phone', $order->delivery_person_phone) }}" placeholder="মোবাইল নম্বর" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--ink-700);" class="bn">নোট বা পণ্যের অবস্থা বিবরণ</label>
                    <textarea name="note" rows="2" placeholder="পণ্য গ্রহণের কোনো বিশেষ পর্যবেক্ষণ বা নোট..." style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 10px; font-size:13px;">{{ old('note') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="panel" style="margin-top:0; margin-bottom:20px;">
            <div class="panel-head" style="padding:14px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:700; font-size:15px; color:var(--ink-900);" class="bn">
                    চালানে প্রাপ্ত পণ্যের পরিমাণ ও ব্যাচ তথ্য
                </div>
                <div style="display:flex; gap:8px;">
                    <x-core::button size="sm" variant="secondary" type="button" id="btn-receive-all">
                        <span class="bn">সব অবশিষ্ট গ্রহণ করুন</span>
                    </x-core::button>
                    <x-core::button size="sm" variant="secondary" type="button" id="btn-reset-zero">
                        <span class="bn">০ রিসেট করুন</span>
                    </x-core::button>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:24%;" class="bn">পণ্য</th>
                                <th style="width:10%; text-align:right;" class="bn">অর্ডার</th>
                                <th style="width:10%; text-align:right;" class="bn">গৃহীত</th>
                                <th style="width:10%; text-align:right;" class="bn">বাকি</th>
                                <th style="width:14%;" class="bn">চালানে প্রাপ্ত পরিমাণ *</th>
                                <th style="width:14%;" class="bn">ব্যাচ নং</th>
                                <th style="width:9%;" class="bn">উৎপাদন তারিখ</th>
                                <th style="width:9%;" class="bn">মেয়াদ উত্তীর্ণ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $idx => $item)
                                @php
                                    $pending = $item->pendingQuantity();
                                    $defaultBatch = 'BT-' . now()->format('ymd') . '-' . $item->product_id . '-' . random_int(100, 999);
                                @endphp
                                <tr style="{{ $pending <= 0 ? 'opacity:0.6; background:var(--paper);' : '' }}">
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][order_item_id]" value="{{ $item->id }}">
                                        <div style="font-weight:600; color:var(--ink-900);">{{ $item->product->name }}</div>
                                        <div style="font-size:11px; color:var(--ink-400);">
                                            একক: {{ $item->unit->name ?? 'মূল একক' }} | SKU: {{ $item->product->sku ?? '—' }}
                                        </div>
                                    </td>
                                    <td style="text-align:right;">{{ rtrim(rtrim(number_format($item->ordered_quantity, 2), '0'), '.') }}</td>
                                    <td style="text-align:right; color:#10b981; font-weight:600;">{{ rtrim(rtrim(number_format($item->received_quantity, 2), '0'), '.') }}</td>
                                    <td style="text-align:right; font-weight:700; color:{{ $pending > 0 ? '#ef4444' : 'var(--ink-400)' }};">
                                        {{ rtrim(rtrim(number_format($pending, 2), '0'), '.') }}
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ $pending }}" name="items[{{ $idx }}][received_quantity]" value="{{ old("items.{$idx}.received_quantity", $pending) }}" class="receive-qty-input" data-max="{{ $pending }}" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:13px; font-weight:700;" {{ $pending <= 0 ? 'readonly' : '' }}>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ old("items.{$idx}.batch_no", $defaultBatch) }}" placeholder="ব্যাচ নম্বর" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:6px 8px; font-size:12px;" {{ $pending <= 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $idx }}][mfg_date]" value="{{ old("items.{$idx}.mfg_date") }}" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:4px 6px; font-size:12px;" {{ $pending <= 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $idx }}][expiry_date]" value="{{ old("items.{$idx}.expiry_date") }}" style="width:100%; border:1px solid var(--border); background:var(--card); color:var(--ink-900); border-radius:6px; padding:4px 6px; font-size:12px;" {{ $pending <= 0 ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:12px;">
                    <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.show', $order) }}">
                        <span class="bn">বাতিল</span>
                    </x-core::button>
                    <x-core::button size="sm" color="primary" type="submit" icon="check">
                        <span class="bn">চালান গ্রহণ ও স্টক আপডেট নিশ্চিত করুন</span>
                    </x-core::button>
                </div>
            </div>
        </div>
    </form>

    <script>
    $(function () {
        $('#btn-receive-all').on('click', function () {
            $('.receive-qty-input').each(function () {
                const max = $(this).data('max');
                $(this).val(max);
            });
        });

        $('#btn-reset-zero').on('click', function () {
            $('.receive-qty-input').val(0);
        });

        $('#receive-form').on('submit', function (e) {
            let totalReceiving = 0;
            $('.receive-qty-input').each(function () {
                totalReceiving += parseFloat($(this).val()) || 0;
            });

            if (totalReceiving <= 0) {
                e.preventDefault();
                alert('কমপক্ষে একটি পণ্যের গ্রহণের পরিমাণ ০ এর বেশি হতে হবে।');
            }
        });
    });
    </script>
</x-core::layout>
