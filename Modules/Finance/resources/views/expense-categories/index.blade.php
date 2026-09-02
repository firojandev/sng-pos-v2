<x-core::layout
    title="ব্যয় ক্যাটাগরি"
    title-en="Expense Category"
    subtitle="ব্যয়ের ক্যাটাগরি পরিচালনা করুন"
    subtitle-en="Manage expense categories"
    active="expense"
>
    <x-finance::tabbar active="expense-categories" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('expense-categories.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন ক্যাটাগরি</span><span class="en">New Category</span>
                </a>
            </div>

            <div class="mini-grid">
                @forelse ($expenseCategories as $expenseCategory)
                    <div class="mini-card pm-card">
                        <div class="mini-card-actions">
                            <a class="act" title="Edit" href="{{ route('expense-categories.edit', $expenseCategory) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                            </a>
                            <form method="POST" action="{{ route('expense-categories.destroy', $expenseCategory) }}" onsubmit="return confirm('এই ক্যাটাগরি মুছে ফেলতে চান?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act" title="Delete">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </div>
                        <div class="nm">{{ $expenseCategory->name }}</div>
                        <div class="sub">{{ $expenseCategory->expenses_count }} ব্যয়</div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="tag"
                            title="কোনো ক্যাটাগরি নেই"
                            title-en="No expense categories found"
                        />
                    </div>
                @endforelse
            </div>

            <div style="margin-top:14px;">
                {{ $expenseCategories->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
