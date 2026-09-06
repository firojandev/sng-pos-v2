<x-core::button-group size="xs" aria-label="Shop Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="teal"
        icon="eye"
        icon-only
        class="btn-view-shop"
        data-id="{{ $shop->id }}"
        data-url="{{ route('shops.show', $shop) }}"
        title="বিস্তারিত দেখুন / View Details"
    />
    <x-core::button
        :href="route('shops.edit', $shop)"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('shops.destroy', $shop) }}"
        class="delete-shop-form"
        onsubmit="return confirm('এই দোকান ও এর সকল তথ্য মুছে ফেলতে চান? / Are you sure you want to delete this shop and all related data?');"
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
