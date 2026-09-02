<x-core::button-group size="xs" aria-label="Batch Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-batch"
        data-id="{{ $batch->id }}"
        data-product-id="{{ $batch->product_id }}"
        data-batch-no="{{ $batch->batch_no }}"
        data-mfg-date="{{ optional($batch->mfg_date)->format('Y-m-d') }}"
        data-expiry-date="{{ optional($batch->expiry_date)->format('Y-m-d') }}"
        data-quantity="{{ $batch->quantity }}"
        data-action="{{ route('batches.update', $batch) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('batches.destroy', $batch) }}"
        class="delete-form"
        data-title="ব্যাচ মুছে ফেলতে চান?"
        data-text="এই ব্যাচটি মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
