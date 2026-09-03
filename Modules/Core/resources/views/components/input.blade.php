@props([
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'placeholderEn' => null,
    'size' => 'md',
    'variant' => 'outline',
    'color' => 'teal',
    'rounded' => 'default',
    'icon' => null,
    'iconLeft' => null,
    'iconRight' => null,
    'prefix' => null,
    'suffix' => null,
    'addonLeft' => null,
    'addonRight' => null,
    'clearable' => false,
    'passwordToggle' => false,
    'stepper' => true,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'loading' => false,
    'error' => null,
    'helper' => null,
    'helperVariant' => 'default',
    'label' => null,
    'labelEn' => null,
    'optional' => false,
    'noMargin' => false,
])

@php
    $inputId = $id ?? ($name ? 'form-field-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) : null);
    $inputValue = $value;
    if ($name && $value === null) {
        $inputValue = old($name);
    }

    $hasError = (bool) ($error || ($name && isset($errors) && $errors->has($name)));
    $errorMessage = $error ?? ($name && isset($errors) && $errors->has($name) ? $errors->first($name) : null);

    $leftIcon = $icon ?? $iconLeft;
    $leftAddon = $addonLeft ?? $prefix;
    $rightAddon = $addonRight ?? $suffix;

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

    // Build control classes
    $controlClasses = ['form-control'];
    
    // Variant
    if (in_array($variant, ['filled', 'soft'])) {
        $controlClasses[] = 'form-control-filled';
    } elseif (in_array($variant, ['flushed', 'underlined'])) {
        $controlClasses[] = 'form-control-flushed';
    } elseif ($variant === 'unstyled') {
        $controlClasses[] = 'form-control-unstyled';
    } else {
        $controlClasses[] = 'form-control-outline';
    }

    // Size
    if (in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'])) {
        $controlClasses[] = 'form-control-' . $size;
    }

    // Color
    if ($resolvedColor) {
        $controlClasses[] = 'form-' . $resolvedColor;
    }

    // Rounded
    if ($rounded === 'pill') {
        $controlClasses[] = 'form-rounded-pill';
    } elseif ($rounded === 'none') {
        $controlClasses[] = 'form-rounded-none';
    } elseif (in_array($rounded, ['sm', 'md', 'lg', 'xl'])) {
        $controlClasses[] = 'form-rounded-' . $rounded;
    }

    // Error state
    if ($hasError) {
        $controlClasses[] = 'is-invalid';
    }

    // Group wrapper classes
    $groupClasses = ['form-input-group'];
    if ($leftIcon) $groupClasses[] = 'has-icon-left';
    if ($iconRight || $loading || $clearable || $passwordToggle) $groupClasses[] = 'has-icon-right';
    if ($leftAddon) $groupClasses[] = 'has-addon-left';
    if ($rightAddon) $groupClasses[] = 'has-addon-right';
    if ($hasError) $groupClasses[] = 'is-invalid';
    if (in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'])) $groupClasses[] = 'form-input-group-' . $size;
    if ($rounded === 'pill') $groupClasses[] = 'form-rounded-pill';

    $hasStepper = ($type === 'number' && $stepper && ! $disabled && ! $readonly);
    if ($hasStepper) $groupClasses[] = 'has-stepper';

    $hasWrapper = (bool) ($label || $helper || $hasError);
@endphp

@if ($hasWrapper)
    <x-core::form-group
        :name="$name"
        :id="$inputId"
        :label="$label"
        :label-en="$labelEn"
        :required="$required"
        :optional="$optional"
        :icon="$leftIcon"
        :helper="$helper"
        :helper-variant="$helperVariant"
        :error="$errorMessage"
        :no-margin="$noMargin"
    >
        <div class="{{ implode(' ', $groupClasses) }}">
            @if ($leftAddon)
                <span class="form-input-addon form-input-addon-left">{{ $leftAddon }}</span>
            @endif

            @if ($leftIcon)
                <span class="form-input-icon form-input-icon-left">
                    <x-core::icon :name="$leftIcon" />
                </span>
            @endif

            <input
                type="{{ $type }}"
                @if ($name) name="{{ $name }}" @endif
                @if ($inputId) id="{{ $inputId }}" @endif
                @if ($inputValue !== null) value="{{ $inputValue }}" @endif
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @if ($placeholderEn) data-placeholder-en="{{ $placeholderEn }}" data-placeholder-bn="{{ $placeholder }}" @endif
                @if ($required) required @endif
                @if ($disabled) disabled @endif
                @if ($readonly) readonly @endif
                {{ $attributes->merge(['class' => implode(' ', $controlClasses)]) }}
            />

            @if ($loading)
                <span class="form-input-icon form-input-icon-right">
                    <x-core::icon name="spinner" class="btn-spinner" />
                </span>
            @elseif ($passwordToggle)
                <button
                    type="button"
                    class="form-input-btn"
                    tabindex="-1"
                    onclick="const inp=this.previousElementSibling; if(inp.type==='password'){inp.type='text'; this.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'app-icon\'><path d=\'M9.88 9.88a3 3 0 1 0 4.24 4.24M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61M2 2l20 20\'/></svg>';} else {inp.type='password'; this.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'app-icon\'><path d=\'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/></svg>';}"
                    title="Show/Hide Password"
                >
                    <x-core::icon name="eye" size="14" />
                </button>
            @elseif ($clearable)
                <button
                    type="button"
                    class="form-input-btn"
                    tabindex="-1"
                    onclick="const inp=this.previousElementSibling; inp.value=''; inp.focus(); inp.dispatchEvent(new Event('input'));"
                    title="Clear"
                >
                    <x-core::icon name="x" size="14" />
                </button>
            @elseif ($iconRight)
                <span class="form-input-icon form-input-icon-right">
                    <x-core::icon :name="$iconRight" />
                </span>
            @endif

            @if ($hasStepper)
                <div class="form-input-stepper" tabindex="-1">
                    <button type="button" class="form-stepper-btn form-stepper-up" tabindex="-1" aria-label="Increase">
                        <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                    </button>
                    <button type="button" class="form-stepper-btn form-stepper-down" tabindex="-1" aria-label="Decrease">
                        <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                </div>
            @endif

            @if ($rightAddon)
                <span class="form-input-addon form-input-addon-right">{{ $rightAddon }}</span>
            @endif
        </div>
    </x-core::form-group>
@else
    <div class="{{ implode(' ', $groupClasses) }}">
        @if ($leftAddon)
            <span class="form-input-addon form-input-addon-left">{{ $leftAddon }}</span>
        @endif

        @if ($leftIcon)
            <span class="form-input-icon form-input-icon-left">
                <x-core::icon :name="$leftIcon" />
            </span>
        @endif

        <input
            type="{{ $type }}"
            @if ($name) name="{{ $name }}" @endif
            @if ($inputId) id="{{ $inputId }}" @endif
            @if ($inputValue !== null) value="{{ $inputValue }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($placeholderEn) data-placeholder-en="{{ $placeholderEn }}" data-placeholder-bn="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif
            {{ $attributes->merge(['class' => implode(' ', $controlClasses)]) }}
        />

        @if ($loading)
            <span class="form-input-icon form-input-icon-right">
                <x-core::icon name="spinner" class="btn-spinner" />
            </span>
        @elseif ($passwordToggle)
            <button
                type="button"
                class="form-input-btn"
                tabindex="-1"
                onclick="const inp=this.previousElementSibling; if(inp.type==='password'){inp.type='text'; this.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'app-icon\'><path d=\'M9.88 9.88a3 3 0 1 0 4.24 4.24M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61M2 2l20 20\'/></svg>';} else {inp.type='password'; this.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'app-icon\'><path d=\'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/></svg>';}"
                title="Show/Hide Password"
            >
                <x-core::icon name="eye" size="14" />
            </button>
        @elseif ($clearable)
            <button
                type="button"
                class="form-input-btn"
                tabindex="-1"
                onclick="const inp=this.previousElementSibling; inp.value=''; inp.focus(); inp.dispatchEvent(new Event('input'));"
                title="Clear"
            >
                <x-core::icon name="x" size="14" />
            </button>
        @elseif ($iconRight)
            <span class="form-input-icon form-input-icon-right">
                <x-core::icon :name="$iconRight" />
            </span>
        @endif

        @if ($hasStepper)
            <div class="form-input-stepper" tabindex="-1">
                <button type="button" class="form-stepper-btn form-stepper-up" tabindex="-1" aria-label="Increase">
                    <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                </button>
                <button type="button" class="form-stepper-btn form-stepper-down" tabindex="-1" aria-label="Decrease">
                    <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>
        @endif

        @if ($rightAddon)
            <span class="form-input-addon form-input-addon-right">{{ $rightAddon }}</span>
        @endif
    </div>
@endif
