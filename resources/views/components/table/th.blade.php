@props([
    'align' => 'left', // 'left', 'center', 'right'
    'sortable' => false,
    'direction' => null, // 'asc', 'desc', null
    'width' => null,
    'icon' => null,
    'checkbox' => false,
    'class' => '',
])

@php
    $classes = [$class];
    if ($align) $classes[] = 'table-cell-' . $align;
    if ($sortable) $classes[] = 'sortable';
    if ($direction === 'asc') $classes[] = 'sorted-asc';
    if ($direction === 'desc') $classes[] = 'sorted-desc';
    if ($checkbox) $classes[] = 'table-check-col';
@endphp

<th
    {{ $attributes->merge(['class' => trim(implode(' ', array_filter($classes)))]) }}
    @if ($width) style="width: {{ $width }};" @endif
>
    @if ($checkbox)
        <label class="form-check form-check-sm" style="margin: 0; justify-content: center;">
            <input type="checkbox" data-table-select-all />
            <span class="form-check-box">
                <x-core::icon name="check" size="12" />
            </span>
        </label>
    @else
        <div class="th-wrap {{ $align === 'right' ? 'justify-end' : ($align === 'center' ? 'justify-center' : '') }}">
            @if ($icon)
                <x-core::icon :name="$icon" size="14" style="color: var(--teal-800);" />
            @endif
            <span>{{ $slot }}</span>
            @if ($sortable)
                <span class="sort-icon">
                    @if ($direction === 'asc')
                        <x-core::icon name="chevron-up" size="14" />
                    @elseif ($direction === 'desc')
                        <x-core::icon name="chevron-down" size="14" />
                    @else
                        <x-core::icon name="chevron-down" size="13" style="opacity: 0.5;" />
                    @endif
                </span>
            @endif
        </div>
    @endif
</th>
