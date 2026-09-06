<div class="modal-backdrop" id="receiptHistoryModal" style="z-index:1000;">
    <div class="modal-box" id="receiptHistoryModalContent" style="width:840px; max-width:96vw; max-height:92vh; overflow-y:auto; padding:24px; border-radius:18px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
        @php
            $totalOrdered = (float) $purchase->items->sum('quantity');
            $totalReceived = (float) $purchase->totalReceivedQuantity();
            $totalPending = (float) $purchase->totalPendingQuantity();
            $isFullyReceived = $purchase->isFullyReceived();
            $receivedPercent = $totalOrdered > 0 ? min(100, round(($totalReceived / $totalOrdered) * 100)) : 0;
            $groupedReceipts = $purchase->receiptItems->sortByDesc('id')->groupBy(function ($r) {
                return ($r->do_number ?? 'no-do').'|'.optional($r->created_at)->format('Y-m-d H:i');
            });
            $receiptCount = $groupedReceipts->count();
        @endphp

        {{-- Modal Header --}}
        <div class="modal-head" style="margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:46px; height:46px; border-radius:12px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.04);">
                    <x-core::icon name="history" size="24" />
                </div>
                <div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <h3 class="modal-title" style="font-size:18px; font-weight:700; color:var(--ink-900); margin:0; line-height:1.3;">
                            <span class="bn">পণ্য গ্রহণের ইতিহাস</span>
                            <span class="en" style="display:none;">Product Received History</span>
                        </h3>
                        @if ($isFullyReceived)
                            <x-core::badge color="green" size="xs" :dot="true" label="সম্পূর্ণ গৃহীত" label-en="Fully Received" />
                        @else
                            <x-core::badge color="gold" size="xs" :dot="true" label="বাকি রয়েছে" label-en="Pending Receiving" />
                        @endif
                    </div>

                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-size:12px; color:var(--ink-500); margin-top:6px;">
                        <span style="display:inline-flex; align-items:center; gap:4px; background:var(--paper-line); padding:2px 8px; border-radius:6px; font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); border:1px solid var(--border);">
                            <x-core::icon name="file-text" size="12" /> #{{ $purchase->invoice_no }}
                        </span>
                        @if ($purchase->supplier)
                            <span style="display:inline-flex; align-items:center; gap:4px; color:var(--ink-700);">
                                <x-core::icon name="truck" size="12" style="color:var(--ink-400);" /> {{ $purchase->supplier->name }}
                                @if ($purchase->supplier->phone)
                                    <span style="color:var(--ink-400); font-family:var(--font-mono, monospace); font-size:11px;">({{ $purchase->supplier->phone }})</span>
                                @endif
                            </span>
                        @endif
                        @if ($purchase->warehouse)
                            <span style="display:inline-flex; align-items:center; gap:4px; color:var(--ink-700);">
                                <x-core::icon name="warehouse" size="12" style="color:var(--ink-400);" /> {{ $purchase->warehouse->name }}
                            </span>
                        @endif
                        @if ($purchase->purchase_date)
                            <span style="display:inline-flex; align-items:center; gap:4px; color:var(--ink-500);">
                                <x-core::icon name="calendar" size="12" style="color:var(--ink-400);" /> {{ $purchase->purchase_date->format('d M, Y') }}
                            </span>
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
                onclick="closeModal('receiptHistoryModal')"
                title="বন্ধ করুন / Close"
            />
        </div>

        {{-- Overview & Progress Dashboard Card --}}
        <div style="background:var(--paper); border:1px solid var(--border); border-radius:14px; padding:16px 18px; margin-bottom:20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; font-size:12.5px;">
                <span style="font-weight:600; color:var(--ink-800); display:flex; align-items:center; gap:6px;">
                    <x-core::icon name="package-check" size="15" style="color:var(--teal-700);" />
                    <span class="bn">পণ্য গ্রহণের সার্বিক অগ্রগতি</span>
                    <span class="en" style="display:none;">Overall Receiving Progress</span>
                </span>
                <span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:13px; color:{{ $isFullyReceived ? 'var(--green-ink)' : 'var(--teal-700)' }};">
                    {{ $receivedPercent }}% <span style="font-size:11px; font-weight:500; color:var(--ink-500);">সম্পন্ন</span>
                </span>
            </div>

            <div style="width:100%; height:8px; background:var(--paper-line); border-radius:999px; overflow:hidden; margin-bottom:16px; border:1px solid var(--border);">
                <div style="height:100%; width:{{ $receivedPercent }}%; background:{{ $isFullyReceived ? 'var(--green-ink)' : 'var(--teal-700)' }}; border-radius:999px; transition:width 0.4s ease;"></div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px;">
                {{-- Metric 1: মোট অর্ডার --}}
                <div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px 14px; display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--paper-line); color:var(--ink-700); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="shopping-bag" size="18" />
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--ink-500); font-weight:600;">মোট অর্ডার</div>
                        <div style="font-size:17px; font-weight:700; font-family:var(--font-mono, monospace); color:var(--ink-900); line-height:1.2; margin-top:2px;">
                            {{ rtrim(rtrim(number_format($totalOrdered, 2), '0'), '.') }}
                        </div>
                    </div>
                </div>

                {{-- Metric 2: মোট গৃহীত --}}
                <div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px 14px; display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--green-100); color:var(--green-ink); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="check-circle" size="18" />
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--green-ink); font-weight:600;">সর্বমোট গৃহীত</div>
                        <div style="font-size:17px; font-weight:700; font-family:var(--font-mono, monospace); color:var(--green-ink); line-height:1.2; margin-top:2px;">
                            {{ rtrim(rtrim(number_format($totalReceived, 2), '0'), '.') }}
                        </div>
                    </div>
                </div>

                {{-- Metric 3: অবশিষ্ট বাকি --}}
                <div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px 14px; display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:{{ $totalPending > 0 ? 'var(--red-100)' : 'var(--paper-line)' }}; color:{{ $totalPending > 0 ? 'var(--red-600)' : 'var(--ink-400)' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="{{ $totalPending > 0 ? 'alert-circle' : 'check' }}" size="18" />
                    </div>
                    <div>
                        <div style="font-size:11px; color:{{ $totalPending > 0 ? 'var(--red-600)' : 'var(--ink-500)' }}; font-weight:600;">অবশিষ্ট বাকি</div>
                        <div style="font-size:17px; font-weight:700; font-family:var(--font-mono, monospace); color:{{ $totalPending > 0 ? 'var(--red-600)' : 'var(--ink-600)' }}; line-height:1.2; margin-top:2px;">
                            {{ rtrim(rtrim(number_format($totalPending, 2), '0'), '.') }}
                        </div>
                    </div>
                </div>

                {{-- Metric 4: চালান সংখ্যা --}}
                <div style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px 14px; display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--blue-100); color:var(--blue-ink); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="truck" size="18" />
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--blue-ink); font-weight:600;">চালানের সংখ্যা</div>
                        <div style="font-size:17px; font-weight:700; font-family:var(--font-mono, monospace); color:var(--blue-ink); line-height:1.2; margin-top:2px;">
                            {{ $receiptCount }} <span style="font-size:12px; font-weight:500;">বার</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Item-Wise Receiving Status Matrix --}}
        <div style="margin-bottom:20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div style="font-size:13.5px; font-weight:700; color:var(--ink-900); display:flex; align-items:center; gap:6px;">
                    <x-core::icon name="layers" size="16" style="color:var(--teal-700);" />
                    <span class="bn">অর্ডারকৃত পণ্যের বর্তমান অবস্থা</span>
                    <span class="en" style="display:none;">Items Receiving Status</span>
                </div>
                <span style="font-size:12px; color:var(--ink-500);">
                    {{ $purchase->items->count() }} টি পণ্য
                </span>
            </div>

            <div style="border:1px solid var(--border); border-radius:12px; overflow:hidden; background:var(--card);">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:var(--paper); border-bottom:1px solid var(--border); color:var(--ink-600); font-weight:600; text-align:left;">
                            <th style="padding:10px 14px;">পণ্য</th>
                            <th style="padding:10px 14px; text-align:center; width:110px;">অর্ডার পরিমাণ</th>
                            <th style="padding:10px 14px; text-align:center; width:110px;">গৃহীত পরিমাণ</th>
                            <th style="padding:10px 14px; text-align:center; width:110px;">বাকি পরিমাণ</th>
                            <th style="padding:10px 14px; text-align:right; width:140px;">অবস্থা</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->items as $item)
                            @php
                                $itemPending = $item->pendingQuantity();
                                $itemReceived = (float) ($item->received_quantity ?? $item->quantity);
                                $itemOrdered = (float) $item->quantity;
                                $itemPercent = $itemOrdered > 0 ? min(100, round(($itemReceived / $itemOrdered) * 100)) : 0;
                            @endphp
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 14px;">
                                    <div style="font-weight:600; color:var(--ink-900);">{{ $item->product->name ?? '—' }}</div>
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:3px; font-size:11px; color:var(--ink-500);">
                                        @if ($item->product?->sku)
                                            <span style="font-family:var(--font-mono, monospace);">SKU: {{ $item->product->sku }}</span>
                                        @endif
                                        @if ($item->batch_no)
                                            <span>ব্যাচ: <code style="font-size:11px;">{{ $item->batch_no }}</code></span>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding:10px 14px; text-align:center; font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-800);">
                                    {{ rtrim(rtrim(number_format($itemOrdered, 2), '0'), '.') }}
                                </td>
                                <td style="padding:10px 14px; text-align:center; font-family:var(--font-mono, monospace); font-weight:700; color:var(--teal-700);">
                                    {{ rtrim(rtrim(number_format($itemReceived, 2), '0'), '.') }}
                                </td>
                                <td style="padding:10px 14px; text-align:center; font-family:var(--font-mono, monospace); font-weight:700; color:{{ $itemPending > 0 ? 'var(--red-600)' : 'var(--ink-400)' }};">
                                    {{ rtrim(rtrim(number_format($itemPending, 2), '0'), '.') }}
                                </td>
                                <td style="padding:10px 14px; text-align:right;">
                                    @if ($itemPending <= 0)
                                        <x-core::badge color="green" size="xs" :dot="true" label="সম্পূর্ণ গৃহীত" label-en="Completed" />
                                    @else
                                        <x-core::badge color="gold" size="xs" :dot="true" label="{{ $itemPercent }}% গৃহীত" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Consignments / Receipt Events Timeline --}}
        <div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <div style="font-size:13.5px; font-weight:700; color:var(--ink-900); display:flex; align-items:center; gap:6px;">
                    <x-core::icon name="truck" size="16" style="color:var(--teal-700);" />
                    <span class="bn">চালান / ডিও ভিত্তিক প্রাপ্তির বিস্তারিত</span>
                    <span class="en" style="display:none;">Consignment & D.O. Delivery Logs</span>
                </div>
                <span style="font-size:12px; color:var(--ink-500);">
                    {{ $receiptCount }} টি প্রাপ্তি রেকর্ড
                </span>
            </div>

            @if ($purchase->receiptItems->isEmpty())
                <div style="padding:32px 16px; background:var(--paper); border:1px dashed var(--border); border-radius:12px; text-align:center;">
                    <x-core::table.empty
                        icon="package"
                        title="কোনো পণ্য গ্রহণের রেকর্ড পাওয়া যায়নি"
                        title-en="No received records found"
                        description="এই ক্রয়ের জন্য এখনো কোনো পণ্য গ্রহণ রেকর্ড করা হয়নি।"
                    />
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:20px;">
                    @foreach ($groupedReceipts as $groupKey => $receiptGroup)
                        @php
                            $first = $receiptGroup->first();
                            $consignmentNo = $receiptCount - $loop->index;
                        @endphp
                        <div style="background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                            {{-- Consignment Card Header --}}
                            <div style="background:var(--paper); border-bottom:1px solid var(--border); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="background:var(--teal-100); color:var(--teal-800); font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                        <x-core::icon name="package-check" size="13" /> চালান #{{ $consignmentNo }}
                                    </span>
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        <span style="font-size:13px; font-weight:700; color:var(--ink-900);">
                                            ডিও নং: <strong style="font-family:var(--font-mono, monospace); color:var(--teal-800);">{{ $first->do_number ?: 'সাধারণ চালান' }}</strong>
                                        </span>
                                        @if ($first->do_date)
                                            <span style="font-size:11.5px; color:var(--ink-500); background:var(--paper-line); padding:2px 6px; border-radius:4px;">
                                                {{ optional($first->do_date)->format('d M, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div style="display:flex; align-items:center; gap:12px; font-size:11.5px; color:var(--ink-500); flex-wrap:wrap;">
                                    @if ($first->vehicle_number)
                                        <span style="display:inline-flex; align-items:center; gap:4px; background:var(--paper-line); padding:2px 8px; border-radius:6px; color:var(--ink-700);">
                                            <x-core::icon name="truck" size="12" style="color:var(--ink-400);" /> {{ $first->vehicle_number }}
                                        </span>
                                    @endif
                                    @if ($first->delivery_person_name)
                                        <span style="display:inline-flex; align-items:center; gap:4px; background:var(--paper-line); padding:2px 8px; border-radius:6px; color:var(--ink-700);">
                                            <x-core::icon name="user" size="12" style="color:var(--ink-400);" /> {{ $first->delivery_person_name }}
                                        </span>
                                    @endif
                                    @if ($first->receiver)
                                        <span style="display:inline-flex; align-items:center; gap:4px; background:var(--paper-line); padding:2px 8px; border-radius:6px; color:var(--ink-700);">
                                            <x-core::icon name="shield-check" size="12" style="color:var(--ink-400);" /> {{ $first->receiver->name }}
                                        </span>
                                    @endif
                                    <span style="display:inline-flex; align-items:center; gap:4px; font-family:var(--font-mono, monospace); color:var(--ink-400);">
                                        <x-core::icon name="clock" size="12" /> {{ optional($first->created_at)->format('d M, Y &middot; h:i A') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Consignment Received Items Table --}}
                            <div style="padding:12px 16px;">
                                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                    <thead>
                                        <tr style="color:var(--ink-500); font-weight:600; border-bottom:1px solid var(--border); text-align:left;">
                                            <th style="padding:6px 8px;">পণ্য</th>
                                            <th style="padding:6px 8px; width:200px;">ব্যাচ নম্বর</th>
                                            <th style="padding:6px 8px; text-align:right; width:140px;">গৃহীত পরিমাণ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($receiptGroup as $rItem)
                                            <tr style="border-bottom:1px dashed var(--border);">
                                                <td style="padding:8px 8px;">
                                                    <div style="font-weight:600; color:var(--ink-900);">{{ $rItem->product->name ?? '—' }}</div>
                                                    @if ($rItem->product?->sku)
                                                        <div style="font-size:11px; color:var(--ink-400); font-family:var(--font-mono, monospace);">SKU: {{ $rItem->product->sku }}</div>
                                                    @endif
                                                </td>
                                                <td style="padding:8px 8px; font-family:var(--font-mono, monospace); font-size:11.5px; color:var(--ink-700);">
                                                    <code>{{ $rItem->batch->batch_no ?? ($rItem->purchaseItem->batch_no ?? '—') }}</code>
                                                </td>
                                                <td style="padding:8px 8px; text-align:right;">
                                                    <span style="display:inline-flex; align-items:center; gap:3px; font-family:var(--font-mono, monospace); font-weight:700; font-size:13px; color:var(--teal-700); background:var(--teal-100); padding:2px 8px; border-radius:6px;">
                                                        +{{ rtrim(rtrim(number_format($rItem->received_quantity, 2), '0'), '.') }} একক
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @if ($first->note)
                                    <div style="margin-top:10px; font-size:12px; color:var(--ink-700); background:var(--paper); padding:8px 12px; border-radius:8px; border-left:3px solid var(--teal-700); display:flex; align-items:center; gap:6px;">
                                        <x-core::icon name="message-square" size="14" style="color:var(--teal-700); flex-shrink:0;" />
                                        <div><strong>নোট:</strong> {{ $first->note }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Modal Footer Actions --}}
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding-top:16px; border-top:1px solid var(--border); margin-top:10px;">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="printer"
                onclick="printSection('receiptHistoryModalContent')"
            >
                <span class="bn">প্রিন্ট রিপোর্ট</span>
                <span class="en" style="display:none;">Print Report</span>
            </x-core::button>

            <div style="display:flex; align-items:center; gap:10px;">
                @if ($purchase->hasPendingItems())
                    <x-core::button
                        type="button"
                        color="primary"
                        size="sm"
                        icon="package-check"
                        class="btn-receive-purchase"
                        data-id="{{ $purchase->id }}"
                        data-url="{{ route('purchase.receive.modal', $purchase) }}"
                        onclick="closeModal('receiptHistoryModal')"
                    >
                        <span class="bn">ডিও দিয়ে বাকি পণ্য গ্রহণ করুন</span>
                        <span class="en" style="display:none;">Receive Remaining by D.O.</span>
                    </x-core::button>
                @endif

                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="modal-close-btn"
                    onclick="closeModal('receiptHistoryModal')"
                >
                    <span class="bn">বন্ধ করুন</span>
                    <span class="en" style="display:none;">Close</span>
                </x-core::button>
            </div>
        </div>
    </div>
</div>
