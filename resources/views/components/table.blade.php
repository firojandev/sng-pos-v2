@props([
    'id' => null,
    'variant' => 'card', // 'card', 'default', 'striped', 'bordered', 'borderless', 'flush'
    'size' => 'md', // 'xs', 'sm', 'md', 'lg'
    'color' => 'teal', // 'teal', 'gold', 'dark', 'blue', 'green'
    'striped' => false,
    'hoverable' => true,
    'bordered' => false,
    'borderless' => false,
    'responsive' => true,
    'stickyHeader' => false,
    'maxHeight' => null,
    'title' => null,
    'subtitle' => null,
    'searchable' => false,
    'searchPlaceholder' => 'খুঁজুন / Search...',
    'empty' => false,
    'emptyTitle' => 'কোনো তথ্য পাওয়া যায়নি',
    'emptyDescription' => null,
    'emptyIcon' => 'box',
    'loading' => false,
    'datatable' => false,
    'ajaxUrl' => null,
])

@php
    $isCard = $variant === 'card' || $variant === 'default';
    $isFlush = $variant === 'flush';
    $isStriped = $striped || $variant === 'striped';
    $isBordered = $bordered || $variant === 'bordered';
    $isBorderless = $borderless || $variant === 'borderless';

    // Container classes
    $containerClasses = ['table-container'];
    if ($isFlush) $containerClasses[] = 'table-flush';
    if ($size !== 'md') $containerClasses[] = 'table-container-' . $size;
    if ($color) $containerClasses[] = 'table-' . $color;

    // Table element classes
    $tableClasses = ['app-table'];
    if ($isStriped) $tableClasses[] = 'app-table-striped';
    if ($isBordered) $tableClasses[] = 'app-table-bordered';
    if ($isBorderless) $tableClasses[] = 'app-table-borderless';
    if ($hoverable) $tableClasses[] = 'app-table-hover';
    if ($datatable) $tableClasses[] = 'dataTable';

    $tableId = $id ?? ($datatable ? 'datatable-' . uniqid() : null);
@endphp

<div {{ $attributes->merge(['class' => implode(' ', $containerClasses)]) }}>
    {{-- Top Toolbar / Title Bar --}}
    @if ($title || $subtitle || $searchable || isset($actions))
        <div class="table-toolbar">
            <div class="table-toolbar-start">
                @if ($title || $subtitle)
                    <div class="table-title-wrap">
                        @if ($title)
                            <div class="table-title">{{ $title }}</div>
                        @endif
                        @if ($subtitle)
                            <div class="table-subtitle">{{ $subtitle }}</div>
                        @endif
                    </div>
                @endif

                @if ($searchable)
                    <div class="table-search-input">
                        <x-core::input
                            type="search"
                            size="sm"
                            icon="search"
                            clearable
                            placeholder="{{ $searchPlaceholder }}"
                            class="table-quick-search"
                            data-target="{{ $tableId }}"
                            no-margin
                        />
                    </div>
                @endif
            </div>

            @if (isset($actions))
                <div class="table-toolbar-end">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    {{-- Bulk Action Bar (Revealed when rows are selected) --}}
    @if (isset($bulkActions))
        <div class="table-bulk-bar" style="display: none;" data-table-bulk-bar>
            <div class="table-bulk-info">
                <x-core::icon name="check-circle" size="16" />
                <span><strong class="selected-count">0</strong> টি আইটেম নির্বাচিত (selected)</span>
            </div>
            <div class="table-bulk-actions">
                {{ $bulkActions }}
            </div>
        </div>
    @endif

    {{-- Table Responsive Area --}}
    <div
        class="{{ $responsive ? 'table-responsive' : '' }} {{ $stickyHeader ? 'table-sticky-header' : '' }}"
        @if ($stickyHeader && $maxHeight) style="max-height: {{ $maxHeight }};" @endif
    >
        <table
            @if ($tableId) id="{{ $tableId }}" @endif
            class="{{ implode(' ', $tableClasses) }}"
            @if ($datatable) data-datatable="true" @endif
            @if ($ajaxUrl) data-ajax-url="{{ $ajaxUrl }}" @endif
        >
            @if (isset($header))
                <thead>
                    <tr>
                        {{ $header }}
                    </tr>
                </thead>
            @endif

            <tbody>
                @if ($empty && !isset($emptySlot))
                    <tr>
                        <td colspan="100">
                            <div class="table-empty">
                                <div class="table-empty-icon">
                                    <x-core::icon :name="$emptyIcon" size="24" />
                                </div>
                                <div class="table-empty-title">{{ $emptyTitle }}</div>
                                @if ($emptyDescription)
                                    <div class="table-empty-desc">{{ $emptyDescription }}</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>

            @if (isset($footer))
                <tfoot>
                    {{ $footer }}
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Bottom Footer / Pagination Bar --}}
    @if (isset($pagination) || isset($paginationInfo))
        <div class="table-footer">
            @if (isset($paginationInfo))
                <div class="table-pagination-info">
                    {{ $paginationInfo }}
                </div>
            @else
                <div></div>
            @endif

            @if (isset($pagination))
                <div class="table-pagination">
                    {{ $pagination }}
                </div>
            @endif
        </div>
    @endif
</div>
