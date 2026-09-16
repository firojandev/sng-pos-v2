<x-core::layout
    title="নতুন ব্র্যান্ড"
    title-en="New Brand"
    subtitle="একটি নতুন ব্র্যান্ড তৈরি করুন"
    subtitle-en="Create a new brand"
    active="products"
>
    <x-product::tabbar active="brands" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ব্র্যান্ডের তথ্য</div>
            <div class="panel-title en" style="display:none;">Brand Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('brands.store') }}">
                @csrf
                @include('product::brands._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" size="sm" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                    <x-core::button as="a" href="{{ route('brands.index') }}" variant="secondary" size="sm" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
