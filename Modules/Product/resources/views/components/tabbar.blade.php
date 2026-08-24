@props(['active' => 'products'])

@php
$tabs = [
    ['key' => 'products', 'route' => 'products.index', 'bn' => 'পণ্য তালিকা', 'en' => 'Product List'],
    ['key' => 'categories', 'route' => 'categories.index', 'bn' => 'ক্যাটাগরি', 'en' => 'Category'],
    ['key' => 'sub-categories', 'route' => 'sub-categories.index', 'bn' => 'সাব-ক্যাটাগরি', 'en' => 'Sub-category'],
    ['key' => 'brands', 'route' => 'brands.index', 'bn' => 'ব্র্যান্ড', 'en' => 'Brand'],
    ['key' => 'models', 'route' => 'models.index', 'bn' => 'মডেল', 'en' => 'Model'],
    ['key' => 'units', 'route' => 'units.index', 'bn' => 'ইউনিট', 'en' => 'Unit'],
    ['key' => 'batches', 'route' => 'batches.index', 'bn' => 'ব্যাচ', 'en' => 'Batch'],
];
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
            <span class="bn">{{ $tab['bn'] }}</span><span class="en">{{ $tab['en'] }}</span>
        </a>
    @endforeach
</div>
