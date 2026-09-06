<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বিক্রয় খাতা প্রতিবেদন (Sales Ledger Report) - {{ $shop->name ?? 'POS' }}</title>
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
            font-size: 12px;
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
            padding: 24px 28px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .shop-info h1 {
            font-size: 22px;
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
            font-size: 18px;
            font-weight: 800;
            color: #0d9488;
            margin-bottom: 6px;
        }
        .report-meta-tag {
            display: inline-block;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            color: #334155;
            font-weight: 600;
        }
        .filter-banner {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 11.5px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }
        .kpi-card {
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }
        .kpi-card.teal { border-left: 4px solid #0d9488; }
        .kpi-card.green { border-left: 4px solid #10b981; }
        .kpi-card.red { border-left: 4px solid #ef4444; }
        .kpi-card.blue { border-left: 4px solid #3b82f6; }
        .kpi-card .k-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }
        .kpi-card .k-value {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', monospace;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 18px;
        }
        th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 7px 9px;
            border: 1px solid #cbd5e1;
        }
        td {
            padding: 7px 9px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tr:nth-child(even) td {
            background: #fcfdfe;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Plus Jakarta Sans', monospace; }
        .badge-status {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-partial { background: #fef3c7; color: #b45309; }
        .badge-due { background: #fee2e2; color: #b91c1c; }
        .footer-totals {
            background: #f1f5f9;
            font-weight: 700;
        }
        .footer-totals td {
            border-top: 2px solid #94a3b8;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }
        .sig-block {
            text-align: center;
            width: 180px;
        }
        .sig-line {
            border-top: 1px solid #475569;
            margin-bottom: 5px;
        }
        .sig-label {
            font-size: 11px;
            color: #475569;
            font-weight: 600;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .report-card {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('sales.ledger') }}" class="btn btn-secondary">
            &larr; বিক্রয় খাতায় ফিরে যান
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                প্রিন্ট করুন (Print)
            </button>
        </div>
    </div>

    <div class="report-card">
        <div class="header">
            <div class="shop-info">
                <h1>{{ $shop->name ?? 'ব্যবসার নাম' }}</h1>
                @if ($shop->address)
                    <p>{{ $shop->address }}</p>
                @endif
                @if ($shop->phone)
                    <p>ফোন: {{ $shop->phone }}</p>
                @endif
            </div>
            <div class="report-info">
                <h2>বিক্রয় খাতা প্রতিবেদন</h2>
                <div style="font-size:12px; color:#475569; font-weight:600; margin-bottom:4px;">Sales Transaction Ledger Report</div>
                <div class="report-meta-tag">
                    প্রিন্টের তারিখ: {{ now()->format('d M, Y · h:i A') }}
                </div>
            </div>
        </div>

        <div class="filter-banner">
            <div>
                <strong>তারিখ পরিসীমা: </strong>
                @if ($from && $to)
                    {{ \Carbon\Carbon::parse($from)->format('d M, Y') }} থেকে {{ \Carbon\Carbon::parse($to)->format('d M, Y') }}
                @elseif ($from)
                    {{ \Carbon\Carbon::parse($from)->format('d M, Y') }} থেকে শুরু
                @elseif ($to)
                    {{ \Carbon\Carbon::parse($to)->format('d M, Y') }} পর্যন্ত
                @else
                    সর্বকালের হিসাব (All Time)
                @endif
                @if ($search)
                    &nbsp;&bull;&nbsp; <strong>অনুসন্ধান: </strong>"{{ $search }}"
                @endif
            </div>
            <div>
                <strong>পেমেন্ট অবস্থা: </strong>
                @if ($status === 'paid')
                    পরিশোধিত (Paid)
                @elseif ($status === 'partial')
                    আংশিক (Partial)
                @elseif ($status === 'due')
                    বাকি (Due)
                @else
                    সব অবস্থা (All)
                @endif
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card teal">
                <div class="k-label">মোট বিক্রয় (Total Sales)</div>
                <div class="k-value">৳{{ number_format($totalAmount, 2) }}</div>
            </div>
            <div class="kpi-card green">
                <div class="k-label">মোট পরিশোধিত (Total Paid)</div>
                <div class="k-value" style="color:#15803d;">৳{{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="kpi-card red">
                <div class="k-label">মোট বাকি (Total Due)</div>
                <div class="k-value" style="color:#b91c1c;">৳{{ number_format($totalDue, 2) }}</div>
            </div>
            <div class="kpi-card blue">
                <div class="k-label">মোট চালান (Total Invoices)</div>
                <div class="k-value">{{ $totalCount }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:30px;" class="text-center">#</th>
                    <th style="width:110px;">ইনভয়েস নং</th>
                    <th>গ্রাহকের নাম ও যোগাযোগ</th>
                    <th>ওয়্যারহাউস</th>
                    <th class="text-center">আইটেম সংখ্যা</th>
                    <th class="text-right">মোট টাকা</th>
                    <th class="text-right">পরিশোধিত</th>
                    <th class="text-right">বাকি</th>
                    <th class="text-center">বিক্রয় তারিখ</th>
                    <th class="text-center">পেমেন্ট অবস্থা</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $idx => $sale)
                    <tr>
                        <td class="text-center mono">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="mono">#{{ $sale->invoice_no }}</strong>
                            @if ($sale->returns->isNotEmpty())
                                <div style="font-size:10px; color:#b45309; font-weight:600;">(ফেরত সমন্বিত)</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $sale->customer->name ?? 'ওয়াক-ইন গ্রাহক' }}</strong>
                            @if ($sale->customer?->phone)
                                <div class="mono" style="font-size:10.5px; color:#64748b;">{{ $sale->customer->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $sale->warehouse->name ?? '—' }}</td>
                        <td class="text-center mono">
                            {{ rtrim(rtrim(number_format((float) $sale->items->sum('quantity'), 2), '0'), '.') }}
                            <span style="font-size:10px; color:#64748b;">({{ $sale->items->count() }})</span>
                        </td>
                        <td class="text-right mono">৳{{ number_format((float) $sale->total, 2) }}</td>
                        <td class="text-right mono" style="color:#15803d;">৳{{ number_format((float) $sale->paid_amount, 2) }}</td>
                        <td class="text-right mono" style="{{ $sale->due_amount > 0 ? 'color:#b91c1c; font-weight:700;' : '' }}">
                            ৳{{ number_format((float) $sale->due_amount, 2) }}
                        </td>
                        <td class="text-center">
                            {{ optional($sale->sale_date)->format('d M, Y') }}
                        </td>
                        <td class="text-center">
                            @if ($sale->payment_status === 'paid')
                                <span class="badge-status badge-paid">পরিশোধিত</span>
                            @elseif ($sale->payment_status === 'partial')
                                <span class="badge-status badge-partial">আংশিক</span>
                            @else
                                <span class="badge-status badge-due">বাকি</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding:24px; color:#64748b;">
                            কোনো বিক্রয় তথ্য পাওয়া যায়নি (No records found)
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="footer-totals">
                    <td colspan="5" class="text-right"><strong>সর্বমোট (Total):</strong></td>
                    <td class="text-right mono">৳{{ number_format($totalAmount, 2) }}</td>
                    <td class="text-right mono" style="color:#15803d;">৳{{ number_format($totalPaid, 2) }}</td>
                    <td class="text-right mono" style="color:#b91c1c;">৳{{ number_format($totalDue, 2) }}</td>
                    <td colspan="2" class="text-center mono">মোট চালান: {{ $totalCount }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">প্রস্তুতকারী (Prepared By)</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">যাচাইকারী (Checked By)</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">অনুমোদনকারী স্বাক্ষর (Authorized Signature)</div>
            </div>
        </div>
    </div>
</body>
</html>
