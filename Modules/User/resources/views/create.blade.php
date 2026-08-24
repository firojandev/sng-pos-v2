<x-core::layout
    title="নতুন ইউজার"
    title-en="New User"
    subtitle="একটি নতুন সিস্টেম লগইন অ্যাকাউন্ট তৈরি করুন"
    subtitle-en="Create a new system login account"
    active="users"
>
    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ইউজারের তথ্য</div>
            <div class="panel-title en" style="display:none;">User Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('user::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
