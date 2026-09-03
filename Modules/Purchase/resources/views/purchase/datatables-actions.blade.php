<x-core::button-group size="xs" aria-label="Purchase Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="secondary"
        icon="eye"
        icon-only
        size="xs"
        class="btn-view-purchase"
        data-id="{{ $purchase->id }}"
        data-url="{{ route('purchase.show', $purchase) }}"
        title="বিস্তারিত / Details"
    />
    <x-core::button
        :href="route('purchase.edit', $purchase)"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        size="xs"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('purchase.destroy', $purchase) }}"
        class="delete-form"
        data-title="ক্রয় মুছে ফেলতে চান?"
        data-text="এই ক্রয় মুছে ফেললে স্টক থেকে পণ্য বিয়োগ হবে। আপনি কি নিশ্চিত?"
        style="display:inline-block;"
    >
        @csrf
        @method('DELETE')
        <x-core::button
            type="submit"
            variant="soft"
            color="danger"
            icon="trash-2"
            icon-only
            size="xs"
            title="মুছুন / Delete"
        />
    </form>
</x-core::button-group>
