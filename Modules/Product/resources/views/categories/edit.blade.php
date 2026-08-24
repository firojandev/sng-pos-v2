<x-core::layout
    title="ক্যাটাগরি সম্পাদনা"
    title-en="Edit Category"
    subtitle="ক্যাটাগরির তথ্য হালনাগাদ করুন"
    subtitle-en="Update category details"
    active="products"
>
    <x-product::tabbar active="categories" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ক্যাটাগরির তথ্য</div>
            <div class="panel-title en" style="display:none;">Category Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf
                @method('PUT')
                @include('product::categories._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
