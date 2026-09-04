<div class="drawer-head">
    <div style="display:flex; align-items:center; gap:8px;">
        <div style="width:30px; height:30px; border-radius:8px; background:var(--gold-100); color:var(--gold-ink); display:flex; align-items:center; justify-content:center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="drawer-title bn" style="font-size:16px; font-weight:700;">বাকির বিস্তারিত (সরবরাহকারী)</div>
            <div class="drawer-title en" style="display:none; font-size:16px; font-weight:700;">Supplier Due Details</div>
        </div>
    </div>
    <button type="button" class="drawer-x" onclick="$('#supplierDetailDrawer').removeClass('open')">&times;</button>
</div>

<div class="tx-section" style="margin-top:16px;">
    <div class="tx-row">
        <span class="lbl bn">সরবরাহকারী</span><span class="lbl en" style="display:none;">Supplier</span>
        <span class="val row-avatar" style="display:flex; align-items:center; gap:8px;">
            <span class="av" style="width:28px; height:28px; border-radius:6px; background:var(--gold-600); color:#ffffff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;">{{ mb_substr($supplier->name, 0, 1) }}</span>
            <span style="font-weight:700; color:var(--ink-900);">{{ $supplier->name }}</span>
        </span>
    </div>
    @if ($supplier->phone)
        <div class="tx-row">
            <span class="lbl bn">মোবাইল</span><span class="lbl en" style="display:none;">Phone</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">{{ $supplier->phone }}</span>
        </div>
    @endif
    @if ($supplier->email)
        <div class="tx-row">
            <span class="lbl bn">ইমেইল</span><span class="lbl en" style="display:none;">Email</span>
            <span class="val">{{ $supplier->email }}</span>
        </div>
    @endif
    @if ($supplier->address)
        <div class="tx-row">
            <span class="lbl bn">ঠিকানা</span><span class="lbl en" style="display:none;">Address</span>
            <span class="val" style="font-size:12.5px;">{{ $supplier->address }}</span>
        </div>
    @endif
    <div class="tx-row">
        <span class="lbl bn">ওপেনিং বাকি</span><span class="lbl en" style="display:none;">Opening Due</span>
        <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format($supplier->opening_due, 2) }}</span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">বিল বাকি</span><span class="lbl en" style="display:none;">Purchase Due</span>
        <span class="val" style="font-family:var(--font-mono, monospace); color:var(--gold-ink, #b45309); font-weight:600;">৳{{ number_format($supplier->purchases->sum('due_amount'), 2) }}</span>
    </div>
    <div class="tx-row strong" style="background:var(--paper-line); padding:10px 14px; border-radius:8px; margin-top:8px;">
        <span class="lbl bn" style="font-weight:700; font-size:14px;">মোট বকেয়া বাকি</span><span class="lbl en" style="display:none; font-weight:700; font-size:14px;">Total Outstanding</span>
        <span class="val" style="font-family:var(--font-mono, monospace); font-weight:800; font-size:16px; color:var(--red-600);">৳{{ number_format($supplier->total_due, 2) }}</span>
    </div>
</div>

<div style="display:flex; align-items:center; justify-content:space-between; margin-top:20px; margin-bottom:10px;">
    <div class="drawer-title bn" style="font-size:14px; font-weight:700;">বাকি ক্রয়সমূহ ({{ $supplier->purchases->count() }})</div>
    <div class="drawer-title en" style="display:none; font-size:14px; font-weight:700;">Outstanding Purchases ({{ $supplier->purchases->count() }})</div>
</div>

<div class="tx-section" style="max-height:360px; overflow-y:auto;">
    @forelse ($supplier->purchases as $purchase)
        <div class="tx-item" style="padding:12px 14px; border:1px solid var(--border); border-radius:10px; margin-bottom:10px; background:var(--card);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:13px; color:var(--ink-900);">#{{ $purchase->invoice_no }}</span>
                    <span style="font-size:11px; padding:1px 6px; border-radius:4px; font-weight:600; background:var(--red-100); color:var(--red-600);">বাকি</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">{{ optional($purchase->purchase_date)->format('d M, Y') }}</div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-top:8px; padding-top:8px; border-top:1px dashed var(--border); font-size:12px;">
                <div>
                    <span style="color:var(--ink-500); display:block; font-size:11px;">মোট ক্রয়</span>
                    <span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-800);">৳{{ number_format($purchase->total, 2) }}</span>
                </div>
                <div>
                    <span style="color:var(--ink-500); display:block; font-size:11px;">পরিশোধ</span>
                    <span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--green-600);">৳{{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div>
                    <span style="color:var(--ink-500); display:block; font-size:11px;">বাকি</span>
                    <span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600);">৳{{ number_format($purchase->due_amount, 2) }}</span>
                </div>
            </div>
        </div>
    @empty
        <div style="padding:20px; text-align:center; background:var(--paper-line); border-radius:10px; color:var(--ink-500); font-size:12.5px;">
            কোনো বাকি ক্রয় বিল নেই, শুধু ওপেনিং বাকি আছে
        </div>
    @endforelse
</div>

<div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <x-core::button
        type="button"
        variant="solid"
        color="green"
        size="sm"
        icon="badge-dollar-sign"
        class="btn-open-supplier-payment"
        data-url="{{ route('due-ledger.supplier.payment-modal', $supplier) }}"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">বাকি পরিশোধ</span>
        <span class="en" style="display:none;">Pay Due</span>
    </x-core::button>
    <x-core::button
        as="a"
        href="{{ route('purchase.create') }}?supplier_id={{ $supplier->id }}"
        variant="solid"
        color="primary"
        size="sm"
        icon="plus"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">নতুন ক্রয়</span>
        <span class="en" style="display:none;">New Purchase</span>
    </x-core::button>
    <x-core::button
        as="a"
        href="{{ route('suppliers.index') }}"
        variant="secondary"
        size="sm"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">সরবরাহকারী তালিকা</span>
        <span class="en" style="display:none;">Suppliers</span>
    </x-core::button>
</div>
