<x-core::button-group size="xs" aria-label="Sale Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="secondary"
        icon="eye"
        icon-only
        size="xs"
        class="btn-view-sale"
        data-id="{{ $sale->id }}"
        data-url="{{ route('sales.show', $sale) }}"
        title="বিস্তারিত / Details"
    />
    <x-core::button
        type="button"
        variant="soft"
        color="secondary"
        icon="printer"
        icon-only
        size="xs"
        class="btn-show-sale-invoice"
        data-id="{{ $sale->id }}"
        data-url="{{ route('sales.invoice-modal', $sale) }}"
        title="ইনভয়েস ও প্রিন্ট / Invoice & Print"
    />
    @can('sales.write')
        <x-core::button
            :href="route('sale-returns.create', $sale)"
            variant="soft"
            color="secondary"
            icon="rotate-ccw"
            icon-only
            size="xs"
            title="বিক্রয় ফেরত / Sale Return"
        />
        @if ($sale->canBeEdited())
            <x-core::button
                :href="route('sales.edit', $sale)"
                variant="soft"
                color="primary"
                icon="edit"
                icon-only
                size="xs"
                title="সম্পাদনা / Edit"
            />
        @else
            <span title="{{ $sale->cannotBeEditedReason() }}" style="display:inline-block; cursor:not-allowed;">
                <x-core::button
                    type="button"
                    variant="soft"
                    color="secondary"
                    icon="edit"
                    icon-only
                    size="xs"
                    disabled
                    style="opacity:0.4; pointer-events:none;"
                />
            </span>
        @endif
    @endcan
    @can('sales.delete')
        @if ($sale->canBeDeleted())
            <form
                method="POST"
                action="{{ route('sales.destroy', $sale) }}"
                class="delete-form"
                data-title="বিক্রয় মুছে ফেলতে চান?"
                data-text="এই বিক্রয় মুছে ফেললে স্টক ফেরত যোগ হবে এবং পরিশোধিত অর্থ সমন্বয় হবে। আপনি কি নিশ্চিত?"
                style="display:inline-block;"
            >
                @csrf
                @method('DELETE')
                <x-core::button
                    type="submit"
                    variant="soft"
                    color="danger"
                    icon="trash-2"
                    icon-only
                    size="xs"
                    title="মুছুন / Delete"
                />
            </form>
        @else
            <span title="{{ $sale->cannotBeDeletedReason() }}" style="display:inline-block; cursor:not-allowed;">
                <x-core::button
                    type="button"
                    variant="soft"
                    color="secondary"
                    icon="trash-2"
                    icon-only
                    size="xs"
                    disabled
                    style="opacity:0.4; pointer-events:none;"
                />
            </span>
        @endif
    @endcan
</x-core::button-group>
