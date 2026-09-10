@props(['active' => 'sales'])

@php
$user = auth()->user();
$shop = $user?->shop;

$allTabs = [
    ['key' => 'sales', 'route' => 'reports.sales', 'feature' => 'report-sales', 'permission' => 'report-sales.view', 'bn' => 'বিক্রয় রিপোর্ট', 'en' => 'Sales Report'],
    ['key' => 'purchase', 'route' => 'reports.purchase', 'feature' => 'report-purchase', 'permission' => 'report-purchase.view', 'bn' => 'ক্রয় রিপোর্ট', 'en' => 'Purchase Report'],
    ['key' => 'stock', 'route' => 'reports.stock', 'feature' => 'report-stock', 'permission' => 'report-stock.view', 'bn' => 'স্টক রিপোর্ট', 'en' => 'Stock Report'],
    ['key' => 'products', 'route' => 'reports.products', 'feature' => 'report-products', 'permission' => 'report-products.view', 'bn' => 'পণ্য রিপোর্ট', 'en' => 'Products Report'],
    ['key' => 'profit-loss', 'route' => 'reports.profit-loss', 'feature' => 'report-profit-loss', 'permission' => 'report-profit-loss.view', 'bn' => 'লাভ-ক্ষতি রিপোর্ট', 'en' => 'Profit & Loss Report'],
    ['key' => 'income', 'route' => 'reports.income', 'feature' => 'report-income', 'permission' => 'report-income.view', 'bn' => 'আয় রিপোর্ট', 'en' => 'Income Report'],
    ['key' => 'expense', 'route' => 'reports.expense', 'feature' => 'report-expense', 'permission' => 'report-expense.view', 'bn' => 'ব্যয় রিপোর্ট', 'en' => 'Expense Report'],
];

$tabs = collect($allTabs)->filter(function ($tab) use ($user, $shop) {
    if (! $shop || ! $user) {
        return false;
    }
    return $shop->hasFeature($tab['feature']) && $user->can($tab['permission']);
});
@endphp

@if($tabs->count() > 1)
<div class="tabbar no-print" style="margin-bottom:16px;">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
            <span class="bn">{{ $tab['bn'] }}</span><span class="en" style="display:none;">{{ $tab['en'] }}</span>
        </a>
    @endforeach
</div>
@endif
