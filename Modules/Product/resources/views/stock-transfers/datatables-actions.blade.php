<x-core::button-group size="xs" aria-label="Transfer Actions">
    @can('stock.view')
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
    @endcan

    @if ($transfer->status === 'pending')
        @can('stock.transfer')
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
        @endcan
        @can('stock.delete')
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
        @endcan
    @elseif ($transfer->status === 'approved')
        @can('stock.transfer')
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
        @endcan
        @can('stock.delete')
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
        @endcan
    @elseif ($transfer->status === 'dispatched')
        @can('stock.transfer')
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
        @endcan
    @endif
</x-core::button-group>
