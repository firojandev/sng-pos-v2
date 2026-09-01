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
    {{ $attributes->merge(['class' => implode(' ', $classes)]) }}
>
    @if ($icon)
        <span class="form-label-icon">
            <x-core::icon :name="$icon" size="14" />
        </span>
    @endif

    @if ($label && $labelEn)
        <span class="bn">{{ $label }}</span>
        <span class="en" style="display:none;">{{ $labelEn }}</span>
    @elseif ($label)
        {{ $label }}
    @else
        {{ $slot }}
    @endif

    @if ($required)
        <span class="form-required" aria-hidden="true" title="Required">*</span>
    @endif

    @if ($optional)
        <span class="form-optional"><span class="bn">ঐচ্ছিক</span><span class="en">Optional</span></span>
    @endif
</label>
