<x-core::button-group size="xs" aria-label="Lend Actions">
    @can('lend.edit')
        <x-core::button
            type="button"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            class="btn-edit-lend"
            data-id="{{ $lend->id }}"
            data-borrower-name="{{ $lend->borrower_name }}"
            data-amount="{{ $lend->amount }}"
            data-date="{{ optional($lend->date)->format('Y-m-d') }}"
            data-status="{{ $lend->status }}"
            data-account-id="{{ $lend->account_id }}"
            data-note="{{ $lend->note }}"
            data-action="{{ route('lend.update', $lend) }}"
            data-url="{{ route('lend.edit', $lend) }}"
            title="সম্পাদনা / Edit"
        />
    @endcan
    @can('lend.delete')
        <form
            method="POST"
            action="{{ route('lend.destroy', $lend) }}"
            class="delete-form"
            data-title="ধারের রেকর্ড মুছে ফেলতে চান?"
            data-text="এই ধারের রেকর্ড মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
        >
            @csrf
            @method('DELETE')
            <x-core::button
                type="submit"
                variant="soft"
                color="danger"
                icon="trash-2"
                icon-only
                title="মুছুন / Delete"
            />
        </form>
    @endcan
</x-core::button-group>
