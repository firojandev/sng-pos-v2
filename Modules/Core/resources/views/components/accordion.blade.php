@props([
    'id' => null,
    'name' => null,
    'value' => '1',
    'checked' => null,
    'open' => null,
    'title' => null,
    'titleEn' => null,
    'description' => null,
    'descriptionEn' => null,
    'icon' => null,
    'type' => null, // 'checkbox', 'toggle', 'default'
    'color' => 'teal',
    'badge' => null,
    'badgeColor' => 'secondary',
    'badgeEn' => null,
    'group' => null,
    'disabled' => false,
    'flush' => false,
    'card' => false,
])

@php
    $accordionId = $id ?? ($name ? 'accordion-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) : 'accordion-' . uniqid());
    $checkId = $name ? 'check-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) . '-' . substr(md5((string) $value), 0, 5) : 'check-' . uniqid();

    // Auto-detect type: if 'name' is provided and 'type' not explicitly set, default to checkbox
    $isCheckboxType = $type === 'checkbox' || ($type === null && $name !== null);
    $isToggleType = $type === 'toggle';
    $hasInput = $isCheckboxType || $isToggleType;

    $isChecked = false;
    if ($hasInput) {
        if ($checked !== null) {
            $isChecked = (bool) $checked;
        } elseif ($name) {
            $isChecked = (bool) old($name, false);
        }
    }

    $isOpen = $open !== null ? (bool) $open : ($hasInput ? $isChecked : false);

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

    $wrapClasses = ['app-accordion', 'feature-box'];
    if ($isOpen) {
        $wrapClasses[] = 'is-open active';
    }
    if ($flush) {
        $wrapClasses[] = 'app-accordion-flush';
    }
    if ($card) {
        $wrapClasses[] = 'app-accordion-card';
    }
    if ($resolvedColor) {
        $wrapClasses[] = 'accordion-' . $resolvedColor;
    }
@endphp

<div
    id="{{ $accordionId }}"
    data-accordion
    @if ($group) data-accordion-group="{{ $group }}" @endif
    {{ $attributes->merge(['class' => implode(' ', $wrapClasses)]) }}
>
    <div class="app-accordion-header feature-box-toggle" data-accordion-trigger>
        <div class="app-accordion-title-wrap">
            @if ($icon)
                <div class="app-accordion-icon">
                    <x-core::icon :name="$icon" size="16" />
                </div>
            @endif

            @if ($isCheckboxType)
                <label for="{{ $checkId }}" class="app-accordion-label">
                    <input
                        type="checkbox"
                        @if ($name) name="{{ $name }}" @endif
                        id="{{ $checkId }}"
                        value="{{ $value }}"
                        class="app-accordion-checkbox"
                        data-accordion-checkbox
                        @if ($isChecked) checked @endif
                        @if ($disabled) disabled @endif
                    />
                    <div class="app-accordion-text">
                        @if ($title && $titleEn)
                            <span class="bn">{{ $title }}</span>
                            <span class="en" style="display:none;">{{ $titleEn }}</span>
                        @elseif ($title)
                            <span>{{ $title }}</span>
                        @elseif (isset($header))
                            {{ $header }}
                        @endif

                        @if ($description && $descriptionEn)
                            <span class="app-accordion-desc bn">{{ $description }}</span>
                            <span class="app-accordion-desc en" style="display:none;">{{ $descriptionEn }}</span>
                        @elseif ($description)
                            <span class="app-accordion-desc">{{ $description }}</span>
                        @endif
                    </div>
                </label>
            @elseif ($isToggleType)
                <div class="app-accordion-label">
                    <x-core::toggle
                        :name="$name"
                        :id="$checkId"
                        :value="$value"
                        :checked="$isChecked"
                        :disabled="$disabled"
                        :color="$resolvedColor"
                        size="sm"
                        data-accordion-checkbox
                    />
                    <div class="app-accordion-text">
                        @if ($title && $titleEn)
                            <span class="bn">{{ $title }}</span>
                            <span class="en" style="display:none;">{{ $titleEn }}</span>
                        @elseif ($title)
                            <span>{{ $title }}</span>
                        @elseif (isset($header))
                            {{ $header }}
                        @endif

                        @if ($description && $descriptionEn)
                            <span class="app-accordion-desc bn">{{ $description }}</span>
                            <span class="app-accordion-desc en" style="display:none;">{{ $descriptionEn }}</span>
                        @elseif ($description)
                            <span class="app-accordion-desc">{{ $description }}</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="app-accordion-text">
                    @if ($title && $titleEn)
                        <span class="app-accordion-title bn">{{ $title }}</span>
                        <span class="app-accordion-title en" style="display:none;">{{ $titleEn }}</span>
                    @elseif ($title)
                        <span class="app-accordion-title">{{ $title }}</span>
                    @elseif (isset($header))
                        {{ $header }}
                    @endif

                    @if ($description && $descriptionEn)
                        <span class="app-accordion-desc bn">{{ $description }}</span>
                        <span class="app-accordion-desc en" style="display:none;">{{ $descriptionEn }}</span>
                    @elseif ($description)
                        <span class="app-accordion-desc">{{ $description }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="app-accordion-actions">
            @if (isset($badgeSlot))
                {{ $badgeSlot }}
            @elseif ($badge)
                <x-core::badge :color="$badgeColor" size="sm">
                    @if ($badgeEn)
                        <span class="bn">{{ $badge }}</span>
                        <span class="en" style="display:none;">{{ $badgeEn }}</span>
                    @else
                        {{ $badge }}
                    @endif
                </x-core::badge>
            @endif

            @if (isset($actions))
                {{ $actions }}
            @endif

            <span class="app-accordion-toggle-icon toggle-icon" data-accordion-icon>
                <x-core::icon name="chevron-down" size="16" />
            </span>
        </div>
    </div>

    <div
        class="app-accordion-body"
        data-accordion-content
        style="{{ $isOpen ? '' : 'display:none;' }}"
    >
        {{ $slot }}
    </div>
</div>
