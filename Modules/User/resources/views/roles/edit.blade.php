<x-core::layout
    title="রোল সম্পাদনা"
    title-en="Edit Role"
    subtitle="রোলের নাম ও পারমিশন হালনাগাদ করুন"
    subtitle-en="Update the role's name and permissions"
    active="users"
>
    <x-user::tabbar active="roles" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">রোলের তথ্য</div>
            <div class="panel-title en" style="display:none;">Role Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('roles.update', $role) }}">
                @csrf
                @method('PUT')
                @include('user::roles._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
