<x-core::layout
    title="নতুন পারচেজ ডেলিভারি অর্ডার"
    title-en="New Purchase Delivery Order"
    subtitle="সরবরাহকারীর নিকট নতুন ক্রয়ের অর্ডার তৈরি করুন"
    subtitle-en="Create a purchase delivery order to track deliveries and inventory"
    active="purchase-delivery-orders"
>
    <div class="cash-page-head">
        <div>
            <div class="ttl bn">নতুন পারচেজ ডেলিভারি অর্ডার</div>
            <div class="ttl en" style="display:none;">New Purchase Delivery Order</div>
        </div>
        <div class="actions">
            <x-core::button size="sm" variant="secondary" href="{{ route('purchase-delivery-orders.index') }}" icon="arrow-left">
                <span class="bn">তালিকায় ফিরে যান</span>
            </x-core::button>
        </div>
    </div>

    <form method="POST" action="{{ route('purchase-delivery-orders.store') }}" id="pdo-form">
        @csrf
        @include('purchase::delivery-orders._form')
    </form>
</x-core::layout>
