<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ক্রয় খাতা প্রতিবেদন (Purchase Ledger Report) - {{ $shop->name ?? 'POS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 12mm 10mm;
        }
        body {
            font-family: 'Noto Sans Bengali', 'Plus Jakarta Sans', sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            padding: 20px;
            font-size: 12.5px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .no-print {
            max-width: 1080px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-group {
            display: inline-flex;
            gap: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-primary {
            background: #0d9488;
            color: #ffffff;
        }
        .btn-primary:hover { background: #0f766e; }
        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover { background: #f8fafc; }
        .report-card {
            max-width: 1080px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px 32px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }
        .shop-info h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .shop-info p {
            color: #475569;
            font-size: 12px;
        }
        .report-info {
            text-align: right;
        }
        .report-info h2 {
            font-size: 19px;
            font-weight: 800;
            color: #0d9488;
            margin-bottom: 6px;
        }
        .report-meta-tag {
            display: inline-block;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11.5px;
            color: #334155;
            font-weight: 600;
        }
        .filter-banner {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            font-size: 12px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .kpi-card {
            padding: 12px 14px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }
        .kpi-card.teal { border-left: 4px solid #0d9488; }
        .kpi-card.green { border-left: 4px solid #10b981; }
        .kpi-card.red { border-left: 4px solid #ef4444; }
        .kpi-card.blue { border-left: 4px solid #3b82f6; }
        .kpi-card .k-label {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 600;
        }
        .kpi-card .k-value {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', monospace;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 7px 10px;
            vertical-align: middle;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            white-space: nowrap;
        }
        tr:nth-child(even) td {
            background: #fafafa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mono { font-family: 'Plus Jakarta Sans', monospace; }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-due { background: #fee2e2; color: #991b1b; }
        .totals-row td {
            background: #f1f5f9 !important;
            font-weight: 800;
            font-size: 12.5px;
            border-top: 2px solid #334155;
            border-bottom: 2px solid #334155;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 10px;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }
        .sig-line {
            border-top: 1px dashed #64748b;
            margin-bottom: 6px;
        }
        .sig-title {
            font-weight: 700;
            font-size: 11.5px;
            color: #334155;
        }
        .footer-note {
            margin-top: 28px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: #94a3b8;
            page-break-inside: avoid;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .no-print { display: none !important; }
            .report-card { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <span style="font-weight:600; color:#475569;">ক্রয় খাতা রিপোর্ট প্রিভিউ (A4 Landscape)</span>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-secondary" onclick="window.close()">উইন্ডো বন্ধ করুন (Close)</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                PDF ডাউনলোড / প্রিন্ট করুন
            </button>
        </div>
    </div>

    <div class="report-card">
        <div class="header">
            <div class="shop-info">
                <h1>{{ $shop->name ?? 'আমার দোকান' }}</h1>
                @if($shop?->address)<p>{{ $shop->address }}</p>@endif
                <p>ফোন: {{ $shop?->phone ?? '—' }} @if($shop?->email) &middot; ইমেইল: {{ $shop->email }} @endif</p>
            </div>
            <div class="report-info">
                <h2>ক্রয় খাতা প্রতিবেদন</h2>
                <div style="font-size:12px; color:#64748b; font-weight:600; margin-bottom:4px;">PURCHASE LEDGER REPORT</div>
                <div class="report-meta-tag">মুদ্রণ: {{ now()->format('d M, Y · h:i A') }}</div>
            </div>
        </div>

        <div class="filter-banner">
            <div>
                <b>সময়কাল: </b>
                @if(!empty($filters['from']) || !empty($filters['to']))
                    <span class="mono">{{ $filters['from'] ? date('d M, Y', strtotime($filters['from'])) : 'শুরু' }}</span>
                    থেকে
                    <span class="mono">{{ $filters['to'] ? date('d M, Y', strtotime($filters['to'])) : 'বর্তমান' }}</span>
                @else
                    <span>সকল লেনদেন (All Records)</span>
                @endif
            </div>
            <div>
                <b>পেমেন্ট অবস্থা: </b>
                @if(($filters['status'] ?? 'all') === 'paid')
                    <span style="color:#166534; font-weight:700;">পরিশোধিত (Paid)</span>
                @elseif(($filters['status'] ?? 'all') === 'partial')
                    <span style="color:#92400e; font-weight:700;">আংশিক (Partial)</span>
                @elseif(($filters['status'] ?? 'all') === 'due')
                    <span style="color:#991b1b; font-weight:700;">বাকি (Due)</span>
                @else
                    <span>সব (All)</span>
                @endif
                @if(!empty($filters['search']))
                    &middot; <b>অনুসন্ধান: </b>"{{ $filters['search'] }}"
                @endif
            </div>
            <div>
                <b>প্রস্তুতকারক: </b>{{ auth()->user()?->name ?? 'Admin' }}
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card teal">
                <div class="k-label">মোট ক্রয় (Total Purchases)</div>
                <div class="k-value">৳{{ number_format((float) ($totals->total_amount ?? 0), 2) }}</div>
            </div>
            <div class="kpi-card green">
                <div class="k-label">মোট পরিশোধিত (Total Paid)</div>
                <div class="k-value" style="color:#166534;">৳{{ number_format((float) ($totals->total_paid ?? 0), 2) }}</div>
            </div>
            <div class="kpi-card red">
                <div class="k-label">মোট বাকি (Total Due)</div>
                <div class="k-value" style="color:#991b1b;">৳{{ number_format((float) ($totals->total_due ?? 0), 2) }}</div>
            </div>
            <div class="kpi-card blue">
                <div class="k-label">মোট চালান (Total Invoices)</div>
                <div class="k-value">{{ $totals->total_count ?? count($purchases) }} টি</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:4%;" class="text-center">#</th>
                    <th style="width:9%;">তারিখ</th>
                    <th style="width:10%;">চালান নং</th>
                    <th style="width:18%;">সরবরাহকারী (Supplier)</th>
                    <th style="width:25%;">পণ্য ও ব্যাচ (Items & Batches)</th>
                    <th style="width:9%;" class="text-right">মোট (৳)</th>
                    <th style="width:9%;" class="text-right">পরিশোধ (৳)</th>
                    <th style="width:8%;" class="text-right">বাকি (৳)</th>
                    <th style="width:8%;" class="text-center">অবস্থা</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $index => $purchase)
                    <tr>
                        <td class="text-center mono">{{ $index + 1 }}</td>
                        <td class="mono" style="white-space:nowrap;">{{ optional($purchase->purchase_date)->format('d M, Y') ?? '—' }}</td>
                        <td class="mono" style="font-weight:700;">#{{ $purchase->invoice_no }}</td>
                        <td>
                            <div style="font-weight:700; color:#0f172a;">{{ $purchase->supplier->name ?? '—' }}</div>
                            @if($purchase->supplier?->phone)
                                <div class="mono" style="font-size:10.5px; color:#64748b;">{{ $purchase->supplier->phone }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $itemNames = $purchase->items->map(function ($it) {
                                    $name = $it->product->name ?? 'পণ্য';
                                    $qty = rtrim(rtrim(number_format((float) $it->quantity, 2), '0'), '.');
                                    return $name . ' (' . $qty . ')';
                                })->implode(', ');
                            @endphp
                            <div>{{ $itemNames ?: '—' }}</div>
                            @php
                                $batches = $purchase->items->pluck('batch_no')->filter()->unique();
                            @endphp
                            @if($batches->isNotEmpty())
                                <div class="mono" style="font-size:10.5px; color:#64748b;">ব্যাচ: {{ $batches->implode(', ') }}</div>
                            @endif
                        </td>
                        <td class="text-right mono" style="font-weight:700;">৳{{ number_format((float) $purchase->total, 2) }}</td>
                        <td class="text-right mono" style="color:#166534;">৳{{ number_format((float) $purchase->paid_amount, 2) }}</td>
                        <td class="text-right mono" style="{{ (float) $purchase->due_amount > 0 ? 'color:#991b1b; font-weight:700;' : 'color:#64748b;' }}">৳{{ number_format((float) $purchase->due_amount, 2) }}</td>
                        <td class="text-center">
                            @if ($purchase->payment_status === 'paid')
                                <span class="badge badge-paid">পরিশোধিত</span>
                            @elseif ($purchase->payment_status === 'partial')
                                <span class="badge badge-partial">আংশিক</span>
                            @else
                                <span class="badge badge-due">বাকি</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding:24px; color:#64748b;">কোনো ক্রয়ের তথ্য পাওয়া যায়নি (No purchase records found)</td>
                    </tr>
                @endforelse
            </tbody>
            @if($purchases->isNotEmpty())
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="5" style="text-align:right;">সর্বমোট (Grand Totals):</td>
                        <td class="text-right mono">৳{{ number_format((float) ($totals->total_amount ?? 0), 2) }}</td>
                        <td class="text-right mono" style="color:#166534;">৳{{ number_format((float) ($totals->total_paid ?? 0), 2) }}</td>
                        <td class="text-right mono" style="color:#991b1b;">৳{{ number_format((float) ($totals->total_due ?? 0), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="signatures">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-title">প্রস্তুতকারী (Prepared By)</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-title">যাচাইকারী (Verified By)</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-title">অনুমোদিত স্বাক্ষর (Authorized By)</div>
            </div>
        </div>

        <div class="footer-note">
            <span>এটি একটি কম্পিউটার প্রস্তুতকৃত নথি যা কোনো স্বাক্ষরের বাধ্যবাধকতা রাখে না।</span>
            <span>পৃষ্ঠা ১ / ১ &middot; {{ $shop->name ?? 'POS' }}</span>
        </div>
    </div>
</body>
</html>
