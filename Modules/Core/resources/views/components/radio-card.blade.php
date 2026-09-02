@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'checked' => false,
    'title' => null,
    'titleEn' => null,
    'description' => null,
    'descriptionEn' => null,
    'icon' => null,
    'badge' => null,
    'badgeColor' => 'teal',
    'color' => 'teal',
    'disabled' => false,
    'isCheckbox' => false,
])

@php
    $cardId = $id ?? ($name ? 'form-card-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) . '-' . substr(md5((string)$value), 0, 5) : 'form-card-' . uniqid());
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

    $wrapClasses = ['form-radio-card'];
    if ($resolvedColor) $wrapClasses[] = 'form-' . $resolvedColor;
    if ($isChecked) $wrapClasses[] = 'active';
@endphp

<label for="{{ $cardId }}" {{ $attributes->merge(['class' => implode(' ', $wrapClasses)]) }}>
    <input
        type="{{ $isCheckbox ? 'checkbox' : 'radio' }}"
        @if ($name) name="{{ $name }}" @endif
        id="{{ $cardId }}"
        value="{{ $value }}"
        @if ($isChecked) checked @endif
        @if ($disabled) disabled @endif
    />
    @if ($icon)
        <span class="card-icon">
            <x-core::icon :name="$icon" size="20" />
        </span>
    @endif
    <span class="card-content">
        <span class="card-title">
            @if ($title && $titleEn)
                <span class="bn">{{ $title }}</span>
                <span class="en" style="display:none;">{{ $titleEn }}</span>
            @elseif ($title)
                {{ $title }}
            @else
                {{ $slot }}
            @endif
        </span>
        @if ($description || $descriptionEn)
            <span class="card-desc">
                @if ($description && $descriptionEn)
                    <span class="bn">{{ $description }}</span>
                    <span class="en" style="display:none;">{{ $descriptionEn }}</span>
                @else
                    {{ $description }}
                @endif
            </span>
        @endif
    </span>
    @if ($badge)
        <span class="card-badge badge-{{ $badgeColor }}">{{ $badge }}</span>
    @endif
</label>
