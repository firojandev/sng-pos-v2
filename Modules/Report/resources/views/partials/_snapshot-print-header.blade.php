@php
    $routeName = request()->route()?->getName();
    $currentShop = auth()->user()?->shop ?? \Modules\Shop\Models\Shop::first();

    $printPerm = $printPermission ?? null;
    $canPrint = $printPerm ? (auth()->user()?->can($printPerm) ?? false) : false;
@endphp

{{-- Screen Toolbar: As-of Date Picker & Right-side PDF Export Button --}}
<div class="report-filter-bar no-print" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
    <form method="GET" action="{{ route($routeName) }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;" class="report-filter-form">
        <div style="width:170px; flex-shrink:0;">
            <x-core::input type="date" name="as_of" size="sm" :no-margin="true" :value="$asOf->toDateString()" :max="now()->toDateString()" />
        </div>
        <x-core::button type="submit" variant="secondary" size="sm" icon="filter">
            <span class="bn">দেখুন</span>
            <span class="en" style="display:none;">View</span>
        </x-core::button>
    </form>

    @if ($canPrint)
        <div class="report-actions" style="display:flex; align-items:center; gap:8px; margin-left:auto;">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="file-text"
                id="btn-report-export-pdf"
                title="প্রিন্ট"
            >
                <span class="bn">প্রিন্ট</span>
                <span class="en" style="display:none;">Print</span>
            </x-core::button>
        </div>
    @endif
</div>

