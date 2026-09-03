<x-core::layout
    title="ব্যয় সম্পাদনা"
    title-en="Edit Expense"
    subtitle="ব্যয়ের তথ্য হালনাগাদ করুন"
    subtitle-en="Update expense details"
    active="expense"
>
    <x-finance::tabbar active="expense" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">ব্যয়ের তথ্য</div>
            <div class="panel-title en" style="display:none;">Expense Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('expense.update', $expense) }}">
                @csrf
                @method('PUT')
                @include('finance::expenses._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </x-core::button>
                    <x-core::button variant="secondary" :href="route('expense.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
