<x-core::layout
    title="পণ্য সম্পাদনা"
    title-en="Edit Product"
    subtitle="পণ্যের তথ্য হালনাগাদ করুন"
    subtitle-en="Update product details and inventory"
    active="products"
>
    <x-product::tabbar active="products" />

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" id="product-form">
        @csrf
        @method('PUT')
        @include('product::products._form')
    </form>
</x-core::layout>
