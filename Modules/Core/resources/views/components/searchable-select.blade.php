@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => 'খুঁজুন...',
    'placeholderEn' => 'Search...',
    'size' => 'md',
    'required' => false,
    'label' => null,
    'labelEn' => null,
    'noMargin' => false,
])

{{-- A dropdown with type-to-search: the chosen option's key is submitted as `name`. --}}
@php
    $inputId = $id ?? ($name ? 'form-field-' . str_replace(['[', ']', '.'], ['-', '', '-'], $name) : 'searchable-' . uniqid());
    $resolvedLabelEn = $labelEn ?? $attributes->get('label-en') ?? null;
    $resolvedPlaceholderEn = $attributes->get('placeholder-en') ?? $placeholderEn;
    $selectedValue = $value ?? ($name ? old($name) : null);
    $selectedLabel = '';
    foreach ($options as $optionKey => $optionLabel) {
        if ((string) $optionKey === (string) $selectedValue) {
            $selectedLabel = $optionLabel;
        }
    }
@endphp

<x-core::form-group :name="$name" :id="$inputId" :label="$label" :label-en="$resolvedLabelEn" :required="$required" :no-margin="$noMargin">
    <div {{ $attributes->except(['label-en', 'placeholder-en'])->merge(['class' => 'searchable-select']) }} data-searchable-select @if ($required) data-required @endif>
        <input type="hidden" @if ($name) name="{{ $name }}" @endif value="{{ $selectedLabel === '' ? '' : $selectedValue }}" data-searchable-value>
        <x-core::input
            :id="$inputId"
            :size="$size"
            :no-margin="true"
            :error="false"
            icon="search"
            :value="$selectedLabel"
            :placeholder="$placeholder"
            :placeholder-en="$resolvedPlaceholderEn"
            :required="$required"
            autocomplete="off"
            role="combobox"
            data-searchable-input
        />
        <div class="searchable-select-menu" role="listbox" hidden>
            @foreach ($options as $optionKey => $optionLabel)
                <div class="searchable-select-item @if ((string) $optionKey === (string) $selectedValue) is-selected @endif" role="option" data-value="{{ $optionKey }}" data-label="{{ $optionLabel }}">{{ $optionLabel }}</div>
            @endforeach
            <div class="searchable-select-empty" hidden><span class="bn">কিছু পাওয়া যায়নি</span><span class="en" style="display:none;">Nothing found</span></div>
        </div>
    </div>
</x-core::form-group>

@once
    @push('styles')
        <style>
            .searchable-select { position: relative; }
            .searchable-select-menu { position: absolute; left: 0; right: 0; top: 100%; z-index: 30; max-height: 240px; overflow-y: auto; margin-top: 4px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; color: var(--ink-900); box-shadow: var(--shadow-card); }
            .searchable-select-item { padding: 7px 10px; font-size: 13px; cursor: pointer; }
            .searchable-select-item:hover, .searchable-select-item.is-active { background: var(--paper); }
            .searchable-select-item.is-selected { font-weight: 700; }
            .searchable-select-empty { padding: 10px; font-size: 12px; color: var(--ink-400); }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function () {
                var root = '[data-searchable-select]';

                function parts($select) {
                    return {
                        input: $select.find('[data-searchable-input]'),
                        value: $select.find('[data-searchable-value]'),
                        menu: $select.find('.searchable-select-menu'),
                        items: $select.find('.searchable-select-item'),
                        empty: $select.find('.searchable-select-empty')
                    };
                }

                function open($select, term) {
                    var p = parts($select);
                    var shown = 0;
                    term = $.trim(term || '').toLowerCase();
                    p.items.each(function () {
                        var match = term === '' || String($(this).data('label')).toLowerCase().indexOf(term) !== -1;
                        $(this).prop('hidden', !match).removeClass('is-active');
                        shown += match ? 1 : 0;
                    });
                    p.empty.prop('hidden', shown > 0);
                    p.menu.prop('hidden', false);
                }

                // Closing puts back the chosen option's name; emptied text clears an optional choice.
                function close($select) {
                    var p = parts($select);
                    if (p.menu.prop('hidden')) {
                        return;
                    }
                    p.menu.prop('hidden', true);
                    if ($.trim(p.input.val()) === '' && !$select.is('[data-required]')) {
                        choose($select, null);
                    } else {
                        p.input.val(p.items.filter('.is-selected').data('label') || '');
                    }
                }

                function choose($select, $item) {
                    var p = parts($select);
                    var value = $item ? String($item.data('value')) : '';
                    var changed = p.value.val() !== value;
                    p.items.removeClass('is-selected');
                    if ($item) {
                        $item.addClass('is-selected');
                    }
                    p.input.val($item ? $item.data('label') : '');
                    p.menu.prop('hidden', true);
                    if (changed) {
                        p.value.val(value).trigger('change');
                    }
                }

                $(document)
                    .on('focus click', root + ' [data-searchable-input]', function () {
                        var $select = $(this).closest(root);
                        if (parts($select).menu.prop('hidden')) {
                            // Everything is listed first; typing narrows it.
                            open($select, '');
                            this.select();
                        }
                    })
                    .on('input', root + ' [data-searchable-input]', function () {
                        open($(this).closest(root), $(this).val());
                    })
                    .on('keydown', root + ' [data-searchable-input]', function (event) {
                        var $select = $(this).closest(root);
                        var p = parts($select);
                        var $visible = p.items.filter(':not([hidden])');
                        var index = $visible.index($visible.filter('.is-active'));

                        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                            event.preventDefault();
                            if (p.menu.prop('hidden')) {
                                open($select, '');
                                $visible = p.items;
                                index = -1;
                            }
                            if (!$visible.length) {
                                return;
                            }
                            index = event.key === 'ArrowDown' ? (index + 1) % $visible.length : (index <= 0 ? $visible.length - 1 : index - 1);
                            p.items.removeClass('is-active');
                            $visible.eq(index).addClass('is-active')[0].scrollIntoView({ block: 'nearest' });
                        } else if (event.key === 'Enter' && !p.menu.prop('hidden')) {
                            event.preventDefault();
                            if (index >= 0) {
                                choose($select, $visible.eq(index));
                            } else if ($visible.length === 1) {
                                choose($select, $visible.eq(0));
                            }
                        } else if (event.key === 'Escape' && !p.menu.prop('hidden')) {
                            event.stopPropagation();
                            close($select);
                        } else if (event.key === 'Tab') {
                            close($select);
                        }
                    })
                    .on('mousedown', root + ' .searchable-select-item', function (event) {
                        event.preventDefault();
                        choose($(this).closest(root), $(this));
                    })
                    .on('mousedown', function (event) {
                        $(root).not($(event.target).closest(root)).each(function () {
                            close($(this));
                        });
                    });
            });
        </script>
    @endpush
@endonce
