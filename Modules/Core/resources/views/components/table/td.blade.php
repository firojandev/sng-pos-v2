@props([
    'align' => 'left', // 'left', 'center', 'right'
    'nowrap' => false,
    'bold' => false,
    'muted' => false,
    'truncate' => false,
    'checkbox' => false,
    'actions' => false,
    'value' => null,
    'class' => '',
])

@php
    $classes = [$class];
    if ($align && !$actions) $classes[] = 'table-cell-' . $align;
    if ($nowrap) $classes[] = 'table-cell-nowrap';
    if ($bold) $classes[] = 'table-cell-bold';
    if ($muted) $classes[] = 'table-cell-muted';
    if ($truncate) $classes[] = 'table-cell-truncate';
    if ($checkbox) $classes[] = 'table-check-col';
    if ($actions) $classes[] = 'table-cell-right';
@endphp

<td {{ $attributes->merge(['class' => trim(implode(' ', array_filter($classes)))]) }}>
    @if ($checkbox)
        <label class="form-check form-check-sm" style="margin: 0; justify-content: center;">
            <input type="checkbox" data-table-select-row value="{{ $value ?? '' }}" />
            <span class="form-check-box">
                <x-core::icon name="check" size="12" />
            </span>
        </label>
    @elseif ($actions)
        <div class="table-cell-actions">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</td>
