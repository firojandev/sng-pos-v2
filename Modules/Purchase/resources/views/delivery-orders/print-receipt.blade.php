<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Received Challan - #{{ $receipt->receipt_no }}</title>
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
        .totals-table { width: 260px; margin-left: auto; margin-bottom: 30px; font-size: 13px; }
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
                <div class="title">পণ্য গ্রহণ চালান (Goods Received Note)</div>
                <div class="meta">চালান রসিদ নং: <b>#{{ $receipt->receipt_no }}</b></div>
                <div class="meta">গ্রহণের তারিখ: {{ optional($receipt->delivery_date)->format('d M, Y') }}</div>
                <div class="meta">মূল অর্ডার নং: <b>#{{ $receipt->order->order_no }}</b></div>
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
                <div><b>{{ $receipt->order->supplier->name ?? 'সাধারণ সরবরাহকারী' }}</b></div>
                @if($receipt->order->supplier?->phone)<div>মোবাইল: {{ $receipt->order->supplier->phone }}</div>@endif
                <div><b>সরবরাহকারী চালান নং:</b> {{ $receipt->challan_no ?? '—' }}</div>
            </div>

            <div class="details-card">
                <div class="card-title">গুদাম ও ডেলিভারি তথ্য:</div>
                <div><b>গৃহীত গুদাম:</b> {{ $receipt->warehouse->name }}</div>
                <div><b>গ্রহণকারী কর্মকর্তা:</b> {{ $receipt->receiver->name ?? '—' }}</div>
                @if($receipt->vehicle_no)<div><b>গাড়ির নম্বর:</b> {{ $receipt->vehicle_no }}</div>@endif
                @if($receipt->delivery_person_name)<div><b>ডেলিভারি ম্যান:</b> {{ $receipt->delivery_person_name }} ({{ $receipt->delivery_person_phone }})</div>@endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:40%;">পণ্যের বিবরণ</th>
                    <th style="width:20%;">ব্যাচ ও মেয়াদ</th>
                    <th style="width:15%;" class="text-right">গৃহীত পরিমাণ</th>
                    <th style="width:20%;" class="text-right">মূল্য (একক)</th>
                    <th style="width:20%;" class="text-right">মোট টাকা</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipt->items as $idx => $rItem)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <b>{{ $rItem->product->name }}</b>
                            @if($rItem->product->sku)<div style="font-size:11px; color:#64748b;">SKU: {{ $rItem->product->sku }}</div>@endif
                        </td>
                        <td>
                            <div>ব্যাচ: <b>{{ $rItem->batch_no }}</b></div>
                            @if($rItem->expiry_date)<div style="font-size:11px; color:#ef4444;">মেয়াদ: {{ optional($rItem->expiry_date)->format('d M, Y') }}</div>@endif
                        </td>
                        <td class="text-right" style="font-weight:700; color:#10b981;">
                            {{ rtrim(rtrim(number_format($rItem->received_quantity, 2), '0'), '.') }}
                        </td>
                        <td class="text-right">৳{{ number_format($rItem->unit_cost, 2) }}</td>
                        <td class="text-right">৳{{ number_format($rItem->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-table">
            <div class="grand-total" style="display:flex; justify-content:space-between;">
                <span>চালানের মোট মূল্য:</span>
                <span>৳{{ number_format($receipt->total_amount, 2) }}</span>
            </div>
        </div>

        @if ($receipt->note)
            <div style="font-size:12px; color:#64748b; margin-bottom:20px;">
                <b>গ্রহণ পর্যবেক্ষণ / নোট:</b> {{ $receipt->note }}
            </div>
        @endif

        <div class="signatures">
            <div class="sig-line">পণ্য প্রদানকারী (কুরিয়ার/ড্রাইভার)</div>
            <div class="sig-line">পণ্য নিরীক্ষক</div>
            <div class="sig-line">গুদাম ইন-চার্জ</div>
        </div>
    </div>
</body>
</html>
