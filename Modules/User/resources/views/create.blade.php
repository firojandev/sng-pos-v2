<x-core::layout
    title="নতুন ইউজার"
    title-en="New User"
    subtitle="একটি নতুন সিস্টেম লগইন অ্যাকাউন্ট তৈরি করুন"
    subtitle-en="Create a new system login account"
    active="users"
>
    <x-user::tabbar active="users" />

    <div style="max-width:560px; margin:0 auto; background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
        <div style="margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                <x-core::icon name="user-plus" size="18" />
            </div>
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--ink-900);">
                    <span class="bn">ইউজারের তথ্য</span>
                    <span class="en" style="display:none;">User Details</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">
                    <span class="bn">নতুন ব্যবহারকারীর তথ্য পূরণ করুন</span>
                    <span class="en" style="display:none;">Fill in the new user information</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('user::_form')

            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-top:24px; padding-top:16px; border-top:1px solid var(--border);">
                <x-core::button
                    variant="secondary"
                    size="sm"
                    :href="route('users.index')"
                >
                    <span class="bn">বাতিল</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>

                <x-core::button
                    type="submit"
                    color="primary"
                    size="sm"
                    icon="check"
                >
                    <span class="bn">সংরক্ষণ করুন</span>
                    <span class="en" style="display:none;">Save User</span>
                </x-core::button>
            </div>
        </form>
    </div>
</x-core::layout>
