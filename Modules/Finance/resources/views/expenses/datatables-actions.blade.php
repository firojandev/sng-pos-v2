<x-core::button-group size="xs" aria-label="Expense Actions">
    <x-core::button
        type="button"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        class="btn-edit-expense"
        data-id="{{ $expense->id }}"
        data-title="{{ $expense->title }}"
        data-amount="{{ $expense->amount }}"
        data-expense-date="{{ optional($expense->expense_date)->format('Y-m-d') }}"
        data-category-id="{{ $expense->expense_category_id }}"
        data-subcategory-id="{{ $expense->expense_sub_category_id }}"
        data-account-id="{{ $expense->account_id }}"
        data-payment-method="{{ $expense->payment_method }}"
        data-note="{{ $expense->note }}"
        data-action="{{ route('expense.update', $expense) }}"
        data-url="{{ route('expense.edit', $expense) }}"
        title="সম্পাদনা / Edit"
    />
    <form
        method="POST"
        action="{{ route('expense.destroy', $expense) }}"
        class="delete-form"
        data-title="ব্যয় মুছে ফেলতে চান?"
        data-text="এই ব্যয়ের রেকর্ড মুছে ফেলা হবে। আপনি কি নিশ্চিত?"
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
