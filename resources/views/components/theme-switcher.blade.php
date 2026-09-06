@props([
    'id' => 'theme-toggle',
    'size' => 'sm',
    'showText' => true,
])

<div {{ $attributes->merge(['class' => 'segmented-switcher theme-segmented-switcher switcher-' . $size]) }}>
    <span class="switch-opt switch-opt-light active" data-action="set-theme-light" title="লাইট মোড / Light Mode" aria-label="Light Mode">
        <x-icon name="sun" :size="$size === 'xs' ? 12 : ($size === 'lg' ? 16 : 13)" />
        @if ($showText)
            <span class="bn">লাইট</span>
            <span class="en" style="display:none;">Light</span>
        @endif
    </span>

    <label class="segmented-switch-track form-toggle-wrap" for="{{ $id }}" title="থিম পরিবর্তন / Toggle Theme">
        <input type="checkbox" id="{{ $id }}" class="segmented-switch-input" aria-label="Theme Toggle Switch" />
        <span class="segmented-switch-slider"></span>
    </label>

    <span class="switch-opt switch-opt-dark" data-action="set-theme-dark" title="ডার্ক মোড / Dark Mode" aria-label="Dark Mode">
        <x-icon name="moon" :size="$size === 'xs' ? 12 : ($size === 'lg' ? 16 : 13)" />
        @if ($showText)
            <span class="bn">ডার্ক</span>
            <span class="en" style="display:none;">Dark</span>
        @endif
    </span>
</div>
