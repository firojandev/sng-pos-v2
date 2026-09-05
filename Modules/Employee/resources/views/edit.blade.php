<x-core::layout
    title="কর্মচারী সম্পাদনা"
    title-en="Edit Employee"
    subtitle="কর্মচারীর তথ্য ও বেতন হালনাগাদ করুন"
    subtitle-en="Update employee details and salary"
    active="employees"
>
    <div style="max-width:640px; margin:0 auto; background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
        <div style="margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                <x-core::icon name="edit" size="18" />
            </div>
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--ink-900);">
                    <span class="bn">কর্মচারী তথ্য সম্পাদনা</span>
                    <span class="en" style="display:none;">Edit Employee Details</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">{{ $employee->name }} (#EMP-{{ str_pad((string) $employee->id, 4, '0', STR_PAD_LEFT) }})</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')
            @include('employee::_form')

            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-top:24px; padding-top:16px; border-top:1px solid var(--border);">
                <x-core::button
                    variant="secondary"
                    size="sm"
                    :href="route('employees.index')"
                >
                    <span class="bn">বাতিল</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>

                <x-core::button
                    type="submit"
                    color="primary"
                    size="sm"
                    icon="check"
                >
                    <span class="bn">হালনাগাদ করুন</span>
                    <span class="en" style="display:none;">Update Employee</span>
                </x-core::button>
            </div>
        </form>
    </div>
</x-core::layout>
