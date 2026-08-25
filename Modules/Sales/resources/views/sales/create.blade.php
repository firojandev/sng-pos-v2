<x-core::layout
    title="নতুন বিক্রয়"
    title-en="New Sale"
    subtitle="একটি নতুন বিক্রয় ইনভয়েস তৈরি করুন"
    subtitle-en="Create a new sales invoice"
    active="sales"
>
    <div class="panel" style="margin-top:0; overflow:hidden;">
        <form method="POST" action="{{ route('sales.store') }}">
            @csrf
            @include('sales::sales._form')
        </form>
    </div>
</x-core::layout>
