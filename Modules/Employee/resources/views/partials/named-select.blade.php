{{-- A designation/department dropdown from the company's list, with "Add new" to type a name. --}}
@php
    $selectedId = $selectedId ?? null;
    $newValue = $newValue ?? null;
    $required = $required ?? false;
    $isNew = (filled($newValue) && blank($selectedId)) || ($required && $options === []);
    $selectOptions = ['' => $required ? '-- নির্বাচন করুন (Select) --' : '-- কোনোটি নয় (None) --'] + $options + ['__new' => '+ নতুন যোগ করুন (Add new)'];
@endphp

<div data-named-field="{{ $field }}">
    <x-core::select
        name="{{ $field }}_id"
        :id="$idPrefix.'_'.$field.'_id'"
        :label="$label"
        :label-en="$labelEn"
        size="sm"
        :required="$required"
        :options="$selectOptions"
        :value="$isNew ? '__new' : (string) $selectedId"
        data-named-select
    />
    <div data-named-new @if (! $isNew) hidden @endif style="margin-top:6px;">
        <x-core::input
            name="{{ $field }}"
            :id="$idPrefix.'_'.$field"
            size="sm"
            :no-margin="true"
            :value="$isNew ? (string) $newValue : ''"
            :placeholder="$placeholder"
            :placeholder-en="$placeholderEn"
            :disabled="! $isNew"
        />
    </div>
</div>
