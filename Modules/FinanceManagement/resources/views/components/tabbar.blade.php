@props(['active' => 'assets'])

@php
$tabs = [
    ['key' => 'assets', 'route' => 'assets.index', 'bn' => 'সম্পদ', 'en' => 'Assets'],
    ['key' => 'debts', 'route' => 'debts.index', 'bn' => 'দেনা', 'en' => 'Debts'],
    ['key' => 'lend', 'route' => 'lend.index', 'bn' => 'ধার', 'en' => 'Lend'],
    ['key' => 'security-money', 'route' => 'security-money.index', 'bn' => 'জামানত', 'en' => 'Security Money'],
];
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        @can($tab['key'] . '.view')
            <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
                <span class="bn">{{ $tab['bn'] }}</span><span class="en">{{ $tab['en'] }}</span>
            </a>
        @endcan
    @endforeach
</div>
