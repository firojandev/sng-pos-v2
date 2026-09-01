<x-core::layout
    title="প্ল্যান সম্পাদনা"
    title-en="Edit Plan"
    subtitle="প্ল্যানের তথ্য হালনাগাদ করুন"
    subtitle-en="Update the subscription plan"
    active="plans"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">প্ল্যানের তথ্য</div>
            <div class="panel-title en" style="display:none;">Plan Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('plans.update', $plan) }}">
                @csrf
                @method('PUT')
                @include('shop::plans._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('plans.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
