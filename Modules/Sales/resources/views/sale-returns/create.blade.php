<x-core::layout
    title="বিক্রয় ফেরত"
    title-en="Sale Return"
    subtitle="ইনভয়েস থেকে পণ্য ফেরত নিন"
    subtitle-en="Process a return against this invoice"
    active="sales"
>
    <div class="panel" style="margin-top:0; max-width:820px;">
        <div class="panel-head">
            <div>
                <div class="panel-title bn">ইনভয়েস #{{ $sale->invoice_no }}</div>
                <div class="panel-title en" style="display:none;">Invoice #{{ $sale->invoice_no }}</div>
            </div>
            <div>
                <span class="bn">গ্রাহক: </span><span class="en" style="display:none;">Customer: </span>
                <b>{{ $sale->customer->name ?? 'ওয়াক-ইন গ্রাহক' }}</b>
            </div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('sale-returns.store', $sale) }}">
                @csrf

                @error('items') <div class="field-error" style="margin-bottom:14px;">{{ $message }}</div> @enderror

                <div class="field" style="margin-top:0; max-width:260px;">
                    <label class="bn">ফেরতের তারিখ</label><label class="en" style="display:none;">Return Date</label>
                    <input type="date" name="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                </div>

                <div class="table-wrap" style="margin-top:14px;">
                    <table>
                        <thead>
                            <tr>
                                <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                                <th class="bn">বিক্রিত পরিমাণ</th><th class="en" style="display:none;">Sold Qty</th>
                                <th class="bn">ফেরতযোগ্য</th><th class="en" style="display:none;">Returnable</th>
                                <th class="bn">মূল্য</th><th class="en" style="display:none;">Price</th>
                                <th class="bn">ফেরত পরিমাণ</th><th class="en" style="display:none;">Return Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                @php
                                    $returned = (float) ($returnedByItem[$item->id] ?? 0);
                                    $returnable = max((float) $item->quantity - $returned, 0);
                                @endphp
                                <tr>
                                    <td class="cell-main">{{ $item->product->name ?? '—' }}</td>
                                    <td>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                                    <td>{{ rtrim(rtrim(number_format($returnable, 2), '0'), '.') }}</td>
                                    <td>৳{{ number_format($item->unit_price, 2) }}</td>
                                    <td style="width:130px;">
                                        @if ($returnable > 0)
                                            <input type="hidden" name="items[{{ $loop->index }}][sale_item_id]" value="{{ $item->id }}">
                                            <input type="number" step="0.01" min="0" max="{{ $returnable }}" name="items[{{ $loop->index }}][quantity]" value="0" style="width:100%; border:1px solid var(--border); border-radius:8px; padding:7px 9px; font-family:'Manrope',sans-serif; text-align:right;">
                                        @else
                                            <span class="badge b-grey bn">সম্পূর্ণ ফেরত</span><span class="badge b-grey en" style="display:none;">Fully Returned</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="field" style="max-width:520px;">
                    <label class="bn">কারণ / নোট</label><label class="en" style="display:none;">Reason / Note</label>
                    <textarea name="note" placeholder="ফেরতের কারণ (ঐচ্ছিক)">{{ old('note') }}</textarea>
                </div>

                <div class="helper">
                    <span class="bn">শূন্য (০) পরিমাণের আইটেম বাদ দিয়ে জমা দিলে চলবে। বাকি থাকলে আগে তা থেকে সমন্বয় হবে, অতিরিক্ত অর্থ ক্যাশবক্সে ফেরত হিসেবে যোগ হবে।</span>
                    <span class="en" style="display:none;">Rows left at zero are skipped. The return first offsets any due balance; anything beyond that is logged as a cash refund.</span>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px; max-width:320px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">ফেরত সম্পন্ন করুন</span><span class="en">Process Return</span>
                    </button>
                    <a href="{{ route('sales.ledger') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
