@props(['active' => 'branches'])

@php
$tabs = [
    ['key' => 'branches', 'route' => 'branches.index', 'bn' => 'শাখা', 'en' => 'Branches'],
    ['key' => 'warehouses', 'route' => 'warehouses.index', 'bn' => 'গুদাম', 'en' => 'Warehouses'],
];
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
            <span class="bn">{{ $tab['bn'] }}</span><span class="en">{{ $tab['en'] }}</span>
        </a>
    @endforeach
</div>
