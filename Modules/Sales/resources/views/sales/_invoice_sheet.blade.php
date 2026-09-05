@php
    use Modules\Core\Support\BanglaNumber;

    $shop = auth()->user()?->shop ?? $sale->shop ?? \Modules\Shop\Models\Shop::first();
    $customer = $sale->customer;

    $previousDue = 0.0;
    if ($customer) {
        $previousDue = (float) ($customer->opening_due ?? 0)
            + (float) ($customer->sales()
                ->where('id', '!=', $sale->id)
                ->where('id', '<', $sale->id)
                ->sum('due_amount') ?? 0);
    }
    $currentDue = (float) $sale->due_amount;
    $totalCustomerDue = $previousDue + $currentDue;

    $printTime = BanglaNumber::toBnDateTime(now());
    $invoiceDate = BanglaNumber::toBnDateTime($sale->sale_date ? $sale->sale_date->setTimeFrom($sale->created_at ?? now()) : $sale->created_at);
    $sellerName = $sale->employee_name ?: (auth()->user()?->name ?? 'অ্যাডমিন');
@endphp

<style>
    .sale-invoice-sheet {
        background: #ffffff;
        width: 100%;
        max-width: 680px;
        margin: 0 auto;
        padding: 24px 28px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        color: #0f172a;
        font-family: 'Noto Sans Bengali', sans-serif;
        box-sizing: border-box;
    }
    .sale-invoice-sheet table.invoice-items-table {
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
    .sale-invoice-sheet table.invoice-items-table th,
    .sale-invoice-sheet table.invoice-items-table td {
        box-sizing: border-box !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
</style>

<div class="sale-invoice-sheet" id="saleInvoiceSheet">
    {{-- Top Header with Shop Info --}}
    <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:10px;">
        <div style="flex-shrink:0; width:48px; height:48px; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
            @if(!empty($shop?->logo))
                <img src="{{ asset($shop->logo) }}" alt="Shop Logo" style="max-width:48px; max-height:48px; object-fit:contain;">
            @else
                <svg width="46" height="46" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="7" y="19" width="34" height="23" rx="2" fill="#38bdf8" stroke="#0f172a" stroke-width="2"/>
                    <rect x="19" y="27" width="10" height="15" fill="#0f172a"/>
                    <rect x="11" y="25" width="5" height="8" rx="1" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
                    <rect x="32" y="25" width="5" height="8" rx="1" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
                    <path d="M4 18L9 8H39L44 18H4Z" fill="#ea580c" stroke="#0f172a" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M4 18C4 20.5 6 22 8.5 22C11 22 13 20.5 13 18C13 20.5 15 22 17.5 22C20 22 22 20.5 22 18C22 20.5 24 22 26.5 22C29 22 31 20.5 31 18C31 20.5 33 22 35.5 22C38 22 40 20.5 40 18C40 20.5 41.8 22 44 22" stroke="#0f172a" stroke-width="2" fill="#f97316"/>
                </svg>
            @endif
        </div>

        <div>
            <div style="font-size:18px; font-weight:800; color:#0f172a; line-height:1.2;">
                {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
            </div>
            @if(!empty($shop?->address))
                <div style="font-size:12px; color:#475569; margin-top:2px;">
                    {{ $shop->address }}
                </div>
            @endif
            @if(!empty($shop?->phone))
                <div style="font-size:12px; color:#475569; margin-top:1px;">
                    মোবাইল : {{ $shop->phone }}
                </div>
            @endif
        </div>
    </div>

    {{-- Centered Title with Horizontal Accent Lines --}}
    <div style="display:flex; align-items:center; justify-content:center; gap:16px; margin:12px 0 14px 0;">
        <div style="flex:1; height:1px; background:#94a3b8;"></div>
        <div style="font-size:22px; font-weight:800; color:#0f172a; letter-spacing:1px; padding:0 8px;">
            ইনভয়েস
        </div>
        <div style="flex:1; height:1px; background:#94a3b8;"></div>
    </div>

    {{-- Metadata: Customer & Sale Information --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:12px; line-height:1.6; margin-bottom:14px;">
        <div style="width:50%;">
            <div><b>ক্রেতার নাম :</b> {{ $customer->name ?? 'ওয়াক-ইন গ্রাহক' }}</div>
            <div><b>মোবাইল নং :</b> {{ $customer->phone ?? '—' }}</div>
            <div><b>ঠিকানা :</b> {{ $customer->address ?? '—' }}</div>
        </div>
        <div style="width:50%; text-align:right;">
            <div><b>বিক্রয় প্রতিনিধি :</b> {{ $sellerName }}</div>
            <div><b>ইনভয়েস নং :</b> <span style="font-weight:700;">#{{ $sale->invoice_no }}</span></div>
            <div><b>তারিখ :</b> {{ $invoiceDate }}</div>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="invoice-items-table">
        <colgroup>
            <col style="width:6%;">
            <col style="width:42%;">
            <col style="width:13%;">
            <col style="width:11%;">
            <col style="width:14%;">
            <col style="width:14%;">
        </colgroup>
        <thead>
            <tr style="background:transparent;">
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">#</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:center; font-weight:700;">পণ্যের নাম</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">পরিমান</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700;">ইউনিট</th>
                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 6px; text-align:center; font-weight:700;">ইউনিট মূল্য</th>
                <th style="border-bottom:1px solid #94a3b8 !important; border-right:none; border-top:none; border-left:none; padding:6px 6px; text-align:center; font-weight:700;">মোট</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $idx => $item)
                @php
                    $unitName = $item->unit?->name
                        ?? $item->product?->units->firstWhere('pivot.is_base', true)?->name
                        ?? $item->product?->units->first()?->name
                        ?? 'পিছ';
                    $barcode = $item->product?->barcode ?: ($item->batch?->batch_no ?: $item->product?->sku);
                @endphp
                <tr>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle;">
                        {{ BanglaNumber::toBn($idx + 1) }}.
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:left; vertical-align:middle;">
                        <div style="font-weight:600; color:#0f172a;">{{ $item->product->name ?? '—' }}</div>
                        @if ($barcode)
                            <div style="font-size:10px; color:#64748b; margin-top:1px;">
                                বারকোড : {{ $barcode }}
                            </div>
                        @endif
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle; white-space:nowrap;">
                        {{ BanglaNumber::toBn(number_format((float) $item->quantity, 2)) }}
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; vertical-align:middle; white-space:nowrap;">
                        {{ $unitName }}
                    </td>
                    <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:right; vertical-align:middle; white-space:nowrap;">
                        {{ BanglaNumber::toBn(number_format((float) $item->unit_price, 2)) }}
                    </td>
                    <td style="border-bottom:1px solid #94a3b8 !important; border-right:none; border-top:none; border-left:none; padding:6px 8px; text-align:right; vertical-align:middle; white-space:nowrap;">
                        {{ BanglaNumber::toBn(number_format((float) $item->total, 2)) }}
                    </td>
                </tr>
            @endforeach

            {{-- Table Subtotal Row with vertical grid lines intact --}}
            <tr style="font-weight:700;">
                <td colspan="2" style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 8px; text-align:center;">
                    সর্বমোট পরিমান
                </td>
                <td style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 4px; text-align:center; white-space:nowrap;">
                    {{ BanglaNumber::toBn(number_format((float) $sale->items->sum('quantity'), 2)) }}
                </td>
                <td colspan="2" style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 8px; text-align:center;">
                    মোট
                </td>
                <td style="border:none; padding:6px 8px; text-align:right; white-space:nowrap;">
                    {{ BanglaNumber::toBn(number_format((float) $sale->subtotal, 2)) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Lower Summary Section (2 Columns) --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:12px; line-height:1.6; color:#0f172a;">
        {{-- Left Column: Dues, Words & Signatures --}}
        <div style="width:48%;">
            <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:2px;">
                <span>পূর্বের বাকি :</span>
                <span>৳{{ BanglaNumber::toBn(number_format($previousDue, 2)) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:2px;">
                <span>বর্তমান বাকি :</span>
                <span>৳{{ BanglaNumber::toBn(number_format($currentDue, 2)) }}</span>
            </div>
            <div style="border-top:1px solid #94a3b8; max-width:210px; margin:4px 0 6px 0;"></div>
            <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:8px; font-weight:700;">
                <span>টোটাল বাকি :</span>
                <span>৳{{ BanglaNumber::toBn(number_format($totalCustomerDue, 2)) }}</span>
            </div>

            <div style="margin-top:16px;">
                <div style="font-weight:700; margin-bottom:2px; font-size:12px;">অ্যামাউন্ট (কথায়):</div>
                <div style="color:#1e293b; font-size:11.5px; line-height:1.4;">
                    {{ BanglaNumber::toBnWords($sale->total) }}
                </div>
            </div>

            <div style="margin-top:42px;">
                <div style="border-top:1px solid #94a3b8; width:135px; text-align:center; padding-top:4px; font-size:11px; font-weight:600;">
                    ক্রেতার স্বাক্ষর
                </div>
                <div style="font-size:9.5px; color:#64748b; margin-top:3px;">
                    প্রিন্ট করার সময়: {{ $printTime }}
                </div>
            </div>
        </div>

        {{-- Right Column: Financial Totals --}}
        <div style="width:44%;">
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">সাব টোটাল</span>
                <span style="font-weight:600;">৳{{ BanglaNumber::toBn(number_format((float) $sale->subtotal, 2)) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">(-) ছাড়</span>
                <span>৳{{ BanglaNumber::toBn(number_format((float) $sale->discount, 2)) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">ভ্যাট</span>
                <span>৳{{ BanglaNumber::toBn('0.00') }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">ডেলিভারি</span>
                <span>৳{{ BanglaNumber::toBn(number_format((float) ($sale->delivery_charge ?? 0), 2)) }}</span>
            </div>

            <div style="border-top:1px solid #94a3b8; margin:4px 0 6px 0;"></div>

            <div style="display:flex; justify-content:space-between; margin-bottom:2px; font-weight:700;">
                <span>মোট</span>
                <span>৳{{ BanglaNumber::toBn(number_format((float) $sale->total, 2)) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">পরিশোধিত</span>
                <span style="font-weight:600;">৳{{ BanglaNumber::toBn(number_format((float) $sale->paid_amount, 2)) }}</span>
            </div>

            <div style="border-top:1px solid #94a3b8; margin:4px 0 6px 0;"></div>

            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span style="color:#334155;">বাকি আছে</span>
                <span style="font-weight:600; {{ (float)$sale->due_amount > 0 ? 'color:#dc2626;' : '' }}">
                    ৳{{ BanglaNumber::toBn(number_format((float) $sale->due_amount, 2)) }}
                </span>
            </div>

            <div style="margin-top:54px; display:flex; justify-content:flex-end;">
                <div style="border-top:1px solid #94a3b8; width:135px; text-align:center; padding-top:4px; font-size:11px; font-weight:600;">
                    বিক্রেতার স্বাক্ষর
                </div>
            </div>
        </div>
    </div>
</div>
