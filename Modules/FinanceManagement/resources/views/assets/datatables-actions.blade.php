<x-core::button-group size="xs" aria-label="Asset Actions">
    @can('assets.edit')
        <x-core::button
            type="button"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            class="btn-edit-asset"
            data-id="{{ $asset->id }}"
            data-name="{{ $asset->name }}"
            data-amount="{{ $asset->amount }}"
            data-note="{{ $asset->note }}"
            data-action="{{ route('assets.update', $asset) }}"
            data-url="{{ route('assets.edit', $asset) }}"
            title="সম্পাদনা / Edit"
        />
    @endcan
    @can('assets.delete')
        <form
            method="POST"
            action="{{ route('assets.destroy', $asset) }}"
            class="delete-form"
            data-title="সম্পদ মুছে ফেলতে চান?"
            data-text="এই সম্পদের রেকর্ড মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
