<x-core::button-group size="xs" aria-label="Stock Actions">
    @can('stock.adjust')
        <x-core::button
            type="button"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            class="btn-adjust-stock"
            data-product-id="{{ $product->id }}"
            data-product-name="{{ $product->name }}"
            title="স্টক সমন্বয় / Adjust Stock"
        />
    @endcan
    @can('stock.view')
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
    @can('products.view')
        <x-core::button
            :href="route('batches.index', ['product_id' => $product->id])"
            variant="soft"
            color="teal"
            icon="layers"
            icon-only
            title="ব্যাচসমূহ / Batches"
        />
    @endcan
</x-core::button-group>
