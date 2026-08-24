<x-core::layout
    title="নতুন পণ্য"
    title-en="New Product"
    subtitle="একটি নতুন পণ্য যোগ করুন"
    subtitle-en="Add a new product"
    active="products"
>
    <x-product::tabbar active="products" />

    <div class="panel" style="margin-top:0; max-width:720px;">
        <div class="panel-head">
            <div class="panel-title bn">পণ্যের তথ্য</div>
            <div class="panel-title en" style="display:none;">Product Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @csrf
                @include('product::products._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
