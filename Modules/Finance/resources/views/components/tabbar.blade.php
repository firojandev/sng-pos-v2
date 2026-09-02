@props(['active' => 'expense'])

@php
$tabs = [
    ['key' => 'expense', 'route' => 'expense.index', 'bn' => 'ব্যয় তালিকা', 'en' => 'Expense List'],
    ['key' => 'expense-categories', 'route' => 'expense-categories.index', 'bn' => 'ব্যয় ক্যাটাগরি', 'en' => 'Expense Category'],
    ['key' => 'expense-sub-categories', 'route' => 'expense-sub-categories.index', 'bn' => 'ব্যয় সাব-ক্যাটাগরি', 'en' => 'Expense Sub-category'],
];
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
            <span class="bn">{{ $tab['bn'] }}</span><span class="en">{{ $tab['en'] }}</span>
        </a>
    @endforeach
</div>
