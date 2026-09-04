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
    class="btn-show-purchase-invoice"
    data-id="{{ $purchase->id }}"
    data-url="{{ route('purchase.invoice-modal', $purchase) }}"
>
    <span class="bn">ইনভয়েস স্লিপ ও প্রিন্ট</span><span class="en">Invoice & Print</span>
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
    @if ($purchase->do_number)
        <div class="tx-row">
            <span class="lbl bn">ডিও নম্বর</span><span class="lbl en" style="display:none;">D.O. No</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">{{ $purchase->do_number }}</span>
        </div>
    @endif
    @if ($purchase->do_date)
        <div class="tx-row">
            <span class="lbl bn">ডিও তারিখ</span><span class="lbl en" style="display:none;">D.O. Date</span>
            <span class="val">{{ optional($purchase->do_date)->format('d M, Y') }}</span>
        </div>
    @endif
    @if ($purchase->vehicle_number)
        <div class="tx-row">
            <span class="lbl bn">গাড়ির নম্বর</span><span class="lbl en" style="display:none;">Vehicle No</span>
            <span class="val">{{ $purchase->vehicle_number }}</span>
        </div>
    @endif
    @if ($purchase->delivery_person_name)
        <div class="tx-row">
            <span class="lbl bn">ডেলিভারি ব্যক্তি</span><span class="lbl en" style="display:none;">Delivery Person</span>
            <span class="val">{{ $purchase->delivery_person_name }}</span>
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
    @if ((float) $purchase->transportation_cost > 0)
        <div class="tx-row">
            <span class="lbl bn">পরিবহন খরচ</span><span class="lbl en" style="display:none;">Transportation Cost</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format($purchase->transportation_cost, 2) }}</span>
        </div>
    @endif
    @if ((float) $purchase->adjustment_cost != 0)
        <div class="tx-row">
            <span class="lbl bn">অ্যাডজাস্টমেন্ট</span><span class="lbl en" style="display:none;">Adjustment Cost</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format($purchase->adjustment_cost, 2) }}</span>
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

@if ($purchase->hasPendingItems())
    <div style="background:var(--paper-line); border:1px solid var(--border); border-left:4px solid var(--teal-700); border-radius:8px; padding:12px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <div>
            <div style="font-weight:700; color:var(--ink-900); font-size:13px; display:flex; align-items:center; gap:6px;">
                <x-core::icon name="truck" size="16" style="color:var(--teal-700);" />
                <span class="bn">বাকি পণ্য রয়েছে ({{ rtrim(rtrim(number_format($purchase->totalPendingQuantity(), 2), '0'), '.') }} একক)</span>
                <span class="en" style="display:none;">Pending Items ({{ rtrim(rtrim(number_format($purchase->totalPendingQuantity(), 2), '0'), '.') }} units)</span>
            </div>
            <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                ডিও নম্বর দিয়ে বাকি পণ্য স্টকে গ্রহণ করুন
            </div>
        </div>
        <x-core::button
            type="button"
            color="primary"
            size="sm"
            icon="package-check"
            class="btn-receive-purchase"
            data-id="{{ $purchase->id }}"
            data-url="{{ route('purchase.receive.modal', $purchase) }}"
        >
            <span class="bn">পণ্য গ্রহণ</span>
            <span class="en" style="display:none;">Receive</span>
        </x-core::button>
    </div>
@endif

<div class="drawer-title bn" style="font-size:14px; margin-bottom:10px;">পণ্য ক্রয়</div>
<div class="drawer-title en" style="display:none; font-size:14px; margin-bottom:10px;">Products Purchased</div>
<div class="tx-section">
    @foreach ($purchase->items as $item)
        @php
            $pending = $item->pendingQuantity();
        @endphp
        <div class="tx-item">
            <div class="nm" style="font-weight:600; color:var(--ink-900); display:flex; align-items:center; justify-content:space-between;">
                <span>{{ $item->product->name ?? '—' }}</span>
                @if ($pending > 0)
                    <x-core::badge color="gold" size="xs" label="বাকি: {{ rtrim(rtrim(number_format($pending, 2), '0'), '.') }}" />
                @else
                    <x-core::badge color="green" size="xs" :dot="true" label="সম্পূর্ণ গৃহীত" label-en="Fully Received" />
                @endif
            </div>
            <div class="meta" style="font-size:12px; color:var(--ink-600); display:flex; flex-wrap:wrap; gap:12px; margin-top:4px;">
                <span>অর্ডার: <b>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</b></span>
                <span>গ্রহণ: <b style="color:var(--teal-700);">{{ rtrim(rtrim(number_format($item->received_quantity ?? $item->quantity, 2), '0'), '.') }}</b></span>
                @if ($pending > 0)
                    <span style="color:var(--red-600); font-weight:700;">বাকি: <b>{{ rtrim(rtrim(number_format($pending, 2), '0'), '.') }}</b></span>
                @endif
                <span>দর: <b>৳{{ number_format($item->purchase_price, 2) }}</b></span>
                <span>মোট: <b style="color:var(--ink-900);">৳{{ number_format($item->total, 2) }}</b></span>
                @if ($item->batch_no)
                    <span>ব্যাচ: <code>{{ $item->batch_no }}</code></span>
                @endif
            </div>
        </div>
    @endforeach
