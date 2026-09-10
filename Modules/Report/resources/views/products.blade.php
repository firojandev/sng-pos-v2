<x-core::layout
    title="পণ্য রিপোর্ট"
    title-en="Product Report"
    subtitle="নির্বাচিত সময়ে পণ্যভিত্তিক বিক্রয় ও মজুদ পারফরম্যান্স"
    subtitle-en="Per-product sales and stock performance for the selected period"
    active="report-products"
>
    <x-report::tabbar active="products" />

    <div class="report-printable-area">
        @include('report::partials._date-range-filter', [
            'reportTitle' => 'পণ্য রিপোর্ট',
            'reportTitleEn' => 'Product Report',
        ])

        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
            <x-core::stat-card icon="tag" color="teal" :value="number_format($totals['qty_sold'], 2)" label="মোট বিক্রিত পরিমাণ" label-en="Total Qty Sold" />
            <x-core::stat-card icon="trending-up" color="green" :value="'৳' . number_format($totals['revenue'], 2)" label="মোট বিক্রয় আয়" label-en="Total Sales Revenue" />
        </div>

        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">পণ্য পারফরম্যান্স তালিকা</span>
                    <span class="en" style="display:none;">Product Performance List</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">মোট {{ $products->count() }} টি পণ্য</span>
                    <span class="en" style="display:none;">Total {{ $products->count() }} products</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                            <th class="bn">SKU</th><th class="en" style="display:none;">SKU</th>
                            <th class="bn" style="text-align:right;">বিক্রিত পরিমাণ</th><th class="en" style="display:none; text-align:right;">Sold Qty</th>
                            <th class="bn" style="text-align:right;">বিক্রয় আয়</th><th class="en" style="display:none; text-align:right;">Sales Revenue</th>
                            <th class="bn" style="text-align:right;">বর্তমান মজুদ</th><th class="en" style="display:none; text-align:right;">Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="cell-main">{{ $product->name }}</td>
                                <td>{{ $product->sku ?? '—' }}</td>
                                <td style="text-align:right;">{{ number_format($product->qty_sold, 2) }}</td>
                                <td style="text-align:right;">৳{{ number_format($product->revenue, 2) }}</td>
                                <td style="text-align:right;">{{ number_format($product->qty_on_hand, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-core::table.empty icon="tag" title="কোনো পণ্য পাওয়া যায়নি" title-en="No products found" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

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
    </div>
</x-core::layout>
