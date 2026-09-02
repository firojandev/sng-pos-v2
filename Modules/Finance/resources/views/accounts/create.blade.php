<x-core::layout
    title="নতুন অ্যাকাউন্ট"
    title-en="New Account"
    subtitle="নতুন ব্যাংক, এমএফএস বা ক্যাশ অ্যাকাউন্ট যোগ করুন"
    subtitle-en="Add a new Bank, MFS, or Cash account"
    active="accounts"
>
    <x-finance::account-tabbar active="accounts" />

    <div class="panel" style="margin-top:0; max-width:760px;">
        <div class="panel-body">
            <form method="POST" action="{{ route('accounts.store') }}" id="account_form">
                @csrf
                @include('finance::accounts._form')
            </form>
        </div>
    </div>
</x-core::layout>
