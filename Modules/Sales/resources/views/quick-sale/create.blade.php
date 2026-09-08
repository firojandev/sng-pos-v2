<x-core::layout
    title="দ্রুত বেচা"
    title-en="Quick Sale"
    subtitle="এক ক্লিকে দ্রুত নগদ বা ব্যাংক বিক্রয় যোগ করুন"
    subtitle-en="Add a quick counter sale via cash, bank, or both"
    active="quick-sale"
>
    @php
        $initialAccountId = old('account_id', $defaultBankAccount?->id);
    @endphp

    <div class="modal-backdrop open" id="quickSaleModal" style="display:flex; align-items:center; justify-content:center; padding:16px;">
        <div class="modal-box" style="width:580px; max-width:96vw; max-height:92vh; overflow-y:auto; padding:24px; border-radius:16px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--teal-50, #f0fdf4); color:var(--teal-700, #0f766e); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="sparkles" size="20" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">দ্রুত বেচা</span>
                            <span class="en" style="display:none;">Quick Sale</span>
                        </div>
                        <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                            <span class="bn">কাউন্টারে দ্রুত নগদ বা ব্যাংক বিক্রয় সম্পন্ন করুন</span>
                            <span class="en" style="display:none;">Complete quick counter sale via cash or bank</span>
                        </div>
                    </div>
                </div>
                <x-core::button href="{{ route('sales.index') }}" variant="ghost" size="xs" icon="x" aria-label="Close" />
            </div>

            <form method="POST" action="{{ route('quick-sale.store') }}" id="quickSaleForm">
                @csrf

                <div class="quick-sale-form-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); column-gap:14px;">
                    {{-- Row 1, Col 1: Sale Date --}}
                    <div>
                        <x-core::input
                            type="date"
                            name="sale_date"
                            id="quick_sale_date"
                            label="বিক্রির তারিখ"
                            label-en="Sale Date"
                            size="sm"
                            value="{{ old('sale_date', now()->format('Y-m-d')) }}"
                            :required="true"
                        />
                    </div>

                    {{-- Row 1, Col 2: Payment Method --}}
                    @php
                        $selectedPaymentType = old('payment_type', 'cash');
                    @endphp
                    <div id="quick-payment-type-group">
                        <x-core::select
                            id="quick-payment-type"
                            name="payment_type"
                            :value="$selectedPaymentType"
                            label="পেমেন্টের মাধ্যম"
                            label-en="Payment Method"
                            size="sm"
                            :required="true"
                        >
                            <option value="cash" {{ $selectedPaymentType === 'cash' ? 'selected' : '' }}>নগদ (Cash)</option>
                            <option value="bank" {{ $selectedPaymentType === 'bank' ? 'selected' : '' }}>ব্যাংক / MFS (Bank)</option>
                            <option value="both" {{ $selectedPaymentType === 'both' ? 'selected' : '' }}>উভয় (ক্যাশ + ব্যাংক)</option>
                        </x-core::select>
                    </div>

                    {{-- Row 2, Col 1 (when bank or both): Bank Account --}}
                    <div id="quick-account-group" style="display:{{ in_array($selectedPaymentType, ['bank', 'both']) ? 'block' : 'none' }};">
                        <x-core::select
                            id="quick-account-select"
                            name="account_id"
                            label="পেমেন্ট অ্যাকাউন্ট"
                            label-en="Payment Account"
                            size="sm"
                        >
                            @forelse ($bankAccounts as $acc)
                                @php
                                    $accNum = $acc->account_number ? ' (' . $acc->account_number . ')' : ($acc->bank_name ? ' (' . $acc->bank_name . ')' : '');
                                    $defBadge = $acc->is_default ? ' [ডিফল্ট]' : '';
                                    $accTitle = \Illuminate\Support\Str::limit($acc->name . $accNum . $defBadge, 45);
                                @endphp
                                <option value="{{ $acc->id }}"
                                        data-type="{{ $acc->type }}"
                                        title="{{ $acc->display_name }}"
                                        {{ (int) $initialAccountId === $acc->id ? 'selected' : '' }}>
                                    {{ $accTitle }}
                                </option>
                            @empty
                                <option value="">কোনো ব্যাংক বা MFS অ্যাকাউন্ট পাওয়া যায়নি</option>
                            @endforelse
                        </x-core::select>
                    </div>

                    {{-- Single Amount (Row 2 Col 1 for cash, Row 3 full-width for bank) --}}
                    <div id="quick-single-amount-group" style="display:{{ $selectedPaymentType === 'both' ? 'none' : 'block' }};">
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="amount"
                            id="quick-amount-input"
                            label="টাকার পরিমান"
                            label-en="Amount"
                            size="sm"
                            prefix="৳"
                            placeholder="0.00"
                            value="{{ old('amount') }}"
                            :stepper="false"
                            :required="true"
                        />
                    </div>

                    {{-- Profit (Row 2 Col 2) --}}
                    <div id="quick-profit-group">
                        <x-core::input
                            type="number"
                            step="0.01"
                            name="profit"
                            id="quick-profit"
                            label="লাভ"
                            label-en="Profit"
                            size="sm"
                            prefix="৳"
                            placeholder="লাভের পরিমাণ (ঐচ্ছিক)"
                            value="{{ old('profit') }}"
                            :stepper="false"
                            :required="true"
                        />
                    </div>

                    {{-- Both: Cash Paid (Row 3 Col 1) --}}
                    <div id="quick-both-cash-group" style="display:{{ $selectedPaymentType === 'both' ? 'block' : 'none' }};">
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            name="cash_amount"
                            id="quick-cash-amount-input"
                            label="ক্যাশ প্রদান"
                            label-en="Cash Paid"
                            size="sm"
                            prefix="৳"
                            placeholder="0.00"
                            value="{{ old('cash_amount') }}"
                            :stepper="false"
                        />
                    </div>

                    {{-- Both: Bank Paid (Row 3 Col 2) --}}
                    <div id="quick-both-bank-group" style="display:{{ $selectedPaymentType === 'both' ? 'block' : 'none' }};">
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            name="bank_amount"
                            id="quick-bank-amount-input"
                            label="ব্যাংক প্রদান"
                            label-en="Bank Paid"
                            size="sm"
                            prefix="৳"
                            placeholder="0.00"
                            value="{{ old('bank_amount') }}"
                            :stepper="false"
                        />
                    </div>

                    {{-- Both: Summary Pill (full width) --}}
                    <div id="quick-both-summary-group" style="grid-column:1 / -1; display:{{ $selectedPaymentType === 'both' ? 'block' : 'none' }};">
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px; padding:8px 12px; background:var(--paper); border:1px dashed var(--border); border-radius:8px; margin-top: 14px;">
                            <span style="color:var(--ink-700); font-weight:600;">
                                <span class="bn">মোট প্রদান: </span>
                                <span class="en" style="display:none;">Total Paid: </span>
                                <strong style="color:var(--teal-800); font-family:var(--font-mono, monospace); font-size:13.5px;">৳<span id="quick-both-total-paid">0.00</span></strong>
                            </span>
                        </div>
                    </div>

                    {{-- Customer Name (Row Col 1) --}}
                    <div style="position:relative;">
                        <x-core::input
                            type="text"
                            id="customerNameInput"
                            name="customer_name"
                            label="কাস্টমার নাম"
                            label-en="Customer Name"
                            size="sm"
                            placeholder="কাস্টমার নাম লিখুন বা খুঁজুন..."
                            autocomplete="off"
                            value="{{ old('customer_name') }}"
                        />
                        <div id="customerResults" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--card); border:1px solid var(--border); border-radius:8px; margin-top:4px; max-height:180px; overflow-y:auto; z-index:50; box-shadow:var(--shadow-card);"></div>
                    </div>

                    {{-- Customer Phone (Row Col 2) --}}
                    <div>
                        <x-core::input
                            type="text"
                            id="customerPhoneInput"
                            name="customer_phone"
                            label="কাস্টমার মোবাইল নম্বর"
                            label-en="Customer Mobile Number"
                            size="sm"
                            placeholder="+88 01XXXXXXXXX"
                            autocomplete="off"
                            value="{{ old('customer_phone') }}"
                        />
                        <input type="hidden" name="customer_id" id="customerIdInput" value="{{ old('customer_id') }}">
                    </div>

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div style="grid-column:1 / -1; color:var(--red-600); font-size:12px; font-weight:600; padding:8px 12px; background:var(--red-100); border-radius:8px; border:1px solid var(--red-200, #fecaca);">
                            <ul style="margin:0; padding-left:16px;">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Comment / Note (full width) --}}
                    <div style="grid-column:1 / -1;">
                        <x-core::textarea
                            name="note"
                            id="quick-note"
                            label="মন্তব্য বা বিবরণ"
                            label-en="Comment / Note"
                            size="sm"
                            placeholder="মন্তব্য লিখুন..."
                            :rows="2"
                            value="{{ old('note') }}"
                        />
                    </div>

                    {{-- Submit Button (full width) --}}
                    <div style="grid-column:1 / -1; margin-top:14px;">
                        <x-core::button
                            type="submit"
                            color="primary"
                            size="sm"
                            icon="check"
                            style="width:100%; justify-content:center; padding:10px 0; font-size:14px; font-weight:700;"
                            id="btn-submit-quick-sale"
                        >
                            <span class="bn">বিক্রি</span>
                            <span class="en" style="display:none;">Sale</span>
                        </x-core::button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        @media (max-width: 560px) {
            .quick-sale-form-grid {
                grid-template-columns: 1fr !important;
            }
            .quick-sale-form-grid > * {
                grid-column: span 1 !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    $(function () {
        var $paymentType = $('#quick-payment-type');
        var $accountGroup = $('#quick-account-group');
        var $singleAmountGroup = $('#quick-single-amount-group');
        var $bothCashGroup = $('#quick-both-cash-group');
        var $bothBankGroup = $('#quick-both-bank-group');
        var $bothSummaryGroup = $('#quick-both-summary-group');
        var $profitGroup = $('#quick-profit-group');
        var $amountInput = $('#quick-amount-input');
        var $cashAmountInput = $('#quick-cash-amount-input');
        var $bankAmountInput = $('#quick-bank-amount-input');
        var $bothTotalPaid = $('#quick-both-total-paid');

        function fmt(val) {
            return parseFloat(val || 0).toFixed(2);
        }

        function updateBothSummary() {
            var cash = parseFloat($cashAmountInput.val()) || 0;
            var bank = parseFloat($bankAmountInput.val()) || 0;
            var total = Math.max(0, cash + bank);
            $bothTotalPaid.text(fmt(total));
            $amountInput.val(total > 0 ? fmt(total) : '');
        }

        function syncPaymentTypeUI() {
            var type = $paymentType.val();
            if (type === 'cash') {
                $accountGroup.hide();
                $singleAmountGroup.show().css('grid-column', '');
                $bothCashGroup.hide();
                $bothBankGroup.hide();
                $bothSummaryGroup.hide();
                $profitGroup.css('grid-column', '');
            } else if (type === 'bank') {
                $accountGroup.show().css('grid-column', '');
                $singleAmountGroup.show().css('grid-column', '1 / -1');
                $bothCashGroup.hide();
                $bothBankGroup.hide();
                $bothSummaryGroup.hide();
                $profitGroup.css('grid-column', '');
            } else if (type === 'both') {
                $accountGroup.show().css('grid-column', '');
                $singleAmountGroup.hide();
                $bothCashGroup.show().css('grid-column', '');
                $bothBankGroup.show().css('grid-column', '');
                $bothSummaryGroup.show();
                $profitGroup.css('grid-column', '');

                var currentCash = parseFloat($cashAmountInput.val()) || 0;
                var currentBank = parseFloat($bankAmountInput.val()) || 0;
                var singleAmt = parseFloat($amountInput.val()) || 0;

                if (currentCash <= 0 && currentBank <= 0 && singleAmt > 0) {
                    $cashAmountInput.val(fmt(singleAmt));
                    $bankAmountInput.val('0.00');
                }
                updateBothSummary();
            }
        }

        $paymentType.on('change', function () {
            var type = $(this).val();
            if (type === 'cash' || type === 'bank') {
                var cash = parseFloat($cashAmountInput.val()) || 0;
                var bank = parseFloat($bankAmountInput.val()) || 0;
                if (cash > 0 || bank > 0) {
                    $amountInput.val(fmt(cash + bank));
                }
            }
            syncPaymentTypeUI();
        });

        $cashAmountInput.on('input change', updateBothSummary);
        $bankAmountInput.on('input change', updateBothSummary);

        $('#quickSaleForm').on('submit', function (e) {
            var type = $paymentType.val();
            if (type === 'both') {
                var cash = parseFloat($cashAmountInput.val()) || 0;
                var bank = parseFloat($bankAmountInput.val()) || 0;
                if (cash <= 0 && bank <= 0) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'টাকার পরিমাণ লিখুন',
                            text: 'অনুগ্রহ করে ক্যাশ অথবা ব্যাংক প্রদানের পরিমাণ লিখুন।',
                            confirmButtonText: 'ঠিক আছে'
                        });
                    } else {
                        alert('অনুগ্রহ করে ক্যাশ অথবা ব্যাংক প্রদানের পরিমাণ লিখুন।');
                    }
                    return false;
                }
                $amountInput.val(fmt(cash + bank));
            } else {
                var amt = parseFloat($amountInput.val()) || 0;
                if (amt <= 0) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'টাকার পরিমাণ লিখুন',
                            text: 'অনুগ্রহ করে বিক্রয়ের মোট টাকার পরিমাণ লিখুন।',
                            confirmButtonText: 'ঠিক আছে'
                        });
                    } else {
                        alert('অনুগ্রহ করে বিক্রয়ের মোট টাকার পরিমাণ লিখুন।');
                    }
                    return false;
                }
            }
        });

        // Customer Search Autocomplete
        var debounceTimer = null;
        function renderCustomerResults(list) {
            var $results = $('#customerResults');
            if (!list || !list.length) {
                $results.hide().empty();
                return;
            }
            var html = list.map(function (c) {
                return '<div class="cust-opt" data-id="' + c.id + '" data-name="' + String(c.name || '').replace(/"/g, '&quot;') + '" data-phone="' + (c.phone || '') + '" ' +
                    'style="padding:9px 12px; font-size:12.5px; cursor:pointer; border-bottom:1px solid var(--border); color:var(--ink-900);">' +
                    (c.name || '') + (c.phone ? ' <span style="color:var(--ink-500);">(' + c.phone + ')</span>' : '') +
                    '</div>';
            }).join('');
            $results.html(html).show();
        }

        function searchCustomer(q) {
            if (!q) {
                $('#customerResults').hide().empty();
                return;
            }
            $.getJSON("{{ route('quick-sale.customers.search') }}", { q: q }, renderCustomerResults);
        }

        $('#customerNameInput').on('input', function () {
            $('#customerIdInput').val('');
            clearTimeout(debounceTimer);
            var val = $(this).val().trim();
            debounceTimer = setTimeout(function () {
                searchCustomer(val);
            }, 250);
        });

        $(document).on('click', '.cust-opt', function () {
            $('#customerIdInput').val($(this).data('id'));
            $('#customerNameInput').val($(this).data('name'));
            $('#customerPhoneInput').val($(this).data('phone'));
            $('#customerResults').hide().empty();
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#customerNameInput, #customerResults').length) {
                $('#customerResults').hide();
            }
        });

        syncPaymentTypeUI();
    });
    </script>
    @endpush
</x-core::layout>
