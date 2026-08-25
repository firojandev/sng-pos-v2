<x-core::layout
    title="ক্রয়"
    title-en="Purchase"
    subtitle="দোকানের ক্রয় পরিচালনা করুন"
    subtitle-en="Manage your shop's purchases"
    active="purchase"
>
    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('purchase.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন ক্রয়</span><span class="en">New Purchase</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">ইনভয়েস</th><th class="en" style="display:none;">Invoice</th>
                            <th class="bn">সরবরাহকারী</th><th class="en" style="display:none;">Supplier</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">মোট</th><th class="en" style="display:none;">Total</th>
                            <th class="bn">বাকি</th><th class="en" style="display:none;">Due</th>
                            <th class="bn">স্ট্যাটাস</th><th class="en" style="display:none;">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            <tr>
                                <td class="cell-main">#{{ $purchase->invoice_no }}</td>
                                <td>{{ $purchase->supplier->name ?? '—' }}</td>
                                <td>{{ optional($purchase->purchase_date)->format('d M, Y') ?? '—' }}</td>
                                <td>৳{{ number_format($purchase->total, 2) }}</td>
                                <td>৳{{ number_format($purchase->due_amount, 2) }}</td>
                                <td>
                                    @if ($purchase->payment_status === 'paid')
                                        <span class="badge b-green bn">পরিশোধিত</span><span class="badge b-green en" style="display:none;">Paid</span>
                                    @elseif ($purchase->payment_status === 'partial')
                                        <span class="badge b-gold bn">আংশিক</span><span class="badge b-gold en" style="display:none;">Partial</span>
                                    @else
                                        <span class="badge b-red bn">বাকি</span><span class="badge b-red en" style="display:none;">Due</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('purchase.edit', $purchase) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('purchase.destroy', $purchase) }}" onsubmit="return confirm('এই ক্রয় মুছে ফেলতে চান? স্টক থেকে বিয়োগ হবে।');">
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
                            <tr><td colspan="7"><div class="helper" style="margin-top:0;">কোনো ক্রয় নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
