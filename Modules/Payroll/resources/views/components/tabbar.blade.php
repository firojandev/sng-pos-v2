@props(['active' => 'runs'])

@php
    $tabs = [
        ['key' => 'runs', 'route' => 'payroll.runs.index', 'bn' => 'বেতন (পে-রোল)', 'en' => 'Payroll Runs', 'can' => 'payroll.view', 'feature' => 'payroll'],
        ['key' => 'loans', 'route' => 'payroll.loans.index', 'bn' => 'অগ্রিম ও ঋণ', 'en' => 'Advances & Loans', 'can' => 'payroll.view', 'feature' => 'payroll'],
        ['key' => 'tax', 'route' => 'payroll.tax.index', 'bn' => 'আয়কর', 'en' => 'Tax Setup', 'can' => 'payroll.view', 'feature' => 'payroll'],
        ['key' => 'statutory', 'route' => 'payroll.statutory.index', 'bn' => 'পিএফ ও আয়কর জমা', 'en' => 'Statutory Payments', 'can' => 'payroll.view', 'feature' => 'payroll'],
        ['key' => 'settlements', 'route' => 'payroll.settlements.index', 'bn' => 'চূড়ান্ত নিষ্পত্তি', 'en' => 'Final Settlement', 'can' => 'payroll.view', 'feature' => 'payroll'],
        ['key' => 'setup', 'route' => 'payroll-setup.index', 'bn' => 'পে-রোল সেটআপ', 'en' => 'Payroll Setup', 'can' => 'payroll-setup.view', 'feature' => 'payroll-setup'],
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
