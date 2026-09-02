@props([
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'label' => null,
    'labelEn' => null,
    'labelOn' => null,
    'labelOff' => null,
    'labelOnEn' => null,
    'labelOffEn' => null,
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
            @if ($iconOn && $iconOff)
                <span class="toggle-icon-off"><x-core::icon :name="$iconOff" size="10" /></span>
                <span class="toggle-icon-on"><x-core::icon :name="$iconOn" size="10" /></span>
            @elseif ($iconOn || $iconOff)
                <x-core::icon :name="$isChecked ? ($iconOn ?? $iconOff) : ($iconOff ?? $iconOn)" size="10" />
            @endif
        </span>
    </span>
    @if ($labelOn || $labelOff || $label || $slot->isNotEmpty())
        <span class="form-toggle-label">
            @if ($labelOn || $labelOff)
                <span class="toggle-label-off">
                    @if ($labelOff && $labelOffEn)
                        <span class="bn">{{ $labelOff }}</span>
                        <span class="en">{{ $labelOffEn }}</span>
                    @elseif ($labelOff)
                        {{ $labelOff }}
                    @elseif ($label && $labelEn)
                        <span class="bn">{{ $label }}</span>
                        <span class="en">{{ $labelEn }}</span>
                    @else
                        {{ $label }}
                    @endif
                </span>
                <span class="toggle-label-on">
                    @if ($labelOn && $labelOnEn)
                        <span class="bn">{{ $labelOn }}</span>
                        <span class="en">{{ $labelOnEn }}</span>
                    @elseif ($labelOn)
                        {{ $labelOn }}
                    @elseif ($label && $labelEn)
                        <span class="bn">{{ $label }}</span>
                        <span class="en">{{ $labelEn }}</span>
                    @else
                        {{ $label }}
                    @endif
                </span>
            @elseif ($label && $labelEn)
                <span class="bn">{{ $label }}</span>
                <span class="en">{{ $labelEn }}</span>
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
