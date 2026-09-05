@php
    $due = (float) $supplier->opening_due + (float) ($supplier->purchases_sum_due_amount ?? 0);
@endphp
<x-core::button-group size="sm" aria-label="Supplier Actions">
    @if ($due > 0)
        <x-core::button
            type="button"
            variant="soft"
            color="green"
            size="sm"
            icon="wallet"
            icon-only
            class="btn-open-supplier-payment"
            data-url="{{ route('due-ledger.supplier.payment-modal', $supplier) }}"
            title="দেনা পরিশোধ / Pay Due"
        />
    @endif

    <x-core::button
        type="button"
        variant="soft"
        color="secondary"
        size="sm"
        icon="eye"
        icon-only
        class="btn-view-supplier-due"
        data-url="{{ route('due-ledger.supplier.details', $supplier) }}"
        title="বিস্তারিত খাতা / View Ledger"
    />

    <x-core::button
        as="a"
        href="{{ route('purchase.create', ['supplier_id' => $supplier->id]) }}"
        variant="soft"
        color="teal"
        size="sm"
        icon="truck"
        icon-only
        title="নতুন ক্রয় / New Purchase"
    />

    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        size="sm"
        icon="edit"
        icon-only
        class="btn-edit-supplier"
        data-id="{{ $supplier->id }}"
        data-url="{{ route('suppliers.edit', $supplier) }}"
        title="সম্পাদনা / Edit"
    />

    <form
        method="POST"
        action="{{ route('suppliers.destroy', $supplier) }}"
        class="delete-form"
        data-title="সরবরাহকারী মুছে ফেলতে চান?"
        data-text="এই সরবরাহকারীর তথ্য মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
