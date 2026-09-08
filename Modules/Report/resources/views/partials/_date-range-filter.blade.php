@php
    $rangeLabels = [
        'week' => ['bn' => 'এই সপ্তাহ', 'en' => 'This Week'],
        'month' => ['bn' => 'এই মাস', 'en' => 'This Month'],
        'year' => ['bn' => 'এই বছর', 'en' => 'This Year'],
        'custom' => ['bn' => 'কাস্টম রেঞ্জ', 'en' => 'Custom Range'],
    ];
    $routeName = request()->route()->getName();
@endphp

<div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
    <div class="range-tabs">
        @foreach ($rangeLabels as $key => $labels)
            <a href="{{ route($routeName, ['range' => $key]) }}" class="{{ $range === $key ? 'active' : '' }}">
                <span class="bn">{{ $labels['bn'] }}</span>
                <span class="en" style="display:none;">{{ $labels['en'] }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route($routeName) }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <input type="hidden" name="range" value="custom">
        <div style="width:150px;">
            <x-core::input type="date" name="from" size="sm" :no-margin="true" :value="$from" />
        </div>
        <div style="width:150px;">
            <x-core::input type="date" name="to" size="sm" :no-margin="true" :value="$to" />
        </div>
        <x-core::button type="submit" variant="outline" color="secondary" size="sm" icon="filter">
            <span class="bn">ফিল্টার</span>
            <span class="en" style="display:none;">Filter</span>
        </x-core::button>
    </form>
</div>
