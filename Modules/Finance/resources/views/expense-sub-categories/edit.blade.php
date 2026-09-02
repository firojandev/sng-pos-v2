<x-core::layout
    title="ব্যয় সাব-ক্যাটাগরি সম্পাদনা"
    title-en="Edit Expense Sub-category"
    subtitle="ব্যয় সাব-ক্যাটাগরির তথ্য পরিবর্তন করুন"
    subtitle-en="Update expense sub-category details"
    active="expense"
>
    <x-finance::tabbar active="expense-sub-categories" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">সাব-ক্যাটাগরির তথ্য</div>
            <div class="panel-title en" style="display:none;">Sub-category Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('expense-sub-categories.update', $subCategory) }}">
                @csrf
                @method('PUT')
                @include('finance::expense-sub-categories._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </x-core::button>
                    <x-core::button :href="route('expense-sub-categories.index')" variant="secondary" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
