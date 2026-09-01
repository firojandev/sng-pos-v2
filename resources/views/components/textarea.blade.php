@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
    'resize' => 'vertical',
    'maxLength' => null,
    'showCount' => false,
    'size' => 'md',
    'variant' => 'outline',
    'color' => 'teal',
    'rounded' => 'default',
    'icon' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
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
    $textValue = $value;
    if ($name && $value === null) {
        $textValue = old($name);
    }

    $hasError = (bool) ($error || ($name && isset($errors) && $errors->has($name)));
    $errorMessage = $error ?? ($name && isset($errors) && $errors->has($name) ? $errors->first($name) : null);

    // Color Normalization
    $colorAliases = [
        'primary' => 'gold',
        'brand' => 'teal',
        'success' => 'green',
        'danger' => 'red',
        'info' => 'blue',
        'ink' => 'dark',
    ];
    $resolvedColor = $colorAliases[$color] ?? $color;

    // Control classes
    $controlClasses = ['form-control', 'form-textarea'];
    
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

    // Resize
    if ($resize === 'none') {
        $controlClasses[] = 'form-textarea-no-resize';
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
    if ($rounded === 'none') {
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
    if ($icon) $groupClasses[] = 'has-icon-left';
    if ($hasError) $groupClasses[] = 'is-invalid';
    if (in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'])) $groupClasses[] = 'form-input-group-' . $size;

    $hasWrapper = (bool) ($label || $helper || $hasError || $showCount);
@endphp

@if ($hasWrapper)
    <x-form-group
        :name="$name"
        :id="$inputId"
        :label="$label"
        :label-en="$labelEn"
        :required="$required"
        :optional="$optional"
        :icon="$icon"
        :helper="$helper"
        :helper-variant="$helperVariant"
        :error="$errorMessage"
        :no-margin="$noMargin"
    >
        <div class="{{ implode(' ', $groupClasses) }}">
            @if ($icon)
                <span class="form-input-icon form-input-icon-left" style="align-items:flex-start; padding-top:10px;">
                    <x-icon :name="$icon" />
                </span>
            @endif

            <textarea
                @if ($name) name="{{ $name }}" @endif
                @if ($inputId) id="{{ $inputId }}" @endif
                rows="{{ $rows }}"
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @if ($maxLength) maxlength="{{ $maxLength }}" @endif
                @if ($required) required @endif
                @if ($disabled) disabled @endif
                @if ($readonly) readonly @endif
                @if ($showCount) oninput="const c=this.parentElement.nextElementSibling; if(c && c.classList.contains('form-textarea-count')) c.querySelector('.count-val').textContent=this.value.length;" @endif
                {{ $attributes->merge(['class' => implode(' ', $controlClasses)]) }}
            >{{ $textValue ?? (string) $slot }}</textarea>
        </div>

        @if ($showCount)
            <div class="form-textarea-count">
                <span class="count-val">{{ mb_strlen((string) ($textValue ?? $slot)) }}</span>@if ($maxLength)/{{ $maxLength }}@endif characters
            </div>
        @endif
    </x-form-group>
@else
    <div class="{{ implode(' ', $groupClasses) }}">
        @if ($icon)
            <span class="form-input-icon form-input-icon-left" style="align-items:flex-start; padding-top:10px;">
                <x-icon :name="$icon" />
            </span>
        @endif

        <textarea
            @if ($name) name="{{ $name }}" @endif
            @if ($inputId) id="{{ $inputId }}" @endif
            rows="{{ $rows }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($maxLength) maxlength="{{ $maxLength }}" @endif
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif
            {{ $attributes->merge(['class' => implode(' ', $controlClasses)]) }}
        >{{ $textValue ?? (string) $slot }}</textarea>
    </div>
@endif
