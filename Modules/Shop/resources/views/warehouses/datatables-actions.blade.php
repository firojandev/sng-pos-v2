<x-core::button-group size="xs" aria-label="Warehouse Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-warehouse"
        data-id="{{ $warehouse->id }}"
        data-url="{{ route('warehouses.edit', $warehouse) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('warehouses.destroy', $warehouse) }}"
        class="delete-form"
        data-title="গুদাম মুছে ফেলতে চান?"
        data-text="এই গুদামটি মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
    >
        @csrf
        @method('DELETE')
        <x-core::button
            type="submit"
            variant="soft"
            color="red"
            icon="trash-2"
            icon-only
            title="মুছুন / Delete"
        />
    </form>
</x-core::button-group>
