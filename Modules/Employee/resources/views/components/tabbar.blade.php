@props(['active' => 'employees'])

@php
    $tabs = [
        ['key' => 'employees', 'route' => 'employees.index', 'bn' => 'কর্মচারী', 'en' => 'Employees', 'can' => 'employees.view', 'feature' => 'employees'],
        ['key' => 'attendance', 'route' => 'attendance.sheet', 'bn' => 'দৈনিক হাজিরা', 'en' => 'Daily Attendance', 'can' => 'attendance.view', 'feature' => 'attendance'],
        ['key' => 'monthly', 'route' => 'attendance.monthly', 'bn' => 'মাসিক হাজিরা', 'en' => 'Monthly Attendance', 'can' => 'attendance.view', 'feature' => 'attendance'],
        ['key' => 'devices', 'route' => 'attendance.devices.index', 'bn' => 'হাজিরা মেশিন', 'en' => 'Attendance Devices', 'can' => 'attendance.edit', 'feature' => 'attendance'],
        ['key' => 'leave', 'route' => 'leave-requests.index', 'bn' => 'ছুটি', 'en' => 'Leave', 'can' => 'leave.view', 'feature' => 'leave'],
        ['key' => 'setup', 'route' => 'hr-setup.index', 'bn' => 'এইচআর সেটআপ', 'en' => 'HR Setup', 'can' => 'hr-setup.view', 'feature' => 'hr-setup'],
    ];
    // A tab shows only when the shop's plan includes its module.
    $hasFeature = fn (string $feature) => (bool) (auth()->user()?->isSuperAdmin() || auth()->user()?->shop?->hasFeature($feature));
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        @continue(! $hasFeature($tab['feature']))
        @can($tab['can'])
            <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
                <span class="bn">{{ $tab['bn'] }}</span><span class="en">{{ $tab['en'] }}</span>
            </a>
        @endcan
    @endforeach
</div>
