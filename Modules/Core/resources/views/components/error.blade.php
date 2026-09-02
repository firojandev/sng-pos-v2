@props([
    'name' => null,
    'message' => null,
])

@php
    $errorMessage = $message ?? ($name && isset($errors) ? $errors->first($name) : null);
@endphp

@if ($errorMessage)
    <div {{ $attributes->merge(['class' => 'form-error']) }}>
        <x-core::icon name="alert-triangle" size="14" />
        <span>{{ $errorMessage }}</span>
    </div>
@endif
