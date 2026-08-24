<x-core::layout
    title="ইউনিট সম্পাদনা"
    title-en="Edit Unit"
    subtitle="ইউনিটের তথ্য হালনাগাদ করুন"
    subtitle-en="Update unit details"
    active="products"
>
    <x-product::tabbar active="units" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ইউনিটের তথ্য</div>
            <div class="panel-title en" style="display:none;">Unit Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('units.update', $unit) }}">
                @csrf
                @method('PUT')
                @include('product::units._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('units.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
