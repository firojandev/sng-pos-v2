<x-core::layout
    title="নতুন সরবরাহকারী"
    title-en="New Supplier"
    subtitle="একটি নতুন সরবরাহকারী রেকর্ড তৈরি করুন"
    subtitle-en="Create a new supplier record"
    active="suppliers"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">সরবরাহকারীর তথ্য</div>
            <div class="panel-title en" style="display:none;">Supplier Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                @include('supplier::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en" style="display:none;">Save Supplier</span>
                    </x-core::button>
                    <x-core::button type="button" variant="secondary" :href="route('suppliers.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
