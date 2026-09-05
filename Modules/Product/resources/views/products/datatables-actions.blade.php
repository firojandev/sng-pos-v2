<x-core::button-group size="xs" aria-label="Product Actions">
    <x-core::button
        :href="route('stock.history', ['product_id' => $product->id])"
        variant="soft"
        color="secondary"
        icon="history"
        icon-only
        class="btn-stock-history"
        data-id="{{ $product->id }}"
        data-url="{{ route('products.stock-history', $product) }}"
        title="স্টকের ইতিহাস / Stock History"
    />
    <x-core::button
        :href="route('products.edit', $product)"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('products.destroy', $product) }}"
        class="delete-form"
        data-title="পণ্য মুছে ফেলতে চান?"
        data-text="এই পণ্যটি মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
