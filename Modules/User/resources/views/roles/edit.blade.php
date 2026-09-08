<x-core::layout
    title="রোল সম্পাদনা"
    title-en="Edit Role"
    subtitle="রোলের নাম ও পারমিশন হালনাগাদ করুন"
    subtitle-en="Update the role's name and permissions"
    active="roles"
>
    <div style="max-width:1120px; margin:0 auto; padding-bottom:40px;">
        <form method="POST" action="{{ route('roles.update', $role) }}" id="role-form">
            @csrf
            @method('PUT')
            @include('user::roles._form')
        </form>
    </div>
</x-core::layout>
