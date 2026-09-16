@php
    use Modules\Core\Support\BanglaNumber;

    $shop = auth()->user()?->shop ?? $purchase->shop ?? \Modules\Shop\Models\Shop::first();
    $printerSetting = $shop ? $shop->getEffectivePrinterSetting() : \Modules\Shop\Models\PrinterSetting::getDefaultForShop(1);
    $supplier = $purchase->supplier;

    $previousDue = 0.0;
    if ($supplier) {
        $previousDue = (float) ($supplier->opening_due ?? 0)
            + (float) ($supplier->purchases()
                ->where('id', '!=', $purchase->id)
                ->where('id', '<', $purchase->id)
                ->sum('due_amount') ?? 0);
    }
    $currentDue = (float) $purchase->due_amount;
    $totalSupplierDue = $previousDue + $currentDue;

    $printTime = BanglaNumber::toBnDateTime(now());
    $invoiceDate = BanglaNumber::toBnDateTime($purchase->purchase_date ? $purchase->purchase_date->setTimeFrom($purchase->created_at ?? now()) : $purchase->created_at);

    $isCompact58 = ($printerSetting->paper_width ?: 80) <= 58;
@endphp

<style>
    .purchase-thermal-receipt-sheet {
        background: #ffffff;
        width: 100%;
        max-width: {{ $printerSetting->getCssPaperWidth() }};
        margin: 0 auto;
        padding: 10px 8px;
        color: #000000;
        font-family: 'Noto Sans Bengali', monospace, sans-serif;
        font-size: {{ $isCompact58 ? '8.5px' : '10px' }};
        line-height: 1.35;
        box-sizing: border-box;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 2px solid #cbd5e1;
        border-radius: 6px;
    }
    .purchase-thermal-receipt-sheet table.receipt-items-table {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        margin: 5px 0 !important;
        font-size: {{ $isCompact58 ? '8px' : '9.5px' }} !important;
        box-sizing: border-box !important;
    }
    .purchase-thermal-receipt-sheet table.receipt-items-table thead th {
        background: transparent !important;
        color: #000000 !important;
        padding: 3px 2px !important;
        letter-spacing: normal !important;
        text-transform: none !important;
        font-weight: 700 !important;
        box-sizing: border-box !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: 1px dashed #000000 !important;
    }
    .purchase-thermal-receipt-sheet table.receipt-items-table tbody td {
        padding: 3px 2px !important;
        box-sizing: border-box !important;
        white-space: normal !important;
        vertical-align: top !important;
        background: transparent !important;
        color: #000000 !important;
        border: none !important;
    }
    .purchase-thermal-receipt-sheet table.receipt-items-table tbody tr:hover {
        background: transparent !important;
    }
    .purchase-thermal-receipt-sheet table.receipt-items-table td.col-product,
    .purchase-thermal-receipt-sheet table.receipt-items-table .product-title {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        display: block !important;
        min-width: 0 !important;
        max-width: 100% !important;
    }
    .thermal-dashed-line {
        border-top: 1px dashed #000000;
        margin: 5px 0;
    }
    .thermal-double-line {
        border-top: 2px solid #000000;
        margin: 5px 0;
    }
    @media print {
        .purchase-thermal-receipt-sheet {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        .thermal-due-box {
            border: 1.5px solid #000000 !important;
            background: transparent !important;
        }
    }
</style>

<div class="purchase-thermal-receipt-sheet" id="purchaseThermalReceiptSheet">
    {{-- Header --}}
    <div style="text-align:center; margin-bottom:5px;">
        @if ($printerSetting->show_header_logo && !empty($shop?->logo))
            <div style="margin-bottom:4px; display:flex; justify-content:center;">
                <img src="{{ $shop->logo_url ?? asset($shop->logo) }}" alt="Logo" style="max-height:34px; max-width:110px; object-fit:contain; filter:grayscale(100%);">
            </div>
        @endif

        <div style="font-size:{{ $isCompact58 ? '13px' : '15px' }}; font-weight:800; color:#000000; line-height:1.2;">
            {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
        </div>

        @if ($printerSetting->show_shop_info)
            @if (!empty($shop?->address))
                <div style="font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }}; margin-top:2px;">
                    {{ $shop->address }}
                </div>
            @endif
            @if (!empty($shop?->phone))
                <div style="font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }}; margin-top:1px;">
                    মোবাইল: {{ $shop->phone }}
                </div>
            @endif
        @endif
    </div>

    <div class="thermal-dashed-line"></div>

    {{-- Invoice Metadata --}}
    <div style="font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }};">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:6px;">
            <span style="white-space:nowrap;">ইনভয়েস নং: <b style="white-space:nowrap;">#{{ $purchase->invoice_no }}</b></span>
            <span style="white-space:nowrap;">তারিখ: {{ $purchase->purchase_date ? $purchase->purchase_date->format('d/m/y') : ($purchase->created_at ? $purchase->created_at->format('d/m/y') : '') }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; gap:6px; margin-top:2px;">
            <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">সাপ্লায়ার: <b>{{ $supplier->name ?? 'সাধারণ সরবরাহকারী' }}</b></span>
            <span style="white-space:nowrap;">সময়: {{ $purchase->created_at ? $purchase->created_at->format('h:i A') : '' }}</span>
        </div>
        @if (!empty($supplier?->phone))
            <div style="margin-top:2px;">
                মোবাইল: {{ $supplier->phone }}
            </div>
        @endif
    </div>

    <div class="thermal-dashed-line"></div>

    {{-- Items Table --}}
    <table class="receipt-items-table">
        <colgroup>
            <col style="width: 38%;">
            <col style="width: 18%;">
            <col style="width: 20%;">
            <col style="width: 24%;">
        </colgroup>
        <thead>
            <tr style="border-bottom:1px dashed #000000;">
                <th style="text-align:left; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9.5px' }};">পণ্য</th>
                <th style="text-align:center; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9.5px' }};">পরিমাণ</th>
                <th style="text-align:right; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9px' }};">মূল্য</th>
                <th style="text-align:right; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9.5px' }};">মোট</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                @php
                    $unitName = $item->unit?->name
                        ?? $item->product?->units->firstWhere('pivot.is_base', true)?->name
                        ?? $item->product?->units->first()?->name
                        ?? 'পিস';
                    $sku = $item->product?->sku ?: $item->product?->barcode;
                    $receivedQty = (float) ($item->received_quantity ?? $item->quantity);
                @endphp
                <tr style="border-bottom:1px dotted #ccc;">
                    <td class="col-product" style="text-align:left; vertical-align:top; padding:3px 2px; white-space:normal !important;">
                        <div class="product-title" style="font-weight:700; color:#000000; line-height:1.25; font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }}; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important; display:block;">
                            {{ $item->product->name ?? '—' }}
                        </div>
                        @if ($sku)
                            <div style="font-size:{{ $isCompact58 ? '7px' : '8px' }}; color:#555555; margin-top:1px; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important;">
                                SKU: {{ $sku }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align:center; vertical-align:top; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }};">
                        {{ BanglaNumber::toBn(rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.')) }}
                        <div style="font-size:{{ $isCompact58 ? '7px' : '8px' }}; color:#555;">{{ $unitName }}</div>
                    </td>
                    <td style="text-align:right; vertical-align:top; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9px' }};">
                        ৳{{ BanglaNumber::toBnMoney($item->purchase_price) }}
                    </td>
                    <td style="text-align:right; vertical-align:top; padding:3px 2px; font-weight:700; white-space:nowrap; font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }};">
                        ৳{{ BanglaNumber::toBnMoney($item->total) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="thermal-dashed-line"></div>

    {{-- Totals --}}
    <div style="font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }};">
        <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
            <span>মোট পরিমাণ:</span>
            <span>{{ BanglaNumber::toBn(rtrim(rtrim(number_format((float) $purchase->items->sum('quantity'), 2), '0'), '.')) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
            <span>সাব টোটাল:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($purchase->subtotal) }}</span>
        </div>
        @if ((float) $purchase->transportation_cost > 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span>পরিবহন খরচ:</span>
                <span>৳ {{ BanglaNumber::toBnMoney($purchase->transportation_cost) }}</span>
            </div>
        @endif
        @if ((float) $purchase->adjustment_cost != 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span>সমন্বয়:</span>
                <span>৳ {{ BanglaNumber::toBnMoney($purchase->adjustment_cost) }}</span>
            </div>
        @endif

        <div class="thermal-double-line"></div>

        <div style="display:flex; justify-content:space-between; font-weight:800; font-size:{{ $isCompact58 ? '10.5px' : '11.5px' }}; margin:3px 0;">
            <span>সর্বমোট:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($purchase->total) }}</span>
        </div>

        <div class="thermal-dashed-line"></div>

        <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
            <span>পরিশোধ:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($purchase->paid_amount) }}</span>
        </div>

        <div style="display:flex; justify-content:space-between; font-weight:700; color:{{ (float)$purchase->due_amount > 0 ? '#b91c1c' : '#000000' }};">
            <span>বর্তমান বাকি:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($purchase->due_amount) }}</span>
        </div>

        {{-- Supplier Dues Breakdown --}}
        @if ($printerSetting->show_customer_due && $supplier)
            <div class="thermal-due-box" style="margin-top:5px; padding:5px 8px; background:#f8fafc; border:2px solid #cbd5e1; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; font-size:{{ $isCompact58 ? '8px' : '9px' }};">
                    <span>পূর্বের বাকি:</span>
                    <span>৳ {{ BanglaNumber::toBnMoney($previousDue) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:{{ $isCompact58 ? '9px' : '10px' }}; margin-top:2px;">
                    <span>সর্বমোট বকেয়া:</span>
                    <span>৳ {{ BanglaNumber::toBnMoney($totalSupplierDue) }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer Note --}}
    @if ($printerSetting->show_footer_note && !empty($shop?->invoice_footer))
        <div class="thermal-dashed-line"></div>
        <div style="text-align:center; font-size:{{ $isCompact58 ? '8px' : '9px' }}; color:#333333; line-height:1.3;">
            {{ $shop->invoice_footer }}
        </div>
    @endif

    {{-- Bottom Greeting --}}
    <div style="text-align:center; margin-top:6px; font-size:{{ $isCompact58 ? '8px' : '9px' }};">
        <div>*** ক্রয় চালান কপি ***</div>
        <div style="font-size:{{ $isCompact58 ? '7px' : '8px' }}; color:#555; margin-top:2px;">{{ $printTime }}</div>
    </div>
</div>
