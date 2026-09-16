@php
    $isThermal = $printerSetting->isThermal();
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইনভয়েস #{{ $sale->invoice_no }} - {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page {
            @if ($isThermal)
                size: {{ $printerSetting->getCssPageSize() }};
                margin: {{ $printerSetting->page_margin ?? 2 }}mm;
            @elseif ($printerSetting->isA5())
                size: A5 {{ $printerSetting->orientation ?: 'portrait' }};
                margin: {{ $printerSetting->page_margin ?? 6 }}mm;
            @else
                size: A4 {{ $printerSetting->orientation ?: 'portrait' }};
                margin: {{ $printerSetting->page_margin ?? 8 }}mm;
            @endif
        }
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            padding: 20px 10px;
            font-size: {{ $isThermal ? '10px' : ($printerSetting->isA5() ? '11.5px' : '12.5px') }};
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .public-topbar {
            width: 100%;
            max-width: 720px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        .public-topbar-title {
            font-weight: 700;
            font-size: 15px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .public-topbar-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-weight: 600;
        }
        .btn-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover { background: #f8fafc; }
        .public-invoice-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .sale-invoice-sheet {
            max-width: {{ $isThermal ? $printerSetting->getCssPaperWidth() : ($printerSetting->isA5() ? '520px' : '680px') }} !important;
        }
        .public-footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .public-invoice-container {
                display: block !important;
            }
            .sale-invoice-sheet,
            .thermal-receipt-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
        @media (max-width: 640px) {
            .public-topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .btn-group {
                justify-content: stretch;
            }
            .btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    {{-- Top Action Bar --}}
    <div class="public-topbar no-print">
        <div class="public-topbar-title">
            <span>{{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}</span>
            <span class="public-topbar-badge">#{{ $sale->invoice_no }}</span>
        </div>
        <div class="btn-group">
            <x-core::button
                type="button"
                color="primary"
                size="sm"
                icon="printer"
                onclick="window.print()"
            >
                <span>প্রিন্ট / PDF ডাউনলোড</span>
            </x-core::button>
        </div>
    </div>

    {{-- Invoice Sheet Container --}}
    <div class="public-invoice-container">
        @include('sales::sales._invoice_sheet')
    </div>

    <div class="public-footer-note no-print">
        <p>অনলাইন বিক্রয় ইনভয়েস পোর্টাল &bull; {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}</p>
    </div>
</body>
</html>
