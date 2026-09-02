<x-core::layout
    title="নতুন ফান্ড ট্রান্সফার"
    title-en="New Fund Transfer"
    subtitle="এক অ্যাকাউন্ট থেকে অন্য অ্যাকাউন্টে টাকা স্থানান্তর করুন"
    subtitle-en="Transfer money between accounts"
    active="accounts"
>
    <x-finance::account-tabbar active="account-transfers" />

    <div class="panel" style="margin-top:0; max-width:760px;">
        <div class="panel-body">
            <form method="POST" action="{{ route('account-transfers.store') }}" id="transfer_form">
                @csrf
                @include('finance::transfers._form')
            </form>
        </div>
    </div>
</x-core::layout>
