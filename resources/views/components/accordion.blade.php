@props([
    'id' => null,
    'name' => null,
    'value' => '1',
    'checked' => null,
    'open' => null,
    'title' => null,
    'titleEn' => null,
    'description' => null,
    'descriptionEn' => null,
    'icon' => null,
    'type' => null, // 'checkbox', 'toggle', 'default'
    'color' => 'teal',
    'badge' => null,
    'badgeColor' => 'secondary',
    'badgeEn' => null,
    'group' => null,
    'disabled' => false,
    'flush' => false,
    'card' => false,
])

<x-core::accordion
    :id="$id"
    :name="$name"
    :value="$value"
    :checked="$checked"
    :open="$open"
    :title="$title"
    :title-en="$titleEn"
    :description="$description"
    :description-en="$descriptionEn"
    :icon="$icon"
    :type="$type"
    :color="$color"
    :badge="$badge"
    :badge-color="$badgeColor"
    :badge-en="$badgeEn"
    :group="$group"
    :disabled="$disabled"
    :flush="$flush"
    :card="$card"
    {{ $attributes }}
>
    @if (isset($header))
        <x-slot:header>{{ $header }}</x-slot:header>
    @endif
    @if (isset($badgeSlot))
        <x-slot:badgeSlot>{{ $badgeSlot }}</x-slot:badgeSlot>
    @endif
    @if (isset($actions))
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endif

    {{ $slot }}
</x-core::accordion>
