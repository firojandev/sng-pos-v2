@props([
    'as' => 'div',
    'orientation' => 'horizontal', // 'horizontal' | 'vertical'
    'size' => null, // 'xs' | 'sm' | 'md' | 'lg' | 'xl'
    'variant' => null, // 'solid' | 'outline' | 'soft' | 'ghost' | 'segmented'
    'color' => null, // 'gold' | 'teal' | 'primary' | 'secondary' | 'green' | 'red' | 'blue' | 'dark'
    'rounded' => 'default', // 'default' | 'none' | 'sm' | 'md' | 'lg' | 'xl' | 'full' | 'pill'
    'attached' => true,
    'spaced' => false,
    'gap' => null,
    'block' => false,
    'fullWidth' => false,
    'justify' => false,
    'toolbar' => false,
    'role' => null,
    'ariaLabel' => null,
    'name' => null,
    'options' => null,
    'value' => null,
    'selected' => null,
])

@php
    $tag = $as ?? 'div';
    $isVertical = $orientation === 'vertical';
    $isToolbar = (bool) $toolbar;
    $isAttached = (bool) ($attached && !$spaced && !$gap);
    $isBlock = (bool) ($block || $fullWidth || $justify);
    $selectedValue = $value ?? $selected ?? ($name && old($name) !== null ? old($name) : null);

    // Color Normalization & Aliases
    $colorAliases = [
        'primary' => 'primary',
        'secondary' => 'secondary',
        'brand' => 'teal',
        'success' => 'green',
        'danger' => 'red',
        'destructive' => 'red',
        'info' => 'blue',
        'ink' => 'dark',
        'neutral' => 'secondary',
        'grey' => 'secondary',
        'warning' => 'gold',
    ];

    $resolvedColor = $color ? strtolower(trim((string) $color)) : null;
    if ($resolvedColor) {
        $resolvedColor = $colorAliases[$resolvedColor] ?? $resolvedColor;
    }

    $classes = [];
    if ($isToolbar) {
        $classes[] = 'btn-toolbar';
    } else {
        $classes[] = 'btn-group';
        if ($isVertical) {
            $classes[] = 'btn-group-vertical';
        }
    }

    // Attached vs Spaced
    if (!$isAttached && !$isToolbar) {
        $classes[] = 'btn-group-spaced';
    }
    if ($gap) {
        $classes[] = 'gap-' . $gap;
    }

    // Size
    if ($size && in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'])) {
        $classes[] = 'btn-group-' . $size;
    }

    // Rounded
    if ($rounded === 'full' || $rounded === 'pill') {
        $classes[] = 'btn-group-pill';
    } elseif ($rounded === 'none') {
        $classes[] = 'btn-group-rounded-none';
    } elseif (in_array($rounded, ['sm', 'md', 'lg', 'xl'])) {
        $classes[] = 'btn-group-rounded-' . $rounded;
    }

    // Block / Justify
    if ($isBlock) {
        $classes[] = 'btn-group-block';
    }

    // Variant
    if ($variant === 'segmented') {
        $classes[] = 'btn-group-segmented';
    } elseif ($variant) {
        $classes[] = 'btn-group-' . $variant;
    }

    // Color
    if ($resolvedColor) {
        $classes[] = 'btn-group-' . $resolvedColor;
    }

    $extraAttrs = [
        'role' => $role ?? ($isToolbar ? 'toolbar' : 'group'),
    ];

    if ($ariaLabel) {
        $extraAttrs['aria-label'] = $ariaLabel;
    }
@endphp

