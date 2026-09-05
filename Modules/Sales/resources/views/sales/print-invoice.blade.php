<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বিক্রয় ইনভয়েস - #{{ $sale->invoice_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            padding: 20px;
            font-size: 12.5px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .no-print {
            max-width: 680px;
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
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-primary {
            background: #1c1c1c;
            color: #ffffff;
        }
        .btn-primary:hover { background: #000000; }
        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover { background: #f8fafc; }
        .sale-invoice-sheet {
            max-width: 680px !important;
        }
        table.invoice-items-table {
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: 1px solid #94a3b8 !important;
            margin-top: 14px !important;
            margin-bottom: 14px !important;
            font-size: 11.5px !important;
        }
        table.invoice-items-table th,
        table.invoice-items-table td {
            box-sizing: border-box !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .sale-invoice-sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            table.invoice-items-table {
                border: 1px solid #94a3b8 !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <span style="font-weight:700; color:#334155;">বিক্রয় ইনভয়েস স্লিপ প্রিভিউ</span>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-secondary" onclick="window.close()">উইন্ডো বন্ধ করুন</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                PDF ডাউনলোড / প্রিন্ট করুন
            </button>
        </div>
    </div>

    @include('sales::sales._invoice_sheet')

    @if(request()->query('autoprint') === '1')
        <script>
            if (document.readyState === 'complete') {
                setTimeout(function() { window.print(); }, 100);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(function() { window.print(); }, 100);
                });
            }
        </script>
    @endif
</body>
</html>
