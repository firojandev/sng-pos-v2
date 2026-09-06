@props(['active' => 'accounts'])

@php
$tabs = [
    ['key' => 'accounts', 'route' => 'accounts.index', 'bn' => 'অ্যাকাউন্টসমূহ', 'en' => 'Accounts'],
    ['key' => 'account-transfers', 'route' => 'account-transfers.index', 'bn' => 'ফান্ড ট্রান্সফার', 'en' => 'Fund Transfers'],
];
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
            <span class="bn">{{ $tab['bn'] }}</span><span class="en" style="display:none;">{{ $tab['en'] }}</span>
        </a>
    @endforeach
</div>
