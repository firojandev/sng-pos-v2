<x-core::layout
    title="নতুন ব্যাচ"
    title-en="New Batch"
    subtitle="একটি নতুন পণ্য ব্যাচ তৈরি করুন"
    subtitle-en="Create a new product batch"
    active="products"
>
    <x-product::tabbar active="batches" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ব্যাচের তথ্য</div>
            <div class="panel-title en" style="display:none;">Batch Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('batches.store') }}">
                @csrf
                @include('product::batches._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('batches.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
