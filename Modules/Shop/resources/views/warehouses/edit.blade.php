<x-core::layout
    title="গুদাম সম্পাদনা"
    title-en="Edit Warehouse"
    subtitle="গুদামের তথ্য হালনাগাদ করুন"
    subtitle-en="Update the warehouse details"
    active="branches"
>
    <x-shop::tabbar active="warehouses" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">গুদামের তথ্য</div>
            <div class="panel-title en" style="display:none;">Warehouse Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
                @csrf
                @method('PUT')
                @include('shop::warehouses._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
