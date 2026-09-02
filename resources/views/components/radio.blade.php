@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'checked' => false,
    'label' => null,
    'labelEn' => null,
    'description' => null,
    'color' => 'teal',
    'size' => 'md',
    'disabled' => false,
])

@php
    $radioId = $id ?? ($name ? 'form-radio-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) . '-' . substr(md5((string)$value), 0, 5) : 'form-radio-' . uniqid());
    $isChecked = (bool) ($checked || ($name && old($name) == $value));

    // Color Normalization
    $colorAliases = [
        'primary' => 'primary',
        'secondary' => 'secondary',
        'brand' => 'teal',
        'success' => 'green',
        'danger' => 'red',
        'info' => 'blue',
        'ink' => 'dark',
        'neutral' => 'secondary',
        'grey' => 'secondary',
    ];
    $resolvedColor = $colorAliases[$color] ?? $color;

    $wrapClasses = ['form-radio-wrap'];
    if (in_array($size, ['sm', 'md', 'lg'])) $wrapClasses[] = 'form-radio-' . $size;
    if ($resolvedColor) $wrapClasses[] = 'form-' . $resolvedColor;
@endphp

<label for="{{ $radioId }}" {{ $attributes->merge(['class' => implode(' ', $wrapClasses)]) }}>
    <input
        type="radio"
        @if ($name) name="{{ $name }}" @endif
        id="{{ $radioId }}"
        value="{{ $value }}"
        @if ($isChecked) checked @endif
        @if ($disabled) disabled @endif
    />
    <span class="form-radio-circle">
        <span class="form-radio-dot"></span>
    </span>
    @if ($label || $slot->isNotEmpty())
        <span class="form-radio-label">
            @if ($label && $labelEn)
                <span class="bn">{{ $label }}</span>
                <span class="en" style="display:none;">{{ $labelEn }}</span>
            @elseif ($label)
                {{ $label }}
            @else
                {{ $slot }}
            @endif

            @if ($description)
                <span class="form-radio-desc">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
