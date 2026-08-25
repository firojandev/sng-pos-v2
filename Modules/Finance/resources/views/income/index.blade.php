<x-core::layout
    title="আয়"
    title-en="Income"
    subtitle="দোকানের আয় পরিচালনা করুন"
    subtitle-en="Manage your shop's income"
    active="income"
>
    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('income.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন আয়</span><span class="en">New Income</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">উৎস</th><th class="en" style="display:none;">Source</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">পরিমাণ</th><th class="en" style="display:none;">Amount</th>
                            <th class="bn">পেমেন্ট পদ্ধতি</th><th class="en" style="display:none;">Payment Method</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($incomes as $income)
                            <tr>
                                <td class="cell-main">{{ $income->source }}</td>
                                <td>{{ optional($income->income_date)->format('d M, Y') ?? '—' }}</td>
                                <td>৳{{ number_format($income->amount, 2) }}</td>
                                <td>{{ $income->payment_method ?? '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('income.edit', $income) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('income.destroy', $income) }}" onsubmit="return confirm('এই আয় মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="helper" style="margin-top:0;">কোনো আয় নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $incomes->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
