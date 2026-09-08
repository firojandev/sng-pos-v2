<x-core::layout
    title="পণ্য রিপোর্ট"
    title-en="Product Report"
    subtitle="নির্বাচিত সময়ে পণ্যভিত্তিক বিক্রয় ও মজুদ পারফরম্যান্স"
    subtitle-en="Per-product sales and stock performance for the selected period"
    active="report-products"
>
    @include('report::partials._date-range-filter')

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card icon="tag" color="teal" :value="number_format($totals['qty_sold'], 2)" label="মোট বিক্রিত পরিমাণ" label-en="Total Qty Sold" />
        <x-core::stat-card icon="trending-up" color="green" :value="'৳' . number_format($totals['revenue'], 2)" label="মোট বিক্রয় আয়" label-en="Total Sales Revenue" />
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="bn">পণ্য</th>
                        <th class="bn">SKU</th>
                        <th class="bn">বিক্রিত পরিমাণ</th>
                        <th class="bn">বিক্রয় আয়</th>
                        <th class="bn">বর্তমান মজুদ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="cell-main">{{ $product->name }}</td>
                            <td>{{ $product->sku ?? '—' }}</td>
                            <td>{{ number_format($product->qty_sold, 2) }}</td>
                            <td>৳{{ number_format($product->revenue, 2) }}</td>
                            <td>{{ number_format($product->qty_on_hand, 2) }}</td>
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
</x-core::layout>
