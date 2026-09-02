<x-core::button-group size="xs" aria-label="Customer Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-customer"
        data-id="{{ $customer->id }}"
        data-url="{{ route('customers.edit', $customer) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('customers.destroy', $customer) }}"
        class="delete-form"
        data-title="গ্রাহক মুছে ফেলতে চান?"
        data-text="এই গ্রাহকের তথ্য মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
