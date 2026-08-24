<x-core::layout
    title="নতুন কর্মচারী"
    title-en="New Employee"
    subtitle="একটি নতুন কর্মচারী রেকর্ড তৈরি করুন"
    subtitle-en="Create a new employee record"
    active="employees"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">কর্মচারীর তথ্য</div>
            <div class="panel-title en" style="display:none;">Employee Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('employees.store') }}">
                @csrf
                @include('employee::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
