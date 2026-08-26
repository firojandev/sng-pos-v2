<x-core::layout
    title="বিক্রয় সম্পাদনা"
    title-en="Edit Sale"
    subtitle="বিক্রয় ইনভয়েসের তথ্য হালনাগাদ করুন"
    subtitle-en="Update the sales invoice"
    active="sales"
>
    <div class="panel" style="margin-top:0; overflow:hidden;">
        <form method="POST" action="{{ route('sales.update', $sale) }}" id="sale-form">
            @csrf
            @method('PUT')
            @include('sales::sales._form')
        </form>
    </div>
</x-core::layout>
