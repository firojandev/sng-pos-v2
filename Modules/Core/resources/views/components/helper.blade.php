@props([
    'variant' => 'default',
    'icon' => null,
    'helper' => null,
    'helperEn' => null,
])

@php
    $resolvedHelperEn = $helperEn ?? $attributes->get('helper-en') ?? null;
    $variantClass = match($variant) {
        'info' => 'form-helper-info',
        'success' => 'form-helper-success',
        'warning' => 'form-helper-warning',
        'danger', 'error' => 'form-helper-danger',
        default => '',
    };
    $defaultIcon = match($variant) {
        'info' => 'info',
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'danger', 'error' => 'alert-triangle',
        default => $icon,
    };
    $resolvedIcon = $icon ?? $defaultIcon;
@endphp

<div {{ $attributes->except(['helper-en'])->merge(['class' => trim('form-helper ' . $variantClass)]) }}>
    @if ($resolvedIcon)
        <x-core::icon :name="$resolvedIcon" size="13" />
    @endif
    <span>
        @if ($helper && $resolvedHelperEn)
            <span class="bn">{{ $helper }}</span>
            <span class="en" style="display:none;">{{ $resolvedHelperEn }}</span>
        @elseif ($helper)
            {{ $helper }}
        @elseif ($resolvedHelperEn)
            <span class="en">{{ $resolvedHelperEn }}</span>
        @else
            {{ $slot }}
        @endif
    </span>
</div>
