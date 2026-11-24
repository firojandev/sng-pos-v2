<x-core::layout
    title="বিক্রয় ফেরত"
    title-en="Sale Returns"
    subtitle="সকল বিক্রয় ফেরতের তালিকা"
    subtitle-en="All processed sale returns"
    active="sales-ledger"
>
    {{-- Header --}}
    <div class="cash-page-head" style="margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('sales.ledger') }}" class="back" title="বেচার খাতায় ফিরে যান">
                <x-core::icon name="arrow-left" style="width:18px; height:18px;" />
            </a>
            <div>
                <div class="ttl bn" style="font-size:19px; line-height:1.2;">বিক্রয় ফেরত তালিকা</div>
                <div class="ttl en" style="display:none; font-size:19px; line-height:1.2;">Sale Returns</div>
                <div style="font-size:12.5px; color:var(--ink-500); margin-top:3px;">
                    <span class="bn">গ্রাহকদের থেকে পণ্য ফেরত ও অর্থ ফেরতের ইতিহাস</span>
                    <span class="en" style="display:none;">History of returned products and cash refunds</span>
                </div>
            </div>
        </div>
        <div class="actions">
            <x-core::button :href="route('sales.ledger')" variant="secondary" size="sm" icon="book-open">
                <span class="bn">বেচার খাতা</span><span class="en" style="display:none;">Sales Ledger</span>
            </x-core::button>
            @can('sales.create')
                <x-core::button :href="route('sales.create')" color="primary" size="sm" icon="plus">
                    <span class="bn">নতুন বিক্রয়</span><span class="en" style="display:none;">New Sale</span>
                </x-core::button>
            @endcan
        </div>
    </div>

    {{-- Executive Summary Stat Grid --}}
    <div class="stat-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:18px;">
        <x-core::stat-card
            icon="rotate-ccw"
            color="teal"
            :value="$totalReturnsCount ?? 0"
            label="মোট ফেরত চালানের সংখ্যা"
            label-en="Total Return Invoices"
        />
        <x-core::stat-card
            icon="shopping-bag"
            color="blue"
            :value="'৳' . number_format((float) ($totalReturnSubtotal ?? 0), 2)"
            label="মোট ফেরত পণ্যের মূল্য"
            label-en="Total Return Value"
        />
        <x-core::stat-card
            icon="tag"
            color="gold"
            :value="'৳' . number_format((float) ($totalReturnDiscount ?? 0), 2)"
            value-color="gold"
            label="মোট সমন্বিত ছাড়"
            label-en="Total Discount Reversed"
        />
        <x-core::stat-card
            icon="check-circle"
            color="red"
            :value="'৳' . number_format((float) ($totalRefundAmount ?? 0), 2)"
            value-color="red"
            label="ক্যাশবক্স থেকে মোট নগদ ফেরত"
            label-en="Total Cash Refunded"
        />
    </div>

    {{-- Search Toolbar --}}
    <form method="GET" action="{{ route('sale-returns.index') }}" class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
            <div style="flex:1; min-width:240px; max-width:380px;">
                <x-core::input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    size="sm"
                    :no-margin="true"
                    placeholder="ফেরত নং, ইনভয়েস নং বা গ্রাহক খুঁজুন"
                    placeholder-en="Search by Return #, Invoice # or Customer"
                    icon="search"
                    clearable
                />
            </div>
            <x-core::button type="submit" variant="secondary" size="sm" icon="search">
                <span class="bn">খুঁজুন</span><span class="en" style="display:none;">Search</span>
            </x-core::button>
            @if ($search)
                <x-core::button :href="route('sale-returns.index')" variant="ghost" size="sm" icon="rotate-ccw">
                    <span class="bn">রিসেট</span><span class="en" style="display:none;">Reset</span>
                </x-core::button>
            @endif
        </div>
    </form>

    {{-- Returns Table Panel --}}
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
                            <th class="bn" style="text-align:right;">ফেরত সাবটোটাল</th><th class="en" style="display:none; text-align:right;">Subtotal</th>
                            <th class="bn">সমন্বয় ও নগদ ফেরত</th><th class="en" style="display:none;">Settlement & Refund</th>
                            <th class="bn">তারিখ ও ইউজার</th><th class="en" style="display:none;">Date & User</th>
                            <th class="bn" style="text-align:center; width:80px;">অ্যাকশন</th><th class="en" style="display:none; text-align:center; width:80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $return)
                            @php
                                $offsetDue = round(max(0, (float) $return->subtotal - (float) $return->refund_amount), 2);
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">
                                        #{{ $return->return_no }}
                                    </span>
                                </td>
                                <td>
                                    @if ($return->sale)
                                        <a
                                            href="javascript:void(0)"
                                            onclick="showSaleInvoice('{{ route('sales.invoice-modal', $return->sale_id) }}')"
                                            style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--teal-700); text-decoration:none;"
                                            title="ইনভয়েস স্লিপ দেখুন"
                                        >
                                            #{{ $return->sale->invoice_no }}
                                        </a>
                                    @else
                                        <span style="color:var(--ink-400);">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-avatar">
                                        <span class="av" style="background:var(--teal-700); color:var(--paper); width:28px; height:28px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">
                                            {{ mb_substr($return->sale?->customer?->name ?? '?', 0, 1) }}
                                        </span>
                                        <div>
                                            <div style="font-weight:600; color:var(--ink-900);">
                                                {{ $return->sale?->customer?->name ?? 'ওয়াক-ইন গ্রাহক' }}
                                            </div>
                                            @if ($return->sale?->customer?->phone)
                                                <div class="cell-sub" style="font-size:11px; color:var(--ink-500); font-family:var(--font-mono, monospace);">
                                                    {{ $return->sale->customer->phone }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:2px;">
                                        <x-core::badge size="xs" color="gray" :label="$return->items->count() . ' টি আইটেম'" :label-en="$return->items->count() . ' items'" />
                                        @foreach ($return->items->take(2) as $retItem)
                                            <div class="cell-sub" style="font-size:11px; color:var(--ink-500);">
                                                {{ $retItem->product?->name ?? '—' }} &times; {{ rtrim(rtrim(number_format((float) $retItem->quantity, 2), '0'), '.') }}
                                            </div>
                                        @endforeach
                                        @if ($return->items->count() > 2)
                                            <div class="cell-sub" style="font-size:10.5px; color:var(--teal-700); font-weight:600;">
                                                +{{ $return->items->count() - 2 }} আরো
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">
                                        ৳{{ number_format((float) ($return->total ?: $return->subtotal), 2) }}
                                    </span>
                                    @php
                                        $retDiscountTotal = (float) ($return->product_discount ?? 0) + (float) ($return->discount ?? 0);
                                    @endphp
                                    @if ($retDiscountTotal > 0)
                                        <div class="cell-sub" style="font-size:11px; color:var(--red-600); margin-top:1px;">
                                            <span class="bn">ছাড়: </span>-৳{{ number_format($retDiscountTotal, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:3px;">
                                        @if ((float) $return->refund_amount > 0)
                                            <x-core::badge
                                                size="xs"
                                                color="danger"
                                                :dot="true"
                                                :label="'নগদ ফেরত: ৳' . number_format((float) $return->refund_amount, 2)"
                                                :label-en="'Refund: ৳' . number_format((float) $return->refund_amount, 2)"
                                            />
                                        @endif
                                        @if ($offsetDue > 0)
                                            <div style="font-size:11.5px; color:var(--ink-600);">
                                                <span class="bn">বাকি সমন্বয়: </span>
                                                <span class="en" style="display:none;">Due offset: </span>
                                                <strong style="font-family:var(--font-mono, monospace); color:var(--gold-600);">৳{{ number_format($offsetDue, 2) }}</strong>
                                            </div>
                                        @endif
                                        @if ((float) $return->refund_amount <= 0 && $offsetDue <= 0)
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12.5px; font-weight:600; color:var(--ink-900);">
                                        {{ optional($return->return_date)->format('d M, Y') }}
                                    </div>
                                    <div class="cell-sub" style="font-size:11px; color:var(--ink-500);">
                                        {{ $return->creator?->name ?? '—' }} &middot; {{ $return->created_at->format('h:i A') }}
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    @if ($return->sale)
                                        <x-core::button
                                            type="button"
                                            size="xs"
                                            variant="ghost"
                                            color="secondary"
                                            icon="printer"
                                            onclick="showSaleInvoice('{{ route('sales.invoice-modal', $return->sale_id) }}')"
                                            title="ইনভয়েস স্লিপ দেখুন"
                                        />
                                    @else
                                        <span style="color:var(--ink-400);">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-core::table.empty
                                        icon="rotate-ccw"
                                        title="কোনো বিক্রয় ফেরত নেই"
                                        title-en="No sale returns found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($returns->hasPages())
                <div style="margin-top:16px;">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Dynamic Sales Invoice Modal Container --}}
    <div id="saleInvoiceModalContainer"></div>

    @push('scripts')
        <script>
            $(function () {
                window.showSaleInvoice = function (url) {
                    if (!url) return;
                    $.get(url, function (html) {
                        $('#saleInvoiceModalContainer').html(html);
                        if (typeof refreshLucideIcons === 'function') {
                            refreshLucideIcons();
                        }
                        if (typeof openModal === 'function') {
                            openModal('saleInvoiceModal');
                        }
                    }).fail(function () {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি!',
                                text: 'ইনভয়েস স্লিপ লোড করতে সমস্যা হয়েছে।'
                            });
                        } else {
                            alert('ইনভয়েস স্লিপ লোড করতে সমস্যা হয়েছে।');
                        }
                    });
                };
            });
        </script>
    @endpush
</x-core::layout>
