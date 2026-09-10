@php
    $rangeLabels = [
        'today' => ['bn' => 'আজ', 'en' => 'Today'],
        'week' => ['bn' => 'এই সপ্তাহ', 'en' => 'This Week'],
        'month' => ['bn' => 'এই মাস', 'en' => 'This Month'],
        'year' => ['bn' => 'এই বছর', 'en' => 'This Year'],
        'custom' => ['bn' => 'কাস্টম রেঞ্জ', 'en' => 'Custom Range'],
    ];
    $routeName = request()->route()->getName();
    $currentShop = auth()->user()?->shop;
@endphp

{{-- Screen Toolbar: Filters & Right-side PDF Export Button --}}
<div class="report-filter-bar no-print" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <div class="range-tabs">
            @foreach ($rangeLabels as $key => $labels)
                <a href="{{ route($routeName, ['range' => $key]) }}" class="{{ $range === $key ? 'active' : '' }}">
                    <span class="bn">{{ $labels['bn'] }}</span>
                    <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route($routeName) }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;" class="report-filter-form">
            <input type="hidden" name="range" value="custom">
            <div style="width:150px; flex-shrink:0;">
                <x-core::input type="date" name="from" size="sm" :no-margin="true" :value="$from" />
            </div>
            <div style="width:150px; flex-shrink:0;">
                <x-core::input type="date" name="to" size="sm" :no-margin="true" :value="$to" />
            </div>
            <x-core::button type="submit" variant="secondary" size="sm" icon="filter">
                <span class="bn">ফিল্টার</span>
                <span class="en" style="display:none;">Filter</span>
            </x-core::button>
        </form>
    </div>

    {{-- Right-side PDF Export Button --}}
    <div class="report-actions" style="display:flex; align-items:center; gap:8px; margin-left:auto;">
        <x-core::button
            type="button"
            variant="secondary"
            size="sm"
            icon="file-text"
            id="btn-report-export-pdf"
            title="পিডিএফ এক্সপোর্ট / প্রিন্ট"
        >
            <span class="bn">পিডিএফ এক্সপোর্ট</span>
            <span class="en" style="display:none;">Export PDF</span>
        </x-core::button>
    </div>
</div>

{{-- Print-only Executive Header (Shown only when printing / saving as PDF) --}}
<div class="report-print-header" style="display:none;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #0d9488; padding-bottom:12px; margin-bottom:16px;">
        <div class="print-shop-info">
            <h1 style="font-size:20px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">{{ $currentShop->name ?? config('app.name', 'MasterPOS') }}</h1>
            @if(!empty($currentShop?->address))
                <p style="margin:0 0 2px 0; font-size:11px; color:#475569;">{{ $currentShop->address }}</p>
            @endif
            @if(!empty($currentShop?->phone))
                <p style="margin:0; font-size:11px; color:#475569;">
                    <span class="bn">ফোন: </span><span class="en" style="display:none;">Phone: </span>{{ $currentShop->phone }}
                </p>
            @endif
        </div>
        <div class="print-report-info" style="text-align:right;">
            <h2 style="font-size:17px; font-weight:800; color:#0d9488; margin:0 0 4px 0;">
                <span class="bn">{{ $reportTitle ?? 'প্রতিবেদন' }}</span>
                <span class="en" style="display:none;">{{ $reportTitleEn ?? 'Report' }}</span>
            </h2>
            <div style="font-size:11.5px; color:#1e293b; margin-bottom:3px;">
                <span class="bn">সময়সীমা: </span><span class="en" style="display:none;">Period: </span>
                <b>
                    @if($range === 'today')
                        <span class="bn">আজ ({{ \Carbon\Carbon::parse($from)->format('d M Y') }})</span>
                        <span class="en" style="display:none;">Today ({{ \Carbon\Carbon::parse($from)->format('d M Y') }})</span>
                    @elseif($from === $to)
                        {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($from)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                    @endif
                </b>
            </div>
            <div style="font-size:10px; color:#64748b;">
                <span class="bn">প্রস্তুতকাল: </span><span class="en" style="display:none;">Generated: </span>
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
        margin: 10mm 10mm 12mm 10mm;
    }
    html, body {
        background: #ffffff !important;
        color: #0f172a !important;
        font-family: 'Noto Sans Bengali', 'Plus Jakarta Sans', sans-serif !important;
        font-size: 11px !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    body * {
        visibility: hidden !important;
    }
    .report-printable-area,
    .report-printable-area * {
        visibility: visible !important;
    }
    .report-printable-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        box-sizing: border-box !important;
    }
    .report-print-header {
        display: block !important;
        margin-bottom: 14px !important;
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

    /* Table in Print */
    .table-container {
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
        background: #ffffff !important;
        border-radius: 6px !important;
        overflow: visible !important;
        width: 100% !important;
        box-sizing: border-box !important;
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
    .app-table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 11px !important;
    }
    .app-table th {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        padding: 6px 8px !important;
        font-weight: 700 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .app-table td {
        border: 1px solid #e2e8f0 !important;
        padding: 6px 8px !important;
        color: #0f172a !important;
    }
    .app-table tr {
        page-break-inside: avoid !important;
    }
    .app-badge, .badge {
        border: 1px solid #cbd5e1 !important;
        font-size: 9.5px !important;
        padding: 2px 6px !important;
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
        window.print();
    });
});
</script>
@endpush
