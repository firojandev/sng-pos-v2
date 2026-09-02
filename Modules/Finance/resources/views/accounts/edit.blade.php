<x-core::layout
    title="অ্যাকাউন্ট সম্পাদনা"
    title-en="Edit Account"
    subtitle="অ্যাকাউন্টের তথ্য পরিবর্তন ও সংশোধন করুন"
    subtitle-en="Edit and update account details"
    active="accounts"
>
    <x-finance::account-tabbar active="accounts" />

    <div class="panel" style="margin-top:0; max-width:760px;">
        <div class="panel-body">
            <form method="POST" action="{{ route('accounts.update', $account) }}" id="account_form">
                @csrf
                @method('PUT')
                @include('finance::accounts._form')
            </form>
        </div>
    </div>
</x-core::layout>
