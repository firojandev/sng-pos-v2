@props([
    'hover' => true,
    'selected' => false,
    'clickable' => false,
    'class' => '',
])

@php
    $classes = [$class];
    if ($selected) $classes[] = 'is-selected';
    if ($clickable) $classes[] = 'is-clickable';
@endphp

<tr {{ $attributes->merge(['class' => trim(implode(' ', array_filter($classes)))]) }}>
    {{ $slot }}
</tr>
