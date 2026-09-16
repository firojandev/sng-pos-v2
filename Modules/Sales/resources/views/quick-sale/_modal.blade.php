@php
    $authUser = auth()->user();
    $canQuickSale = $authUser && $authUser->shop && $authUser->shop->hasFeature('quick-sale') && $authUser->can('quick-sale.create');
@endphp

@if ($canQuickSale)
@php
    $accounts = \Modules\Finance\Models\Account::active()->orderByDesc('is_default')->orderBy('name')->get();
    $bankAccounts = $accounts->whereIn('type', ['bank', 'mfs']);
    $defaultCashAccount = $accounts->firstWhere('type', 'cash');
    $defaultBankAccount = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first();
@endphp

<div class="modal-backdrop" id="quickSaleModal" style="z-index:9999; display:none; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-box" style="width:580px; max-width:96vw; max-height:92vh; overflow-y:auto; padding:24px; border-radius:16px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
        <div class="modal-head" style="margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
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
            <x-core::button type="button" variant="ghost" size="xs" icon="x" aria-label="Close" class="quick-sale-modal-close" />
        </div>

        <form method="POST" action="{{ route('quick-sale.store') }}" id="quickSaleGlobalForm">
            @csrf

            <div id="quickSaleErrors" style="display:none; color:var(--red-600); font-size:12px; font-weight:600; padding:10px 14px; background:var(--red-100); border-radius:8px; border:1px solid var(--border); margin-bottom:14px;"></div>

            <div class="quick-sale-form-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); column-gap:14px; row-gap:12px;">
                {{-- Row 1, Col 1: Sale Date --}}
                <div>
                    <x-core::input
                        type="date"
                        name="sale_date"
                        id="quick_sale_date"
                        label="বিক্রির তারিখ"
                        label-en="Sale Date"
                        size="sm"
                        value="{{ now()->format('Y-m-d') }}"
                        :required="true"
                    />
                </div>

                {{-- Row 1, Col 2: Payment Method --}}
                <div id="quick-payment-type-group">
                    <x-core::select
                        id="quick-payment-type"
                        name="payment_type"
                        value="cash"
                        label="পেমেন্টের মাধ্যম"
                        label-en="Payment Method"
                        size="sm"
                        :required="true"
                    >
                        <option value="cash">নগদ (Cash)</option>
                        <option value="bank">ব্যাংক / MFS (Bank)</option>
                        <option value="both">উভয় (ক্যাশ + ব্যাংক)</option>
                    </x-core::select>
                </div>

                {{-- Row 2, Col 1 (when bank or both): Bank Account --}}
                <div id="quick-account-group" style="display:none;">
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
                                    {{ (int) ($defaultBankAccount?->id ?? 0) === $acc->id ? 'selected' : '' }}>
                                {{ $accTitle }}
                            </option>
                        @empty
                            <option value="">কোনো ব্যাংক বা MFS অ্যাকাউন্ট পাওয়া যায়নি</option>
                        @endforelse
                    </x-core::select>
                </div>

                {{-- Single Amount --}}
                <div id="quick-single-amount-group">
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
                        :stepper="false"
                        :required="true"
                    />
                </div>

                {{-- Profit --}}
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
                        :stepper="false"
                    />
                </div>

                {{-- Both: Cash Paid --}}
                <div id="quick-both-cash-group" style="display:none;">
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
                        :stepper="false"
                    />
                </div>

                {{-- Both: Bank Paid --}}
                <div id="quick-both-bank-group" style="display:none;">
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
                        :stepper="false"
                    />
                </div>

                {{-- Both: Summary Pill (full width) --}}
                <div id="quick-both-summary-group" style="grid-column:1 / -1; display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px; padding:8px 12px; background:var(--paper); border:1px dashed var(--border); border-radius:8px;">
                        <span style="color:var(--ink-700); font-weight:600;">
                            <span class="bn">মোট প্রদান: </span>
                            <span class="en" style="display:none;">Total Paid: </span>
                            <strong style="color:var(--teal-800); font-family:var(--font-mono, monospace); font-size:13.5px;">৳<span id="quick-both-total-paid">0.00</span></strong>
                        </span>
                    </div>
                </div>

                {{-- Customer Name --}}
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
                    />
                    <div id="customerResults" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--card); border:1px solid var(--border); border-radius:8px; margin-top:4px; max-height:180px; overflow-y:auto; z-index:50; box-shadow:var(--shadow-card);"></div>
                </div>

                {{-- Customer Phone --}}
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
                    />
                    <input type="hidden" name="customer_id" id="customerIdInput" value="">
                </div>

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
                    />
                </div>

                {{-- Submit & Cancel Buttons (full width) --}}
                <div style="grid-column:1 / -1; margin-top:6px; display:flex; gap:10px; justify-content:flex-end;">
                    <x-core::button
                        type="button"
                        variant="secondary"
                        size="sm"
                        class="quick-sale-modal-close"
                    >
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button
                        type="submit"
                        color="primary"
                        size="sm"
                        icon="check"
                        id="btn-submit-quick-sale"
                    >
                        <span class="bn">বিক্রি সম্পন্ন করুন</span>
                        <span class="en" style="display:none;">Complete Sale</span>
                    </x-core::button>
                </div>
            </div>
        </form>
    </div>
</div>

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

