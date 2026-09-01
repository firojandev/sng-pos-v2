@props([
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'label' => null,
    'labelEn' => null,
    'description' => null,
    'iconOn' => null,
    'iconOff' => null,
    'color' => 'teal',
    'size' => 'md',
    'disabled' => false,
    'error' => null,
])

@php
    $toggleId = $id ?? ($name ? 'form-toggle-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) . '-' . substr(md5($value), 0, 5) : 'form-toggle-' . uniqid());
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

    $wrapClasses = ['form-toggle-wrap'];
    if (in_array($size, ['sm', 'md', 'lg'])) $wrapClasses[] = 'form-toggle-' . $size;
    if ($resolvedColor) $wrapClasses[] = 'form-' . $resolvedColor;
@endphp

<label for="{{ $toggleId }}" {{ $attributes->merge(['class' => implode(' ', $wrapClasses)]) }}>
    <input
        type="checkbox"
        @if ($name) name="{{ $name }}" @endif
        id="{{ $toggleId }}"
        value="{{ $value }}"
        @if ($isChecked) checked @endif
        @if ($disabled) disabled @endif
    />
    <span class="form-toggle-track">
        <span class="form-toggle-thumb">
            @if ($iconOn || $iconOff)
                <x-icon :name="$isChecked ? ($iconOn ?? $iconOff) : ($iconOff ?? $iconOn)" size="10" />
            @endif
        </span>
    </span>
    @if ($label || $slot->isNotEmpty())
        <span class="form-toggle-label">
            @if ($label && $labelEn)
                <span class="bn">{{ $label }}</span>
                <span class="en" style="display:none;">{{ $labelEn }}</span>
            @elseif ($label)
                {{ $label }}
            @else
                {{ $slot }}
            @endif

            @if ($description)
                <span class="form-toggle-desc">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
