@props([
    'for' => null,
    'required' => false,
    'optional' => false,
    'icon' => null,
    'size' => 'md',
    'color' => null,
    'label' => null,
    'labelEn' => null,
])

@php
    $resolvedLabelEn = $labelEn ?? $attributes->get('label-en') ?? null;
    $classes = ['form-label'];
    if (in_array($size, ['sm', 'md', 'lg'])) {
        $classes[] = 'form-label-' . $size;
    }
    if ($color) {
        $classes[] = 'form-' . $color;
    }
@endphp

<label
    @if ($for) for="{{ $for }}" @endif
    {{ $attributes->except(['label-en'])->merge(['class' => implode(' ', $classes)]) }}
>
    @if ($icon)
        <span class="form-label-icon">
            <x-core::icon :name="$icon" size="14" />
        </span>
    @endif

    @if ($label && $resolvedLabelEn)
        <span class="bn">{{ $label }}</span>
        <span class="en" style="display:none;">{{ $resolvedLabelEn }}</span>
    @elseif ($label)
        {{ $label }}
    @elseif ($resolvedLabelEn)
        <span class="en">{{ $resolvedLabelEn }}</span>
    @else
        {{ $slot }}
    @endif

    @if ($required)
        <span class="form-required" aria-hidden="true" title="Required">*</span>
    @endif

    @if ($optional)
        <span class="form-optional"><span class="bn">ঐচ্ছিক</span><span class="en" style="display:none;">Optional</span></span>
    @endif
</label>
