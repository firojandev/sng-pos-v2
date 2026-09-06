<x-core::button-group size="xs" aria-label="Expense Sub-category Actions">
    @can('expense.edit')
        <x-core::button
            type="button"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            class="btn-edit-expense-subcategory"
            data-id="{{ $subCategory->id }}"
            data-parent-id="{{ $subCategory->parent_id }}"
            data-name="{{ $subCategory->name }}"
            data-description="{{ $subCategory->description }}"
            data-action="{{ route('expense-sub-categories.update', $subCategory) }}"
            title="সম্পাদনা / Edit"
        />
    @endcan
    @can('expense.delete')
        <form
            method="POST"
            action="{{ route('expense-sub-categories.destroy', $subCategory) }}"
            class="delete-form"
            data-title="সাব-ক্যাটাগরি মুছে ফেলতে চান?"
            data-text="এই সাব-ক্যাটাগরিটি মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
    @endcan
</x-core::button-group>
