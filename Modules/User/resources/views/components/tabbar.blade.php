@props(['active' => 'users'])

@php
$tabs = [
    ['key' => 'users', 'route' => 'users.index', 'bn' => 'ইউজার', 'en' => 'Users'],
    ['key' => 'roles', 'route' => 'roles.index', 'bn' => 'রোল ও পারমিশন', 'en' => 'Roles & Permissions'],
];
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
            <span class="bn">{{ $tab['bn'] }}</span><span class="en">{{ $tab['en'] }}</span>
        </a>
    @endforeach
</div>
