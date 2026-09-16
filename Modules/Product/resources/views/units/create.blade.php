<x-core::layout
    title="নতুন ইউনিট"
    title-en="New Unit"
    subtitle="একটি নতুন পরিমাপের ইউনিট তৈরি করুন"
    subtitle-en="Create a new measurement unit"
    active="products"
>
    <x-product::tabbar active="units" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ইউনিটের তথ্য</div>
            <div class="panel-title en" style="display:none;">Unit Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('units.store') }}">
                @csrf
                @include('product::units._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" size="sm" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
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
