<x-core::button-group size="xs" aria-label="Branch Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-branch"
        data-id="{{ $branch->id }}"
        data-url="{{ route('branches.edit', $branch) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('branches.destroy', $branch) }}"
        class="delete-form"
        data-title="শাখা মুছে ফেলতে চান?"
        data-text="এই শাখাটি মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
