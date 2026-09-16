<x-core::layout
    title="বিক্রয় ভ্যাট রিপোর্ট"
    title-en="Sales VAT Report"
    subtitle="নির্বাচিত সময়ের বিক্রয় ভ্যাট (আউটপুট ভ্যাট) ও মূসক সারসংক্ষেপ"
    subtitle-en="Sales VAT (Output VAT) calculation and summary for the selected period"
    active="report-sales-vat"
>
    <x-report::tabbar active="sales-vat" />

    <div class="report-printable-area">
        @include('report::partials._date-range-filter', [
            'reportTitle' => 'বিক্রয় ভ্যাট রিপোর্ট (Sales VAT Report)',
            'reportTitleEn' => 'Sales VAT Report',
            'printPermission' => 'report-sales-vat.print',
        ])

        {{-- Optional Branch / Warehouse Filter --}}
        @if ($warehouses->count() > 1)
            <div class="no-print" style="margin-bottom:16px; background:var(--card); border:1px solid var(--border); border-radius:8px; padding:10px 14px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:13px; font-weight:600; color:var(--ink-700);">
                    <span class="bn">শাখা / গুদাম ফিল্টার:</span>
                    <span class="en" style="display:none;">Branch / Warehouse Filter:</span>
                </span>
                <form method="GET" action="{{ route('reports.sales-vat') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <input type="hidden" name="range" value="{{ $range }}">
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="to" value="{{ $to }}">
                    <div style="width:200px; flex-shrink:0;">
                        <x-core::select
                            name="warehouse_id"
                            size="sm"
                            :no-margin="true"
                            :options="$warehouses->pluck('name', 'id')->prepend('সকল শাখা ও গুদাম (All)', '')"
                            :value="$warehouseId"
                            onchange="$(this).closest('form').submit()"
                        />
                    </div>
                    @if ($warehouseId)
                        <a href="{{ route('reports.sales-vat', ['range' => $range, 'from' => $from, 'to' => $to]) }}">
                            <x-core::button type="button" variant="secondary" size="sm">
                                <span class="bn">ফিল্টার মুছুন</span>
                                <span class="en" style="display:none;">Clear</span>
                            </x-core::button>
                        </a>
                    @endif
                </form>
            </div>
        @endif

        {{-- Reconciliation Alert / Verification Badge --}}
        @if (! $reconciliation['is_valid'])
            <div class="panel" style="margin-bottom:16px; border-left:4px solid var(--red-600); background:var(--red-50); padding:12px 16px;">
                <div style="display:flex; align-items:center; gap:8px; color:var(--red-700); font-weight:700; font-size:13px;">
                    <x-core::icon name="alert-triangle" />
                    <span class="bn">ডাটা সমন্বয় সতর্কতা: বিক্রয় ও ফেরত ভ্যাটের হিসাবে গরমিল শনাক্ত হয়েছে।</span>
                    <span class="en" style="display:none;">Data Reconciliation Warning: A mismatch was detected between sales and return VAT calculations.</span>
                </div>
            </div>
        @endif

        {{-- Top Summary Stat Cards --}}
        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); margin-bottom:16px;">
            <x-core::stat-card icon="shopping-cart" color="teal" :value="'৳' . number_format($summary['gross_sales'], 2)" label="মোট বিক্রয় (Gross Sales)" label-en="Gross Sales" />
            <x-core::stat-card icon="rotate-ccw" color="gold" :value="'৳' . number_format($summary['sales_return'], 2)" label="বিক্রয় ফেরত (Sales Return)" label-en="Sales Return" />
            <x-core::stat-card icon="dollar-sign" color="blue" :value="'৳' . number_format($summary['net_sales'], 2)" label="নিট বিক্রয় (Net Sales)" label-en="Net Sales" />
            <x-core::stat-card icon="receipt" color="purple" :value="'৳' . number_format($summary['taxable_sales_value'], 2)" label="করযোগ্য বিক্রয় মূল্য (Taxable Sales)" label-en="Taxable Sales Value" />
            <x-core::stat-card icon="percent" color="green" :value="'৳' . number_format($summary['output_vat'], 2)" label="নিট আউটপুট ভ্যাট (Net Output VAT)" label-en="Output VAT" />
        </div>

        <style>
        .summary-two-col-grid {
            display: grid;
            grid-template-columns: minmax(0, 42%) minmax(0, calc(58% - 16px));
            gap: 16px;
            margin-bottom: 16px;
            align-items: start;
            width: 100%;
            max-width: 100%;
        }
        .summary-two-col-grid > * {
            min-width: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            overflow: hidden !important;
        }
        .summary-two-col-grid .app-table {
            table-layout: fixed !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .summary-two-col-grid .app-table th,
        .summary-two-col-grid .app-table td {
            padding: 8px 12px;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .summary-two-col-grid .app-table thead th {
            white-space: normal;
        }
        .summary-two-col-grid .app-table thead th .th-wrap {
            width: 100% !important;
            display: flex !important;
        }
        .summary-two-col-grid .app-table thead th .th-wrap.justify-end {
            justify-content: flex-end !important;
        }
        .summary-two-col-grid .cell-amount {
            white-space: nowrap !important;
            text-align: right !important;
        }
        .details-table-card {
            margin-bottom: 16px;
            width: 100%;
            max-width: 100%;
        }
        .details-table-card .app-table {
            min-width: 640px;
        }
        .details-table-card .app-table th,
        .details-table-card .app-table td {
            padding: 8px 12px;
        }
        @media (max-width: 1024px) {
            .summary-two-col-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .stat-grid {
                grid-template-columns: repeat(auto-fit, minmax(135px, 1fr)) !important;
                gap: 8px !important;
            }
            .table-toolbar {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 6px !important;
                padding: 10px 12px !important;
            }
            .table-title {
                font-size: 13.5px !important;
            }
            .app-table th, .app-table td {
                padding: 6px 8px !important;
                font-size: 11.5px !important;
            }
            .details-table-card .app-table {
                min-width: 580px;
            }
        }
        @media print {
            .summary-two-col-grid {
                display: grid !important;
                grid-template-columns: 42% calc(58% - 12px) !important;
                gap: 12px !important;
                margin-bottom: 14px !important;
                page-break-inside: avoid !important;
            }
            .details-table-card .app-table {
                min-width: 100% !important;
            }
        }
        </style>

        <div class="summary-two-col-grid">
            {{-- 1. SALES / OUTPUT VAT SUMMARY TABLE (42% width) --}}
            <x-core::table
                color="teal"
                :responsive="false"
                title="১. বিক্রয় ভ্যাট সারসংক্ষেপ"
                title-en="1. SALES VAT SUMMARY"
                class="summary-table-container"
            >
                <x-slot:actions>
                    @if ($reconciliation['is_valid'])
                        <x-core::badge color="green" size="xs" variant="soft">
                            <span class="bn">হিসাব সমন্বিত (Reconciled)</span>
                            <span class="en" style="display:none;">Reconciled</span>
                        </x-core::badge>
                    @else
                        <x-core::badge color="red" size="xs" variant="soft">
                            <span class="bn">অসমন্বিত (Mismatch)</span>
                            <span class="en" style="display:none;">Mismatch</span>
                        </x-core::badge>
                    @endif
                </x-slot:actions>

                <x-slot:header>
                    <x-core::table.th width="56%">
                        <span class="bn">বিবরণ</span>
                        <span class="en" style="display:none;">Particulars</span>
                    </x-core::table.th>
                    <x-core::table.th width="44%" align="right">
                        <span class="bn">পরিমাণ (BDT)</span>
                        <span class="en" style="display:none;">Amount (BDT)</span>
                    </x-core::table.th>
                </x-slot:header>

                <x-core::table.tr>
                    <x-core::table.td bold>
                        <span class="bn">মোট বিক্রয়</span>
                        <span class="en" style="display:none;">Gross Sales</span>
                    </x-core::table.td>
                    <x-core::table.td align="right" bold class="cell-amount" style="color:var(--ink-900);">
                        ৳{{ number_format($summary['gross_sales'], 2) }}
                    </x-core::table.td>
                </x-core::table.tr>

                <x-core::table.tr>
                    <x-core::table.td bold>
                        <span class="bn">বাদ: বিক্রয় ফেরত</span>
                        <span class="en" style="display:none;">Less: Return</span>
                    </x-core::table.td>
                    <x-core::table.td align="right" bold class="cell-amount" style="color:var(--red-600);">
                        - ৳{{ number_format($summary['sales_return'], 2) }}
                    </x-core::table.td>
                </x-core::table.tr>

                <x-core::table.tr style="background:var(--paper-line);">
                    <x-core::table.td bold style="font-weight:700; color:var(--ink-900);">
                        <span class="bn">নিট বিক্রয়</span>
                        <span class="en" style="display:none;">Net Sales</span>
                    </x-core::table.td>
                    <x-core::table.td align="right" bold class="cell-amount" style="font-weight:800; color:var(--ink-900);">
                        ৳{{ number_format($summary['net_sales'], 2) }}
                    </x-core::table.td>
                </x-core::table.tr>

                <x-core::table.tr>
                    <x-core::table.td bold>
                        <span class="bn">করযোগ্য বিক্রয় মূল্য</span>
                        <span class="en" style="display:none;">Taxable Sales Value</span>
                    </x-core::table.td>
                    <x-core::table.td align="right" bold class="cell-amount" style="color:var(--ink-900);">
                        ৳{{ number_format($summary['taxable_sales_value'], 2) }}
                    </x-core::table.td>
                </x-core::table.tr>

                <x-core::table.tr style="background:var(--paper); border-top:2px solid var(--border);">
                    <x-core::table.td bold style="font-weight:800; font-size:13px; color:var(--green-ink);">
                        <span class="bn">নিট আউটপুট ভ্যাট</span>
                        <span class="en" style="display:none;">Output VAT</span>
                    </x-core::table.td>
                    <x-core::table.td align="right" bold class="cell-amount" style="font-weight:800; font-size:14px; color:var(--green-ink);">
                        ৳{{ number_format($summary['output_vat'], 2) }}
                    </x-core::table.td>
                </x-core::table.tr>
            </x-core::table>

            {{-- 2. VAT RATE-WISE SUMMARY --}}
            <x-core::table
                color="teal"
                :responsive="false"
                title="২. ভ্যাট হারভিত্তিক সারসংক্ষেপ"
                title-en="2. VAT RATE-WISE SUMMARY"
                class="summary-table-container"
            >
                <x-slot:actions>
                    <div style="font-size:12px; color:var(--ink-500);">
                        <span class="bn">{{ count($rateWiseSummary) }} টি ভ্যাট হার প্রয়োগকৃত</span>
                        <span class="en" style="display:none;">{{ count($rateWiseSummary) }} VAT rates applied</span>
                    </div>
                </x-slot:actions>

                <x-slot:header>
                    <x-core::table.th width="28%">
                        <span class="bn">ভ্যাট হার</span>
                        <span class="en" style="display:none;">VAT Rate</span>
                    </x-core::table.th>
                    <x-core::table.th width="38%" align="right">
                        <span class="bn">করযোগ্য বিক্রয়</span>
                        <span class="en" style="display:none;">Taxable Sales</span>
                    </x-core::table.th>
                    <x-core::table.th width="34%" align="right">
                        <span class="bn">আউটপুট ভ্যাট</span>
                        <span class="en" style="display:none;">Output VAT</span>
                    </x-core::table.th>
                </x-slot:header>

                @forelse ($rateWiseSummary as $row)
                    <x-core::table.tr>
                        <x-core::table.td bold>
                            <x-core::badge :color="$row['rate'] === '0% / Exempt' ? 'grey' : 'teal'" size="xs">
                                {{ $row['rate'] }}
                            </x-core::badge>
                        </x-core::table.td>
                        <x-core::table.td align="right" class="cell-amount">
                            ৳{{ number_format($row['taxable_sales'], 2) }}
                        </x-core::table.td>
                        <x-core::table.td align="right" bold class="cell-amount" style="color:var(--green-ink);">
                            ৳{{ number_format($row['output_vat'], 2) }}
                        </x-core::table.td>
                    </x-core::table.tr>
                @empty
                    <x-core::table.tr>
                        <x-core::table.td colspan="3">
                            <x-core::table.empty icon="percent" title="কোনো ভ্যাট হিসাব পাওয়া যায়নি" title-en="No VAT records found" />
                        </x-core::table.td>
                    </x-core::table.tr>
                @endforelse

                @if (!empty($rateWiseSummary))
                    <x-slot:footer>
                        <x-core::table.tr style="background:var(--paper); font-weight:800;">
                            <x-core::table.td bold style="color:var(--ink-900);">
                                <span class="bn">সর্বমোট</span>
                                <span class="en" style="display:none;">Total</span>
                            </x-core::table.td>
                            <x-core::table.td align="right" bold class="cell-amount" style="color:var(--ink-900);">
                                ৳{{ number_format($rateWiseTotalTaxable, 2) }}
                            </x-core::table.td>
                            <x-core::table.td align="right" bold class="cell-amount" style="color:var(--green-ink); font-size:13px;">
                                ৳{{ number_format($rateWiseTotalVat, 2) }}
                            </x-core::table.td>
                        </x-core::table.tr>
                    </x-slot:footer>
                @endif
            </x-core::table>
        </div>

        {{-- 3. SALES / OUTPUT VAT DETAILS --}}
        <x-core::table
            color="teal"
            title="৩. বিক্রয় / আউটপুট ভ্যাট বিস্তারিত"
            title-en="3. SALES / OUTPUT VAT DETAILS"
            class="details-table-card"
        >
            <x-slot:actions>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট {{ count($salesDetails) }} টি ইনভয়েস এন্ট্রি</span>
                    <span class="en" style="display:none;">Total {{ count($salesDetails) }} invoice entries</span>
                </div>
            </x-slot:actions>

            <x-slot:header>
                <x-core::table.th width="14%">
                    <span class="bn">তারিখ</span>
                    <span class="en" style="display:none;">Date</span>
                </x-core::table.th>
                <x-core::table.th width="16%">
                    <span class="bn">ইনভয়েস নং</span>
                    <span class="en" style="display:none;">Invoice No.</span>
                </x-core::table.th>
                <x-core::table.th width="22%">
                    <span class="bn">গ্রাহক</span>
                    <span class="en" style="display:none;">Customer</span>
                </x-core::table.th>
                <x-core::table.th width="18%" align="right">
                    <span class="bn">করযোগ্য মূল্য</span>
                    <span class="en" style="display:none;">Taxable Value</span>
                </x-core::table.th>
                <x-core::table.th width="12%" align="center">
                    <span class="bn">ভ্যাট হার</span>
                    <span class="en" style="display:none;">VAT Rate</span>
                </x-core::table.th>
                <x-core::table.th width="18%" align="right">
                    <span class="bn">ভ্যাট পরিমাণ</span>
                    <span class="en" style="display:none;">VAT Amount</span>
                </x-core::table.th>
            </x-slot:header>

            @forelse ($salesDetails as $item)
                <x-core::table.tr>
                    <x-core::table.td nowrap>{{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('d M, Y') : '—' }}</x-core::table.td>
                    <x-core::table.td bold>{{ $item['invoice_no'] }}</x-core::table.td>
                    <x-core::table.td>
                        <div>{{ $item['customer_name'] }}</div>
                        @if(!empty($item['customer_phone']))
                            <div style="font-size:11px; color:var(--ink-500);">{{ $item['customer_phone'] }}</div>
                        @endif
                    </x-core::table.td>
                    <x-core::table.td align="right">৳{{ number_format($item['taxable_value'], 2) }}</x-core::table.td>
                    <x-core::table.td align="center">
                        <x-core::badge color="teal" size="xs">{{ $item['vat_rate'] }}</x-core::badge>
                    </x-core::table.td>
                    <x-core::table.td align="right" bold style="color:var(--green-ink);">৳{{ number_format($item['vat_amount'], 2) }}</x-core::table.td>
                </x-core::table.tr>
            @empty
                <x-core::table.tr>
                    <x-core::table.td colspan="6">
                        <x-core::table.empty icon="shopping-cart" title="কোনো বিক্রয় ভ্যাট রেকর্ড পাওয়া যায়নি" title-en="No sales VAT records found" />
                    </x-core::table.td>
                </x-core::table.tr>
            @endforelse

            @if (!empty($salesDetails))
                <x-slot:footer>
                    <x-core::table.tr style="background:var(--paper); font-weight:800;">
                        <x-core::table.td colspan="3" bold style="color:var(--ink-900);">
                            <span class="bn">মোট বিক্রয় ভ্যাট</span>
                            <span class="en" style="display:none;">Total Sales VAT</span>
                        </x-core::table.td>
                        <x-core::table.td align="right" bold style="color:var(--ink-900);">
                            ৳{{ number_format($summary['gross_taxable_sales'], 2) }}
                        </x-core::table.td>
                        <x-core::table.td></x-core::table.td>
                        <x-core::table.td align="right" bold style="color:var(--green-ink); font-size:13px;">
                            ৳{{ number_format($summary['gross_output_vat'], 2) }}
                        </x-core::table.td>
                    </x-core::table.tr>
                </x-slot:footer>
            @endif
        </x-core::table>

        {{-- 4. SALES RETURN VAT --}}
        <x-core::table
            color="teal"
            title="৪. বিক্রয় ফেরত ভ্যাট"
            title-en="4. SALES RETURN VAT"
            class="details-table-card"
        >
            <x-slot:actions>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট {{ count($returnDetails) }} টি ফেরত এন্ট্রি</span>
                    <span class="en" style="display:none;">Total {{ count($returnDetails) }} return entries</span>
                </div>
            </x-slot:actions>

            <x-slot:header>
                <x-core::table.th width="14%">
                    <span class="bn">তারিখ</span>
                    <span class="en" style="display:none;">Date</span>
                </x-core::table.th>
                <x-core::table.th width="16%">
                    <span class="bn">ফেরত / ইনভয়েস নং</span>
                    <span class="en" style="display:none;">Return / Invoice No.</span>
                </x-core::table.th>
                <x-core::table.th width="22%">
                    <span class="bn">গ্রাহক</span>
                    <span class="en" style="display:none;">Customer</span>
                </x-core::table.th>
                <x-core::table.th width="18%" align="right">
                    <span class="bn">করযোগ্য মূল্য</span>
                    <span class="en" style="display:none;">Taxable Value</span>
                </x-core::table.th>
                <x-core::table.th width="12%" align="center">
                    <span class="bn">ভ্যাট হার</span>
                    <span class="en" style="display:none;">VAT Rate</span>
                </x-core::table.th>
                <x-core::table.th width="18%" align="right">
                    <span class="bn">ভ্যাট পরিমাণ</span>
                    <span class="en" style="display:none;">VAT Amount</span>
                </x-core::table.th>
            </x-slot:header>

            @forelse ($returnDetails as $item)
                <x-core::table.tr>
                    <x-core::table.td nowrap>{{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('d M, Y') : '—' }}</x-core::table.td>
                    <x-core::table.td bold>
                        <div>{{ $item['return_no'] }}</div>
                        @if(!empty($item['invoice_no']))
                            <div style="font-size:11px; color:var(--ink-500);">
                                <span class="bn">ইনভয়েস: </span>{{ $item['invoice_no'] }}
                            </div>
                        @endif
                    </x-core::table.td>
                    <x-core::table.td>
                        <div>{{ $item['customer_name'] }}</div>
                        @if(!empty($item['customer_phone']))
                            <div style="font-size:11px; color:var(--ink-500);">{{ $item['customer_phone'] }}</div>
                        @endif
                    </x-core::table.td>
                    <x-core::table.td align="right" style="color:var(--red-600);">৳{{ number_format($item['taxable_value'], 2) }}</x-core::table.td>
                    <x-core::table.td align="center">
                        <x-core::badge color="gold" size="xs">{{ $item['vat_rate'] }}</x-core::badge>
                    </x-core::table.td>
                    <x-core::table.td align="right" bold style="color:var(--red-600);">- ৳{{ number_format($item['vat_amount'], 2) }}</x-core::table.td>
                </x-core::table.tr>
            @empty
                <x-core::table.tr>
                    <x-core::table.td colspan="6">
                        <x-core::table.empty icon="rotate-ccw" title="কোনো বিক্রয় ফেরত ভ্যাট রেকর্ড পাওয়া যায়নি" title-en="No sales return VAT records found" />
                    </x-core::table.td>
                </x-core::table.tr>
            @endforelse

            @if (!empty($returnDetails))
                <x-slot:footer>
                    <x-core::table.tr style="background:var(--paper); font-weight:800;">
                        <x-core::table.td colspan="3" bold style="color:var(--ink-900);">
                            <span class="bn">মোট বিক্রয় ফেরত ভ্যাট</span>
                            <span class="en" style="display:none;">Total Return VAT</span>
                        </x-core::table.td>
                        <x-core::table.td align="right" bold style="color:var(--red-600);">
                            ৳{{ number_format($summary['return_taxable_sales'], 2) }}
                        </x-core::table.td>
                        <x-core::table.td></x-core::table.td>
                        <x-core::table.td align="right" bold style="color:var(--red-600); font-size:13px;">
                            - ৳{{ number_format($summary['return_output_vat'], 2) }}
                        </x-core::table.td>
                    </x-core::table.tr>
                </x-slot:footer>
            @endif
        </x-core::table>

        @can('report-sales-vat.print')
        <div class="report-print-footer" style="display:none;">
            <div>
                <span class="bn">এটি একটি কম্পিউটার প্রস্তুতকৃত বিক্রয় ভ্যাট প্রতিবেদন &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
                <span class="en" style="display:none;">Computer generated Sales VAT report &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
            </div>
            <div>
                <span class="bn">মুদ্রণ সময়: {{ now()->format('d M Y, h:i A') }}</span>
                <span class="en" style="display:none;">Printed: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        @endcan
    </div>
</x-core::layout>
