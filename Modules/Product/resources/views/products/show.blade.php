<x-core::layout title="পণ্যের বিবরণ" title-en="Product Details" active="products">
    <x-product::tabbar active="products" />
    <section style="background:var(--card); border:1px solid var(--border); padding:16px; margin:16px 0;">
        <h2>{{ $product->name }}</h2>
        <p>Code / SKU: {{ $product->sku ?: '—' }}</p>
        <p>Category: {{ $product->category?->name }}</p>
        <p>Selling price: {{ number_format((float) $product->sale_price, 2) }} / {{ $product->baseUnit()?->name ?? 'base unit' }}</p>
        <p>Units: {{ $product->units->pluck('name')->join(', ') }}</p>
        <x-core::badge :color="$product->status === 'active' ? 'success' : 'secondary'">{{ $product->status }}</x-core::badge>
    </section>
    <section style="background:var(--card); border:1px solid var(--border); padding:16px; margin:16px 0;">
        <h3>বারকোড / Barcode</h3>
        <p>{{ $product->barcode ?: 'No barcode assigned' }}</p>
        <p>This barcode identifies the product. Select the selling unit separately when scanning.</p>
        <x-core::button size="sm" icon="barcode" :href="route('barcodes.index', ['product_id' => $product->id])">Generate / Preview Barcode</x-core::button>
    </section>
    <x-core::button size="sm" variant="secondary" :href="route('products.index')">Back to Products</x-core::button>
</x-core::layout>
