@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'labelEn' => null,
    'required' => false,
    'optional' => false,
    'icon' => null,
    'helper' => null,
    'helperVariant' => 'default',
    'error' => null,
    'noMargin' => false,
])

@php
    $inputId = $id ?? ($name ? 'form-field-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) : null);
    $errorMessage = $error ?? ($name && isset($errors) && $errors->has($name) ? $errors->first($name) : null);
@endphp

<div {{ $attributes->merge(['class' => 'form-group' . ($noMargin ? ' no-margin' : '')]) }}>
    @if ($label || isset($labelSlot))
        <x-label
            :for="$inputId"
            :required="$required"
            :optional="$optional"
            :icon="$icon"
            :label="$label"
            :label-en="$labelEn"
        >
            {{ $labelSlot ?? '' }}
        </x-label>
    @endif

    {{ $slot }}

    @if ($errorMessage)
        <x-error :message="$errorMessage" />
    @elseif ($helper)
        <x-helper :variant="$helperVariant">{{ $helper }}</x-helper>
    @endif
</div>
