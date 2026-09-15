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
    'descriptionEn' => null,
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

    $resolvedLabelEn = $labelEn ?? $attributes->get('label-en') ?? null;
    $resolvedLabelOnEn = $labelOnEn ?? $attributes->get('label-on-en') ?? null;
    $resolvedLabelOffEn = $labelOffEn ?? $attributes->get('label-off-en') ?? null;
    $resolvedDescriptionEn = $descriptionEn ?? $attributes->get('description-en') ?? null;

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

<label for="{{ $toggleId }}" {{ $attributes->except(['label-en', 'label-on-en', 'label-off-en', 'description-en'])->merge(['class' => implode(' ', $wrapClasses)]) }}>
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
    @if ($labelOn || $labelOff || $label || $resolvedLabelEn || $slot->isNotEmpty())
        <span class="form-toggle-label">
            @if ($labelOn || $labelOff)
                <span class="toggle-label-off">
                    @if ($labelOff && $resolvedLabelOffEn)
                        <span class="bn">{{ $labelOff }}</span>
                        <span class="en" style="display:none;">{{ $resolvedLabelOffEn }}</span>
                    @elseif ($labelOff)
                        {{ $labelOff }}
                    @elseif ($label && $resolvedLabelEn)
                        <span class="bn">{{ $label }}</span>
                        <span class="en" style="display:none;">{{ $resolvedLabelEn }}</span>
                    @else
                        {{ $label }}
                    @endif
                </span>
                <span class="toggle-label-on">
                    @if ($labelOn && $resolvedLabelOnEn)
                        <span class="bn">{{ $labelOn }}</span>
                        <span class="en" style="display:none;">{{ $resolvedLabelOnEn }}</span>
                    @elseif ($labelOn)
                        {{ $labelOn }}
                    @elseif ($label && $resolvedLabelEn)
                        <span class="bn">{{ $label }}</span>
                        <span class="en" style="display:none;">{{ $resolvedLabelEn }}</span>
                    @else
                        {{ $label }}
                    @endif
                </span>
            @elseif ($label && $resolvedLabelEn)
                <span class="bn">{{ $label }}</span>
                <span class="en" style="display:none;">{{ $resolvedLabelEn }}</span>
            @elseif ($label)
                {{ $label }}
            @elseif ($resolvedLabelEn)
                <span class="en">{{ $resolvedLabelEn }}</span>
            @else
                {{ $slot }}
            @endif

            @if ($description || $resolvedDescriptionEn)
                <span class="form-toggle-desc">
                    @if ($description && $resolvedDescriptionEn)
                        <span class="bn">{{ $description }}</span>
                        <span class="en" style="display:none;">{{ $resolvedDescriptionEn }}</span>
                    @elseif ($description)
                        {{ $description }}
                    @else
                        <span class="en">{{ $resolvedDescriptionEn }}</span>
                    @endif
                </span>
            @endif
        </span>
    @endif
</label>
