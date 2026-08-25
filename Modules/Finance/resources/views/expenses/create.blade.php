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
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('expense.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
