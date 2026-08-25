<x-core::layout
    title="নতুন আয়"
    title-en="New Income"
    subtitle="একটি নতুন আয় যোগ করুন"
    subtitle-en="Add a new income"
    active="income"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">আয়ের তথ্য</div>
            <div class="panel-title en" style="display:none;">Income Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('income.store') }}">
                @csrf
                @include('finance::income._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('income.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
