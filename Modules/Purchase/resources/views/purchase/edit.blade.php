<x-core::layout
    title="ক্রয় সম্পাদনা"
    title-en="Edit Purchase"
    subtitle="ক্রয় ইনভয়েসের তথ্য হালনাগাদ করুন"
    subtitle-en="Update the purchase invoice"
    active="purchase"
>
    <div class="panel" style="margin-top:0; overflow:hidden;">
        <form method="POST" action="{{ route('purchase.update', $purchase) }}" id="purchase-form">
            @csrf
            @method('PUT')
            @include('purchase::purchase._form')
        </form>
    </div>
</x-core::layout>
