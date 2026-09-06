@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'placeholderEn' => null,
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

    $formatOption = function ($key, $val, $selectedVal) {
        $keyStr = (string) $key;
        $isSelected = false;
        if (is_array($selectedVal)) {
            $isSelected = in_array($keyStr, array_map('strval', $selectedVal));
        } else {
            $isSelected = ((string) $selectedVal === $keyStr);
        }

        $textBn = null;
        $textEn = null;
        if (is_array($val)) {
            $textBn = $val['bn'] ?? ($val[0] ?? '');
            $textEn = $val['en'] ?? ($val[1] ?? $textBn);
        } elseif (is_string($val) && preg_match('/^(--\s*)?(.+?)\s*\(([^)]+)\)(\s*--)?$/u', $val, $m)) {
            $prefix = $m[1] ?? '';
            $part1 = trim($m[2] ?? '');
            $part2 = trim($m[3] ?? '');
            $suffix = $m[4] ?? '';
            if (preg_match('/[\x{0980}-\x{09FF}]/u', $part1) && preg_match('/[a-zA-Z]/', $part2)) {
                $textBn = $prefix . $part1 . $suffix;
                $textEn = $prefix . $part2 . $suffix;
            }
        }

        $display = $textBn ?? (is_array($val) ? ($val['bn'] ?? '') : $val);

        $attrs = ' value="' . e($key) . '"';
        if ($isSelected) {
            $attrs .= ' selected';
        }
        if ($textBn && $textEn) {
            $attrs .= ' data-text-bn="' . e($textBn) . '" data-text-en="' . e($textEn) . '"';
        }

        return '<option' . $attrs . '>' . e($display) . '</option>';
    };

    $formatPlaceholder = function ($pl, $plEn, $selectedVal) {
        $pBn = $pl;
        $pEn = $plEn;
        if (! $pEn && is_string($pl) && preg_match('/^(--\s*)?(.+?)\s*\(([^)]+)\)(\s*--)?$/u', $pl, $pm)) {
            $pBn = ($pm[1] ?? '') . trim($pm[2] ?? '') . ($pm[4] ?? '');
            $pEn = ($pm[1] ?? '') . trim($pm[3] ?? '') . ($pm[4] ?? '');
        }

        $isSelected = ($selectedVal === null || $selectedVal === '');
        $attrs = ' value=""' . ($isSelected ? ' selected' : '') . ' disabled';
        if ($pEn) {
            $attrs .= ' data-text-bn="' . e($pBn) . '" data-text-en="' . e($pEn) . '"';
        }

        return '<option' . $attrs . '>' . e($pBn) . '</option>';
    };
@endphp

@if ($hasWrapper)
    <x-core::form-group
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
                    <x-core::icon :name="$icon" />
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
                    {!! $formatPlaceholder($placeholder, $placeholderEn, $selectedValue) !!}
                @endif

                @if (!empty($options))
                    @foreach ($options as $optKey => $optVal)
                        {!! $formatOption($optKey, $optVal, $selectedValue) !!}
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </select>
        </div>
    </x-core::form-group>
@else
    <div class="{{ implode(' ', $groupClasses) }}">
        @if ($icon)
            <span class="form-input-icon form-input-icon-left">
                <x-core::icon :name="$icon" />
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
                {!! $formatPlaceholder($placeholder, $placeholderEn, $selectedValue) !!}
            @endif

            @if (!empty($options))
                @foreach ($options as $optKey => $optVal)
                    {!! $formatOption($optKey, $optVal, $selectedValue) !!}
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>
    </div>
@endif
