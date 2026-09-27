@php
    $canChangeProduct = ! $product->isShared() || auth()->user()?->isSuperAdmin();
@endphp
<x-core::button-group size="xs" aria-label="Product Actions">
    @can('products.view')
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
    @endcan
    @can('products.edit')
        <x-core::button
            :href="route('products.shop-price.edit', $product)"
            variant="soft"
            color="teal"
            icon="tag"
            icon-only
            title="দোকানের নিজস্ব মূল্য / Shop Price"
        />
        @if (! $product->isShared() && ! $product->suggested_at)
            <form method="POST" action="{{ route('products.suggest', $product) }}">
                @csrf
                <x-core::button
                    type="submit"
                    variant="soft"
                    color="blue"
                    icon="send"
                    icon-only
                    title="শেয়ার্ড ক্যাটালগে প্রস্তাব করুন / Suggest to Shared Catalogue"
                />
            </form>
        @endif
    @endcan
    @if ($canChangeProduct)
    @can('products.edit')
        <x-core::button
            :href="route('products.edit', $product)"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            title="সম্পাদনা / Edit"
        />
    @endcan
    @can('products.delete')
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
    @endcan
    @endif
</x-core::button-group>
