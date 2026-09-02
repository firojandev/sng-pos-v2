<x-core::layout
    title="কেনার খাতা"
    title-en="Purchase Ledger"
    subtitle="ক্রয়ের লেনদেনের ইতিহাস দেখুন"
    subtitle-en="Browse your shop's purchase transaction history"
    active="purchase-ledger"
>
    <div class="cash-page-head">
        <div class="ttl bn">লেনদেনের ইতিহাস</div>
        <div class="ttl en" style="display:none;">Transaction History</div>

        <div class="actions">
            <div class="total-pill">
                <span class="bn">মোট ক্রয়: </span><span class="en" style="display:none;">Total Purchase: </span>
                <b>৳{{ number_format($totalAmount, 2) }}</b>
            </div>
            <button type="button" class="btn btn-outline" onclick="printSection('purchase-list-print')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-5a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-2" stroke="#1C2B27" stroke-width="1.7" stroke-linejoin="round"/><rect x="6" y="14" width="12" height="7" stroke="#1C2B27" stroke-width="1.7" stroke-linejoin="round"/></svg>
                <span class="bn">ডাউনলোড/প্রিন্ট</span><span class="en">Download/Print</span>
            </button>
            <a class="btn btn-gold" href="{{ route('purchase.create') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                <span class="bn">নতুন ক্রয়</span><span class="en">New Purchase</span>
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('purchase.ledger') }}" class="section-row">
        <div class="filters">
            <div class="search-inline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#8B978F" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="#8B978F" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="নাম অথবা মোবাইল দিয়ে খোঁজ করুন">
            </div>
            <input type="date" name="from" value="{{ $from }}">
            <input type="date" name="to" value="{{ $to }}">
            <select name="status" onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>সব</option>
                <option value="paid" @selected($status === 'paid')>পরিশোধিত</option>
                <option value="partial" @selected($status === 'partial')>আংশিক</option>
                <option value="due" @selected($status === 'due')>বাকি</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 0 1 13.7-5.7L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 0 1-13.7 5.7L4 15.5M4 20v-4.5h4.5" stroke="#1C2B27" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="bn">রিফ্রেশ</span><span class="en">Refresh</span>
        </button>
    </form>

    <div class="panel" style="margin-top:0;" id="purchase-list-print">
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">যোগাযোগ</th><th class="en" style="display:none;">Contact</th>
                            <th class="bn">ইনভয়েস নং</th><th class="en" style="display:none;">Invoice No</th>
                            <th class="bn">ব্যাচ নং</th><th class="en" style="display:none;">Batch No</th>
                            <th class="bn">আইটেম</th><th class="en" style="display:none;">Item</th>
                            <th class="bn">টাকার পরিমাণ</th><th class="en" style="display:none;">Amount</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">পেমেন্ট অবস্থা</th><th class="en" style="display:none;">Payment Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            <tr style="cursor:pointer;" onclick="openModal('purchaseDetail-{{ $purchase->id }}')">
                                <td>
                                    <div class="cell-main">{{ $purchase->supplier->name ?? '—' }}</div>
                                    @if ($purchase->supplier?->phone)
                                        <div class="cell-sub">{{ $purchase->supplier->phone }}</div>
                                    @endif
                                </td>
                                <td>#{{ $purchase->invoice_no }}</td>
                                <td>{{ $purchase->items->pluck('batch_no')->unique()->implode(', ') ?: '—' }}</td>
                                <td>{{ rtrim(rtrim(number_format($purchase->items->sum('quantity'), 2), '0'), '.') }}</td>
                                <td>৳{{ number_format($purchase->total, 2) }}</td>
                                <td>{{ optional($purchase->purchase_date)->format('d M, Y') ?? '—' }}</td>
                                <td>
                                    @if ($purchase->payment_status === 'paid')
                                        <span class="badge b-green bn">পরিশোধিত</span><span class="badge b-green en" style="display:none;">Paid</span>
                                    @elseif ($purchase->payment_status === 'partial')
                                        <span class="badge b-gold bn">আংশিক</span><span class="badge b-gold en" style="display:none;">Partial</span>
                                    @else
                                        <span class="badge b-red bn">বাকি</span><span class="badge b-red en" style="display:none;">Due</span>
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <div class="row-actions">
                                        <button type="button" class="act" title="Details" onclick="openModal('purchaseDetail-{{ $purchase->id }}')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="5.5" r="1.6" fill="#5C6B65"/><circle cx="12" cy="12" r="1.6" fill="#5C6B65"/><circle cx="12" cy="18.5" r="1.6" fill="#5C6B65"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-core::table.empty
                                        icon="truck"
                                        title="কোনো ক্রয় নেই"
                                        title-en="No purchase records found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>

    @foreach ($purchases as $purchase)
        <div class="drawer-backdrop" id="purchaseDetail-{{ $purchase->id }}">
            <div class="drawer">
                <div class="drawer-head">
                    <div class="drawer-title bn">লেনদেনের বিস্তারিত</div>
                    <div class="drawer-title en" style="display:none;">Transaction Details</div>
                    <button type="button" class="drawer-x" onclick="closeModal('purchaseDetail-{{ $purchase->id }}')">&times;</button>
                </div>

                <button type="button" class="btn btn-outline" style="width:100%; justify-content:center; margin-bottom:16px;" onclick="printSection('purchaseDetail-{{ $purchase->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-5a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-2" stroke="#1C2B27" stroke-width="1.7" stroke-linejoin="round"/><rect x="6" y="14" width="12" height="7" stroke="#1C2B27" stroke-width="1.7" stroke-linejoin="round"/></svg>
                    <span class="bn">প্রিন্ট করুন</span><span class="en">Print</span>
                </button>

                <div class="tx-section">
                    <div class="tx-row">
                        <span class="lbl bn">মোট আইটেম</span><span class="lbl en" style="display:none;">Total Items</span>
                        <span class="val">{{ rtrim(rtrim(number_format($purchase->items->sum('quantity'), 2), '0'), '.') }}</span>
                    </div>
                    <div class="tx-row">
                        <span class="lbl bn">সাপ্লায়ার নাম</span><span class="lbl en" style="display:none;">Supplier Name</span>
                        <span class="val row-avatar">
                            <span class="av" style="background:var(--teal-700);">{{ mb_substr($purchase->supplier->name ?? '?', 0, 1) }}</span>
                            {{ $purchase->supplier->name ?? '—' }}
                        </span>
                    </div>
                    <div class="tx-row">
                        <span class="lbl bn">ক্রয় তারিখ</span><span class="lbl en" style="display:none;">Purchase Date</span>
                        <span class="val">{{ optional($purchase->purchase_date)->format('d M, Y') }} &middot; {{ $purchase->created_at->format('h:i A') }}</span>
                    </div>
                </div>

                <div class="tx-section">
                    <div class="tx-row strong">
                        <span class="lbl bn">পেমেন্ট</span><span class="lbl en" style="display:none;">Payment</span>
                        <span class="val">
                            ৳{{ number_format($purchase->total, 2) }}
                            @if ($purchase->payment_status === 'paid')
                                <span class="badge b-green bn">পরিশোধিত</span><span class="badge b-green en" style="display:none;">Paid</span>
                            @elseif ($purchase->payment_status === 'partial')
                                <span class="badge b-gold bn">আংশিক</span><span class="badge b-gold en" style="display:none;">Partial</span>
                            @else
                                <span class="badge b-red bn">বাকি</span><span class="badge b-red en" style="display:none;">Due</span>
                            @endif
                        </span>
                    </div>
                    <div class="tx-row">
                        <span class="lbl bn">মোট</span><span class="lbl en" style="display:none;">Subtotal</span>
                        <span class="val">৳{{ number_format($purchase->subtotal, 2) }}</span>
                    </div>
                    <div class="tx-row">
                        <span class="lbl bn">ডিস্কাউন্ট</span><span class="lbl en" style="display:none;">Discount</span>
                        <span class="val">৳{{ number_format($purchase->discount, 2) }}</span>
                    </div>
                    <div class="tx-row">
                        <span class="lbl bn">পরিশোধিত</span><span class="lbl en" style="display:none;">Paid</span>
                        <span class="val">
                            ৳{{ number_format($purchase->paid_amount, 2) }}
                            @if ($purchase->payments->isNotEmpty())
                                <div style="font-size:11px; font-weight:400; color:var(--ink-600); margin-top:2px;">
                                    {{ $purchase->payments->map(fn ($p) => $p->methodLabel()['bn'].' ৳'.number_format($p->amount, 2))->implode(', ') }}
                                </div>
                            @endif
                        </span>
                    </div>
                    <div class="tx-row">
                        <span class="lbl bn">বাকি</span><span class="lbl en" style="display:none;">Due</span>
                        <span class="val" style="{{ $purchase->due_amount > 0 ? 'color:var(--red-600);' : '' }}">৳{{ number_format($purchase->due_amount, 2) }}</span>
                    </div>
                    <div class="tx-row strong">
                        <span class="lbl bn">সর্বমোট</span><span class="lbl en" style="display:none;">Grand Total</span>
                        <span class="val">৳{{ number_format($purchase->total, 2) }}</span>
                    </div>
                </div>

                <div class="drawer-title bn" style="font-size:14px; margin-bottom:10px;">পণ্য ক্রয়</div>
                <div class="drawer-title en" style="display:none; font-size:14px; margin-bottom:10px;">Products Purchased</div>
                <div class="tx-section">
                    @foreach ($purchase->items as $item)
                        <div class="tx-item">
                            <div class="nm">{{ $item->product->name ?? '—' }}</div>
                            <div class="meta">
                                <span>Qty: {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</span>
                                <span>Price: ৳{{ number_format($item->purchase_price, 2) }}</span>
                                <span>Total: ৳{{ number_format($item->total, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($purchase->note)
                    <div class="tx-section">
                        <div class="lbl bn" style="margin-bottom:6px;">নোট</div>
                        <div class="lbl en" style="display:none; margin-bottom:6px;">Notes</div>
                        <div class="val" style="font-weight:400;">{{ $purchase->note }}</div>
                    </div>
                @endif

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <form method="POST" action="{{ route('purchase.destroy', $purchase) }}" style="flex:1;" onsubmit="return confirm('এই ক্রয় মুছে ফেলতে চান? স্টক থেকে বিয়োগ হবে।');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-red" style="width:100%; justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/></svg>
                            <span class="bn">মুছে ফেলুন</span><span class="en">Delete</span>
                        </button>
                    </form>
                    <a href="{{ route('purchase-returns.create', $purchase) }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">ফেরত</span><span class="en">Return</span>
                    </a>
                    <a href="{{ route('purchase.edit', $purchase) }}" class="btn btn-teal" style="flex:1; justify-content:center;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/></svg>
                        <span class="bn">কেনাকাটা এডিট</span><span class="en">Edit Purchase</span>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</x-core::layout>
