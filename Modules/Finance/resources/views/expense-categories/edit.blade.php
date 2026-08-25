<x-core::layout
    title="ক্যাটাগরি সম্পাদনা"
    title-en="Edit Category"
    subtitle="ব্যয় ক্যাটাগরির তথ্য হালনাগাদ করুন"
    subtitle-en="Update expense category details"
    active="expense"
>
    <x-finance::tabbar active="expense-categories" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ক্যাটাগরির তথ্য</div>
            <div class="panel-title en" style="display:none;">Category Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('expense-categories.update', $expenseCategory) }}">
                @csrf
                @method('PUT')
                @include('finance::expense-categories._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('expense-categories.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
