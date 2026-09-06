<x-core::layout
    title="গ্রাহক সম্পাদনা"
    title-en="Edit Customer"
    subtitle="গ্রাহকের তথ্য হালনাগাদ করুন"
    subtitle-en="Update customer details"
    active="customers"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">গ্রাহকের তথ্য</div>
            <div class="panel-title en" style="display:none;">Customer Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @csrf
                @method('PUT')
                @include('customer::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en" style="display:none;">Update Customer</span>
                    </x-core::button>
                    <x-core::button type="button" variant="secondary" :href="route('customers.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
