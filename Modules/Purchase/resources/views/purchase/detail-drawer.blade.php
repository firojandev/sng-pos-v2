<div class="drawer-head">
    <div class="drawer-title bn">লেনদেনের বিস্তারিত</div>
    <div class="drawer-title en" style="display:none;">Transaction Details</div>
    <x-core::button
        type="button"
        variant="ghost"
        size="xs"
        icon="x"
        icon-only
        class="drawer-x"
        onclick="closeModal('purchaseDetailDrawer')"
    />
</div>

<x-core::button
    type="button"
    variant="secondary"
    size="sm"
    icon="printer"
    style="width:100%; justify-content:center; margin-bottom:16px;"
    onclick="printSection('purchaseDetailDrawerContent')"
>
    <span class="bn">প্রিন্ট করুন</span><span class="en">Print</span>
</x-core::button>

<div class="tx-section">
    <div class="tx-row">
        <span class="lbl bn">ইনভয়েস নং</span><span class="lbl en" style="display:none;">Invoice No</span>
        <span class="val" style="font-family:var(--font-mono, monospace); font-weight:700;">#{{ $purchase->invoice_no }}</span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">মোট আইটেম</span><span class="lbl en" style="display:none;">Total Items</span>
        <span class="val" style="font-family:var(--font-mono, monospace);">{{ rtrim(rtrim(number_format($purchase->items->sum('quantity'), 2), '0'), '.') }}</span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">সাপ্লায়ার নাম</span><span class="lbl en" style="display:none;">Supplier Name</span>
        <span class="val row-avatar">
            <span class="av" style="background:var(--teal-700); color:var(--paper); width:28px; height:28px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;">{{ mb_substr($purchase->supplier->name ?? '?', 0, 1) }}</span>
            <span style="font-weight:600; color:var(--ink-900);">{{ $purchase->supplier->name ?? '—' }}</span>
        </span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">ক্রয় তারিখ</span><span class="lbl en" style="display:none;">Purchase Date</span>
        <span class="val">{{ optional($purchase->purchase_date)->format('d M, Y') }} &middot; {{ $purchase->created_at->format('h:i A') }}</span>
    </div>
    @if ($purchase->warehouse)
        <div class="tx-row">
            <span class="lbl bn">ওয়্যারহাউস</span><span class="lbl en" style="display:none;">Warehouse</span>
            <span class="val">{{ $purchase->warehouse->name }}</span>
        </div>
    @endif
</div>

<div class="tx-section">
    <div class="tx-row strong">
        <span class="lbl bn">পেমেন্ট অবস্থা</span><span class="lbl en" style="display:none;">Payment Status</span>
        <span class="val">
            @if ($purchase->payment_status === 'paid')
                <x-core::badge color="green" size="xs" :dot="true" label="পরিশোধিত" label-en="Paid" />
            @elseif ($purchase->payment_status === 'partial')
                <x-core::badge color="gold" size="xs" :dot="true" label="আংশিক" label-en="Partial" />
            @else
                <x-core::badge color="red" size="xs" :dot="true" label="বাকি" label-en="Due" />
            @endif
        </span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">মোট</span><span class="lbl en" style="display:none;">Subtotal</span>
        <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format($purchase->subtotal, 2) }}</span>
    </div>
    @if ((float) $purchase->discount > 0)
        <div class="tx-row">
            <span class="lbl bn">ডিস্কাউন্ট</span><span class="lbl en" style="display:none;">Discount</span>
            <span class="val" style="font-family:var(--font-mono, monospace); color:var(--green-ink);">৳{{ number_format($purchase->discount, 2) }}</span>
        </div>
    @endif
    @if ((float) $purchase->delivery_charge > 0)
        <div class="tx-row">
            <span class="lbl bn">ডেলিভারি চার্জ</span><span class="lbl en" style="display:none;">Delivery Charge</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format($purchase->delivery_charge, 2) }}</span>
        </div>
    @endif
    <div class="tx-row strong">
        <span class="lbl bn">সর্বমোট</span><span class="lbl en" style="display:none;">Grand Total</span>
        <span class="val" style="font-family:var(--font-mono, monospace); font-weight:700;">৳{{ number_format($purchase->total, 2) }}</span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">পরিশোধিত</span><span class="lbl en" style="display:none;">Paid</span>
        <span class="val">
            <span style="font-family:var(--font-mono, monospace); font-weight:600;">৳{{ number_format($purchase->paid_amount, 2) }}</span>
            @if ($purchase->payments->isNotEmpty())
                <div style="font-size:11px; font-weight:400; color:var(--ink-500); margin-top:2px;">
                    {{ $purchase->payments->map(fn ($p) => $p->methodLabel()['bn'].' ৳'.number_format($p->amount, 2))->implode(', ') }}
                </div>
            @endif
        </span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">বাকি</span><span class="lbl en" style="display:none;">Due</span>
        <span class="val" style="font-family:var(--font-mono, monospace); font-weight:700; {{ $purchase->due_amount > 0 ? 'color:var(--red-600);' : '' }}">৳{{ number_format($purchase->due_amount, 2) }}</span>
    </div>
</div>

<div class="drawer-title bn" style="font-size:14px; margin-bottom:10px;">পণ্য ক্রয়</div>
<div class="drawer-title en" style="display:none; font-size:14px; margin-bottom:10px;">Products Purchased</div>
<div class="tx-section">
    @foreach ($purchase->items as $item)
        <div class="tx-item">
            <div class="nm" style="font-weight:600; color:var(--ink-900);">{{ $item->product->name ?? '—' }}</div>
            <div class="meta" style="font-size:12px; color:var(--ink-600); display:flex; gap:12px; margin-top:4px;">
                <span>পরিমাণ: <b>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</b></span>
                <span>দর: <b>৳{{ number_format($item->purchase_price, 2) }}</b></span>
                <span>মোট: <b style="color:var(--ink-900);">৳{{ number_format($item->total, 2) }}</b></span>
                @if ($item->batch_no)
                    <span>ব্যাচ: <code>{{ $item->batch_no }}</code></span>
                @endif
            </div>
        </div>
    @endforeach
</div>

@if ($purchase->note)
    <div class="tx-section">
        <div class="lbl bn" style="margin-bottom:6px;">নোট</div>
        <div class="lbl en" style="display:none; margin-bottom:6px;">Notes</div>
        <div class="val" style="font-weight:400; color:var(--ink-700);">{{ $purchase->note }}</div>
    </div>
@endif

<div style="display:flex; gap:10px; margin-top:20px;">
    <form
        method="POST"
        action="{{ route('purchase.destroy', $purchase) }}"
        class="delete-form"
        data-title="এই ক্রয় মুছে ফেলতে চান?"
        data-text="এই ক্রয় মুছে ফেললে স্টক থেকে পণ্য বিয়োগ হবে। আপনি কি নিশ্চিত?"
        style="flex:1;"
    >
        @csrf
        @method('DELETE')
        <x-core::button
            type="submit"
            color="danger"
            size="sm"
            icon="trash-2"
            style="width:100%; justify-content:center;"
        >
            <span class="bn">মুছে ফেলুন</span><span class="en">Delete</span>
        </x-core::button>
    </form>
    <x-core::button
        variant="secondary"
        size="sm"
        icon="undo-2"
        :href="route('purchase-returns.create', $purchase)"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">ফেরত</span><span class="en">Return</span>
    </x-core::button>
    <x-core::button
        color="primary"
        size="sm"
        icon="edit"
        :href="route('purchase.edit', $purchase)"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">কেনাকাটা এডিট</span><span class="en">Edit Purchase</span>
    </x-core::button>
</div>
