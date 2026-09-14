<x-core::button-group size="xs" aria-label="Security Money Actions">
    @can('security-money.edit')
        <x-core::button
            type="button"
            variant="soft"
            color="primary"
            icon="edit"
            icon-only
            class="btn-edit-security-money"
            data-id="{{ $securityMoney->id }}"
            data-receiver-name="{{ $securityMoney->receiver_name }}"
            data-amount="{{ $securityMoney->amount }}"
            data-date="{{ optional($securityMoney->date)->format('Y-m-d') }}"
            data-status="{{ $securityMoney->status }}"
            data-account-id="{{ $securityMoney->account_id }}"
            data-note="{{ $securityMoney->note }}"
            data-action="{{ route('security-money.update', $securityMoney) }}"
            data-url="{{ route('security-money.edit', $securityMoney) }}"
            title="সম্পাদনা / Edit"
        />
    @endcan
    @can('security-money.delete')
        <form
            method="POST"
            action="{{ route('security-money.destroy', $securityMoney) }}"
            class="delete-form"
            data-title="জামানতের রেকর্ড মুছে ফেলতে চান?"
            data-text="এই জামানতের রেকর্ড মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
