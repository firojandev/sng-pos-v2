<x-core::button-group size="xs" aria-label="Sub-category Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-subcategory"
        data-id="{{ $subCategory->id }}"
        data-parent-id="{{ $subCategory->parent_id }}"
        data-name="{{ $subCategory->name }}"
        data-description="{{ $subCategory->description }}"
        data-action="{{ route('sub-categories.update', $subCategory) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('sub-categories.destroy', $subCategory) }}"
        class="delete-form"
        data-title="সাব-ক্যাটাগরি মুছে ফেলতে চান?"
        data-text="এই সাব-ক্যাটাগরিটি মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
