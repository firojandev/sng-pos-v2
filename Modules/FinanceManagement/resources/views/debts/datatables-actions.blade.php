<x-core::button-group size="xs" aria-label="Debt Actions">
    @can('debts.edit')
        <x-core::button
            type="button"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            class="btn-edit-debt"
            data-id="{{ $debt->id }}"
            data-lender-name="{{ $debt->lender_name }}"
            data-amount="{{ $debt->amount }}"
            data-date="{{ optional($debt->date)->format('Y-m-d') }}"
            data-status="{{ $debt->status }}"
            data-account-id="{{ $debt->account_id }}"
            data-note="{{ $debt->note }}"
            data-action="{{ route('debts.update', $debt) }}"
            data-url="{{ route('debts.edit', $debt) }}"
            title="সম্পাদনা / Edit"
        />
    @endcan
    @can('debts.delete')
        <form
            method="POST"
            action="{{ route('debts.destroy', $debt) }}"
            class="delete-form"
            data-title="দেনার রেকর্ড মুছে ফেলতে চান?"
            data-text="এই দেনার রেকর্ড মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
