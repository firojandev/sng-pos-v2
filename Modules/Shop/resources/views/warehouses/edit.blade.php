<x-core::layout
    title="গুদাম সম্পাদনা"
    title-en="Edit Warehouse"
    subtitle="গুদামের তথ্য হালনাগাদ করুন"
    subtitle-en="Update the warehouse details"
    active="branches"
>
    <x-shop::tabbar active="warehouses" />

    <div class="panel" style="margin-top:0; max-width:560px;">
        <div class="panel-head">
            <div class="panel-title">
                <span class="bn">গুদামের তথ্য</span>
                <span class="en" style="display:none;">Warehouse Details</span>
            </div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
                @csrf
                @method('PUT')
                @include('shop::warehouses._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button
                        type="submit"
                        variant="solid"
                        color="primary"
                        style="flex:1; justify-content:center;"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                    <x-core::button
                        as="a"
                        href="{{ route('warehouses.index') }}"
                        variant="secondary"
                        style="flex:1; justify-content:center;"
                    >
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
