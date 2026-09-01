<x-core::layout
    title="বাকির খাতা"
    title-en="Due Ledger"
    subtitle="গ্রাহক ও সরবরাহকারীর বাকি ব্যালেন্স দেখুন"
    subtitle-en="View outstanding balances for customers and suppliers"
    active="due-ledger"
>
    <div class="cash-page-head">
        <div class="ttl bn">বাকির খাতা</div>
        <div class="ttl en" style="display:none;">Due Ledger</div>

        <div class="actions">
            <div class="total-pill" style="background:var(--red-100); color:var(--red-600);">
                <span class="bn">গ্রাহক বাকি: </span><span class="en" style="display:none;">Customer Due: </span>
                <b>৳{{ number_format($customerTotalDue, 2) }}</b>
            </div>
            <div class="total-pill" style="background:var(--gold-100); color:#8A611B;">
                <span class="bn">সরবরাহকারী বাকি: </span><span class="en" style="display:none;">Supplier Due: </span>
                <b>৳{{ number_format($supplierTotalDue, 2) }}</b>
            </div>
        </div>
    </div>

    <div class="tabbar">
        <a href="{{ route('due-ledger.index', ['type' => 'customer', 'q' => $search]) }}" class="tabbtn {{ $type === 'customer' ? 'active' : '' }}">
            <span class="bn">গ্রাহক বাকি</span><span class="en">Customer Dues</span>
        </a>
        <a href="{{ route('due-ledger.index', ['type' => 'supplier', 'q' => $search]) }}" class="tabbtn {{ $type === 'supplier' ? 'active' : '' }}">
            <span class="bn">সরবরাহকারী বাকি</span><span class="en">Supplier Dues</span>
        </a>
    </div>

    <form method="GET" action="{{ route('due-ledger.index') }}" class="section-row" style="margin-top:14px;">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="filters">
            <div class="search-inline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#8B978F" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="#8B978F" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="নাম অথবা মোবাইল দিয়ে খোঁজ করুন">
            </div>
        </div>
        <button type="submit" class="btn btn-outline">
            <span class="bn">খুঁজুন</span><span class="en">Search</span>
        </button>
    </form>

    @if ($type === 'customer')
        <div class="panel" style="margin-top:0;">
            <div class="panel-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="bn">গ্রাহক</th><th class="en" style="display:none;">Customer</th>
                                <th class="bn">সর্বশেষ লেনদেন</th><th class="en" style="display:none;">Last Transaction</th>
                                <th class="bn" style="text-align:right;">মোট বাকি</th><th class="en" style="display:none; text-align:right;">Total Due</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                                <tr style="cursor:pointer;" onclick="openModal('customerDue-{{ $customer->id }}')">
                                    <td>
                                        <div class="row-avatar">
                                            <span class="av" style="background:var(--teal-700);">{{ mb_substr($customer->name, 0, 1) }}</span>
                                            <div>
                                                <div class="cell-main">{{ $customer->name }}</div>
                                                @if ($customer->phone)
                                                    <div class="cell-sub">{{ $customer->phone }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ optional($customer->sales->first())->sale_date?->format('d M, Y') ?? '—' }}</td>
                                    <td style="text-align:right; font-weight:700; color:var(--red-600);">৳{{ number_format($customer->total_due, 2) }}</td>
                                    <td onclick="event.stopPropagation();">
                                        <div class="row-actions">
                                            <button type="button" class="act" title="Details" onclick="openModal('customerDue-{{ $customer->id }}')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="5.5" r="1.6" fill="#5C6B65"/><circle cx="12" cy="12" r="1.6" fill="#5C6B65"/><circle cx="12" cy="18.5" r="1.6" fill="#5C6B65"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="helper" style="margin-top:0;">কোনো গ্রাহকের বাকি নেই</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach ($customers as $customer)
            <div class="drawer-backdrop" id="customerDue-{{ $customer->id }}">
                <div class="drawer">
                    <div class="drawer-head">
                        <div class="drawer-title bn">বাকির বিস্তারিত</div>
                        <div class="drawer-title en" style="display:none;">Due Details</div>
                        <button type="button" class="drawer-x" onclick="closeModal('customerDue-{{ $customer->id }}')">&times;</button>
                    </div>

                    <div class="tx-section">
                        <div class="tx-row">
                            <span class="lbl bn">গ্রাহক</span><span class="lbl en" style="display:none;">Customer</span>
                            <span class="val row-avatar">
                                <span class="av" style="background:var(--teal-700);">{{ mb_substr($customer->name, 0, 1) }}</span>
                                {{ $customer->name }}
                            </span>
                        </div>
                        @if ($customer->phone)
                            <div class="tx-row">
                                <span class="lbl bn">মোবাইল</span><span class="lbl en" style="display:none;">Phone</span>
                                <span class="val">{{ $customer->phone }}</span>
                            </div>
                        @endif
                        <div class="tx-row">
                            <span class="lbl bn">ওপেনিং বাকি</span><span class="lbl en" style="display:none;">Opening Due</span>
                            <span class="val">৳{{ number_format($customer->opening_due, 2) }}</span>
                        </div>
                        <div class="tx-row strong">
                            <span class="lbl bn">মোট বাকি</span><span class="lbl en" style="display:none;">Total Due</span>
                            <span class="val" style="color:var(--red-600);">৳{{ number_format($customer->total_due, 2) }}</span>
                        </div>
                    </div>

                    <div class="drawer-title bn" style="font-size:14px; margin-bottom:10px;">বাকি বিক্রয়সমূহ</div>
                    <div class="drawer-title en" style="display:none; font-size:14px; margin-bottom:10px;">Outstanding Sales</div>
                    <div class="tx-section">
                        @forelse ($customer->sales as $sale)
                            <div class="tx-item">
                                <div class="nm">#{{ $sale->invoice_no }}</div>
                                <div class="meta">
                                    <span>{{ optional($sale->sale_date)->format('d M, Y') }}</span>
                                    <span>Total: ৳{{ number_format($sale->total, 2) }}</span>
                                    <span>Due: ৳{{ number_format($sale->due_amount, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="helper" style="margin-top:0;">কোনো বাকি বিক্রয় নেই, শুধু ওপেনিং বাকি আছে</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="panel" style="margin-top:0;">
            <div class="panel-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="bn">সরবরাহকারী</th><th class="en" style="display:none;">Supplier</th>
                                <th class="bn">সর্বশেষ লেনদেন</th><th class="en" style="display:none;">Last Transaction</th>
                                <th class="bn" style="text-align:right;">মোট বাকি</th><th class="en" style="display:none; text-align:right;">Total Due</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                                <tr style="cursor:pointer;" onclick="openModal('supplierDue-{{ $supplier->id }}')">
                                    <td>
                                        <div class="row-avatar">
                                            <span class="av" style="background:var(--gold-600);">{{ mb_substr($supplier->name, 0, 1) }}</span>
                                            <div>
                                                <div class="cell-main">{{ $supplier->name }}</div>
                                                @if ($supplier->phone)
                                                    <div class="cell-sub">{{ $supplier->phone }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ optional($supplier->purchases->first())->purchase_date?->format('d M, Y') ?? '—' }}</td>
                                    <td style="text-align:right; font-weight:700; color:var(--red-600);">৳{{ number_format($supplier->total_due, 2) }}</td>
                                    <td onclick="event.stopPropagation();">
                                        <div class="row-actions">
                                            <button type="button" class="act" title="Details" onclick="openModal('supplierDue-{{ $supplier->id }}')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="5.5" r="1.6" fill="#5C6B65"/><circle cx="12" cy="12" r="1.6" fill="#5C6B65"/><circle cx="12" cy="18.5" r="1.6" fill="#5C6B65"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="helper" style="margin-top:0;">কোনো সরবরাহকারীর বাকি নেই</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach ($suppliers as $supplier)
            <div class="drawer-backdrop" id="supplierDue-{{ $supplier->id }}">
                <div class="drawer">
                    <div class="drawer-head">
                        <div class="drawer-title bn">বাকির বিস্তারিত</div>
                        <div class="drawer-title en" style="display:none;">Due Details</div>
                        <button type="button" class="drawer-x" onclick="closeModal('supplierDue-{{ $supplier->id }}')">&times;</button>
                    </div>

                    <div class="tx-section">
                        <div class="tx-row">
                            <span class="lbl bn">সরবরাহকারী</span><span class="lbl en" style="display:none;">Supplier</span>
                            <span class="val row-avatar">
                                <span class="av" style="background:var(--gold-600);">{{ mb_substr($supplier->name, 0, 1) }}</span>
                                {{ $supplier->name }}
                            </span>
                        </div>
                        @if ($supplier->phone)
                            <div class="tx-row">
                                <span class="lbl bn">মোবাইল</span><span class="lbl en" style="display:none;">Phone</span>
                                <span class="val">{{ $supplier->phone }}</span>
                            </div>
                        @endif
                        <div class="tx-row">
                            <span class="lbl bn">ওপেনিং বাকি</span><span class="lbl en" style="display:none;">Opening Due</span>
                            <span class="val">৳{{ number_format($supplier->opening_due, 2) }}</span>
                        </div>
                        <div class="tx-row strong">
                            <span class="lbl bn">মোট বাকি</span><span class="lbl en" style="display:none;">Total Due</span>
                            <span class="val" style="color:var(--red-600);">৳{{ number_format($supplier->total_due, 2) }}</span>
                        </div>
                    </div>

                    <div class="drawer-title bn" style="font-size:14px; margin-bottom:10px;">বাকি ক্রয়সমূহ</div>
                    <div class="drawer-title en" style="display:none; font-size:14px; margin-bottom:10px;">Outstanding Purchases</div>
                    <div class="tx-section">
                        @forelse ($supplier->purchases as $purchase)
                            <div class="tx-item">
                                <div class="nm">#{{ $purchase->invoice_no }}</div>
                                <div class="meta">
                                    <span>{{ optional($purchase->purchase_date)->format('d M, Y') }}</span>
                                    <span>Total: ৳{{ number_format($purchase->total, 2) }}</span>
                                    <span>Due: ৳{{ number_format($purchase->due_amount, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="helper" style="margin-top:0;">কোনো বাকি ক্রয় নেই, শুধু ওপেনিং বাকি আছে</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</x-core::layout>
