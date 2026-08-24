<x-core::layout
    title="ব্র্যান্ড সম্পাদনা"
    title-en="Edit Brand"
    subtitle="ব্র্যান্ডের তথ্য হালনাগাদ করুন"
    subtitle-en="Update brand details"
    active="products"
>
    <x-product::tabbar active="brands" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ব্র্যান্ডের তথ্য</div>
            <div class="panel-title en" style="display:none;">Brand Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('brands.update', $brand) }}">
                @csrf
                @method('PUT')
                @include('product::brands._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('brands.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
