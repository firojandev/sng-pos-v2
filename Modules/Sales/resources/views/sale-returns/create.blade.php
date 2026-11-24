<x-core::layout
    title="বিক্রয় ফেরত"
    title-en="Sale Return"
    subtitle="ইনভয়েস থেকে পণ্য ফেরত গ্রহণ করুন"
    subtitle-en="Process a return against this invoice"
    active="sales-ledger"
>
    {{-- Header --}}
    <div class="cash-page-head" style="margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('sales.ledger') }}" class="back" title="বেচার খাতায় ফিরে যান">
                <x-core::icon name="arrow-left" style="width:18px; height:18px;" />
            </a>
            <div>
                <div class="ttl bn" style="font-size:19px; line-height:1.2;">বিক্রয় ফেরত &mdash; ইনভয়েস #{{ $sale->invoice_no }}</div>
                <div class="ttl en" style="display:none; font-size:19px; line-height:1.2;">Sale Return &mdash; Invoice #{{ $sale->invoice_no }}</div>
                <div style="font-size:12.5px; color:var(--ink-500); margin-top:3px;">
                    <span class="bn">বিক্রয়ের তারিখ: {{ optional($sale->sale_date)->format('d M, Y') }} &middot; সময়: {{ $sale->created_at->format('h:i A') }}</span>
                    <span class="en" style="display:none;">Sale Date: {{ optional($sale->sale_date)->format('d M, Y') }} &middot; Time: {{ $sale->created_at->format('h:i A') }}</span>
                </div>
            </div>
        </div>
        <div class="actions">
            <x-core::button :href="route('sale-returns.index')" variant="secondary" size="sm" icon="rotate-ccw">
                <span class="bn">সকল ফেরত তালিকা</span><span class="en" style="display:none;">All Returns</span>
            </x-core::button>
            <x-core::button :href="route('sales.ledger')" variant="secondary" size="sm" icon="book-open">
                <span class="bn">বেচার খাতা</span><span class="en" style="display:none;">Sales Ledger</span>
            </x-core::button>
        </div>
    </div>

    {{-- Invoice Overview Cards --}}
    <div class="stat-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:18px;">
        <div class="panel" style="margin-top:0; padding:16px; display:flex; align-items:center; gap:12px;">
            <span class="av" style="background:var(--teal-700); color:var(--paper); width:42px; height:42px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; flex-shrink:0;">
                {{ mb_substr($sale->customer->name ?? '?', 0, 1) }}
            </span>
            <div style="min-width:0;">
                <div style="font-size:11.5px; color:var(--ink-500); font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                    <span class="bn">গ্রাহকের বিবরণ</span><span class="en" style="display:none;">Customer</span>
                </div>
                <div style="font-size:14px; font-weight:700; color:var(--ink-900); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ $sale->customer->name ?? 'ওয়াক-ইন গ্রাহক' }}
                </div>
                <div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace);">
                    {{ $sale->customer->phone ?? 'ফোন নম্বর নেই' }}
                </div>
            </div>
        </div>

        <x-core::stat-card
            icon="shopping-bag"
            color="teal"
            :value="'৳' . number_format((float) $sale->total, 2)"
            label="মূল মোট বিক্রয়"
            label-en="Original Sale Total"
            :subtext="'সাবটোটাল: ৳' . number_format((float) $sale->subtotal, 2)"
            :subtext-en="'Subtotal: ৳' . number_format((float) $sale->subtotal, 2)"
        />

        <x-core::stat-card
            icon="check-circle"
            color="green"
            :value="'৳' . number_format((float) $sale->paid_amount, 2)"
            value-color="green"
            label="পরিশোধিত অর্থ"
            label-en="Paid Amount"
            :subtext="$sale->payment_status === 'paid' ? 'সম্পূর্ণ পরিশোধিত' : ($sale->payment_status === 'partial' ? 'আংশিক পরিশোধিত' : 'পরিশোধ বাকি')"
            :subtext-en="ucfirst($sale->payment_status)"
        />

        <x-core::stat-card
            icon="alert-circle"
            :color="(float) $sale->due_amount > 0 ? 'red' : 'neutral'"
            :value="'৳' . number_format((float) $sale->due_amount, 2)"
            :value-color="(float) $sale->due_amount > 0 ? 'red' : null"
            label="বর্তমান বাকি"
            label-en="Current Due"
            :subtext="(float) $sale->due_amount > 0 ? 'ফেরত থেকে আগে সমন্বয় হবে' : 'কোনো বাকি নেই'"
            :subtext-en="(float) $sale->due_amount > 0 ? 'Will offset due first' : 'No outstanding due'"
        />
    </div>

    {{-- Main Return Form --}}
    <form method="POST" action="{{ route('sale-returns.store', $sale) }}" id="saleReturnForm">
        @csrf

        <div style="display:flex; gap:20px; flex-wrap:wrap; align-items:flex-start;">
            {{-- Left Column: Items & Notes --}}
            <div style="flex:1; min-width:320px;">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head">
                        <div>
                            <div class="panel-title bn" style="display:flex; align-items:center; gap:8px;">
                                <x-core::icon name="package" style="width:18px; height:18px; color:var(--teal-700);" />
                                <span>ফেরতযোগ্য পণ্যের তালিকা</span>
                            </div>
                            <div class="panel-title en" style="display:none; align-items:center; gap:8px;">
                                <x-core::icon name="package" style="width:18px; height:18px; color:var(--teal-700);" />
                                <span>Returnable Items</span>
                            </div>
                            <div style="font-size:12px; color:var(--ink-500); margin-top:3px;">
                                <span class="bn">যে পণ্যগুলো ফেরত নিতে চান সেগুলোর সঠিক ফেরত পরিমাণ (Qty) ঘরে লিখুন।</span>
                                <span class="en" style="display:none;">Enter return quantity for the items being returned.</span>
                            </div>
                        </div>
                    </div>

                    <div class="panel-body">
                        @if ($errors->has('items'))
                            <div style="background:var(--red-100); border:1px solid var(--red-200, var(--border)); border-radius:10px; padding:12px 16px; margin-bottom:16px; color:var(--red-600); display:flex; align-items:center; gap:10px;">
                                <x-core::icon name="alert-triangle" style="width:18px; height:18px; flex-shrink:0;" />
                                <div style="font-size:13px; font-weight:600;">{{ $errors->first('items') }}</div>
                            </div>
                        @endif

                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                                        <th class="bn" style="text-align:right;">বিক্রিত</th><th class="en" style="display:none; text-align:right;">Sold</th>
                                        <th class="bn" style="text-align:right;">পূর্বের ফেরত</th><th class="en" style="display:none; text-align:right;">Returned</th>
                                        <th class="bn" style="text-align:right;">ফেরতযোগ্য</th><th class="en" style="display:none; text-align:right;">Returnable</th>
                                        <th class="bn" style="text-align:right;">মূল্য</th><th class="en" style="display:none; text-align:right;">Price</th>
                                        <th class="bn" style="text-align:right; width:130px;">ফেরত পরিমাণ</th><th class="en" style="display:none; text-align:right; width:130px;">Return Qty</th>
                                        <th class="bn" style="text-align:right; width:120px;">মোট ফেরত</th><th class="en" style="display:none; text-align:right; width:120px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sale->items as $item)
                                        @php
                                            $retData = $returnedByItem->get($item->id);
                                            $alreadyReturned = is_object($retData) ? (float) ($retData->returned ?? 0) : (is_array($retData) ? (float) ($retData['returned'] ?? 0) : (float) $retData);
                                            $alreadyReturnedDiscount = is_object($retData) ? (float) ($retData->returned_discount ?? 0) : (is_array($retData) ? (float) ($retData['returned_discount'] ?? 0) : 0);
                                            $returnable = max((float) $item->quantity - $alreadyReturned, 0);
                                            $remainingDiscount = max((float) $item->discount - $alreadyReturnedDiscount, 0);
                                            $unitName = $item->unit?->short_code ?? $item->unit?->name ?? '';
                                            $unitDiscount = (float) $item->quantity > 0 ? (float) $item->discount / (float) $item->quantity : 0;
                                            $netUnitPrice = max(0, (float) $item->unit_price - $unitDiscount);
                                        @endphp
                                        <tr data-row="{{ $loop->index }}">
                                            <td>
                                                <div class="cell-main" style="color:var(--ink-900);">{{ $item->product->name ?? '—' }}</div>
                                                <div class="cell-sub" style="font-size:11px; color:var(--ink-500); display:flex; gap:8px;">
                                                    @if($item->product?->sku)
                                                        <span>SKU: {{ $item->product->sku }}</span>
                                                    @endif
                                                    @if($unitName)
                                                        <span>&middot; ইউনিট: {{ $unitName }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="text-align:right; font-family:var(--font-mono, monospace);">
                                                {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                                                @if($unitName) <span style="font-size:11px; color:var(--ink-500);">{{ $unitName }}</span> @endif
                                            </td>
                                            <td style="text-align:right; font-family:var(--font-mono, monospace); color:var(--ink-600);">
                                                {{ rtrim(rtrim(number_format((float) $alreadyReturned, 2), '0'), '.') }}
                                                @if($unitName) <span style="font-size:11px; color:var(--ink-500);">{{ $unitName }}</span> @endif
                                            </td>
                                            <td style="text-align:right;">
                                                @if ($returnable > 0)
                                                    <span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--teal-700);">
                                                        {{ rtrim(rtrim(number_format($returnable, 2), '0'), '.') }}
                                                    </span>
                                                    @if($unitName) <span style="font-size:11px; color:var(--ink-500);">{{ $unitName }}</span> @endif
                                                @else
                                                    <x-core::badge size="xs" color="gray" label="সম্পূর্ণ ফেরত" label-en="Fully Returned" />
                                                @endif
                                            </td>
                                            <td style="text-align:right;">
                                                <div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">
                                                    ৳{{ number_format((float) $item->unit_price, 2) }}
                                                </div>
                                                @if ((float) $item->discount > 0)
                                                    <div class="cell-sub" style="font-size:11px; color:var(--red-600); margin-top:1px;">
                                                        <span class="bn">ছাড়: -৳{{ number_format((float) $item->discount, 2) }}</span>
                                                    </div>
                                                    <div class="cell-sub" style="font-size:11px; color:var(--teal-700); font-weight:600;">
                                                        <span class="bn">কার্যকর: ৳{{ number_format($netUnitPrice, 2) }}</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="text-align:right;">
                                                @if ($returnable > 0)
                                                    <input type="hidden" name="items[{{ $loop->index }}][sale_item_id]" value="{{ $item->id }}">
                                                    <div style="max-width:120px; margin-left:auto;">
                                                        <x-core::input
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            max="{{ $returnable }}"
                                                            name="items[{{ $loop->index }}][quantity]"
                                                            value="{{ old('items.' . $loop->index . '.quantity', 0) }}"
                                                            size="sm"
                                                            :no-margin="true"
                                                            class="return-qty-input"
                                                            data-price="{{ (float) $item->unit_price }}"
                                                            data-sold-qty="{{ (float) $item->quantity }}"
                                                            data-discount="{{ (float) $item->discount }}"
                                                            data-remaining-discount="{{ $remainingDiscount }}"
                                                            data-max="{{ $returnable }}"
                                                            data-row="{{ $loop->index }}"
                                                            style="text-align:right; font-family:var(--font-mono, monospace); font-weight:700;"
                                                        />
                                                    </div>
                                                @else
                                                    <span style="color:var(--ink-400);">&mdash;</span>
                                                @endif
                                            </td>
                                            <td style="text-align:right;">
                                                <span class="row-total" id="row-total-{{ $loop->index }}" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">
                                                    ৳0.00
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top:18px;">
                            <x-core::textarea
                                name="note"
                                size="sm"
                                label="ফেরতের কারণ / মন্তব্য"
                                label-en="Reason / Note"
                                placeholder="ফেরতের কারণ বা বিবরণ লিখুন (ঐচ্ছিক)"
                                placeholder-en="Reason or details of the return (optional)"
                                rows="2"
                            >{{ old('note') }}</x-core::textarea>
                        </div>

                        {{-- Info alert --}}
                        <div style="background:var(--paper); border:1px solid var(--border); border-left:4px solid var(--teal-600); border-radius:10px; padding:12px 16px; margin-top:16px; display:flex; align-items:flex-start; gap:12px;">
                            <x-core::icon name="info" style="width:18px; height:18px; color:var(--teal-600); flex-shrink:0; margin-top:1px;" />
                            <div style="font-size:12.5px; color:var(--ink-700); line-height:1.5;">
                                <span class="bn">
                                    <strong>হিসাব সমন্বয় নীতি:</strong> শূন্য (০) পরিমাণের আইটেম বাদ দিয়ে ফেরত জমা হবে। ফেরত গ্রহণের পর পণ্যের স্টক ও ইনভেন্টরিতে বৃদ্ধি ঘটবে, মোট বিক্রয় ও অর্জিত লাভ স্বয়ংক্রিয়ভাবে সমন্বয় হবে। ইনভয়েসে বাকি (Due) থাকলে তা থেকে প্রথমে সমন্বয় হবে এবং অতিরিক্ত অবশিষ্ট অর্থ ক্যাশবক্স থেকে নগদ ফেরত হিসেবে বিবেচিত হবে।
                                </span>
                                <span class="en" style="display:none;">
                                    <strong>Adjustment Policy:</strong> Zero quantity rows are ignored. Processed returns automatically restore inventory, recalculate net sales and profit, offset any invoice due balance first, and log any remaining value as a cash refund from the cashbox.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Return Calculation Sidebar Widget --}}
            <div style="width:330px; flex-shrink:0;">
                <div class="panel" style="margin-top:0; position:sticky; top:18px;">
                    <div class="panel-head">
                        <div class="panel-title bn" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="calculator" style="width:18px; height:18px; color:var(--teal-700);" />
                            <span>ফেরতের সারসংক্ষেপ</span>
                        </div>
                        <div class="panel-title en" style="display:none; align-items:center; gap:8px;">
                            <x-core::icon name="calculator" style="width:18px; height:18px; color:var(--teal-700);" />
                            <span>Return Summary</span>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div style="margin-bottom:16px;">
                            <x-core::input
                                type="date"
                                name="return_date"
                                value="{{ old('return_date', now()->format('Y-m-d')) }}"
                                size="sm"
                                label="ফেরতের তারিখ"
                                label-en="Return Date"
                                icon="calendar"
                                required
                            />
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:var(--ink-600);"><span class="bn">নির্বাচিত আইটেম:</span><span class="en" style="display:none;">Selected Items:</span></span>
                                <span id="summary-items-count" style="font-weight:700; color:var(--ink-900);">0 টি</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:var(--ink-600);"><span class="bn">মোট ফেরত পরিমাণ:</span><span class="en" style="display:none;">Total Return Qty:</span></span>
                                <span id="summary-total-qty" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">0</span>
                            </div>
                            <div id="summary-gross-row" style="display:none; justify-content:space-between; align-items:center;">
                                <span style="color:var(--ink-600);"><span class="bn">বিক্রয় মূল্য (গ্রস):</span><span class="en" style="display:none;">Gross Value:</span></span>
                                <span id="summary-gross-subtotal" style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-700);">৳0.00</span>
                            </div>
                            <div id="summary-discount-row" style="display:none; justify-content:space-between; align-items:center;">
                                <span style="color:var(--red-600); font-weight:600;"><span class="bn">(-) পণ্য ছাড়:</span><span class="en" style="display:none;">Product Discount:</span></span>
                                <span id="summary-discount" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600);">-৳0.00</span>
                            </div>
                            <div id="summary-inv-discount-row" style="display:none; justify-content:space-between; align-items:center;">
                                <span style="color:var(--red-600); font-weight:600;"><span class="bn">(-) ইনভয়েস ছাড়:</span><span class="en" style="display:none;">Invoice Discount:</span></span>
                                <span id="summary-inv-discount" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600);">-৳0.00</span>
                            </div>
                            <div id="summary-adjustment-row" style="display:none; justify-content:space-between; align-items:center;">
                                <span style="color:var(--ink-700); font-weight:600;"><span class="bn">(+/-) সমন্বয়:</span><span class="en" style="display:none;">Adjustment:</span></span>
                                <span id="summary-adjustment" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800);">৳0.00</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-weight:600; color:var(--ink-700);"><span class="bn">মোট প্রদেয় ফেরত:</span><span class="en" style="display:none;">Net Return Total:</span></span>
                                <span id="summary-subtotal" style="font-family:var(--font-mono, monospace); font-weight:800; font-size:16px; color:var(--teal-700);">৳0.00</span>
                            </div>

                            <div style="border-top:1px dashed var(--border); margin:4px 0;"></div>

                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="color:var(--ink-600);"><span class="bn">বাকি সমন্বয়:</span><span class="en" style="display:none;">Due Offset:</span></span>
                                    <span id="summary-due-offset" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--gold-600);">-৳0.00</span>
                                </div>
                                <div style="font-size:11px; color:var(--ink-400); margin-top:2px; text-align:right;">
                                    <span class="bn">ইনভয়েসের বর্তমান বাকি: ৳{{ number_format((float) $sale->due_amount, 2) }}</span>
                                    <span class="en" style="display:none;">Current Due: ৳{{ number_format((float) $sale->due_amount, 2) }}</span>
                                </div>
                            </div>

                            {{-- Cash Refund Alert Box --}}
                            <div style="background:var(--red-100); border:1px solid var(--red-200, var(--border)); border-radius:10px; padding:12px 14px; margin-top:6px;">
                                <div style="font-size:12px; font-weight:600; color:var(--red-600);">
                                    <span class="bn">গ্রাহককে নগদ ফেরত:</span>
                                    <span class="en" style="display:none;">Cash Refund:</span>
                                </div>
                                <div id="summary-cash-refund" style="font-family:var(--font-mono, monospace); font-size:22px; font-weight:800; color:var(--red-600); margin-top:2px;">
                                    ৳0.00
                                </div>
                                <div id="cash-refund-hint" style="font-size:11px; color:var(--ink-600); margin-top:4px;">
                                    <span class="bn">বাকি কাটার পর ক্যাশবক্স থেকে পরিশোধ হবে</span>
                                    <span class="en" style="display:none;">Payable from cashbox after offsetting due</span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div style="margin-top:20px; display:flex; flex-direction:column; gap:8px;">
                            <x-core::button
                                type="submit"
                                color="primary"
                                size="sm"
                                icon="rotate-ccw"
                                style="width:100%; justify-content:center;"
                                id="btn-submit-return"
                            >
                                <span class="bn">ফেরত সম্পন্ন করুন</span>
                                <span class="en" style="display:none;">Process Return</span>
                            </x-core::button>

                            <x-core::button
                                :href="route('sales.ledger')"
                                variant="secondary"
                                size="sm"
                                style="width:100%; justify-content:center;"
                            >
                                <span class="bn">বাতিল</span>
                                <span class="en" style="display:none;">Cancel</span>
                            </x-core::button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            $(function () {
                var saleDue = {{ (float) $sale->due_amount }};
                var saleSubtotal = {{ (float) $sale->subtotal }};
                var saleInvoiceDiscount = {{ (float) ($sale->discount ?? 0) }};
                var saleAdjustment = {{ (float) ($sale->adjustment ?? 0) }};

                function formatCurrency(amount) {
                    return '৳' + Number(amount).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function recalculateReturn() {
                    var grossSubtotal = 0;
                    var totalProductDiscount = 0;
                    var netLinesSubtotal = 0;
                    var totalQty = 0;
                    var activeItems = 0;

                    $('.return-qty-input').each(function () {
                        var $input = $(this);
                        var rowIndex = $input.data('row');
                        var price = parseFloat($input.data('price')) || 0;
                        var soldQty = parseFloat($input.data('sold-qty')) || 1;
                        var itemTotalDiscount = parseFloat($input.data('discount')) || 0;
                        var remainingDiscount = parseFloat($input.data('remaining-discount')) || 0;
                        var maxQty = parseFloat($input.data('max')) || 0;
                        var qty = parseFloat($input.val()) || 0;

                        if (qty < 0) {
                            qty = 0;
                            $input.val(0);
                        } else if (qty > maxQty) {
                            qty = maxQty;
                            $input.val(maxQty);
                        }

                        var lineGross = qty * price;
                        var lineDiscount = 0;
                        if (qty >= maxQty) {
                            lineDiscount = remainingDiscount;
                        } else if (soldQty > 0) {
                            lineDiscount = (itemTotalDiscount / soldQty) * qty;
                        }
                        lineDiscount = Math.round(lineDiscount * 100) / 100;
                        var lineNet = Math.max(0, Math.round((lineGross - lineDiscount) * 100) / 100);

                        var rowHtml = '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">' + formatCurrency(lineNet) + '</span>';
                        if (lineDiscount > 0) {
                            rowHtml += '<div style="font-size:11px; font-weight:500; color:var(--red-600); margin-top:1px;">(-' + formatCurrency(lineDiscount) + ' ছাড়)</div>';
                        }
                        $('#row-total-' + rowIndex).html(rowHtml);

                        if (qty > 0) {
                            activeItems++;
                            totalQty += qty;
                            grossSubtotal += lineGross;
                            totalProductDiscount += lineDiscount;
                            netLinesSubtotal += lineNet;
                        }
                    });

                    grossSubtotal = Math.round(grossSubtotal * 100) / 100;
                    totalProductDiscount = Math.round(totalProductDiscount * 100) / 100;
                    netLinesSubtotal = Math.round(netLinesSubtotal * 100) / 100;

                    // Prorate invoice-level discount and adjustment
                    var subtotalRatio = saleSubtotal > 0 ? Math.min(1, netLinesSubtotal / saleSubtotal) : 0;
                    var invDiscount = Math.round(saleInvoiceDiscount * subtotalRatio * 100) / 100;
                    var adj = Math.round(saleAdjustment * subtotalRatio * 100) / 100;

                    var finalNetReturn = Math.max(0, Math.round((netLinesSubtotal - invDiscount + adj) * 100) / 100);
                    var offsetDue = Math.min(saleDue, finalNetReturn);
                    var cashRefund = Math.max(0, Math.round((finalNetReturn - offsetDue) * 100) / 100);

                    $('#summary-items-count').text(activeItems + ' টি');
                    $('#summary-total-qty').text(totalQty.toFixed(2).replace(/\.00$/, ''));
                    $('#summary-gross-subtotal').text(formatCurrency(grossSubtotal));
                    $('#summary-discount').text('-' + formatCurrency(totalProductDiscount));
                    $('#summary-inv-discount').text('-' + formatCurrency(invDiscount));
                    $('#summary-adjustment').text((adj >= 0 ? '+' : '') + formatCurrency(adj));
                    $('#summary-subtotal').text(formatCurrency(finalNetReturn));
                    $('#summary-due-offset').text('-' + formatCurrency(offsetDue));
                    $('#summary-cash-refund').text(formatCurrency(cashRefund));

                    if (totalProductDiscount > 0 || invDiscount > 0 || adj !== 0) {
                        $('#summary-gross-row').css('display', 'flex');
                    } else {
                        $('#summary-gross-row').hide();
                    }

                    if (totalProductDiscount > 0) {
                        $('#summary-discount-row').css('display', 'flex');
                    } else {
                        $('#summary-discount-row').hide();
                    }

                    if (invDiscount > 0) {
                        $('#summary-inv-discount-row').css('display', 'flex');
                    } else {
                        $('#summary-inv-discount-row').hide();
                    }

                    if (adj !== 0) {
                        $('#summary-adjustment-row').css('display', 'flex');
                    } else {
                        $('#summary-adjustment-row').hide();
                    }

                    if (cashRefund > 0) {
                        $('#cash-refund-hint').show();
                    } else if (finalNetReturn > 0 && offsetDue > 0) {
                        $('#cash-refund-hint').html('<span class="bn" style="color:var(--green-ink);">সম্পূর্ণ ফেরত বাকি থেকে সমন্বয় হয়েছে</span><span class="en" style="display:none; color:var(--green-ink);">Fully adjusted against invoice due</span>');
                    } else {
                        $('#cash-refund-hint').html('<span class="bn">বাকি কাটার পর ক্যাশবক্স থেকে পরিশোধ হবে</span><span class="en" style="display:none;">Payable from cashbox after offsetting due</span>');
                    }
                }

                $(document).on('input change', '.return-qty-input', function () {
                    recalculateReturn();
                });

                // Initial calculation on load
                recalculateReturn();

                // Form submit handler with validation & SweetAlert2 confirmation
                $('#saleReturnForm').on('submit', function (e) {
                    var totalQty = 0;
                    $('.return-qty-input').each(function () {
                        totalQty += parseFloat($(this).val()) || 0;
                    });

                    if (totalQty <= 0) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'পরিমাণ প্রয়োজন',
                                text: 'অনুগ্রহ করে অন্তত একটি পণ্যের ফেরত পরিমাণ লিখুন।',
                                confirmButtonColor: 'var(--teal-700)'
                            });
                        } else {
                            alert('অনুগ্রহ করে অন্তত একটি পণ্যের ফেরত পরিমাণ লিখুন।');
                        }
                        return false;
                    }

                    // Form confirmed submission
                    if (!$(this).data('confirmed')) {
                        e.preventDefault();
                        var $form = $(this);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'ফেরত নিশ্চিতকরণ',
                                text: 'আপনি কি নিশ্চিত যে এই ইনভয়েস থেকে পণ্য ফেরত নিতে চান?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: 'var(--teal-700)',
                                cancelButtonColor: 'var(--ink-500)',
                                confirmButtonText: 'হ্যাঁ, ফেরত নিন',
                                cancelButtonText: 'বাতিল'
                            }).then(function (result) {
                                if (result.isConfirmed) {
                                    $form.data('confirmed', true);
                                    $form.submit();
                                }
                            });
                        } else {
                            if (confirm('আপনি কি নিশ্চিত যে এই ইনভয়েস থেকে পণ্য ফেরত নিতে চান?')) {
                                $form.data('confirmed', true);
                                $form.submit();
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-core::layout>
