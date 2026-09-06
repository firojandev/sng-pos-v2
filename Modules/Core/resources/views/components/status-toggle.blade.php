@props([
    'name' => 'status',
    'id' => null,
    'value' => null,
    'activeVal' => 'active',
    'inactiveVal' => 'inactive',
    'activeLabel' => 'সক্রিয় (Active)',
    'activeLabelEn' => 'Active',
    'inactiveLabel' => 'নিষ্ক্রিয় (Inactive)',
    'inactiveLabelEn' => 'Inactive',
    'activeIcon' => 'check-circle',
    'inactiveIcon' => 'x-circle',
    'size' => 'md',
    'fullWidth' => true,
    'disabled' => false,
    'label' => null,
    'labelEn' => null,
])

@php
    $inputId = $id ?? 'status-toggle-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) . '-' . uniqid();
    $currentVal = old($name, $value ?? $activeVal);
    $isActive = (string) $currentVal === (string) $activeVal;
@endphp

<div {{ $attributes->merge(['class' => 'status-toggle-wrapper' . ($fullWidth ? ' status-toggle-full' : '')]) }}>
    @if ($label || $labelEn)
        <label class="form-label">
            @if ($label && $labelEn)
                <span class="bn">{{ $label }}</span>
                <span class="en" style="display:none;">{{ $labelEn }}</span>
            @elseif ($label)
                <span>{{ $label }}</span>
            @endif
        </label>
    @endif

    <div
        class="segmented-switcher status-segmented-switcher switcher-{{ $size }} {{ $isActive ? 'is-active' : 'is-inactive' }}"
        data-status-switcher
    >
        <span
            class="switch-opt switch-opt-active {{ $isActive ? 'active' : '' }}"
            data-status-opt="{{ $activeVal }}"
            role="button"
            tabindex="0"
            title="{{ $activeLabelEn ?? $activeLabel }}"
        >
            @if ($activeIcon)
                <x-core::icon :name="$activeIcon" :size="$size === 'sm' ? 12 : ($size === 'lg' ? 16 : 14)" />
            @endif
            @if ($activeLabel && $activeLabelEn)
                <span class="bn">{{ $activeLabel }}</span>
                <span class="en" style="display:none;">{{ $activeLabelEn }}</span>
            @elseif ($activeLabel)
                <span>{{ $activeLabel }}</span>
            @endif
        </span>

        <label class="segmented-switch-track form-toggle-wrap" for="{{ $inputId }}">
            <input
                type="checkbox"
                id="{{ $inputId }}"
                class="segmented-switch-input"
                data-status-toggle
                data-active-val="{{ $activeVal }}"
                data-inactive-val="{{ $inactiveVal }}"
                {{ !$isActive ? 'checked' : '' }}
                @if ($disabled) disabled @endif
            />
            <span class="segmented-switch-slider"></span>
        </label>

        <span
            class="switch-opt switch-opt-inactive {{ !$isActive ? 'active' : '' }}"
            data-status-opt="{{ $inactiveVal }}"
            role="button"
            tabindex="0"
            title="{{ $inactiveLabelEn ?? $inactiveLabel }}"
        >
            @if ($inactiveIcon)
                <x-core::icon :name="$inactiveIcon" :size="$size === 'sm' ? 12 : ($size === 'lg' ? 16 : 14)" />
            @endif
            @if ($inactiveLabel && $inactiveLabelEn)
                <span class="bn">{{ $inactiveLabel }}</span>
                <span class="en" style="display:none;">{{ $inactiveLabelEn }}</span>
            @elseif ($inactiveLabel)
                <span>{{ $inactiveLabel }}</span>
            @endif
        </span>
    </div>

    <input
        type="hidden"
        name="{{ $name }}"
        id="status-input"
        value="{{ $currentVal }}"
        data-status-input
    />
</div>
