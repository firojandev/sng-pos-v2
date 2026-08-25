<x-core::layout
    title="ইউজার সম্পাদনা"
    title-en="Edit User"
    subtitle="ইউজারের তথ্য হালনাগাদ করুন"
    subtitle-en="Update user account details"
    active="users"
>
    <x-user::tabbar active="users" />

    <div class="panel" style="margin-top:0; max-width:520px;">
        <div class="panel-head">
            <div class="panel-title bn">ইউজারের তথ্য</div>
            <div class="panel-title en" style="display:none;">User Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('user::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
