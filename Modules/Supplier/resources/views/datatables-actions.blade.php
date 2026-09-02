<x-core::button-group size="xs" aria-label="Supplier Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-supplier"
        data-id="{{ $supplier->id }}"
        data-url="{{ route('suppliers.edit', $supplier) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('suppliers.destroy', $supplier) }}"
        class="delete-form"
        data-title="সরবরাহকারী মুছে ফেলতে চান?"
        data-text="এই সরবরাহকারীর তথ্য মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
    >
        @csrf
        @method('DELETE')
        <x-core::button
            type="submit"
            variant="soft"
            color="danger"
            icon="trash-2"
            icon-only
            title="মুছুন / Delete"
        />
    </form>
</x-core::button-group>
