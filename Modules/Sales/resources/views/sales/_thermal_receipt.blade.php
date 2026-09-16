@php
    use Modules\Core\Support\BanglaNumber;

    $shop = auth()->user()?->shop ?? $sale->shop ?? \Modules\Shop\Models\Shop::first();
    $printerSetting = $shop?->printerSetting ?? \Modules\Shop\Models\PrinterSetting::getDefaultForShop($shop->id ?? 1);
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

    $isCompact58 = ($printerSetting->paper_width ?: 80) <= 58;
@endphp

<style>
    .thermal-receipt-sheet {
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
    .thermal-receipt-sheet table.receipt-items-table {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        margin: 5px 0 !important;
        font-size: {{ $isCompact58 ? '8px' : '9.5px' }} !important;
        box-sizing: border-box !important;
    }
    .thermal-receipt-sheet table.receipt-items-table thead th {
        background: transparent !important;
        color: #000000 !important;
        padding: 3px 2px !important;
        letter-spacing: normal !important;
        text-transform: none !important;
        font-weight: 600 !important;
        box-sizing: border-box !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: 1px dashed #000000 !important;
    }
    .thermal-receipt-sheet table.receipt-items-table tbody td {
        padding: 3px 2px !important;
        box-sizing: border-box !important;
        white-space: normal !important;
        vertical-align: top !important;
        background: transparent !important;
        color: #000000 !important;
        border: none !important;
    }
    .thermal-receipt-sheet table.receipt-items-table tbody tr:hover {
        background: transparent !important;
    }
    .thermal-receipt-sheet table.receipt-items-table td.col-product,
    .thermal-receipt-sheet table.receipt-items-table .product-title {
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
        border-top: 1px solid #000000;
        margin: 5px 0;
    }
    @media print {
        .thermal-receipt-sheet {
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

<div class="thermal-receipt-sheet" id="thermalReceiptSheet">
    {{-- Header --}}
    <div style="text-align:center; margin-bottom:5px;">
        @if ($printerSetting->show_header_logo && !empty($shop?->logo))
            <div style="margin-bottom:4px; display:flex; justify-content:center;">
                <img src="{{ $shop->logo_url ?? asset($shop->logo) }}" alt="Logo" style="max-height:34px; max-width:110px; object-fit:contain; filter:grayscale(100%);">
            </div>
        @endif

        <div style="font-size:{{ $isCompact58 ? '13px' : '15px' }}; font-weight:600; color:#000000; line-height:1.2;">
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
            <span style="white-space:nowrap;">ইনভয়েস নং: <b style="white-space:nowrap; font-weight: 600;">#{{ $sale->invoice_no }}</b></span>
            <span style="white-space:nowrap;">তারিখ: {{ $sale->created_at ? $sale->created_at->format('d/m/y') : '' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; gap:6px; margin-top:2px;">
            <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">ক্রেতা: <b style="font-weight: 600;">{{ $customer->name ?? 'ওয়াক-ইন' }}</b></span>
            <span style="white-space:nowrap;">সময়: {{ $sale->created_at ? $sale->created_at->format('h:i A') : '' }}</span>
        </div>
        @if ($customer && $customer->phone)
            <div style="margin-top:2px;">মোবাইল: {{ $customer->phone }}</div>
        @endif
        <div style="margin-top:2px;">বিক্রেতা: {{ $sellerName }}</div>
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
                <th style="text-align:left; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9.5px' }};">আইটেম</th>
                <th style="text-align:center; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9.5px' }};">পরিমাণ</th>
                <th style="text-align:right; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9px' }};">দর</th>
                <th style="text-align:right; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9.5px' }};">মোট</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->grouped_items as $index => $item)
                @php
                    $productName = $item->product->name ?? 'অজানা পণ্য';
                    $variantName = $item->variant->name ?? null;
                    $itemQty = (float) $item->quantity;
                    $itemPrice = (float) $item->unit_price;
                    $itemTotal = (float) $item->total;
                    $sku = $item->product?->sku;
                @endphp
                <tr style="border-bottom:1px dotted #cccccc;">
                    <td class="col-product" style="text-align:left; vertical-align:top; padding:3px 2px; white-space:normal !important;">
                        <div class="product-title" style="font-weight:600; color:#000000; line-height:1.25; font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }}; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important; display:block;">
                            {{ $productName }} @if($variantName) ({{ $variantName }}) @endif
                        </div>
                        @if($sku)
                            <div style="font-size:{{ $isCompact58 ? '7px' : '8px' }}; color:#555555; margin-top:1px; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important;">
                                SKU : {{ $sku }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align:center; vertical-align:top; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }};">
                        {{ BanglaNumber::toBn($itemQty) }}
                    </td>
                    <td style="text-align:right; vertical-align:top; padding:3px 2px; white-space:nowrap; font-size:{{ $isCompact58 ? '8px' : '9px' }};">
                        ৳{{ BanglaNumber::toBnMoney($itemPrice) }}
                    </td>
                    <td style="text-align:right; vertical-align:top; padding:3px 2px; font-weight:700; white-space:nowrap; font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }};">
                        ৳{{ BanglaNumber::toBnMoney($itemTotal) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="thermal-dashed-line"></div>

    {{-- Totals Summary --}}
    <div style="font-size:{{ $isCompact58 ? '8.5px' : '9.5px' }};">
        <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
            <span>সাব টোটাল:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($sale->subtotal) }}</span>
        </div>
        @if ((float) ($sale->product_discount ?? 0) > 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span>(-) পণ্য ছাড়:</span>
                <span>৳ {{ BanglaNumber::toBnMoney($sale->product_discount) }}</span>
            </div>
        @endif
        @if ((float) ($sale->discount ?? 0) > 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span>(-) বিশেষ ছাড়:</span>
                <span>৳ {{ BanglaNumber::toBnMoney($sale->discount) }}</span>
            </div>
        @endif
        @if ((float) ($sale->tax ?? 0) > 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span>ভ্যাট:</span>
                <span>৳ {{ BanglaNumber::toBnMoney($sale->tax) }}</span>
            </div>
        @endif
        @if ((float) ($sale->delivery_charge ?? 0) > 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                <span>ডেলিভারি চার্জ:</span>
                <span>৳ {{ BanglaNumber::toBnMoney($sale->delivery_charge) }}</span>
            </div>
        @endif

        <div class="thermal-double-line"></div>

        <div style="display:flex; justify-content:space-between; font-weight:600; font-size:{{ $isCompact58 ? '10.5px' : '11.5px' }}; margin:3px 0;">
            <span>সর্বমোট:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($sale->total) }}</span>
        </div>

        <div class="thermal-dashed-line"></div>

        <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
            <span>পরিশোধ:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($sale->paid_amount) }}</span>
        </div>

        <div style="display:flex; justify-content:space-between; font-weight:700; color:{{ (float)$sale->due_amount > 0 ? '#b91c1c' : '#000000' }};">
            <span>বর্তমান বাকি:</span>
            <span>৳ {{ BanglaNumber::toBnMoney($sale->due_amount) }}</span>
        </div>

        {{-- Customer Dues Breakdown --}}
        @if ($printerSetting->show_customer_due && $customer)
            <div class="thermal-due-box" style="margin-top:5px; padding:5px 8px; background:#f8fafc; border:2px solid #cbd5e1; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; font-size:{{ $isCompact58 ? '8px' : '9px' }};">
                    <span>পূর্বের বাকি:</span>
                    <span>৳ {{ BanglaNumber::toBnMoney($previousDue) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:{{ $isCompact58 ? '9px' : '10px' }}; margin-top:2px;">
                    <span>সর্বমোট বকেয়া:</span>
                    <span>৳ {{ BanglaNumber::toBnMoney($totalCustomerDue) }}</span>
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
        <div>*** ধন্যবাদ, আবার আসবেন ***</div>
        <div style="font-size:{{ $isCompact58 ? '7px' : '8px' }}; color:#555; margin-top:2px;">{{ $printTime }}</div>
    </div>
</div>
