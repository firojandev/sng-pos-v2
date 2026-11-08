<x-core::layout
    title="পণ্য সম্পাদনা"
    title-en="Edit Product"
    subtitle="পণ্যের তথ্য হালনাগাদ করুন"
    subtitle-en="Update product details and inventory"
    active="products"
>
    <x-product::tabbar active="products" />

    <section style="background:var(--card); border:1px solid var(--border); padding:16px; margin:16px 0;">
        <h3>বারকোড / Barcode</h3>
        <p>{{ $product->barcode ?: 'No barcode assigned' }}</p>
        @can('products.view')
            <x-core::button size="sm" icon="barcode" :href="route('barcodes.index', ['product_id' => $product->id])">Generate / Preview Barcode</x-core::button>
        @endcan
    </section>

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" id="product-form">
        @csrf
        @method('PUT')
        @include('product::products._form')
    </form>
</x-core::layout>
