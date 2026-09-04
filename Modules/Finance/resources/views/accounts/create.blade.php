<x-core::layout
    title="নতুন অ্যাকাউন্ট"
    title-en="New Account"
    subtitle="নতুন ব্যাংক বা মোবাইল ব্যাংকিং (MFS) অ্যাকাউন্ট যোগ করুন"
    subtitle-en="Add a new Bank or Mobile Banking (MFS) account"
    active="accounts"
>

    <div class="panel" style="margin-top:0; max-width:760px;">
        <div class="panel-body">
            <form method="POST" action="{{ route('accounts.store') }}" id="account_form">
                @csrf
                @include('finance::accounts._form')
            </form>
        </div>
    </div>
</x-core::layout>
