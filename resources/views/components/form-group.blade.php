@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'labelEn' => null,
    'required' => false,
    'optional' => false,
    'icon' => null,
    'helper' => null,
    'helperEn' => null,
    'helperVariant' => 'default',
    'error' => null,
    'noMargin' => false,
])

@php
    $inputId = $id ?? ($name ? 'form-field-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) : null);
    $resolvedLabelEn = $labelEn ?? $attributes->get('label-en') ?? null;
    $resolvedHelperEn = $helperEn ?? $attributes->get('helper-en') ?? null;
    $errorMessage = $error ?? ($name && isset($errors) && $errors->has($name) ? $errors->first($name) : null);
@endphp

<div {{ $attributes->except(['label-en', 'helper-en'])->merge(['class' => 'form-group' . ($noMargin ? ' no-margin' : '')]) }}>
    @if ($label || $resolvedLabelEn || isset($labelSlot))
        <x-label
            :for="$inputId"
            :required="$required"
            :optional="$optional"
            :icon="$icon"
            :label="$label"
            :label-en="$resolvedLabelEn"
        >
            {{ $labelSlot ?? '' }}
        </x-label>
    @endif

    {{ $slot }}

    @if ($errorMessage)
        <x-error :message="$errorMessage" />
    @elseif ($helper || $resolvedHelperEn)
        <x-helper :variant="$helperVariant" :helper="$helper" :helper-en="$resolvedHelperEn" />
    @endif
</div>
