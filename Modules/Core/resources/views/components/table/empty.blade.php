@props([
    'icon' => 'box',
    'title' => 'কোনো তথ্য পাওয়া যায়নি',
    'titleEn' => null,
    'description' => null,
    'descriptionEn' => null,
])

@php
    $resolvedTitleEn = $titleEn ?? $attributes->get('title-en') ?? 'No records found';
    $resolvedDescription = $description ?? $attributes->get('description') ?? null;
    $resolvedDescriptionEn = $descriptionEn ?? $attributes->get('description-en') ?? null;
@endphp

<div {{ $attributes->except(['title-en', 'description-en'])->merge(['class' => 'table-empty']) }}>
    <div class="table-empty-icon">
        <x-core::icon :name="$icon" size="24" />
    </div>
    <div class="table-empty-title">
        <span class="bn">{{ $title }}</span>
        <span class="en" style="display:none;">{{ $resolvedTitleEn }}</span>
    </div>
    @if ($resolvedDescription || $resolvedDescriptionEn)
        <div class="table-empty-desc">
            @if ($resolvedDescription)<span class="bn">{{ $resolvedDescription }}</span>@endif
            @if ($resolvedDescriptionEn)<span class="en" style="display:none;">{{ $resolvedDescriptionEn }}</span>@endif
        </div>
    @endif
    @if ($slot->isNotEmpty())
        <div style="margin-top:12px;">
            {{ $slot }}
        </div>
    @endif
</div>
