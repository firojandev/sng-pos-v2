@props([
    'id' => 'lang-toggle',
    'size' => 'sm',
    'showFull' => false,
])

<div {{ $attributes->merge(['class' => 'segmented-switcher lang-segmented-switcher switcher-' . $size]) }}>
    <span class="switch-opt switch-opt-bn active" data-action="set-lang-bn" title="বাংলা ভাষা / Bengali" aria-label="Bangla Language">
        @if ($showFull)
            <span class="bn">বাংলা</span>
            <span class="en" style="display:none;">Bangla</span>
        @else
            <span>বাং</span>
        @endif
    </span>

    <label class="segmented-switch-track form-toggle-wrap" for="{{ $id }}" title="ভাষা পরিবর্তন / Toggle Language">
        <input type="checkbox" id="{{ $id }}" class="segmented-switch-input" aria-label="Language Toggle Switch" />
        <span class="segmented-switch-slider"></span>
    </label>

    <span class="switch-opt switch-opt-en" data-action="set-lang-en" title="English Language" aria-label="English Language">
        <span>EN</span>
    </span>
</div>
