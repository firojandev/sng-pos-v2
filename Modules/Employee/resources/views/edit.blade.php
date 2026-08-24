<x-core::layout
    title="কর্মচারী সম্পাদনা"
    title-en="Edit Employee"
    subtitle="কর্মচারীর তথ্য হালনাগাদ করুন"
    subtitle-en="Update employee record"
    active="employees"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">কর্মচারীর তথ্য</div>
            <div class="panel-title en" style="display:none;">Employee Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('employees.update', $employee) }}">
                @csrf
                @method('PUT')
                @include('employee::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
