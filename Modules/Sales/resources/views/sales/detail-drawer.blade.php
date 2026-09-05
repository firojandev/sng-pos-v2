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
        onclick="closeModal('saleDetailDrawer')"
    />
</div>

<x-core::button
    type="button"
    variant="secondary"
    size="sm"
    icon="printer"
    style="width:100%; justify-content:center; margin-bottom:16px;"
    class="btn-show-sale-invoice"
    data-id="{{ $sale->id }}"
    data-url="{{ route('sales.invoice-modal', $sale) }}"
    onclick="showSaleInvoice('{{ route('sales.invoice-modal', $sale) }}', 'saleDetailDrawer');"
>
    <span class="bn">ইনভয়েস ও প্রিন্ট</span><span class="en" style="display:none;">Invoice & Print</span>
</x-core::button>

<div class="tx-section">
    <div class="tx-row">
        <span class="lbl bn">ইনভয়েস নং</span><span class="lbl en" style="display:none;">Invoice No</span>
        <span class="val" style="font-family:var(--font-mono, monospace); font-weight:700;">#{{ $sale->invoice_no }}</span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">মোট আইটেম</span><span class="lbl en" style="display:none;">Total Items</span>
        <span class="val" style="font-family:var(--font-mono, monospace);">{{ rtrim(rtrim(number_format((float) $sale->items->sum('quantity'), 2), '0'), '.') }} ({{ $sale->items->count() }} টি পণ্য)</span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">গ্রাহকের নাম</span><span class="lbl en" style="display:none;">Customer Name</span>
        <span class="val row-avatar">
            <span class="av" style="background:var(--teal-700); color:var(--paper); width:28px; height:28px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;">{{ mb_substr($sale->customer->name ?? '?', 0, 1) }}</span>
            <span style="font-weight:600; color:var(--ink-900);">{{ $sale->customer->name ?? 'ওয়াক-ইন গ্রাহক' }}</span>
        </span>
    </div>
    @if ($sale->customer?->phone)
        <div class="tx-row">
            <span class="lbl bn">মোবাইল</span><span class="lbl en" style="display:none;">Phone</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">{{ $sale->customer->phone }}</span>
        </div>
    @endif
    @if ($sale->customer?->address)
        <div class="tx-row">
            <span class="lbl bn">ঠিকানা</span><span class="lbl en" style="display:none;">Address</span>
            <span class="val">{{ $sale->customer->address }}</span>
        </div>
    @endif
    <div class="tx-row">
        <span class="lbl bn">বিক্রয় তারিখ</span><span class="lbl en" style="display:none;">Sale Date</span>
        <span class="val">{{ optional($sale->sale_date)->format('d M, Y') }} &middot; {{ $sale->created_at->format('h:i A') }}</span>
    </div>
    @if ($sale->warehouse)
        <div class="tx-row">
            <span class="lbl bn">ওয়্যারহাউস</span><span class="lbl en" style="display:none;">Warehouse</span>
            <span class="val">{{ $sale->warehouse->name }}</span>
        </div>
    @endif
    @if ($sale->employee_name)
        <div class="tx-row">
            <span class="lbl bn">বিক্রয় প্রতিনিধি</span><span class="lbl en" style="display:none;">Sales Representative</span>
            <span class="val">{{ $sale->employee_name }} @if($sale->employee_phone)({{ $sale->employee_phone }})@endif</span>
        </div>
    @endif
</div>

