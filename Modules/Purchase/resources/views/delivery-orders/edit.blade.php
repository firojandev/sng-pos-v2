<x-core::layout
    title="ডেলিভারি অর্ডার সম্পাদনা"
    title-en="Edit Purchase Delivery Order"
    subtitle="অর্ডারের তথ্য হালনাগাদ করুন"
    subtitle-en="Edit purchase delivery order details"
    active="purchase-delivery-orders"
>
    <div class="cash-page-head">
        <div>
            <div class="ttl bn">অর্ডার সম্পাদনা: #{{ $order->order_no }}</div>
            <div class="ttl en" style="display:none;">Edit Order: #{{ $order->order_no }}</div>
        </div>
        <div class="actions">
            <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.show', $order) }}" icon="arrow-left">
                <span class="bn">অর্ডারে ফিরে যান</span>
            </x-core::button>
        </div>
    </div>

    <form method="POST" action="{{ route('purchase-delivery-orders.update', $order) }}" id="pdo-form">
        @csrf
        @method('PUT')
        @include('purchase::delivery-orders._form')
    </form>
</x-core::layout>
