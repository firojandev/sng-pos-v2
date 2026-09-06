<x-core::button-group size="xs" aria-label="Model Actions">
    @can('products.edit')
        <x-core::button
            type="button"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            class="btn-edit-model"
            data-id="{{ $model->id }}"
            data-brand-id="{{ $model->brand_id }}"
            data-name="{{ $model->name }}"
            data-action="{{ route('models.update', $model) }}"
            title="সম্পাদনা / Edit"
        />
    @endcan
    @can('products.delete')
        <form
            method="POST"
            action="{{ route('models.destroy', $model) }}"
            class="delete-form"
            data-title="মডেল মুছে ফেলতে চান?"
            data-text="এই মডেলটি মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
    @endcan
</x-core::button-group>
