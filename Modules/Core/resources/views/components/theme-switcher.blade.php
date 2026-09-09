@props([
    'id' => 'theme-toggle',
    'size' => 'sm',
    'showText' => true,
])

@php
    $cookieTheme = request()->cookie('theme');
    $isDark = $cookieTheme === 'dark';
@endphp

<div {{ $attributes->merge(['class' => 'segmented-switcher theme-segmented-switcher switcher-' . $size]) }}>
    <span class="switch-opt switch-opt-light {{ ! $isDark ? 'active' : '' }}" data-action="set-theme-light" title="লাইট মোড / Light Mode" aria-label="Light Mode">
        <x-core::icon name="sun" :size="$size === 'xs' ? 12 : ($size === 'lg' ? 16 : 13)" />
        @if ($showText)
            <span class="bn">লাইট</span>
            <span class="en" style="display:none;">Light</span>
        @endif
    </span>

    <label class="segmented-switch-track form-toggle-wrap" for="{{ $id }}" title="থিম পরিবর্তন / Toggle Theme">
        <input type="checkbox" id="{{ $id }}" class="segmented-switch-input" aria-label="Theme Toggle Switch" {{ $isDark ? 'checked' : '' }} />
        <span class="segmented-switch-slider"></span>
    </label>
    <script>
        (function() {
            var el = document.getElementById('{{ $id }}');
            if (el) {
                var d = document.documentElement.getAttribute('data-theme');
                if (d === 'dark') el.checked = true;
                else if (d === 'light') el.checked = false;
            }
        })();
    </script>

    <span class="switch-opt switch-opt-dark {{ $isDark ? 'active' : '' }}" data-action="set-theme-dark" title="ডার্ক মোড / Dark Mode" aria-label="Dark Mode">
        <x-core::icon name="moon" :size="$size === 'xs' ? 12 : ($size === 'lg' ? 16 : 13)" />
        @if ($showText)
            <span class="bn">ডার্ক</span>
            <span class="en" style="display:none;">Dark</span>
        @endif
    </span>
</div>
