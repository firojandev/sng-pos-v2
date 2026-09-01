@props([
    'variant' => 'default',
    'icon' => null,
])

@php
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

<div {{ $attributes->merge(['class' => trim('form-helper ' . $variantClass)]) }}>
    @if ($resolvedIcon)
        <x-icon :name="$resolvedIcon" size="13" />
    @endif
    <span>{{ $slot }}</span>
</div>
