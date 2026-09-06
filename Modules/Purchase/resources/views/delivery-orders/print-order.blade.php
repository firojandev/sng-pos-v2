<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Delivery Order - #{{ $order->order_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Noto Sans Bengali', sans-serif; color: #1e293b; background: #f8fafc; padding: 24px; }
        .invoice-box { max-width: 800px; margin: 0 auto; background: #fff; padding: 32px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: 700; color: #0f172a; }
        .meta { font-size: 13px; color: #64748b; margin-top: 4px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .details-card { background: #f8fafc; padding: 14px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px; }
        .card-title { font-weight: 700; margin-bottom: 6px; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px 12px; }
        th { background: #f1f5f9; text-align: left; font-weight: 600; }
        .text-right { text-align: right; }
        .totals-table { width: 280px; margin-left: auto; margin-bottom: 30px; font-size: 13px; }
        .totals-table td { border: none; padding: 6px 0; }
        .grand-total { border-top: 2px solid #0f172a !important; font-size: 15px; font-weight: 700; padding-top: 8px !important; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; padding-top: 20px; font-size: 13px; text-align: center; }
        .sig-line { border-top: 1px solid #94a3b8; width: 180px; padding-top: 6px; }
        .print-btn { display: inline-block; padding: 10px 20px; background: #0f172a; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-family: inherit; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { border: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width:800px; margin:0 auto 16px; text-align:right;">
        <button class="print-btn" onclick="window.print()">ডাউনলোড / প্রিন্ট করুন</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div>
                <div class="title">পারচেজ ডেলিভারি অর্ডার (PDO)</div>
                <div class="meta">অর্ডার নং: <b>#{{ $order->order_no }}</b></div>
                <div class="meta">তারিখ: {{ optional($order->order_date)->format('d M, Y') }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:18px; font-weight:700; color:#0f172a;">{{ auth()->user()?->shop?->name ?? 'ব্যবসা প্রতিষ্ঠান' }}</div>
                <div class="meta">{{ auth()->user()?->shop?->address ?? '' }}</div>
                <div class="meta">{{ auth()->user()?->shop?->phone ?? '' }}</div>
            </div>
        </div>

        <div class="details-grid">
            <div class="details-card">
                <div class="card-title">সরবরাহকারীর বিবরণ:</div>
                <div><b>{{ $order->supplier->name ?? 'সাধারণ সরবরাহকারী' }}</b></div>
                @if($order->supplier?->phone)<div>মোবাইল: {{ $order->supplier->phone }}</div>@endif
                @if($order->supplier?->address)<div>ঠিকানা: {{ $order->supplier->address }}</div>@endif
            </div>

            <div class="details-card">
                <div class="card-title">গন্তব্য ও ডেলিভারি তথ্য:</div>
                <div><b>গুদাম:</b> {{ $order->warehouse->name }} @if($order->warehouse->branch) ({{ $order->warehouse->branch->name }}) @endif</div>
                <div><b>প্রত্যাশিত ডেলিভারি:</b> {{ optional($order->expected_delivery_date)->format('d M, Y') ?? '—' }}</div>
                @if($order->delivery_person_name)<div><b>ডেলিভারি ম্যান:</b> {{ $order->delivery_person_name }} ({{ $order->delivery_person_phone }})</div>@endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:45%;">পণ্যের বিবরণ</th>
                    <th style="width:15%;">একক</th>
                    <th style="width:15%;" class="text-right">অর্ডার পরিমাণ</th>
                    <th style="width:20%;" class="text-right">একক মূল্য</th>
                    <th style="width:20%;" class="text-right">মোট টাকা</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <b>{{ $item->product->name }}</b>
                            @if($item->product->sku)<div style="font-size:11px; color:#64748b;">SKU: {{ $item->product->sku }}</div>@endif
                        </td>
                        <td>{{ $item->unit->name ?? 'মূল একক' }}</td>
                        <td class="text-right">{{ rtrim(rtrim(number_format($item->ordered_quantity, 2), '0'), '.') }}</td>
                        <td class="text-right">৳{{ number_format($item->purchase_price, 2) }}</td>
                        <td class="text-right">৳{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-table">
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span>সাবটোটাল:</span>
                <b>৳{{ number_format($order->subtotal, 2) }}</b>
            </div>
            @if($order->discount > 0)
                <div style="display:flex; justify-content:space-between; margin-bottom:6px; color:#ef4444;">
                    <span>ছাড়:</span>
                    <b>-৳{{ number_format($order->discount, 2) }}</b>
                </div>
            @endif
            @if($order->delivery_charge > 0)
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span>ডেলিভারি চার্জ:</span>
                    <b>৳{{ number_format($order->delivery_charge, 2) }}</b>
                </div>
            @endif
            <div class="grand-total" style="display:flex; justify-content:space-between;">
                <span>সর্বমোট:</span>
                <span>৳{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        @if ($order->note)
            <div style="font-size:12px; color:#64748b; margin-bottom:20px;">
                <b>বিশেষ দ্রষ্টব্য:</b> {{ $order->note }}
            </div>
        @endif

        <div class="signatures">
            <div class="sig-line">অর্ডারকারী কর্মকর্তা</div>
            <div class="sig-line">অনুমোদনকারীর স্বাক্ষর</div>
            <div class="sig-line">সরবরাহকারীর স্বীকৃতি</div>
        </div>
    </div>
</body>
</html>