<{{ $tag }} {{ $attributes->merge(array_merge(['class' => implode(' ', array_filter($classes))], $extraAttrs)) }}>
    @if (!empty($options) && (is_array($options) || is_iterable($options)))
        @foreach ($options as $optKey => $optVal)
            @php
                if (is_array($optVal)) {
                    $itemVal = $optVal['value'] ?? $optKey;
                    $itemLabel = $optVal['label'] ?? $optVal['text'] ?? $itemVal;
                    $itemLabelEn = $optVal['labelEn'] ?? $optVal['label_en'] ?? null;
                    $itemIcon = $optVal['icon'] ?? null;
                    $itemIconRight = $optVal['iconRight'] ?? $optVal['icon_right'] ?? $optVal['iconAfter'] ?? null;
                    $itemIconOnly = (bool) ($optVal['iconOnly'] ?? $optVal['icon_only'] ?? false);
                    $itemVariant = $optVal['variant'] ?? ($variant === 'segmented' ? 'ghost' : ($variant ?? 'outline'));
                    $itemColor = $optVal['color'] ?? ($resolvedColor ?? ($variant === 'segmented' ? null : 'secondary'));
                    $itemDisabled = (bool) ($optVal['disabled'] ?? false);
                    $itemBadge = $optVal['badge'] ?? null;
                    $itemBadgeColor = $optVal['badgeColor'] ?? null;
                } else {
                    $itemVal = is_numeric($optKey) ? $optVal : $optKey;
                    $itemLabel = $optVal;
                    $itemLabelEn = null;
                    $itemIcon = null;
                    $itemIconRight = null;
                    $itemIconOnly = false;
                    $itemVariant = $variant === 'segmented' ? 'ghost' : ($variant ?? 'outline');
                    $itemColor = $resolvedColor ?? ($variant === 'segmented' ? null : 'secondary');
                    $itemDisabled = false;
                    $itemBadge = null;
                    $itemBadgeColor = null;
                }

                $isActive = $selectedValue !== null && (string) $selectedValue === (string) $itemVal;
            @endphp

            @if ($name)
                <label
                    class="btn {{ $size ? 'btn-' . $size : '' }} {{ $isActive ? 'active' : '' }} {{ $itemDisabled ? 'disabled' : '' }}"
                    data-btn-group-item
                    data-value="{{ $itemVal }}"
                    @if ($itemDisabled) aria-disabled="true" @endif
                >
                    <input
                        type="radio"
                        name="{{ $name }}"
                        value="{{ $itemVal }}"
                        @if ($isActive) checked @endif
                        @if ($itemDisabled) disabled @endif
                        style="position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0; margin: 0;"
                    />
                    @if ($itemIcon)
                        <span class="btn-icon">
                            <x-core::icon :name="$itemIcon" />
                        </span>
                    @endif

                    @if (!$itemIconOnly && $itemLabel)
                        <span class="btn-text">
                            @if ($itemLabelEn)
                                <span class="bn">{{ $itemLabel }}</span>
                                <span class="en" style="display:none;">{{ $itemLabelEn }}</span>
                            @else
                                {{ $itemLabel }}
                            @endif
                        </span>
                    @endif

                    @if ($itemIconRight)
                        <span class="btn-icon btn-icon-right">
                            <x-core::icon :name="$itemIconRight" />
                        </span>
                    @endif

                    @if ($itemBadge !== null && $itemBadge !== '')
                        <span class="btn-badge {{ $itemBadgeColor ? 'badge-' . $itemBadgeColor : '' }}">{{ $itemBadge }}</span>
                    @endif
                </label>
            @else
                <x-core::button
                    :variant="$isActive && $variant !== 'segmented' ? 'solid' : $itemVariant"
                    :color="$isActive && $variant !== 'segmented' ? ($resolvedColor ?? 'teal') : $itemColor"
                    :size="$size"
                    :icon="$itemIcon"
                    :icon-right="$itemIconRight"
                    :icon-only="$itemIconOnly"
                    :disabled="$itemDisabled"
                    :badge="$itemBadge"
                    :badge-color="$itemBadgeColor"
                    :class="$isActive ? 'active' : ''"
                    :data-value="$itemVal"
                    data-btn-group-item
                    :aria-pressed="$isActive ? 'true' : 'false'"
                >
                    @if ($itemLabelEn)
                        <span class="bn">{{ $itemLabel }}</span>
                        <span class="en" style="display:none;">{{ $itemLabelEn }}</span>
                    @else
                        {{ $itemLabel }}
                    @endif
                </x-core::button>
            @endif
        @endforeach
    @endif

    {{ $slot }}
</{{ $tag }}>
