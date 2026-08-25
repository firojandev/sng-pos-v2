<x-core::layout
    title="নতুন ক্রয়"
    title-en="New Purchase"
    subtitle="একটি নতুন ক্রয় ইনভয়েস তৈরি করুন"
    subtitle-en="Create a new purchase invoice"
    active="purchase"
>
    <div class="panel" style="margin-top:0; overflow:hidden;">
        <form method="POST" action="{{ route('purchase.store') }}">
            @csrf
            @include('purchase::purchase._form')
        </form>
    </div>
</x-core::layout>
