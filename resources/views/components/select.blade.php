@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'size' => 'md',
    'variant' => 'outline',
    'color' => 'teal',
    'rounded' => 'default',
    'icon' => null,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
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
    $selectedValue = $value;
    if ($name && $value === null) {
        $selectedValue = old($name);
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
    $controlClasses = ['form-control', 'form-select'];
    
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
    if ($icon) $groupClasses[] = 'has-icon-left';
    if ($hasError) $groupClasses[] = 'is-invalid';
    if (in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'])) $groupClasses[] = 'form-input-group-' . $size;
    if ($rounded === 'pill') $groupClasses[] = 'form-rounded-pill';

    $hasWrapper = (bool) ($label || $helper || $hasError);
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
                <span class="form-input-icon form-input-icon-left">
                    <x-icon :name="$icon" />
                </span>
            @endif

            <select
                @if ($name) name="{{ $name }}" @endif
                @if ($inputId) id="{{ $inputId }}" @endif
                @if ($required) required @endif
                @if ($disabled) disabled @endif
                @if ($multiple) multiple @endif
                {{ $attributes->merge(['class' => implode(' ', $controlClasses)]) }}
            >
                @if ($placeholder)
                    <option value="" @if ($selectedValue === null || $selectedValue === '') selected @endif disabled>
                        {{ $placeholder }}
                    </option>
                @endif

                @if (!empty($options))
                    @foreach ($options as $optKey => $optVal)
                        @php
                            $optKeyStr = (string) $optKey;
                            $isSelected = false;
                            if (is_array($selectedValue)) {
                                $isSelected = in_array($optKeyStr, array_map('strval', $selectedValue));
                            } else {
                                $isSelected = ((string) $selectedValue === $optKeyStr);
                            }
                        @endphp
                        <option value="{{ $optKey }}" @if ($isSelected) selected @endif>
                            {{ $optVal }}
                        </option>
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </select>
        </div>
    </x-form-group>
@else
    <div class="{{ implode(' ', $groupClasses) }}">
        @if ($icon)
            <span class="form-input-icon form-input-icon-left">
                <x-icon :name="$icon" />
            </span>
        @endif

        <select
            @if ($name) name="{{ $name }}" @endif
            @if ($inputId) id="{{ $inputId }}" @endif
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            @if ($multiple) multiple @endif
            {{ $attributes->merge(['class' => implode(' ', $controlClasses)]) }}
        >
            @if ($placeholder)
                <option value="" @if ($selectedValue === null || $selectedValue === '') selected @endif disabled>
                    {{ $placeholder }}
                </option>
            @endif

            @if (!empty($options))
                @foreach ($options as $optKey => $optVal)
                    @php
                        $optKeyStr = (string) $optKey;
                        $isSelected = false;
                        if (is_array($selectedValue)) {
                            $isSelected = in_array($optKeyStr, array_map('strval', $selectedValue));
                        } else {
                            $isSelected = ((string) $selectedValue === $optKeyStr);
                        }
                    @endphp
                    <option value="{{ $optKey }}" @if ($isSelected) selected @endif>
                        {{ $optVal }}
                    </option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>
    </div>
@endif
