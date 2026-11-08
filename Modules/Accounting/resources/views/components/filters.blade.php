@props(['shops', 'dates' => [], 'extra' => null])

{{-- Report filter bar: optional shop, and the given date fields (name => [bn, en, value]). --}}
<form method="GET" class="section-row" style="margin:16px 0; display:flex; align-items:flex-end; gap:8px; flex-wrap:wrap;">
    {{ $extra }}
    @if ($shops->count() > 1)
        <div style="width:200px; flex-shrink:0;">
            <x-core::select
                size="sm"
                name="shop_id"
                label="দোকান"
                label-en="Shop"
                :options="['' => ['bn' => 'সব দোকান', 'en' => 'All shops']] + $shops->pluck('name', 'id')->all()"
                :value="request('shop_id')"
            />
        </div>
    @endif
    @foreach ($dates as $name => [$labelBn, $labelEn, $value])
        <div style="width:170px; flex-shrink:0;">
            <x-core::input size="sm" type="date" :name="$name" :label="$labelBn" :label-en="$labelEn" :value="$value" />
        </div>
    @endforeach
    <x-core::button type="submit" size="sm" variant="secondary" icon="filter">
        <span class="bn">দেখুন</span>
        <span class="en" style="display:none;">Apply</span>
    </x-core::button>
</form>
