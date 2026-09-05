<x-core::button-group size="xs" aria-label="Income Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-income"
        data-id="{{ $income->id }}"
        data-source="{{ $income->source }}"
        data-amount="{{ $income->amount }}"
        data-income-date="{{ optional($income->income_date)->format('Y-m-d') }}"
        data-account-id="{{ $income->account_id }}"
        data-payment-method="{{ $income->payment_method }}"
        data-note="{{ $income->note }}"
        data-action="{{ route('income.update', $income) }}"
        data-url="{{ route('income.edit', $income) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('income.destroy', $income) }}"
        class="delete-form"
        data-title="আয় মুছে ফেলতে চান?"
        data-text="এই আয়ের রেকর্ড মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
</x-core::button-group>
