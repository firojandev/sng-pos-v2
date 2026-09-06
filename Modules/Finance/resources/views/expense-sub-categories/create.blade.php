<x-core::layout
    title="নতুন ব্যয় সাব-ক্যাটাগরি"
    title-en="New Expense Sub-category"
    subtitle="একটি নতুন ব্যয় সাব-ক্যাটাগরি তৈরি করুন"
    subtitle-en="Create a new expense sub-category"
    active="expense"
>
    <x-finance::tabbar active="expense-sub-categories" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">সাব-ক্যাটাগরির তথ্য</div>
            <div class="panel-title en" style="display:none;">Sub-category Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('expense-sub-categories.store') }}">
                @csrf
                @include('finance::expense-sub-categories._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </x-core::button>
                    <x-core::button :href="route('expense-sub-categories.index')" variant="secondary" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