<div class="tx-section">
    <div class="tx-row strong">
        <span class="lbl bn">পেমেন্ট অবস্থা</span><span class="lbl en" style="display:none;">Payment Status</span>
        <span class="val">
            @if ($sale->payment_status === 'paid')
                <x-core::badge color="green" size="xs" :dot="true" label="পরিশোধিত" label-en="Paid" />
            @elseif ($sale->payment_status === 'partial')
                <x-core::badge color="gold" size="xs" :dot="true" label="আংশিক" label-en="Partial" />
            @else
                <x-core::badge color="red" size="xs" :dot="true" label="বাকি" label-en="Due" />
            @endif
        </span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">মোট</span><span class="lbl en" style="display:none;">Subtotal</span>
        <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format((float) $sale->subtotal, 2) }}</span>
    </div>
    @if ((float) $sale->discount > 0)
        <div class="tx-row">
            <span class="lbl bn">ডিস্কাউন্ট</span><span class="lbl en" style="display:none;">Discount</span>
            <span class="val" style="font-family:var(--font-mono, monospace); color:var(--green-ink);">৳{{ number_format((float) $sale->discount, 2) }}</span>
        </div>
    @endif
    @if ((float) $sale->delivery_charge > 0)
        <div class="tx-row">
            <span class="lbl bn">ডেলিভারি চার্জ</span><span class="lbl en" style="display:none;">Delivery Charge</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format((float) $sale->delivery_charge, 2) }}</span>
        </div>
    @endif
    <div class="tx-row">
        <span class="lbl bn">পরিশোধিত</span><span class="lbl en" style="display:none;">Paid</span>
        <span class="val" style="font-family:var(--font-mono, monospace);">
            ৳{{ number_format((float) $sale->paid_amount, 2) }}
            @if ($sale->payments->isNotEmpty())
                <div style="font-size:11px; font-weight:400; color:var(--ink-600); margin-top:2px;">
                    {{ $sale->payments->map(fn ($p) => ($p->methodLabel()['bn'] ?? $p->method).' ৳'.number_format((float) $p->amount, 2))->implode(', ') }}
                </div>
            @endif
        </span>
    </div>
    @if (isset($settledPreviousDue) && $settledPreviousDue > 0)
        <div class="tx-row">
            <span class="lbl bn">পূর্ববর্তী বকেয়া সমন্বয়</span><span class="lbl en" style="display:none;">Previous Due Paid</span>
            <span class="val" style="color:var(--teal-700); font-family:var(--font-mono, monospace);">৳{{ number_format($settledPreviousDue, 2) }}</span>
        </div>
        <div class="tx-row" style="background:var(--paper-line); padding:6px 10px; border-radius:6px; margin:4px 0;">
            <span class="lbl bn" style="font-weight:700; color:var(--ink-900);">মোট নগদ/ব্যাংক গ্রহণ</span><span class="lbl en" style="display:none; font-weight:700;">Total Received</span>
            <span class="val" style="font-weight:700; color:var(--teal-800); font-family:var(--font-mono, monospace);">৳{{ number_format((float) $sale->paid_amount + $settledPreviousDue, 2) }}</span>
        </div>
    @endif
    <div class="tx-row">
        <span class="lbl bn">বাকি</span><span class="lbl en" style="display:none;">Due</span>
        <span class="val" style="font-family:var(--font-mono, monospace); {{ $sale->due_amount > 0 ? 'color:var(--red-600); font-weight:700;' : '' }}">৳{{ number_format((float) $sale->due_amount, 2) }}</span>
    </div>
    <div class="tx-row strong" style="border-top:1px solid var(--border); padding-top:8px; margin-top:4px;">
        <span class="lbl bn">সর্বমোট</span><span class="lbl en" style="display:none;">Grand Total</span>
        <span class="val" style="font-family:var(--font-mono, monospace); font-size:15px; font-weight:700; color:var(--ink-900);">৳{{ number_format((float) $sale->total, 2) }}</span>
    </div>
</div>

