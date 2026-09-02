<x-core::layout
    title="নতুন পণ্য"
    title-en="New Product"
    subtitle="একটি নতুন পণ্য যোগ করুন"
    subtitle-en="Add a new product to inventory"
    active="products"
>
    <x-product::tabbar active="products" />

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="product-form">
        @csrf
        @include('product::products._form')
    </form>
</x-core::layout>
