<div class="drawer-head">
    <div style="display:flex; align-items:center; gap:8px;">
        <div style="width:30px; height:30px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="drawer-title bn" style="font-size:16px; font-weight:700;">বাকির বিস্তারিত (গ্রাহক)</div>
            <div class="drawer-title en" style="display:none; font-size:16px; font-weight:700;">Customer Due Details</div>
        </div>
    </div>
    <button type="button" class="drawer-x" onclick="$('#customerDetailDrawer').removeClass('open')">&times;</button>
</div>

<div class="tx-section" style="margin-top:16px;">
    <div class="tx-row">
        <span class="lbl bn">গ্রাহক</span><span class="lbl en" style="display:none;">Customer</span>
        <span class="val row-avatar" style="display:flex; align-items:center; gap:8px;">
            <span class="av" style="width:28px; height:28px; border-radius:6px; background:var(--teal-700); color:#ffffff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;">{{ mb_substr($customer->name, 0, 1) }}</span>
            <span style="font-weight:700; color:var(--ink-900);">{{ $customer->name }}</span>
        </span>
    </div>
    @if ($customer->phone)
        <div class="tx-row">
            <span class="lbl bn">মোবাইল</span><span class="lbl en" style="display:none;">Phone</span>
            <span class="val" style="font-family:var(--font-mono, monospace);">{{ $customer->phone }}</span>
        </div>
    @endif
    @if ($customer->email)
        <div class="tx-row">
            <span class="lbl bn">ইমেইল</span><span class="lbl en" style="display:none;">Email</span>
            <span class="val">{{ $customer->email }}</span>
        </div>
    @endif
    @if ($customer->address)
        <div class="tx-row">
            <span class="lbl bn">ঠিকানা</span><span class="lbl en" style="display:none;">Address</span>
            <span class="val" style="font-size:12.5px;">{{ $customer->address }}</span>
        </div>
    @endif
    <div class="tx-row">
        <span class="lbl bn">ওপেনিং বাকি</span><span class="lbl en" style="display:none;">Opening Due</span>
        <span class="val" style="font-family:var(--font-mono, monospace);">৳{{ number_format($customer->opening_due, 2) }}</span>
    </div>
    <div class="tx-row">
        <span class="lbl bn">চালান বাকি</span><span class="lbl en" style="display:none;">Invoice Due</span>
        <span class="val" style="font-family:var(--font-mono, monospace); color:var(--gold-ink, #b45309); font-weight:600;">৳{{ number_format($customer->sales->sum('due_amount'), 2) }}</span>
    </div>
    <div class="tx-row strong" style="background:var(--paper-line); padding:10px 14px; border-radius:8px; margin-top:8px;">
        <span class="lbl bn" style="font-weight:700; font-size:14px;">মোট বকেয়া বাকি</span><span class="lbl en" style="display:none; font-weight:700; font-size:14px;">Total Outstanding</span>
        <span class="val" style="font-family:var(--font-mono, monospace); font-weight:800; font-size:16px; color:var(--red-600);">৳{{ number_format($customer->total_due, 2) }}</span>
    </div>
</div>

<div style="display:flex; align-items:center; justify-content:space-between; margin-top:20px; margin-bottom:10px;">
    <div class="drawer-title bn" style="font-size:14px; font-weight:700;">বাকি বিক্রয়সমূহ ({{ $customer->sales->count() }})</div>
    <div class="drawer-title en" style="display:none; font-size:14px; font-weight:700;">Outstanding Sales ({{ $customer->sales->count() }})</div>
</div>

<div class="tx-section" style="max-height:360px; overflow-y:auto;">
    @forelse ($customer->sales as $sale)
        <div class="tx-item" style="padding:12px 14px; border:1px solid var(--border); border-radius:10px; margin-bottom:10px; background:var(--card);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:13px; color:var(--ink-900);">#{{ $sale->invoice_no }}</span>
                    <span style="font-size:11px; padding:1px 6px; border-radius:4px; font-weight:600; background:var(--red-100); color:var(--red-600);">বাকি</span>
                </div>
                <div style="font-size:12px; color:var(--ink-500);">{{ optional($sale->sale_date)->format('d M, Y') }}</div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-top:8px; padding-top:8px; border-top:1px dashed var(--border); font-size:12px;">
                <div>
                    <span style="color:var(--ink-500); display:block; font-size:11px;">মোট বিল</span>
                    <span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-800);">৳{{ number_format($sale->total, 2) }}</span>
                </div>
                <div>
                    <span style="color:var(--ink-500); display:block; font-size:11px;">পরিশোধ</span>
                    <span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--green-600);">৳{{ number_format($sale->paid_amount, 2) }}</span>
                </div>
                <div>
                    <span style="color:var(--ink-500); display:block; font-size:11px;">বাকি</span>
                    <span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600);">৳{{ number_format($sale->due_amount, 2) }}</span>
                </div>
            </div>
        </div>
    @empty
        <div style="padding:20px; text-align:center; background:var(--paper-line); border-radius:10px; color:var(--ink-500); font-size:12.5px;">
            কোনো বাকি বিক্রয় চালান নেই, শুধু ওপেনিং বাকি আছে
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
        class="btn-open-customer-payment"
        data-url="{{ route('due-ledger.customer.payment-modal', $customer) }}"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">বাকি আদায়</span>
        <span class="en" style="display:none;">Collect Due</span>
    </x-core::button>
    <x-core::button
        as="a"
        href="{{ route('sales.create') }}?customer_id={{ $customer->id }}"
        variant="solid"
        color="primary"
        size="sm"
        icon="plus"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">নতুন বিক্রয়</span>
        <span class="en" style="display:none;">New Sale</span>
    </x-core::button>
    <x-core::button
        as="a"
        href="{{ route('customers.index') }}"
        variant="secondary"
        size="sm"
        style="flex:1; justify-content:center;"
    >
        <span class="bn">গ্রাহক তালিকা</span>
        <span class="en" style="display:none;">Customers</span>
    </x-core::button>
</div>
