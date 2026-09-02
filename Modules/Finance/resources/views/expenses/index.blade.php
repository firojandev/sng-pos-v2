<x-core::layout
    title="ব্যয়"
    title-en="Expense"
    subtitle="দোকানের ব্যয় পরিচালনা করুন"
    subtitle-en="Manage your shop's expenses"
    active="expense"
>
    <x-finance::tabbar active="expense" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <x-core::button color="primary" :href="route('expense.create')" icon="plus">
                    <span class="bn">নতুন ব্যয়</span><span class="en">New Expense</span>
                </x-core::button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">শিরোনাম</th><th class="en" style="display:none;">Title</th>
                            <th class="bn">ক্যাটাগরি</th><th class="en" style="display:none;">Category</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">পরিমাণ</th><th class="en" style="display:none;">Amount</th>
                            <th class="bn">পেমেন্ট অ্যাকাউন্ট</th><th class="en" style="display:none;">Payment Account</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="cell-main">{{ $expense->title }}</td>
                                <td>
                                    {{ $expense->category->name ?? '—' }}
                                    @if ($expense->subCategory)
                                        <span style="color:var(--text-muted, #71717a); font-size:12px;"> / {{ $expense->subCategory->name }}</span>
                                    @endif
                                </td>
                                <td>{{ optional($expense->expense_date)->format('d M, Y') ?? '—' }}</td>
                                <td>৳{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->account->display_name ?? '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('expense.edit', $expense) }}">
                                            <x-core::icon name="edit" size="14" />
                                        </a>
                                        <form method="POST" action="{{ route('expense.destroy', $expense) }}" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <x-core::icon name="trash-2" size="14" class="text-danger" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-core::table.empty
                                        icon="trending-down"
                                        title="কোনো ব্যয় নেই"
                                        title-en="No expense records found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
