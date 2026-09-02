<x-core::layout
    title="ক্রয় ফেরত"
    title-en="Purchase Returns"
    subtitle="সকল ক্রয় ফেরতের তালিকা"
    subtitle-en="All processed purchase returns"
    active="purchase"
>
    <form method="GET" action="{{ route('purchase-returns.index') }}" class="section-row">
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
                            <th class="bn">সরবরাহকারী</th><th class="en" style="display:none;">Supplier</th>
                            <th class="bn">আইটেম</th><th class="en" style="display:none;">Items</th>
                            <th class="bn">সাবটোটাল</th><th class="en" style="display:none;">Subtotal</th>
                            <th class="bn">নগদ প্রাপ্তি</th><th class="en" style="display:none;">Cash Received</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $return)
                            <tr>
                                <td class="cell-main">#{{ $return->return_no }}</td>
                                <td>#{{ $return->purchase->invoice_no ?? '—' }}</td>
                                <td>{{ $return->purchase->supplier->name ?? '—' }}</td>
                                <td>{{ $return->items->count() }}</td>
                                <td>৳{{ number_format($return->subtotal, 2) }}</td>
                                <td>
                                    @if ($return->refund_amount > 0)
                                        <span class="badge b-green">৳{{ number_format($return->refund_amount, 2) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $return->return_date->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-core::table.empty
                                        icon="rotate-ccw"
                                        title="কোনো ক্রয় ফেরত নেই"
                                        title-en="No purchase returns found"
                                    />
                                </td>
                            </tr>
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
