@props([
    'name' => 'status',
    'id' => null,
    'value' => null,
    'activeVal' => 'active',
    'inactiveVal' => 'inactive',
    'activeLabel' => 'সক্রিয় (Active)',
    'activeLabelEn' => 'Active',
    'inactiveLabel' => 'নিষ্ক্রিয় (Inactive)',
    'inactiveLabelEn' => 'Inactive',
    'activeIcon' => 'check-circle',
    'inactiveIcon' => 'x-circle',
    'size' => 'md',
    'fullWidth' => true,
    'disabled' => false,
    'label' => null,
    'labelEn' => null,
])

<x-core::status-toggle
    :name="$name"
    :id="$id"
    :value="$value"
    :active-val="$activeVal"
    :inactive-val="$inactiveVal"
    :active-label="$activeLabel"
    :active-label-en="$activeLabelEn"
    :inactive-label="$inactiveLabel"
    :inactive-label-en="$inactiveLabelEn"
    :active-icon="$activeIcon"
    :inactive-icon="$inactiveIcon"
    :size="$size"
    :full-width="$fullWidth"
    :disabled="$disabled"
    :label="$label"
    :label-en="$labelEn"
    {{ $attributes }}
/>
