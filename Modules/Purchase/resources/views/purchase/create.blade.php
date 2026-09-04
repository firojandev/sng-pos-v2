<x-core::layout
    title="নতুন ক্রয়"
    title-en="New Purchase"
    subtitle="একটি নতুন ক্রয় ইনভয়েস তৈরি করুন"
    subtitle-en="Create a new purchase invoice"
    active="purchase"
>
    <div class="panel" style="margin-top:0; overflow:hidden;">
        <form method="POST" action="{{ route('purchase.store') }}" id="purchase-form">
            @csrf
            @include('purchase::purchase._form')
        </form>
    </div>

    @include('purchase::purchase._quick_supplier_modal')

    @if(isset($invoicePurchase) && $invoicePurchase)
        @include('purchase::purchase._invoice_modal', ['purchase' => $invoicePurchase])
        @push('scripts')
            <script>
                $(function() {
                    openModal('purchaseInvoiceModal');
                });
            </script>
        @endpush
    @endif
</x-core::layout>
