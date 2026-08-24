<x-core::layout
    title="নতুন মডেল"
    title-en="New Model"
    subtitle="একটি নতুন পণ্য মডেল তৈরি করুন"
    subtitle-en="Create a new product model"
    active="products"
>
    <x-product::tabbar active="models" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">মডেলের তথ্য</div>
            <div class="panel-title en" style="display:none;">Model Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('models.store') }}">
                @csrf
                @include('product::models._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('models.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
