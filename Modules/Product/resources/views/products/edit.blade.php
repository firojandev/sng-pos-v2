<x-core::layout
    title="পণ্য সম্পাদনা"
    title-en="Edit Product"
    subtitle="পণ্যের তথ্য হালনাগাদ করুন"
    subtitle-en="Update product details"
    active="products"
>
    <x-product::tabbar active="products" />

    <div class="panel" style="margin-top:0; max-width:720px;">
        <div class="panel-head">
            <div class="panel-title bn">পণ্যের তথ্য</div>
            <div class="panel-title en" style="display:none;">Product Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('product::products._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
