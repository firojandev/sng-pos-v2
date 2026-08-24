<x-core::layout
    title="নতুন সাব-ক্যাটাগরি"
    title-en="New Sub-category"
    subtitle="একটি নতুন সাব-ক্যাটাগরি তৈরি করুন"
    subtitle-en="Create a new sub-category"
    active="products"
>
    <x-product::tabbar active="sub-categories" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">সাব-ক্যাটাগরির তথ্য</div>
            <div class="panel-title en" style="display:none;">Sub-category Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('sub-categories.store') }}">
                @csrf
                @include('product::sub-categories._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('sub-categories.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
