<x-core::layout
    title="বিক্রয় ফেরত"
    title-en="Sale Returns"
    subtitle="সকল বিক্রয় ফেরতের তালিকা"
    subtitle-en="All processed sale returns"
    active="sales"
>
    <form method="GET" action="{{ route('sale-returns.index') }}" class="section-row">
        <div class="filters">
            <div class="search-inline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#8B978F" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="#8B978F" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="ইনভয়েস নং দিয়ে খুঁজুন">
            </div>
        </div>
        <button type="submit" class="btn btn-outline"><span class="bn">খুঁজুন</span><span class="en">Search</span></button>
    </form>

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">ফেরত নং</th><th class="en" style="display:none;">Return No</th>
                            <th class="bn">ইনভয়েস</th><th class="en" style="display:none;">Invoice</th>
                            <th class="bn">গ্রাহক</th><th class="en" style="display:none;">Customer</th>
                            <th class="bn">আইটেম</th><th class="en" style="display:none;">Items</th>
                            <th class="bn">সাবটোটাল</th><th class="en" style="display:none;">Subtotal</th>
                            <th class="bn">নগদ ফেরত</th><th class="en" style="display:none;">Cash Refund</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $return)
                            <tr>
                                <td class="cell-main">#{{ $return->return_no }}</td>
                                <td>#{{ $return->sale->invoice_no ?? '—' }}</td>
                                <td>{{ $return->sale->customer->name ?? 'ওয়াক-ইন গ্রাহক' }}</td>
                                <td>{{ $return->items->count() }}</td>
                                <td>৳{{ number_format($return->subtotal, 2) }}</td>
                                <td>
                                    @if ($return->refund_amount > 0)
                                        <span class="badge b-red">৳{{ number_format($return->refund_amount, 2) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $return->return_date->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="helper" style="margin-top:0;">কোনো বিক্রয় ফেরত নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
