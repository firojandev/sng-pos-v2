<x-core::button-group size="xs" aria-label="Transfer Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="secondary"
        icon="eye"
        icon-only
        class="btn-view-transfer-detail"
        data-url="{{ route('stock-transfers.show', $transfer) }}"
        title="বিস্তারিত / View Details"
    />

    @if ($transfer->status === 'pending')
        <form method="POST" action="{{ route('stock-transfers.approve', $transfer) }}" class="inline-transfer-action-form" style="display:inline;">
            @csrf
            <x-core::button
                type="submit"
                variant="soft"
                color="green"
                icon="check"
                icon-only
                title="অনুমোদন করুন / Approve"
            />
        </form>
        <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" class="delete-form inline-transfer-action-form" data-title="ট্রান্সফার বাতিল করতে চান?" data-text="এই ট্রান্সফার বাতিল করা হবে। আপনি কি নিশ্চিত?" style="display:inline;">
            @csrf
            <x-core::button
                type="submit"
                variant="soft"
                color="red"
                icon="x"
                icon-only
                title="বাতিল করুন / Cancel"
            />
        </form>
    @elseif ($transfer->status === 'approved')
        <form method="POST" action="{{ route('stock-transfers.dispatch', $transfer) }}" class="inline-transfer-action-form" style="display:inline;">
            @csrf
            <x-core::button
                type="submit"
                variant="soft"
                color="primary"
                icon="arrow-right"
                icon-only
                title="প্রেরণ করুন / Dispatch"
            />
        </form>
        <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" class="delete-form inline-transfer-action-form" data-title="ট্রান্সফার বাতিল করতে চান?" data-text="এই ট্রান্সফার বাতিল করা হবে। আপনি কি নিশ্চিত?" style="display:inline;">
            @csrf
            <x-core::button
                type="submit"
                variant="soft"
                color="red"
                icon="x"
                icon-only
                title="বাতিল করুন / Cancel"
            />
        </form>
    @elseif ($transfer->status === 'dispatched')
        <form method="POST" action="{{ route('stock-transfers.receive', $transfer) }}" class="inline-transfer-action-form" style="display:inline;">
            @csrf
            <x-core::button
                type="submit"
                variant="soft"
                color="green"
                icon="check-circle"
                icon-only
                title="গ্রহণ নিশ্চিত করুন / Confirm Receipt"
            />
        </form>
    @endif
</x-core::button-group>
