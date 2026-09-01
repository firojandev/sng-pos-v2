@props([
    'as' => null,
    'href' => null,
    'type' => 'button',
    'variant' => 'solid',
    'color' => 'gold',
    'size' => 'md',
    'rounded' => 'default',
    'icon' => null,
    'iconRight' => null,
    'iconAfter' => null,
    'iconOnly' => false,
    'block' => false,
    'fullWidth' => false,
    'loading' => false,
    'loadingText' => null,
    'disabled' => false,
    'badge' => null,
    'badgeColor' => null,
    'target' => null,
    'rel' => null,
])

@php
    $tag = $as ?? ($href ? 'a' : 'button');
    $isButton = $tag === 'button';
    $isLink = $tag === 'a';
    $isDisabled = (bool) ($disabled || $loading);
    $rightIconProp = $iconRight ?? $iconAfter;
    $isBlock = (bool) ($block || $fullWidth);

    // Color Normalization & Aliases
    $colorAliases = [
        'primary' => 'gold',
        'brand' => 'teal',
        'success' => 'green',
        'danger' => 'red',
        'destructive' => 'red',
        'info' => 'blue',
        'ink' => 'dark',
        'secondary' => 'grey',
        'neutral' => 'grey',
    ];

    $resolvedColor = strtolower(trim((string) $color));
    $resolvedColor = $colorAliases[$resolvedColor] ?? $resolvedColor;

    // Check composite variants like "outline-teal", "soft-red", "ghost-gold", "link-primary", "btn-teal"
    $resolvedVariant = strtolower(trim((string) $variant));
    if (str_starts_with($resolvedVariant, 'btn-')) {
        $resolvedVariant = substr($resolvedVariant, 4);
    }

    if (str_contains($resolvedVariant, '-')) {
        $parts = explode('-', $resolvedVariant, 2);
        if (in_array($parts[0], ['solid', 'outline', 'soft', 'ghost', 'link'])) {
            $resolvedVariant = $parts[0];
            $resolvedColor = $colorAliases[$parts[1]] ?? $parts[1];
        }
    }

    if (in_array($resolvedVariant, ['gold', 'teal', 'green', 'red', 'blue', 'dark', 'grey', 'secondary', 'primary', 'brand', 'success', 'danger', 'info'])) {
        $resolvedColor = $colorAliases[$resolvedVariant] ?? $resolvedVariant;
        $resolvedVariant = 'solid';
    }

    // Build CSS classes
    $classes = ['btn'];

    // Variant & Color
    if ($resolvedVariant === 'outline') {
        $classes[] = ($resolvedColor === 'grey' || $resolvedColor === 'neutral')
            ? 'btn-outline'
            : 'btn-outline btn-outline-' . $resolvedColor;
    } elseif ($resolvedVariant === 'soft') {
        $classes[] = 'btn-soft btn-soft-' . $resolvedColor;
    } elseif ($resolvedVariant === 'ghost') {
        $classes[] = ($resolvedColor === 'grey' || $resolvedColor === 'neutral')
            ? 'btn-ghost'
            : 'btn-ghost btn-ghost-' . $resolvedColor;
    } elseif ($resolvedVariant === 'link') {
        $classes[] = ($resolvedColor === 'grey' || $resolvedColor === 'neutral')
            ? 'btn-link'
            : 'btn-link btn-link-' . $resolvedColor;
    } else {
        // solid (default)
        $classes[] = 'btn-solid-' . $resolvedColor;
        $classes[] = 'btn-' . $resolvedColor; // For backwards compatibility
    }

    // Size
    $sizeClassMap = [
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'md' => 'btn-md',
        'lg' => 'btn-lg',
        'xl' => 'btn-xl',
    ];
    $classes[] = $sizeClassMap[$size] ?? 'btn-md';

    // Rounded
    if ($rounded === 'full' || $rounded === 'pill') {
        $classes[] = 'btn-pill';
    } elseif ($rounded === 'none') {
        $classes[] = 'btn-rounded-none';
    } elseif (in_array($rounded, ['sm', 'md', 'lg', 'xl'])) {
        $classes[] = 'btn-rounded-' . $rounded;
    }

    // Icon only
    if ($iconOnly) {
        $classes[] = 'btn-icon-only';
    }

    // Block / Full width
    if ($isBlock) {
        $classes[] = 'btn-block';
    }

    // Loading & Disabled
    if ($loading) {
        $classes[] = 'is-loading';
    }
    if ($isDisabled) {
        $classes[] = 'disabled';
    }

    // Rel resolution for external link
    $computedRel = $rel;
    if ($isLink && $target === '_blank' && !$computedRel) {
        $computedRel = 'noopener noreferrer';
    }

    $extraAttrs = [];
    if ($isButton) {
        $extraAttrs['type'] = $type;
        if ($isDisabled) {
            $extraAttrs['disabled'] = true;
        }
    } elseif ($isLink) {
        $extraAttrs['href'] = $isDisabled ? 'javascript:void(0)' : ($href ?? '#');
        if ($target) {
            $extraAttrs['target'] = $target;
        }
        if ($computedRel) {
            $extraAttrs['rel'] = $computedRel;
        }
        if ($isDisabled) {
            $extraAttrs['aria-disabled'] = 'true';
            $extraAttrs['tabindex'] = '-1';
        }
    }
    if ($loading) {
        $extraAttrs['aria-busy'] = 'true';
    }
@endphp

<{{ $tag }} {{ $attributes->merge(array_merge(['class' => implode(' ', array_filter($classes))], $extraAttrs)) }}>
    {{-- Left Icon or Spinner --}}
    @if ($loading)
        <span class="btn-spinner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" stroke-dasharray="32" stroke-linecap="round" fill="none" opacity="0.25" />
                <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            </svg>
        </span>
    @elseif (isset($iconSlot))
        <span class="btn-icon">{{ $iconSlot }}</span>
    @elseif ($icon)
        <span class="btn-icon">
            <x-core::icon :name="$icon" />
        </span>
    @endif

    {{-- Content / Slot / Loading text --}}
    @if ($loading && $loadingText)
        <span class="btn-text">{{ $loadingText }}</span>
    @elseif (!$iconOnly || (!empty((string) $slot) && empty($icon) && !isset($iconSlot)))
        @if (!empty((string) $slot))
            <span class="btn-text">{{ $slot }}</span>
        @endif
    @endif

    {{-- Right Icon --}}
    @if (!$loading)
        @if (isset($iconRightSlot))
            <span class="btn-icon btn-icon-right">{{ $iconRightSlot }}</span>
        @elseif ($rightIconProp)
            <span class="btn-icon btn-icon-right">
                <x-core::icon :name="$rightIconProp" />
            </span>
        @endif
    @endif

    {{-- Optional Badge --}}
    @if ($badge !== null && $badge !== '')
        <span class="btn-badge {{ $badgeColor ? 'badge-' . $badgeColor : '' }}">{{ $badge }}</span>
    @endif
</{{ $tag }}>
