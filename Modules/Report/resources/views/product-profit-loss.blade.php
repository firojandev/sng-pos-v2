<x-core::layout
    title="পণ্যভিত্তিক লাভ-ক্ষতি রিপোর্ট"
    title-en="Product-wise Profit & Loss Report"
    subtitle="নির্বাচিত সময়ের পণ্য ও ব্যাচভিত্তিক ক্রয় মূল্য, বিক্রয় মূল্য এবং অর্জিত লাভ-ক্ষতি"
    subtitle-en="Product and batch-wise purchase price, sale price, and profit/loss summary for the selected period"
    active="report-product-profit-loss"
>
    <x-report::tabbar active="product-profit-loss" />

    <div class="report-printable-area">
        @php
            $rangeLabels = [
                'today' => ['bn' => 'আজ', 'en' => 'Today'],
                'week' => ['bn' => 'এই সপ্তাহ', 'en' => 'This Week'],
                'month' => ['bn' => 'এই মাস', 'en' => 'This Month'],
                'year' => ['bn' => 'এই বছর', 'en' => 'This Year'],
                'custom' => ['bn' => 'কাস্টম রেঞ্জ', 'en' => 'Custom Range'],
            ];
            $currentShop = auth()->user()?->shop ?? \Modules\Shop\Models\Shop::first();
            $canPrint = auth()->user()?->can('report-product-profit-loss.print') ?? false;
        @endphp

        {{-- Filter Bar --}}
        <div class="report-filter-bar no-print" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div class="range-tabs">
                    @foreach ($rangeLabels as $key => $labels)
                        <a href="{{ route('reports.product-profit-loss', ['range' => $key, 'warehouse_id' => $warehouseId, 'search' => $search]) }}" class="{{ $range === $key ? 'active' : '' }}">
                            <span class="bn">{{ $labels['bn'] }}</span>
                            <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('reports.product-profit-loss') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;" class="report-filter-form">
                    <input type="hidden" name="range" value="custom">

                    <div style="width:140px; flex-shrink:0;">
                        <x-core::input type="date" name="from" size="sm" :no-margin="true" :value="$from" />
                    </div>
                    <div style="width:140px; flex-shrink:0;">
                        <x-core::input type="date" name="to" size="sm" :no-margin="true" :value="$to" />
                    </div>

                    @if($warehouses->isNotEmpty())
                        <div style="width:160px; flex-shrink:0;">
                            <x-core::select name="warehouse_id" size="sm" :no-margin="true">
                                <option value="">সব গুদাম / All Warehouses</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </x-core::select>
                        </div>
                    @endif

                    <div style="width:180px; flex-shrink:0;">
                        <x-core::input
                            type="text"
                            name="search"
                            size="sm"
                            :no-margin="true"
                            placeholder="পণ্য বা SKU খুঁজুন..."
                            placeholder-en="Search product or SKU..."
                            :value="$search"
                        />
                    </div>

                    <x-core::button type="submit" variant="secondary" size="sm" icon="filter">
                        <span class="bn">ফিল্টার</span>
                        <span class="en" style="display:none;">Filter</span>
                    </x-core::button>

                    @if(!empty($search) || !empty($warehouseId))
                        <a href="{{ route('reports.product-profit-loss', ['range' => $range, 'from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-secondary" style="text-decoration:none; padding:6px 10px; font-size:12px; border-radius:6px;">
                            <span class="bn">রিসেট</span>
                            <span class="en" style="display:none;">Reset</span>
                        </a>
                    @endif
                </form>
            </div>

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
            {{-- Print Header (Logo on Left Side) --}}
            <div class="report-print-header" style="display:none;">
                <div style="display:flex; align-items:center; justify-content:center; gap:14px; margin-bottom:12px;">
                    <div style="flex-shrink:0; width:52px; height:52px; border-radius:8px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                        @if(!empty($currentShop?->logo))
                            <img src="{{ $currentShop->logo_url ?? asset($currentShop->logo) }}" alt="Shop Logo" style="max-width:52px; max-height:52px; object-fit:contain;">
                        @else
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                        <div style="font-size:20px; font-weight:800; color:#0f172a; line-height:1.2;">
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

                <div style="display:flex; align-items:center; justify-content:center; gap:16px; margin:10px 0 14px 0;">
                    <div style="flex:1; height:1px; background:#94a3b8;"></div>
                    <div style="font-size:14px; font-weight:600; color:#334155; letter-spacing:0.5px; padding:0 8px;">
                        <span class="bn">পণ্যভিত্তিক লাভ-ক্ষতি রিপোর্ট</span>
                        <span class="en" style="display:none;">Product-wise Profit & Loss Report</span>
                    </div>
                    <div style="flex:1; height:1px; background:#94a3b8;"></div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:12px; line-height:1.6; margin-bottom:14px; color:#0f172a;">
                    <div>
                        <div>
                            <b><span class="bn">সময়সীমা : </span><span class="en" style="display:none;">Period : </span></b>
                            @if($range === 'today')
                                <span class="bn">আজ ({{ \Carbon\Carbon::parse($from)->format('d M Y') }})</span>
                                <span class="en" style="display:none;">Today ({{ \Carbon\Carbon::parse($from)->format('d M Y') }})</span>
                            @elseif($from === $to)
                                {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                            @endif
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
        @endif

        {{-- Stat Cards --}}
        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); margin-bottom:16px;">
            <x-core::stat-card
                icon="shopping-cart"
                color="teal"
                :value="'৳' . number_format($totals['sale_revenue'], 2)"
                label="মোট বিক্রয় মূল্য"
                label-en="Total Sale Price"
            />
            <x-core::stat-card
                icon="truck"
                color="blue"
                :value="'৳' . number_format($totals['purchase_cost'], 2)"
                label="মোট ক্রয় মূল্য"
                label-en="Total Purchase Price"
            />
            <x-core::stat-card
                icon="trending-up"
                :color="$totals['profit'] >= 0 ? 'green' : 'red'"
                :value="'৳' . number_format($totals['profit'], 2)"
                label="মোট লাভ / ক্ষতি"
                label-en="Total Profit / Loss"
            />
            @php
                $marginPercent = $totals['sale_revenue'] > 0 ? ($totals['profit'] / $totals['sale_revenue']) * 100 : 0.0;
            @endphp
            <x-core::stat-card
                icon="percent"
                color="gold"
                :value="number_format($marginPercent, 2) . '%'"
                label="গড় মুনাফা মার্জিন"
                label-en="Profit Margin Rate"
            />
        </div>

        {{-- Main Product-wise Profit & Loss Table --}}
        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">পণ্য ও ব্যাচভিত্তিক লাভ-ক্ষতির তালিকা</span>
                    <span class="en" style="display:none;">Product & Batch-wise Profit & Loss Statement</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট পণ্য: {{ $totals['products_count'] }} টি &middot; মোট ব্যাচ: {{ $totals['batches_count'] }} টি</span>
                    <span class="en" style="display:none;">Total Products: {{ $totals['products_count'] }} &middot; Batches: {{ $totals['batches_count'] }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="bn" style="width:28%;">পণ্যের নাম</th>
                            <th class="en" style="display:none; width:28%;">Product Name</th>

                            <th class="bn" style="width:20%;">ব্যাচ / লট</th>
                            <th class="en" style="display:none; width:20%;">Batch/Lot</th>

                            <th class="bn" style="text-align:right; width:17%;">ক্রয় মূল্য</th>
                            <th class="en" style="display:none; text-align:right; width:17%;">Purchase Price</th>

                            <th class="bn" style="text-align:right; width:17%;">বিক্রয় মূল্য</th>
                            <th class="en" style="display:none; text-align:right; width:17%;">Sale Price</th>

                            <th class="bn" style="text-align:right; width:18%;">লাভ / ক্ষতি</th>
                            <th class="en" style="display:none; text-align:right; width:18%;">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData as $productData)
                            @php
                                $batchCount = count($productData['batches']);
                            @endphp
                            @foreach ($productData['batches'] as $index => $batch)
                                <tr>
                                    @if ($index === 0)
                                        <td rowspan="{{ $batchCount }}" class="cell-main" style="vertical-align:middle; border-right:1px solid var(--border);">
                                            <div style="font-weight:700; font-size:13.5px; color:var(--ink-900);">
                                                {{ $productData['name'] }}
                                            </div>
                                            @if(!empty($productData['sku']))
                                                <div style="font-size:11px; color:var(--ink-400); margin-top:2px;">
                                                    SKU: {{ $productData['sku'] }}
                                                </div>
                                            @endif
                                        </td>
                                    @endif

                                    <td style="vertical-align:middle;">
                                        @if($batch['is_default'])
                                            <span style="color:var(--ink-500); font-size:12px;">
                                                <span class="bn">ডিফল্ট ব্যাচ</span>
                                                <span class="en" style="display:none;">Default Batch</span>
                                            </span>
                                        @else
                                            <span class="badge badge-soft-info" style="font-size:11.5px; font-weight:600;">
                                                {{ $batch['batch_no'] }}
                                            </span>
                                        @endif
                                    </td>

                                    <td style="text-align:right; font-weight:600; vertical-align:middle; color:var(--ink-700);">
                                        ৳{{ number_format($batch['purchase_cost'], 2) }}
                                    </td>

                                    <td style="text-align:right; font-weight:600; vertical-align:middle; color:var(--ink-900);">
                                        ৳{{ number_format($batch['sale_revenue'], 2) }}
                                    </td>

                                    <td style="text-align:right; font-weight:700; vertical-align:middle; font-size:13px; color:{{ $batch['profit'] < 0 ? 'var(--red-600)' : 'var(--green-ink, #059669)' }};">
                                        ৳{{ number_format($batch['profit'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-core::table.empty icon="package" title="নির্বাচিত সময়ে কোনো পণ্যভিত্তিক লাভ-ক্ষতির তথ্য নেই" title-en="No product profit & loss data found for the selected period" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(count($reportData) > 0)
                        <tfoot>
                            <tr style="border-top:2px solid var(--border); font-weight:800; background:var(--paper-line);">
                                <td colspan="2" style="text-align:right; font-size:13px; color:var(--ink-900); padding:10px 12px;">
                                    <span class="bn">সর্বমোট</span>
                                    <span class="en" style="display:none;">Total</span>
                                </td>
                                <td style="text-align:right; font-size:13.5px; font-weight:800; color:var(--ink-900); padding:10px 12px;">
                                    ৳{{ number_format($totals['purchase_cost'], 2) }}
                                </td>
                                <td style="text-align:right; font-size:13.5px; font-weight:800; color:var(--ink-900); padding:10px 12px;">
                                    ৳{{ number_format($totals['sale_revenue'], 2) }}
                                </td>
                                <td style="text-align:right; font-size:14px; font-weight:800; color:{{ $totals['profit'] < 0 ? 'var(--red-600)' : 'var(--green-ink, #059669)' }}; padding:10px 12px;">
                                    ৳{{ number_format($totals['profit'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @can('report-product-profit-loss.print')
            <div class="report-print-footer" style="display:none;">
                <div>
                    <span class="bn">এটি একটি কম্পিউটার প্রস্তুতকৃত রিপোর্ট &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
                    <span class="en" style="display:none;">Computer generated report &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
                </div>
                <div>
                    <span class="bn">মুদ্রণ সময়: {{ now()->format('d M Y, h:i A') }}</span>
                    <span class="en" style="display:none;">Printed: {{ now()->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        @endcan
    </div>

    <style>
    /* Strictly hidden on screen */
    .report-print-header,
    .report-print-footer,
    .report-credit-watermark {
        display: none !important;
    }

    @if ($canPrint)
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 10mm 12mm 10mm;
            }
            html, body {
                background: #ffffff !important;
                color: #0f172a !important;
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif !important;
                font-size: 11px !important;
                letter-spacing: normal !important;
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
                letter-spacing: normal !important;
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
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', sans-serif !important;
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
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif !important;
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
</x-core::layout>
