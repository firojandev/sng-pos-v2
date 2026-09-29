@props(['active' => 'accounts'])

@php
    $tabs = [
        ['key' => 'accounts', 'route' => 'ledger-accounts.index', 'bn' => 'হিসাবের তালিকা', 'en' => 'Chart of Accounts', 'can' => 'accounting.view'],
        ['key' => 'journals', 'route' => 'journal-entries.index', 'bn' => 'জার্নাল', 'en' => 'Journal Entries', 'can' => 'accounting.view'],
        ['key' => 'trial-balance', 'route' => 'accounting-reports.trial-balance', 'bn' => 'রেওয়ামিল', 'en' => 'Trial Balance', 'can' => 'accounting.view'],
        ['key' => 'general-ledger', 'route' => 'accounting-reports.general-ledger', 'bn' => 'খতিয়ান', 'en' => 'General Ledger', 'can' => 'accounting.view'],
        ['key' => 'profit-loss', 'route' => 'accounting-reports.profit-loss', 'bn' => 'লাভ-ক্ষতি', 'en' => 'Profit & Loss', 'can' => 'accounting.view'],
        ['key' => 'balance-sheet', 'route' => 'accounting-reports.balance-sheet', 'bn' => 'উদ্বৃত্তপত্র', 'en' => 'Balance Sheet', 'can' => 'accounting.view'],
        ['key' => 'setup', 'route' => 'accounting-setup.index', 'bn' => 'সেটআপ', 'en' => 'Setup', 'can' => 'accounting.edit'],
    ];
@endphp

<div class="tabbar">
    @foreach ($tabs as $tab)
        @can($tab['can'])
            <a href="{{ route($tab['route']) }}" class="tabbtn {{ $active === $tab['key'] ? 'active' : '' }}">
                <span class="bn">{{ $tab['bn'] }}</span><span class="en">{{ $tab['en'] }}</span>
            </a>
        @endcan
    @endforeach
</div>
