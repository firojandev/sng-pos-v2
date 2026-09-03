<x-core::layout
    title="নতুন ব্যয়"
    title-en="New Expense"
    subtitle="একটি নতুন ব্যয় যোগ করুন"
    subtitle-en="Add a new expense"
    active="expense"
>
    <x-finance::tabbar active="expense" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">ব্যয়ের তথ্য</div>
            <div class="panel-title en" style="display:none;">Expense Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('expense.store') }}">
                @csrf
                @include('finance::expenses._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </x-core::button>
                    <x-core::button variant="secondary" :href="route('expense.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
