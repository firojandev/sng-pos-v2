@php
    $due = (float) $customer->opening_due + (float) ($customer->sales_sum_due_amount ?? 0);
@endphp
<x-core::button-group size="sm" aria-label="Customer Actions">
    @if ($due > 0)
        <x-core::button
            type="button"
            variant="soft"
            color="green"
            size="sm"
            icon="wallet"
            icon-only
            class="btn-open-customer-payment"
            data-url="{{ route('due-ledger.customer.payment-modal', $customer) }}"
            title="বাকি জমা / Collect Due"
        />
    @endif

    <x-core::button
        type="button"
        variant="soft"
        color="secondary"
        size="sm"
        icon="eye"
        icon-only
        class="btn-view-customer-due"
        data-url="{{ route('due-ledger.customer.details', $customer) }}"
        title="বিস্তারিত খাতা / View Ledger"
    />

    <x-core::button
        as="a"
        href="{{ route('sales.create', ['customer_id' => $customer->id]) }}"
        variant="soft"
        color="teal"
        size="sm"
        icon="shopping-cart"
        icon-only
        title="নতুন বিক্রয় / New Sale"
    />

    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        size="sm"
        icon="edit"
        icon-only
        class="btn-edit-customer"
        data-id="{{ $customer->id }}"
        data-url="{{ route('customers.edit', $customer) }}"
        title="সম্পাদনা / Edit"
    />

    <form
        method="POST"
        action="{{ route('customers.destroy', $customer) }}"
        class="delete-form"
        data-title="গ্রাহক মুছে ফেলতে চান?"
        data-text="এই গ্রাহকের তথ্য মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
        style="display:inline-block;"
    >
        @csrf
        @method('DELETE')
        <x-core::button
            type="submit"
            variant="soft"
            color="danger"
            size="sm"
            icon="trash-2"
            icon-only
            title="মুছুন / Delete"
        />
    </form>
</x-core::button-group>
