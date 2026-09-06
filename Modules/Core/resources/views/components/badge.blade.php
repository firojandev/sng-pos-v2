@props([
    'color' => 'teal',
    'variant' => 'subtle',
    'size' => 'sm',
    'rounded' => 'pill',
    'icon' => null,
    'iconRight' => null,
    'dot' => false,
    'label' => null,
    'labelEn' => null,
])

@php
    $colorAliases = [
        'primary' => 'gold',
        'secondary' => 'grey',
        'brand' => 'teal',
        'success' => 'green',
        'danger' => 'red',
        'destructive' => 'red',
        'warning' => 'gold',
        'info' => 'blue',
        'neutral' => 'grey',
        'dark' => 'grey',
    ];

    $resolvedColor = strtolower(trim((string) $color));
    $resolvedColor = $colorAliases[$resolvedColor] ?? $resolvedColor;

    $badgeClasses = ['badge'];

    // Variant & Color
    if ($variant === 'solid') {
        $badgeClasses[] = 'badge-solid-' . $resolvedColor;
    } elseif ($variant === 'outline') {
        $badgeClasses[] = 'badge-outline';
        $badgeClasses[] = 'badge-' . $resolvedColor;
    } else {
        $badgeClasses[] = 'b-' . $resolvedColor;
        $badgeClasses[] = 'badge-' . $resolvedColor;
    }

    // Size
    if ($size && in_array($size, ['xs', 'sm', 'md', 'lg'])) {
        $badgeClasses[] = 'badge-' . $size;
    }

    // Rounded
    if ($rounded === 'rounded') {
        $badgeClasses[] = 'badge-rounded';
    } elseif ($rounded === 'square') {
        $badgeClasses[] = 'badge-square';
    } else {
        $badgeClasses[] = 'badge-pill';
    }
@endphp

<span {{ $attributes->merge(['class' => implode(' ', array_unique($badgeClasses))]) }}>
    @if ($dot)
        <span class="badge-dot"></span>
    @endif

    @if ($icon)
        <x-core::icon :name="$icon" :size="$size === 'xs' ? 10 : 12" />
    @endif

    @if ($label && $labelEn)
        <span class="bn">{{ $label }}</span>
        <span class="en" style="display:none;">{{ $labelEn }}</span>
    @elseif ($label)
        {{ $label }}
    @else
        {{ $slot }}
    @endif

    @if ($iconRight)
        <x-core::icon :name="$iconRight" :size="$size === 'xs' ? 10 : 12" />
    @endif
</span>
