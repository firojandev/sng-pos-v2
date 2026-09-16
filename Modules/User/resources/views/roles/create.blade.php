<x-core::layout
    title="নতুন রোল"
    title-en="New Role"
    subtitle="দোকানের জন্য একটি নতুন রোল তৈরি করুন"
    subtitle-en="Create a new role for your shop"
    active="roles"
>
    <div style="max-width:1120px; margin:0 auto; padding-bottom:40px;">
        <form method="POST" action="{{ route('roles.store') }}" id="role-form">
            @csrf
            @include('user::roles._form')
        </form>
    </div>
</x-core::layout>