<script>
$(function () {
    var $modal = $('#quickSaleModal');
    if (!$modal.length) return;

    var $form = $('#quickSaleGlobalForm');
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
    var $errors = $('#quickSaleErrors');

    window.openQuickSaleModal = function () {
        $modal.css('display', 'flex').addClass('open');
        $errors.hide().empty();
        setTimeout(function () {
            var type = $paymentType.val();
            if (type === 'both') {
                $cashAmountInput.focus();
            } else {
                $amountInput.focus();
            }
        }, 80);
    };

    window.closeQuickSaleModal = function () {
        $modal.removeClass('open').hide();
        $('#customerResults').hide().empty();
        $errors.hide().empty();
    };

    $(document).on('click', '[data-quick-sale-trigger]', function (e) {
        e.preventDefault();
        if (typeof window.toggleSidebar === 'function' && window.innerWidth <= 1024) {
            window.toggleSidebar(false);
        }
        openQuickSaleModal();
    });

    $(document).on('keydown', function (e) {
        if (e.altKey && (e.key === 'q' || e.key === 'Q' || e.keyCode === 81)) {
            e.preventDefault();
            if ($modal.hasClass('open')) {
                closeQuickSaleModal();
            } else {
                openQuickSaleModal();
            }
        } else if (e.key === 'Escape' && $modal.hasClass('open')) {
            closeQuickSaleModal();
        }
    });

    $(document).on('click', '.quick-sale-modal-close', function (e) {
        e.preventDefault();
        closeQuickSaleModal();
    });

    $(document).on('click', '#quickSaleModal', function (e) {
        if ($(e.target).is('#quickSaleModal')) {
            closeQuickSaleModal();
        }
    });

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

    // Customer Autocomplete
    var debounceTimer = null;
    function renderCustomerResults(list) {
        var $results = $('#customerResults');
        if (!list || !list.length) {
            $results.hide().empty();
            return;
        }
        var html = list.map(function (c) {
            var safeName = String(c.name || '').replace(/"/g, '&quot;');
            var safePhone = String(c.phone || '').replace(/"/g, '&quot;');
            return '<div class="cust-opt" data-id="' + c.id + '" data-name="' + safeName + '" data-phone="' + safePhone + '" ' +
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

    $(document).on('mouseenter', '.cust-opt', function () {
        $(this).css('background', 'var(--paper)');
    }).on('mouseleave', '.cust-opt', function () {
        $(this).css('background', 'transparent');
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

    // AJAX Form Submission
    $form.on('submit', function (e) {
        e.preventDefault();
        $errors.hide().empty();

        var type = $paymentType.val();
        var amt = parseFloat($amountInput.val()) || 0;
        var cash = parseFloat($cashAmountInput.val()) || 0;
        var bank = parseFloat($bankAmountInput.val()) || 0;

        if (type === 'both') {
            if (cash <= 0 && bank <= 0) {
                $errors.html('অনুগ্রহ করে ক্যাশ অথবা ব্যাংক প্রদানের পরিমাণ লিখুন।').show();
                return false;
            }
            $amountInput.val((cash + bank).toFixed(2));
        } else {
            if (amt <= 0) {
                $errors.html('অনুগ্রহ করে বিক্রয়ের মোট টাকার পরিমাণ লিখুন।').show();
                return false;
            }
        }

        var $btn = $('#btn-submit-quick-sale');
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).css('opacity', '0.7');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (res) {
                $btn.prop('disabled', false).css('opacity', '1').html(originalBtnHtml);
                closeQuickSaleModal();

                // Reset form
                $form[0].reset();
                $('#customerIdInput').val('');
                $('#quick_sale_date').val(new Date().toISOString().split('T')[0]);
                syncPaymentTypeUI();

                // Trigger custom event
                $(document).trigger('quick-sale:created', [res.sale]);

                // Reload DataTables if present
                if (window.LaravelDataTables && window.LaravelDataTables['sales-table']) {
                    window.LaravelDataTables['sales-table'].ajax.reload(null, false);
                }

                var invoiceNo = res.sale && res.sale.invoice_no ? res.sale.invoice_no : '';
                var printUrl = res.sale && res.sale.print_url ? res.sale.print_url : '';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'দ্রুত বেচা সম্পন্ন হয়েছে!',
                        html: '<div style="font-size:13.5px; color:var(--ink-700); line-height:1.6;">' +
                            (invoiceNo ? '<p style="margin:4px 0 10px; font-weight:700;">ইনভয়েস নম্বর: #' + invoiceNo + '</p>' : '') +
                            '<p style="margin:0; font-size:12px; color:var(--ink-500);">আপনার বর্তমান পেজের তথ্য সম্পূর্ণ সংরক্ষিত আছে।</p>' +
                            '</div>',
                        showCancelButton: Boolean(printUrl),
                        confirmButtonText: 'ঠিক আছে',
                        cancelButtonText: 'ইনভয়েস প্রিন্ট করুন',
                        confirmButtonColor: '#0f766e',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.dismiss === Swal.DismissReason.cancel && printUrl) {
                            window.open(printUrl, '_blank');
                        }
                    });
                } else if (typeof window.toast === 'function') {
                    toast('দ্রুত বেচা সম্পন্ন হয়েছে' + (invoiceNo ? ' (#' + invoiceNo + ')' : ''), 'Quick sale completed successfully');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).css('opacity', '1').html(originalBtnHtml);

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var listHtml = '<ul style="margin:0; padding-left:18px;">';
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        $.each(messages, function (i, msg) {
                            listHtml += '<li>' + msg + '</li>';
                        });
                    });
                    listHtml += '</ul>';
                    $errors.html(listHtml).show();
                } else {
                    var errorMsg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'একটি সমস্যা দেখা দিয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
                    $errors.html(errorMsg).show();
                }
            }
        });
    });

    syncPaymentTypeUI();
});
</script>
@endif
