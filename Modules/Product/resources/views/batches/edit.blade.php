<x-core::layout
    title="ব্যাচ সম্পাদনা"
    title-en="Edit Batch"
    subtitle="ব্যাচের তথ্য হালনাগাদ করুন"
    subtitle-en="Update batch details"
    active="products"
>
    <x-product::tabbar active="batches" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ব্যাচের তথ্য</div>
            <div class="panel-title en" style="display:none;">Batch Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('batches.update', $batch) }}">
                @csrf
                @method('PUT')
                @include('product::batches._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('batches.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
