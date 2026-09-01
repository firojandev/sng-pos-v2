@props([
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'label' => null,
    'labelEn' => null,
    'description' => null,
    'indeterminate' => false,
    'color' => 'teal',
    'size' => 'md',
    'disabled' => false,
    'error' => null,
])

@php
    $checkId = $id ?? ($name ? 'form-check-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) . '-' . substr(md5($value), 0, 5) : 'form-check-' . uniqid());
    $isChecked = (bool) ($checked || ($name && old($name) == $value));

    // Color Normalization
    $colorAliases = [
        'primary' => 'gold',
        'brand' => 'teal',
        'success' => 'green',
        'danger' => 'red',
        'info' => 'blue',
        'ink' => 'dark',
    ];
    $resolvedColor = $colorAliases[$color] ?? $color;

    $wrapClasses = ['form-check'];
    if (in_array($size, ['sm', 'md', 'lg'])) $wrapClasses[] = 'form-check-' . $size;
    if ($resolvedColor) $wrapClasses[] = 'form-' . $resolvedColor;
@endphp

<label for="{{ $checkId }}" {{ $attributes->merge(['class' => implode(' ', $wrapClasses)]) }}>
    <input
        type="checkbox"
        @if ($name) name="{{ $name }}" @endif
        id="{{ $checkId }}"
        value="{{ $value }}"
        @if ($isChecked) checked @endif
        @if ($indeterminate) data-indeterminate="true" @endif
        @if ($disabled) disabled @endif
    />
    <span class="form-check-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="check-icon">
            <polyline points="20 6 9 17 4 12" />
        </svg>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="indeterminate-icon" style="display:none;">
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
    </span>
    @if ($label || $slot->isNotEmpty())
        <span class="form-check-label">
            @if ($label && $labelEn)
                <span class="bn">{{ $label }}</span>
                <span class="en" style="display:none;">{{ $labelEn }}</span>
            @elseif ($label)
                {{ $label }}
            @else
                {{ $slot }}
            @endif

            @if ($description)
                <span class="form-check-desc">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
