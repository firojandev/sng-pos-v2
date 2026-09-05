@php
    use Modules\Core\Support\BanglaNumber;

    $shop = auth()->user()?->shop ?? $purchase->shop;

    $previousDue = 0.0;
    if ($purchase->supplier) {
        $previousDue = (float) ($purchase->supplier->opening_due ?? 0)
            + (float) ($purchase->supplier->purchases()
                ->where('id', '!=', $purchase->id)
                ->where('id', '<', $purchase->id)
                ->sum('due_amount') ?? 0);
    }
    $currentDue = (float) $purchase->due_amount;
    $totalSupplierDue = $previousDue + $currentDue;

    $printTime = BanglaNumber::toBnDateTime(now());
    $invoiceDate = BanglaNumber::toBnDateTime($purchase->purchase_date ? $purchase->purchase_date->setTimeFrom($purchase->created_at ?? now()) : $purchase->created_at);
@endphp

<style>
    .purchase-invoice-sheet {
        background: #ffffff;
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
        padding: 24px 28px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        color: #0f172a;
        font-family: 'Noto Sans Bengali', sans-serif;
        box-sizing: border-box;
    }
    .purchase-invoice-sheet table.invoice-items-table {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: 1px solid #94a3b8 !important;
        margin-top: 14px !important;
        margin-bottom: 14px !important;
        font-size: 11.5px !important;
    }
    .purchase-invoice-sheet table.invoice-items-table th,
    .purchase-invoice-sheet table.invoice-items-table td {
        box-sizing: border-box !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
</style>

<div class="purchase-invoice-sheet" id="purchaseInvoiceSheet">
    {{-- Top Header with Shop Info --}}
    <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:10px;">
        {{-- Shop Storefront Illustration Icon matching reference UI --}}
        <div style="flex-shrink:0; width:48px; height:48px; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
            <svg width="46" height="46" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="7" y="19" width="34" height="23" rx="2" fill="#38bdf8" stroke="#0f172a" stroke-width="2"/>
                <rect x="19" y="27" width="10" height="15" fill="#0f172a"/>
                <rect x="11" y="25" width="5" height="8" rx="1" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
                <rect x="32" y="25" width="5" height="8" rx="1" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
                <path d="M4 18L9 8H39L44 18H4Z" fill="#ea580c" stroke="#0f172a" stroke-width="2" stroke-linejoin="round"/>
                <path d="M4 18C4 20.5 6 22 8.5 22C11 22 13 20.5 13 18C13 20.5 15 22 17.5 22C20 22 22 20.5 22 18C22 20.5 24 22 26.5 22C29 22 31 20.5 31 18C31 20.5 33 22 35.5 22C38 22 40 20.5 40 18C40 20.5 41.8 22 44 22" stroke="#0f172a" stroke-width="2" fill="#f97316"/>
            </svg>
        </div>

        <div>
            <div style="font-size:17px; font-weight:800; color:#0f172a; line-height:1.2;">
                {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
            </div>
            @if(!empty($shop->address))
                <div style="font-size:12px; color:#475569; margin-top:2px;">
                    {{ $shop->address }}
                </div>
            @endif
            @if(!empty($shop->phone))
                <div style="font-size:12px; color:#475569; margin-top:1px;">
                    {{ $shop->phone }}
                </div>
            @endif
        </div>
    </div>

    {{-- Horizontal Border Line --}}
    <div style="border-top:2px solid #94a3b8; margin:10px 0 8px 0;"></div>

    {{-- Large Centered Title --}}
    <div style="text-align:center; font-size:23px; font-weight:800; color:#000000; letter-spacing:0.5px; margin-bottom:12px;">
        ইনভয়েস
    </div>

    {{-- Metadata: Supplier & Purchase Information --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:12px; line-height:1.6; margin-bottom:16px;">
        <div style="width:50%;">
            <div><b>সাপ্লায়ার:</b> {{ $purchase->supplier->name ?? 'সাধারণ সরবরাহকারী' }}</div>
            @if(!empty(trim($purchase->supplier?->phone ?? '')))
                <div><b>মোবাইল:</b> {{ $purchase->supplier->phone }}</div>
            @endif
            @if(!empty(trim($purchase->supplier?->address ?? '')))
                <div><b>ঠিকানা:</b> {{ $purchase->supplier->address }}</div>
            @endif
        </div>
        <div style="width:50%; text-align:right;">
            <div><b>কিনেছেন:</b> {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}</div>
            <div><b>ইনভয়েস নম্বর:</b> <span style="font-family:inherit; font-weight:700;">{{ $purchase->invoice_no }}</span></div>
            <div><b>তারিখ:</b> {{ $invoiceDate }}</div>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="invoice-items-table" style="width:100%; min-width:0; max-width:100%; table-layout:fixed; border-collapse:separate !important; border-spacing:0 !important; border:1px solid #94a3b8 !important; margin-top:14px; margin-bottom:14px; font-size:11.5px;">
        <colgroup>
            <col style="width:5%;">
            <col style="width:33%;">
            <col style="width:10%;">
            <col style="width:10%;">
            <col style="width:10%;">
            <col style="width:9%;">
            <col style="width:11%;">
            <col style="width:12%;">
        </colgroup>
        <thead>
            <tr style="background:transparent;">
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">#</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:center; font-weight:700;">পণ্যের নাম</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">অর্ডার</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">গৃহীত</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">বাকি</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">ইউনিট</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 6px; text-align:center; font-weight:700;">ইউনিট মূল্য</th>
                <th style="border-bottom:1px solid #94a3b8 !important; border-right:none; border-top:none; border-left:none; padding:6px 6px; text-align:center; font-weight:700;">মোট</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $idx => $item)
                @php
                    $unitName = $item->unit?->name
                        ?? $item->product?->units->firstWhere('pivot.is_base', true)?->name
                        ?? $item->product?->units->first()?->name
                        ?? 'পিস';
                    $barcode = $item->product?->barcode ?: $item->product?->sku;
                    $receivedQty = (float) ($item->received_quantity ?? $item->quantity);
                    $pendingQty = max(0.0, (float) $item->quantity - $receivedQty);
                @endphp
                <tr>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle;">
                        {{ BanglaNumber::toBn($idx + 1) }}.
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:left; vertical-align:middle;">
                        <div style="font-weight:600; color:#0f172a;">{{ $item->product->name ?? '—' }}</div>
                        @if ($barcode)
                            <div style="font-size:10.5px; color:#64748b; margin-top:1px;">
                                বারকোড: {{ $barcode }}
                            </div>
                        @endif
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle; white-space:nowrap;">
                        {{ BanglaNumber::toBn(rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.')) }}
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle; white-space:nowrap; color:#0f766e; font-weight:600;">
                        {{ BanglaNumber::toBn(rtrim(rtrim(number_format($receivedQty, 2), '0'), '.')) }}
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle; white-space:nowrap; {{ $pendingQty > 0 ? 'color:#dc2626; font-weight:700;' : 'color:#64748b;' }}">
                        {{ BanglaNumber::toBn(rtrim(rtrim(number_format($pendingQty, 2), '0'), '.')) }}
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle; white-space:nowrap;">
                        {{ $unitName }}
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:right; vertical-align:middle; white-space:nowrap;">
                        {{ BanglaNumber::toBnMoney($item->purchase_price) }}
                    </td>
                    <td style="border-bottom:1px solid #94a3b8 !important; border-right:none; border-top:none; border-left:none; padding:6px 8px; text-align:right; vertical-align:middle; white-space:nowrap;">
                        {{ BanglaNumber::toBnMoney($item->total) }}
                    </td>
                </tr>
            @endforeach

            {{-- Table Subtotal Row with all vertical grid borders intact --}}
            <tr style="font-weight:700;">
                <td colspan="2" style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 8px; text-align:center;">
                    মোট
                </td>
                <td style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 4px; text-align:center; white-space:nowrap;">
                    {{ BanglaNumber::toBn(rtrim(rtrim(number_format((float) $purchase->items->sum('quantity'), 2), '0'), '.')) }}
                </td>
                <td style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 4px; text-align:center; white-space:nowrap; color:#0f766e;">
                    {{ BanglaNumber::toBn(rtrim(rtrim(number_format((float) $purchase->items->sum(fn ($i) => (float) ($i->received_quantity ?? $i->quantity)), 2), '0'), '.')) }}
                </td>
                <td style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 4px; text-align:center; white-space:nowrap; color:#dc2626;">
                    {{ BanglaNumber::toBn(rtrim(rtrim(number_format((float) $purchase->items->sum(fn ($i) => $i->pendingQuantity()), 2), '0'), '.')) }}
                </td>
                <td style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 4px;"></td>
                <td style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 6px;"></td>
                <td style="border:none; padding:6px 8px; text-align:right; white-space:nowrap;">
                    {{ BanglaNumber::toBnMoney($purchase->subtotal) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Lower Summary Section (2 Columns) --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:12px; line-height:1.5; color:#0f172a;">
        {{-- Left Column: Dues, Words & Signatures --}}
        <div style="width:48%;">
            <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:2px;">
                <span><b>পূর্বের বাকি:</b></span>
                <span>৳ {{ BanglaNumber::toBnMoney($previousDue) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:2px;">
                <span><b>বর্তমান বাকি:</b></span>
                <span>৳ {{ BanglaNumber::toBnMoney($currentDue) }}</span>
            </div>
            <div style="border-top:1px solid #94a3b8; max-width:210px; margin:4px 0 6px 0;"></div>
            <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:8px;">
                <span><b>টোটাল বাকি:</b></span>
                <span>৳ {{ BanglaNumber::toBnMoney($totalSupplierDue) }}</span>
            </div>

            <div style="margin-top:14px;">
                <div style="font-weight:700; margin-bottom:2px;">অ্যামাউন্ট (কথায়):</div>
                <div style="color:#1e293b; font-size:11.5px; line-height:1.4;">
                    {{ BanglaNumber::toBnWords($purchase->total) }}
                </div>
            </div>

            <div style="margin-top:42px;">
                <div style="border-top:1px solid #94a3b8; width:135px; text-align:center; padding-top:4px; font-size:11px; font-weight:600;">
                    ক্রেতার স্বাক্ষর
                </div>
                <div style="font-size:9.5px; color:#64748b; margin-top:3px;">
                    প্রিন্ট করার সময়: {{ $printTime }}
                </div>
            </div>
        </div>

        {{-- Right Column: Financial Totals --}}
        <div style="width:44%;">
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">সাব টোটাল</span>
                <span style="font-weight:600;">{{ BanglaNumber::toBnMoney($purchase->subtotal) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">(-) ছাড়</span>
                <span>৳ {{ BanglaNumber::toBnMoney($purchase->discount) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">ডেলিভারি</span>
                <span>৳ {{ BanglaNumber::toBnMoney($purchase->delivery_charge) }}</span>
            </div>
            @if((float) $purchase->transportation_cost > 0)
                <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                    <span style="color:#334155;">পরিবহন খরচ</span>
                    <span>৳ {{ BanglaNumber::toBnMoney($purchase->transportation_cost) }}</span>
                </div>
            @endif
            @if((float) $purchase->adjustment_cost != 0)
                <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                    <span style="color:#334155;">অ্যাডজাস্টমেন্ট</span>
                    <span>৳ {{ BanglaNumber::toBnMoney($purchase->adjustment_cost) }}</span>
                </div>
            @endif

            <div style="border-top:1px solid #94a3b8; margin:4px 0 6px 0;"></div>

            <div style="display:flex; justify-content:space-between; margin-bottom:2px; font-weight:700;">
                <span>মোট</span>
                <span>{{ BanglaNumber::toBnMoney($purchase->total) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">পরিশোধিত</span>
                <span style="font-weight:600;">{{ BanglaNumber::toBnMoney($purchase->paid_amount) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">বাকি আছে</span>
                <span style="font-weight:600; {{ (float)$purchase->due_amount > 0 ? 'color:#dc2626;' : '' }}">৳ {{ BanglaNumber::toBnMoney($purchase->due_amount) }}</span>
            </div>

            <div style="margin-top:54px; display:flex; justify-content:flex-end;">
                <div style="border-top:1px solid #94a3b8; width:135px; text-align:center; padding-top:4px; font-size:11px; font-weight:600;">
                    বিক্রেতার স্বাক্ষর
                </div>
            </div>
        </div>
    </div>
</div>