<div class="drawer-title bn" style="font-size:14px; margin-bottom:10px; font-weight:700; color:var(--ink-900);">বিক্রীত পণ্য তালিকা (Products Sold)</div>
<div class="tx-section">
    @foreach ($sale->items as $item)
        <div class="tx-item" style="padding:10px 0; border-bottom:1px dashed var(--border);">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div class="nm" style="font-weight:600; color:var(--ink-900);">{{ $item->product->name ?? '—' }}</div>
                    @if ($item->batch)
                        <div style="font-size:11px; color:var(--ink-500); font-family:var(--font-mono, monospace); margin-top:2px;">
                            ব্যাচ: {{ $item->batch->batch_no }}
                        </div>
                    @endif
                    @if ($item->warranty_expires_at)
                        <div style="font-size:11px; color:var(--teal-700); display:flex; align-items:center; gap:3px; margin-top:2px;">
                            <x-core::icon name="shield-check" size="12" /> ওয়ারেন্টি: {{ $item->warranty_expires_at->format('d M, Y') }} পর্যন্ত
                        </div>
                    @endif
                </div>
                <div style="text-align:right;">
                    <div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">৳{{ number_format((float) $item->total, 2) }}</div>
                    <div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace);">
                        {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} {{ $item->unit?->name ?? 'টি' }} &times; ৳{{ number_format((float) $item->unit_price, 2) }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if ($sale->returns->isNotEmpty())
    <div class="drawer-title bn" style="font-size:14px; margin-top:14px; margin-bottom:10px; font-weight:700; color:var(--gold-ink, #b45309);">ফেরত রেকর্ড (Sale Returns)</div>
    <div class="tx-section">
        @foreach ($sale->returns as $ret)
            <div class="tx-item" style="padding:8px 0; border-bottom:1px dashed var(--border);">
                <div style="display:flex; justify-content:space-between;">
                    <div>
                        <span style="font-weight:600; font-family:var(--font-mono, monospace);">#{{ $ret->return_no }}</span>
                        <div style="font-size:11.5px; color:var(--ink-500);">{{ optional($ret->return_date)->format('d M, Y') }}</div>
                    </div>
                    <div style="text-align:right; color:var(--red-600); font-weight:700; font-family:var(--font-mono, monospace);">
                        -৳{{ number_format((float) $ret->refund_amount, 2) }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if ($sale->note)
    <div class="tx-section">
        <div class="lbl bn" style="margin-bottom:6px; font-weight:600;">নোট / বিবরণ</div>
        <div class="val" style="font-weight:400; color:var(--ink-700); background:var(--paper-line); padding:8px 12px; border-radius:6px; font-size:12.5px;">{{ $sale->note }}</div>
    </div>
@endif

<div style="display:flex; gap:10px; margin-top:20px;">
    @can('sales.delete')
        @if ($sale->canBeDeleted())
            <form method="POST" action="{{ route('sales.destroy', $sale) }}" class="delete-form" data-title="এই বিক্রয় মুছে ফেলতে চান?" data-text="স্টক ফেরত যোগ হবে এবং পরিশোধিত অর্থ রোলব্যাক হবে। আপনি কি নিশ্চিত?" style="flex:1;">
                @csrf
                @method('DELETE')
                <x-core::button type="submit" color="danger" size="sm" icon="trash-2" style="width:100%; justify-content:center;">
                    <span class="bn">মুছে ফেলুন</span><span class="en" style="display:none;">Delete</span>
                </x-core::button>
            </form>
        @else
            <span title="{{ $sale->cannotBeDeletedReason() }}" style="flex:1; cursor:not-allowed;">
                <x-core::button type="button" variant="secondary" size="sm" icon="trash-2" disabled style="width:100%; justify-content:center; opacity:0.5; pointer-events:none;">
                    <span class="bn">মুছে ফেলুন</span>
                </x-core::button>
            </span>
        @endif
    @endcan

    @can('sales.write')
        <x-core::button :href="route('sale-returns.create', $sale)" variant="secondary" size="sm" icon="rotate-ccw" style="flex:1; justify-content:center;">
            <span class="bn">ফেরত</span><span class="en" style="display:none;">Return</span>
        </x-core::button>

        @if ($sale->canBeEdited())
            <x-core::button :href="route('sales.edit', $sale)" color="primary" size="sm" icon="edit" style="flex:1; justify-content:center;">
                <span class="bn">এডিট</span><span class="en" style="display:none;">Edit</span>
            </x-core::button>
        @else
            <span title="{{ $sale->cannotBeEditedReason() }}" style="flex:1; cursor:not-allowed;">
                <x-core::button type="button" variant="secondary" size="sm" icon="edit" disabled style="width:100%; justify-content:center; opacity:0.5; pointer-events:none;">
                    <span class="bn">এডিট</span>
                </x-core::button>
            </span>
        @endif
    @endcan
</div>
