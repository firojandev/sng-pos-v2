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
                    <x-core::button type="submit" color="primary" size="sm" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update</span>
                    </x-core::button>
                    <x-core::button as="a" href="{{ route('units.index') }}" variant="secondary" size="sm" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
