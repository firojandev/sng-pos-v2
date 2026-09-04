<div class="modal-backdrop" id="receiveRemainingModal" style="z-index:1000;">
    <div class="modal-box" style="width:760px; max-width:96vw; max-height:92vh; overflow-y:auto; padding:24px; border-radius:16px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
        {{-- Modal Header --}}
        <div class="modal-head" style="margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                    <x-core::icon name="package-check" size="20" />
                </div>
                <div>
                    <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                        <span class="bn">ডিও দিয়ে বাকি পণ্য গ্রহণ</span>
                        <span class="en" style="display:none;">Receive Remaining Goods by D.O.</span>
                    </div>
                    <div style="font-size:12px; color:var(--ink-500); margin-top:2px;">
                        <span>ইনভয়েস: <strong style="font-family:var(--font-mono, monospace); color:var(--ink-800);">#{{ $purchase->invoice_no }}</strong></span>
                        &nbsp;&bull;&nbsp;
                        <span>সরবরাহকারী: <strong style="color:var(--ink-800);">{{ $purchase->supplier->name ?? '—' }}</strong></span>
                        @if ($purchase->warehouse)
                            &nbsp;&bull;&nbsp;
                            <span>গুদাম: <strong style="color:var(--ink-800);">{{ $purchase->warehouse->name }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>
            <x-core::button
                type="button"
                variant="ghost"
                size="sm"
                icon="x"
                icon-only
                class="modal-close-btn"
                onclick="closeModal('receiveRemainingModal')"
            />
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('purchase.receive.store', $purchase) }}" id="receive-remaining-form">
            @csrf

            {{-- D.O. Details Card --}}
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:14px; margin-bottom:16px;">
                <div style="font-size:13px; font-weight:700; color:var(--ink-800); margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <x-core::icon name="truck" size="16" style="color:var(--teal-700);" />
                    <span class="bn">ডেলিভারি চালান (D.O.) বিবরণ</span>
                    <span class="en" style="display:none;">Delivery Order (D.O.) Information</span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:10px;">
                    <div>
                        <x-core::input
                            name="do_number"
                            id="receive_do_number"
                            label="ডিও নম্বর *"
                            label-en="D.O. Number *"
                            size="sm"
                            :required="true"
                            :no-margin="true"
                            placeholder="যেমন: PD-002"
                            value="{{ old('do_number', $purchase->do_number) }}"
                        />
                    </div>
                    <div>
                        <x-core::input
                            type="date"
                            name="do_date"
                            id="receive_do_date"
                            label="ডিও তারিখ"
                            label-en="D.O. Date"
                            size="sm"
                            :no-margin="true"
                            value="{{ date('Y-m-d') }}"
                        />
                    </div>
                    <div>
                        <x-core::input
                            name="vehicle_number"
                            id="receive_vehicle_number"
                            label="গাড়ির নম্বর"
                            label-en="Vehicle No"
                            size="sm"
                            :no-margin="true"
                            placeholder="যেমন: ঢাকা মেট্রো-১১"
                            value="{{ old('vehicle_number', $purchase->vehicle_number) }}"
                        />
                    </div>
                    <div>
                        <x-core::input
                            name="delivery_person_name"
                            id="receive_delivery_person_name"
                            label="ডেলিভারি ব্যক্তি"
                            label-en="Delivery Person"
                            size="sm"
                            :no-margin="true"
                            placeholder="নাম বা মোবাইল"
                            value="{{ old('delivery_person_name', $purchase->delivery_person_name) }}"
                        />
                    </div>
                </div>
            </div>

            {{-- Products Table --}}
            <div style="margin-bottom:16px;">
                <div style="font-size:13px; font-weight:700; color:var(--ink-800); margin-bottom:8px; display:flex; align-items:center; justify-content:space-between;">
                    <span class="bn">বাকি পণ্যের তালিকা</span>
                    <span class="en" style="display:none;">Pending Items</span>
                    <span style="font-size:11.5px; font-weight:500; color:var(--ink-500);">
                        সর্বমোট বাকি: <strong style="color:var(--red-600);">{{ rtrim(rtrim(number_format($purchase->totalPendingQuantity(), 2), '0'), '.') }}</strong> একক
                    </span>
                </div>

                <div class="table-wrap" style="border:1px solid var(--border); border-radius:8px; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                                <th style="padding:8px 10px; text-align:left; color:var(--ink-700); font-weight:600;">পণ্য</th>
                                <th style="padding:8px 8px; text-align:right; width:65px; color:var(--ink-700); font-weight:600;">অর্ডার</th>
                                <th style="padding:8px 8px; text-align:right; width:65px; color:var(--teal-700); font-weight:600;">গৃহীত</th>
                                <th style="padding:8px 8px; text-align:right; width:65px; color:var(--red-600); font-weight:600;">বাকি</th>
                                <th style="padding:8px 8px; text-align:left; width:110px; color:var(--ink-900); font-weight:700;">প্রাপ্ত পরিমাণ *</th>
                                <th style="padding:8px 8px; text-align:left; width:140px; color:var(--ink-700); font-weight:600;">ব্যাচ নম্বর</th>
                                <th style="padding:8px 8px; text-align:left; width:100px; color:var(--ink-700); font-weight:600;">মেয়াদ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->items as $idx => $item)
                                @php
                                    $pending = $item->pendingQuantity();
                                    $isPending = $pending > 0;
                                @endphp
                                <tr style="border-bottom:1px solid var(--border); {{ ! $isPending ? 'opacity:0.6; background:var(--paper);' : '' }}">
                                    <td style="padding:8px 10px;">
                                        <input type="hidden" name="items[{{ $idx }}][purchase_item_id]" value="{{ $item->id }}">
                                        <div style="font-weight:600; color:var(--ink-900);">{{ $item->product->name ?? '—' }}</div>
                                        <div style="font-size:11px; color:var(--ink-400);">
                                            SKU: {{ $item->product->sku ?? '—' }}
                                        </div>
                                    </td>
                                    <td style="padding:8px 8px; text-align:right; font-family:var(--font-mono, monospace);">
                                        {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}
                                    </td>
                                    <td style="padding:8px 8px; text-align:right; font-family:var(--font-mono, monospace); color:var(--teal-700); font-weight:600;">
                                        {{ rtrim(rtrim(number_format($item->received_quantity ?? $item->quantity, 2), '0'), '.') }}
                                    </td>
                                    <td style="padding:8px 8px; text-align:right; font-family:var(--font-mono, monospace); font-weight:700; color:{{ $isPending ? 'var(--red-600)' : 'var(--ink-400)' }};">
                                        {{ rtrim(rtrim(number_format($pending, 2), '0'), '.') }}
                                    </td>
                                    <td style="padding:6px 8px;">
                                        @if ($isPending)
                                            <x-core::input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                max="{{ $pending }}"
                                                name="items[{{ $idx }}][received_qty]"
                                                value="{{ $pending }}"
                                                size="sm"
                                                :no-margin="true"
                                                style="text-align:right; font-weight:700; color:var(--teal-800);"
                                            />
                                        @else
                                            <span style="font-size:11px; color:var(--green-ink); font-weight:600; display:inline-flex; align-items:center; gap:4px;">
                                                <x-core::icon name="check" size="14" /> শতভাগ গৃহীত
                                            </span>
                                            <input type="hidden" name="items[{{ $idx }}][received_qty]" value="0">
                                        @endif
                                    </td>
                                    <td style="padding:6px 8px;">
                                        @if ($isPending)
                                            <x-core::input
                                                name="items[{{ $idx }}][batch_no]"
                                                value="{{ $item->batch_no }}"
                                                size="sm"
                                                :no-margin="true"
                                                placeholder="ব্যাচ নং"
                                                style="font-family:var(--font-mono, monospace); font-size:11.5px;"
                                            />
                                        @else
                                            <span style="font-family:var(--font-mono, monospace); font-size:11px; color:var(--ink-500);">
                                                {{ $item->batch_no ?: '—' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding:6px 8px;">
                                        @if ($isPending)
                                            <x-core::input
                                                type="date"
                                                name="items[{{ $idx }}][expiry_date]"
                                                value="{{ optional($item->expiry_date)->format('Y-m-d') }}"
                                                size="sm"
                                                :no-margin="true"
                                                style="font-size:11px;"
                                            />
                                        @else
                                            <span style="font-size:11px; color:var(--ink-400);">
                                                {{ optional($item->expiry_date)->format('d M, Y') ?: '—' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Note Input --}}
            <div style="margin-bottom:18px;">
                <x-core::input
                    name="note"
                    id="receive_note"
                    label="চালানের নোট / মন্তব্য (ঐচ্ছিক)"
                    label-en="Notes (Optional)"
                    size="sm"
                    :no-margin="true"
                    placeholder="বাকি পণ্য গ্রহণ সংক্রান্ত কোনো মন্তব্য থাকলে লিখুন..."
                />
            </div>

            {{-- Modal Actions --}}
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:12px; border-top:1px solid var(--border);">
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="modal-close-btn"
                    onclick="closeModal('receiveRemainingModal')"
                >
                    <span class="bn">বাতিল</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>

                <x-core::button
                    type="submit"
                    color="primary"
                    size="sm"
                    icon="check"
                    id="btn-submit-receive-remaining"
                >
                    <span class="bn">পণ্য গ্রহণ ও স্টক আপডেট নিশ্চিত করুন</span>
                    <span class="en" style="display:none;">Confirm Receive & Update Stock</span>
                </x-core::button>
            </div>
        </form>
    </div>
</div>
