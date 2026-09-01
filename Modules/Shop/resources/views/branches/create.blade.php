<x-core::layout
    title="নতুন শাখা"
    title-en="New Branch"
    subtitle="একটি নতুন শাখা তৈরি করুন"
    subtitle-en="Create a new branch"
    active="branches"
>
    <x-shop::tabbar active="branches" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">শাখার তথ্য</div>
            <div class="panel-title en" style="display:none;">Branch Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('branches.store') }}">
                @csrf
                @include('shop::branches._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('branches.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