</div>

@if ($purchase->receiptItems->isNotEmpty())
    <div class="drawer-title bn" style="font-size:14px; margin-top:14px; margin-bottom:10px;">চালান / গ্রহণের ইতিহাস</div>
    <div class="drawer-title en" style="display:none; font-size:14px; margin-top:14px; margin-bottom:10px;">Receipt History</div>
    <div class="tx-section">
        @foreach ($purchase->receiptItems->sortByDesc('id') as $receipt)
            <div class="tx-item" style="padding:8px 0; border-bottom:1px dashed var(--border);">
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:12px;">
                    <div style="font-weight:600; color:var(--ink-900);">
                        <span>{{ $receipt->product->name ?? '—' }}</span>
                        <span style="color:var(--teal-700); font-weight:700;">&times; {{ rtrim(rtrim(number_format($receipt->received_quantity, 2), '0'), '.') }} একক</span>
                    </div>
                    <div style="font-size:11px; color:var(--ink-500); font-family:var(--font-mono, monospace);">
                        {{ optional($receipt->created_at)->format('d M, Y h:i A') }}
                    </div>
                </div>
                <div style="font-size:11.5px; color:var(--ink-500); display:flex; flex-wrap:wrap; gap:12px; margin-top:3px;">
                    @if ($receipt->do_number)
                        <span>ডিও: <strong style="font-family:var(--font-mono, monospace); color:var(--ink-800);">{{ $receipt->do_number }}</strong></span>
                    @endif
                    @if ($receipt->do_date)
                        <span>ডিও তারিখ: {{ optional($receipt->do_date)->format('d M, Y') }}</span>
                    @endif
                    @if ($receipt->vehicle_number)
                        <span>গাড়ি: {{ $receipt->vehicle_number }}</span>
                    @endif
                    @if ($receipt->receiver)
                        <span>গ্রহণকারী: {{ $receipt->receiver->name }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

@if ($purchase->note)
    <div class="tx-section">
        <div class="lbl bn" style="margin-bottom:6px;">নোট</div>
        <div class="lbl en" style="display:none; margin-bottom:6px;">Notes</div>
        <div class="val" style="font-weight:400; color:var(--ink-700);">{{ $purchase->note }}</div>
    </div>
@endif

@if ($purchase->hasUsedQuantity())
    <div style="background:var(--paper-line); border:1px solid var(--border); border-left:4px solid var(--gold-500, #f59e0b); border-radius:8px; padding:10px 12px; margin-top:16px; display:flex; align-items:center; gap:8px;">
        <x-core::icon name="info" size="16" style="color:var(--gold-ink); flex-shrink:0;" />
        <div style="font-size:12px; color:var(--ink-700);">
            {{ $purchase->cannotBeDeletedReason() }}
        </div>
    </div>
@endif

<div style="display:flex; gap:10px; margin-top:20px; flex-wrap:wrap;">
    @if ($purchase->canBeDeleted())
        <form
            method="POST"
            action="{{ route('purchase.destroy', $purchase) }}"
            class="delete-form"
            data-title="এই ক্রয় মুছে ফেলতে চান?"
            data-text="এই ক্রয় মুছে ফেললে স্টক থেকে পণ্য ও পরিশোধিত অর্থ রোলব্যাক হবে। আপনি কি নিশ্চিত?"
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
    @else
        <div style="flex:1; cursor:not-allowed;" title="{{ $purchase->cannotBeDeletedReason() }}">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="trash-2"
                disabled
                style="width:100%; justify-content:center; opacity:0.4; pointer-events:none;"
            >
                <span class="bn">মুছে ফেলা যাবে না</span><span class="en">Cannot Delete</span>
            </x-core::button>
        </div>
    @endif
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
        type="button"
        variant="secondary"
        size="sm"
        icon="history"
        class="btn-receipt-history"
        data-id="{{ $purchase->id }}"
        data-url="{{ route('purchase.receipt-history', $purchase) }}"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">গ্রহণের ইতিহাস</span><span class="en">Receipt History</span>
    </x-core::button>
    @if ($purchase->hasPendingItems())
        <x-core::button
            type="button"
            color="primary"
            size="sm"
            icon="package-check"
            class="btn-receive-purchase"
            data-id="{{ $purchase->id }}"
            data-url="{{ route('purchase.receive.modal', $purchase) }}"
            style="flex:1; justify-content:center;"
        >
            <span class="bn">পণ্য গ্রহণ</span><span class="en" style="display:none;">Receive</span>
        </x-core::button>
    @endif
    @if ($purchase->canBeEdited())
        <x-core::button
            variant="secondary"
            size="sm"
            icon="edit"
            :href="route('purchase.edit', $purchase)"
            style="flex:1; justify-content:center;"
        >
            <span class="bn">কেনাকাটা এডিট</span><span class="en">Edit Purchase</span>
        </x-core::button>
    @else
        <div style="flex:1; cursor:not-allowed;" title="{{ $purchase->cannotBeEditedReason() }}">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="edit"
                disabled
                style="width:100%; justify-content:center; opacity:0.4; pointer-events:none;"
            >
                <span class="bn">এডিট করা যাবে না</span><span class="en">Cannot Edit</span>
            </x-core::button>
        </div>
    @endif
</div>