@if ($canPrint)
{{-- Print-only Executive Header (Shown only when printing / saving as PDF) --}}
<div class="report-print-header" style="display:none;">
    {{-- Top Header with Shop Info --}}
    <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:10px;">
        <div style="flex-shrink:0; width:48px; height:48px; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
            @if(!empty($currentShop?->logo))
                <img src="{{ $currentShop->logo_url ?? asset($currentShop->logo) }}" alt="Shop Logo" style="max-width:48px; max-height:48px; object-fit:contain;">
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
                {{ $currentShop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
            </div>
            @if(!empty($currentShop?->address))
                <div style="font-size:12px; color:#475569; margin-top:2px;">
                    {{ $currentShop->address }}
                </div>
            @endif
            @if(!empty($currentShop?->phone))
                <div style="font-size:12px; color:#475569; margin-top:1px;">
                    <span class="bn">মোবাইল : </span><span class="en" style="display:none;">Mobile : </span>{{ $currentShop->phone }}
                </div>
            @endif
        </div>
    </div>

    {{-- Centered Title with Horizontal Accent Lines --}}
    <div style="display:flex; align-items:center; justify-content:center; gap:16px; margin:8px 0 10px 0;">
        <div style="flex:1; height:1px; background:#94a3b8;"></div>
        <div style="font-size:18px; font-weight:800; color:#0f172a; letter-spacing:0.5px; padding:0 8px;">
            <span class="bn">{{ $reportTitle ?? 'প্রতিবেদন' }}</span>
            <span class="en" style="display:none;">{{ $reportTitleEn ?? 'Report' }}</span>
        </div>
        <div style="flex:1; height:1px; background:#94a3b8;"></div>
    </div>

    {{-- Metadata: Report Information --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:11px; line-height:1.5; margin-bottom:10px; color:#0f172a;">
        <div>
            <div>
                <b><span class="bn">তারিখ অনুযায়ী : </span><span class="en" style="display:none;">As of : </span></b>
                {{ $asOf->format('d M Y') }}
            </div>
        </div>
        <div style="text-align:right;">
            <div>
                <b><span class="bn">প্রস্তুতকাল : </span><span class="en" style="display:none;">Generated : </span></b>
                {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>
</div>

{{-- Scoped Print Styles for Reports --}}
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 8mm 8mm 10mm 8mm;
    }

    /* ===== RESET DARK MODE: Force light values in print ===== */
    :root, :root[data-theme="dark"], html, html[data-theme="dark"] {
        --ink-900: #0f172a !important;
        --ink-800: #1e293b !important;
        --ink-700: #334155 !important;
        --ink-600: #475569 !important;
        --ink-500: #64748b !important;
        --ink-400: #94a3b8 !important;
        --card: #ffffff !important;
        --paper: #f8fafc !important;
        --paper-line: #f1f5f9 !important;
        --border: #e2e8f0 !important;
        --table-color: #0f172a !important;
        --table-bg: #ffffff !important;
        --table-border: #e2e8f0 !important;
        --table-header-bg: #f8fafc !important;
        --table-header-color: #334155 !important;
        --table-font-size: 9.5px !important;
        --table-header-font-size: 9px !important;
        --table-px: 6px !important;
        --table-py: 3.5px !important;
        --table-radius: 4px !important;
        --shadow-sm: none !important;
        --shadow-card: none !important;
        color-scheme: light !important;
    }

    html, body {
        background: #ffffff !important;
        color: #0f172a !important;
        font-family: 'Noto Sans Bengali', 'Plus Jakarta Sans', sans-serif !important;
        font-size: 10px !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    body * {
        visibility: hidden !important;
    }
    .report-printable-area,
    .report-printable-area * {
        visibility: visible !important;
        color-adjust: exact !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    /* Force all text inside printable area to be dark */
    .report-printable-area {
        color: #0f172a !important;
    }
    .app, .main, .content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        min-width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        display: block !important;
        position: static !important;
        overflow: visible !important;
    }
    .report-printable-area {
        position: static !important;
        left: auto !important;
        top: auto !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        box-sizing: border-box !important;
        overflow: visible !important;
    }
    .report-print-header {
        display: block !important;
        margin-bottom: 10px !important;
    }
    .report-print-footer {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-top: 24px !important;
        padding-top: 10px !important;
        border-top: 1px solid #cbd5e1 !important;
        font-size: 9.5px !important;
        color: #64748b !important;
        page-break-inside: avoid !important;
    }
    .report-credit-watermark {
        display: block !important;
        visibility: visible !important;
        position: fixed !important;
        bottom: 2mm !important;
        right: 0 !important;
        font-size: 7.5px !important;
        color: #94a3b8 !important;
        opacity: 0.6 !important;
        font-weight: 400 !important;
        text-align: right !important;
        font-family: 'Noto Sans Bengali', 'Plus Jakarta Sans', sans-serif !important;
        z-index: 9999 !important;
    }
    .no-print,
    .sidebar,
    .topbar,
    .sidebar-overlay,
    .report-filter-bar,
    .tabbar,
    footer,
    .toast,
    .btn,
    .report-actions {
        display: none !important;
    }

    /* Stat Cards Row in Print - Responsive & Non-clipping */
    .stat-grid {
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
        gap: 8px !important;
        margin-bottom: 14px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        page-break-inside: avoid !important;
    }
    .stat-grid > .stat-card {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        border: 1px solid #cbd5e1 !important;
        background: #f8fafc !important;
        box-shadow: none !important;
        border-radius: 6px !important;
        padding: 6px 8px !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 6px !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
        page-break-inside: avoid !important;
    }
    .stat-card .ic {
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
        max-width: 26px !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .stat-card .ic svg,
    .stat-card .ic .app-icon {
        width: 14px !important;
        height: 14px !important;
    }
    .stat-card .stat-card-details {
        min-width: 0 !important;
        flex: 1 !important;
        overflow: hidden !important;
    }
    .stat-card .val {
        font-family: 'Plus Jakarta Sans', 'Manrope', 'Noto Sans Bengali', sans-serif !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        line-height: 1.15 !important;
        white-space: nowrap !important;
        letter-spacing: -0.01em !important;
        color: #0f172a !important;
    }
    .stat-card .lbl {
        font-size: 9.5px !important;
        line-height: 1.15 !important;
        margin-top: 1px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        color: #475569 !important;
        font-weight: 600 !important;
    }
    .stat-card .stat-card-subtext {
        font-size: 8.5px !important;
        line-height: 1.15 !important;
        margin-top: 1px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        color: #64748b !important;
    }
    .stat-card .trend {
        display: none !important;
    }

    /* ===== TWO-COLUMN TABLE GRID ===== */
    .table-container {
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
        background: #ffffff !important;
        border-radius: 6px !important;
        overflow: visible !important;
        box-sizing: border-box !important;
        width: auto !important;
    }
    .report-snapshot-grid,
    .fin-snapshot-grid {
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
        gap: 10px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid !important;
    }
    .report-snapshot-grid > .table-container,
    .fin-snapshot-grid > .table-container {
        flex: 1 1 0 !important;
        width: 50% !important;
        max-width: 50% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        overflow: visible !important;
    }
    .table-responsive {
        overflow: visible !important;
        width: 100% !important;
    }
    .panel-head {
        background: #f8fafc !important;
        border-bottom: 1px solid #cbd5e1 !important;
        padding: 8px 12px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ===== TABLE LAYOUT ===== */
    .app-table {
        width: 100% !important;
        table-layout: auto !important;
        border-collapse: collapse !important;
        font-size: 9.5px !important;
        box-sizing: border-box !important;
        color: #0f172a !important;
    }
    .app-table colgroup {
        display: table-column-group !important;
    }
    .app-table col.col-name {
        width: 55% !important;
    }
    .app-table col.col-amount {
        width: 45% !important;
    }
    .app-table th:first-child:not([colspan]),
    .app-table td:first-child:not([colspan]) {
        width: 60% !important;
        max-width: 60% !important;
        box-sizing: border-box !important;
        word-break: break-word !important;
        overflow: visible !important;
    }
    .app-table th:last-child:not([colspan]),
    .app-table td:last-child:not([colspan]),
    .app-table th:nth-child(2):not([colspan]),
    .app-table td:nth-child(2):not([colspan]) {
        width: 40% !important;
        max-width: 40% !important;
        text-align: right !important;
        white-space: nowrap !important;
        box-sizing: border-box !important;
    }
    .app-table th[colspan],
    .app-table td[colspan] {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .app-table th {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        padding: 4px 6px !important;
        font-weight: 700 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        box-sizing: border-box !important;
    }
    .app-table td {
        border: 1px solid #e2e8f0 !important;
        padding: 3.5px 6px !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
        box-sizing: border-box !important;
    }
    /* Higher-specificity override to beat compiled CSS rules */
    .app-table tbody td,
    .report-printable-area .app-table td,
    .report-printable-area .app-table tbody td {
        padding: 3.5px 6px !important;
        color: #0f172a !important;
        font-size: 9.5px !important;
        line-height: 1.25 !important;
        box-sizing: border-box !important;
    }
    .app-table tr {
        page-break-inside: avoid !important;
    }
    .app-badge, .badge {
        border: 1px solid #cbd5e1 !important;
        font-size: 9px !important;
        padding: 1px 5px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

@push('scripts')
<script>
$(function () {
    $(document).on('click', '#btn-report-export-pdf', function (e) {
        e.preventDefault();

        // Remember current theme and force light mode for print
        var $html = $('html');
        var originalTheme = $html.attr('data-theme') || '';
        $html.attr('data-theme', 'light');

        // Small delay to allow CSS variables to recalculate
        setTimeout(function () {
            window.print();
        }, 50);

        // Restore theme after print dialog closes
        $(window).one('afterprint', function () {
            if (originalTheme) {
                $html.attr('data-theme', originalTheme);
            } else {
                $html.removeAttr('data-theme');
            }
        });

        // Fallback: restore after 3 seconds if afterprint doesn't fire
        setTimeout(function () {
            if (originalTheme) {
                $html.attr('data-theme', originalTheme);
            } else {
                $html.removeAttr('data-theme');
            }
        }, 3000);
    });
});
</script>
@endpush

@if (\Modules\Core\Models\Setting::isCreditTextEnabled())
    <div class="report-credit-watermark" style="display:none;">
        {{ \Modules\Core\Models\Setting::getCreditText() }}
    </div>
@endif
@else
<style>
@media print {
    .report-printable-area {
        display: none !important;
    }
}
</style>
@endif
