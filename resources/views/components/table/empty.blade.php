@props([
    'icon' => 'box',
    'title' => 'কোনো তথ্য পাওয়া যায়নি',
    'titleEn' => 'No records found',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'table-empty']) }}>
    <div class="table-empty-icon">
        <x-core::icon :name="$icon" size="24" />
    </div>
    <div class="table-empty-title">
        <span class="bn">{{ $title }}</span>
        <span class="en" style="display: none;">{{ $titleEn }}</span>
    </div>
    @if ($description)
        <div class="table-empty-desc">{{ $description }}</div>
    @endif
    @if ($slot->isNotEmpty())
        <div style="margin-top: 10px;">
            {{ $slot }}
        </div>
    @endif
</div>
