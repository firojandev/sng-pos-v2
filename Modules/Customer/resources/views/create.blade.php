<x-core::layout
    title="নতুন গ্রাহক"
    title-en="New Customer"
    subtitle="একটি নতুন গ্রাহক রেকর্ড তৈরি করুন"
    subtitle-en="Create a new customer record"
    active="customers"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">গ্রাহকের তথ্য</div>
            <div class="panel-title en" style="display:none;">Customer Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                @include('customer::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
