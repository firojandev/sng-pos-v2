<x-core::layout
    title="বাকির খাতা"
    title-en="Sales Due Ledger"
    subtitle="গ্রাহকদের বাকি ব্যালেন্স ও লেনদেন দেখুন"
    subtitle-en="View outstanding balances for customers"
    active="due-ledger"
>
    <div class="cash-page-head">
        <div>
            <div class="ttl bn">বাকির খাতা</div>
            <div class="ttl en" style="display:none;">Sales Due Ledger</div>
        </div>

        <div class="actions">
            <div class="total-pill" style="background:var(--red-100); color:var(--red-600);">
                <span class="bn">মোট গ্রাহক বাকি: </span><span class="en" style="display:none;">Total Customer Due: </span>
                <b id="total-customer-due-amount">৳{{ number_format($customerTotalDue, 2) }}</b>
            </div>
            <x-core::button
                as="a"
                href="{{ route('sales.create') }}"
                variant="solid"
                color="primary"
                size="sm"
                icon="plus"
            >
                <span class="bn">নতুন বিক্রয়</span>
                <span class="en" style="display:none;">New Sale</span>
            </x-core::button>
        </div>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'sales-due-data-table']) !!}
        </div>
    </div>

    {{-- Customer Due Detail Drawer --}}
    <div class="drawer-backdrop" id="customerDetailDrawer">
        <div class="drawer" id="customerDetailDrawerContent">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    {{-- Customer Due Payment Modal --}}
    <div class="modal-backdrop" id="customerPaymentModal">
        <div class="modal-box" id="customerPaymentModalContent" style="width:760px; max-width:96vw; max-height:92vh; display:flex; flex-direction:column; padding:0; overflow:hidden; background:var(--card);">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            // Live update totals from AJAX response
            $('#sales-due-data-table').on('xhr.dt', function (e, settings, json) {
                if (json && json.totalDue !== undefined) {
                    $('#total-customer-due-amount').text('৳' + json.totalDue);
                }
            });

            // Open drawer via row click or details button
            $(document).on('click', '.clickable-customer-row td:not(:last-child), .btn-view-customer-due', function (e) {
                e.stopPropagation();
                var $btn = $(this).closest('.btn-view-customer-due');
                var url = $btn.length ? $btn.data('url') : $(this).closest('tr').data('url');
                if (!url) return;

                var $content = $('#customerDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:60px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#customerDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load due details</div></div>');
                });
            });

            // Close Drawer
            $(document).on('click', '#customerDetailDrawer .drawer-x', function () {
                $('#customerDetailDrawer').removeClass('open');
            });

            $('#customerDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#customerDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Open Payment Modal
            $(document).on('click', '.btn-open-customer-payment', function (e) {
                e.stopPropagation();
                var url = $(this).data('url');
                if (!url) return;

                var $content = $('#customerPaymentModalContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:80px 20px; color:var(--ink-500);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#customerPaymentModal').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:30px; color:var(--red-600); text-align:center;">পেমেন্ট ফর্ম লোড করতে সমস্যা হয়েছে</div>');
                });
            });

            // Close Payment Modal on backdrop click
            $('#customerPaymentModal').on('click', function (e) {
                if ($(e.target).is('#customerPaymentModal')) {
                    $(this).removeClass('open');
                }
            });

            // FIFO Auto-Allocation function for Customer
            function recalculateCustomerAllocations(totalVal) {
                var remaining = Math.max(parseFloat(totalVal) || 0, 0);
                var totalCustomerDue = 0;

                $('#customer-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalCustomerDue += rowDue;
                    var allocate = Math.min(remaining, rowDue);
                    $(this).find('.allocation-input').val(allocate > 0 ? allocate.toFixed(2) : '0.00');
                    remaining = Math.max(remaining - allocate, 0);

                    var rowRemain = Math.max(rowDue - allocate, 0);
                    $(this).find('.cell-remaining').text('৳' + rowRemain.toFixed(2));
                    var $status = $(this).find('.cell-status');
                    if (rowRemain <= 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--green-100); color:var(--green-ink);">পরিশোধিত</span>');
                    } else if (allocate > 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--gold-100); color:var(--gold-ink);">আংশিক</span>');
                    } else {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600);">বাকি</span>');
                    }
                });

                var enteredTotal = Math.max(parseFloat(totalVal) || 0, 0);
                var afterDue = Math.max(totalCustomerDue - enteredTotal, 0);
                $('#customer-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-customer-pay-total').text('৳' + enteredTotal.toFixed(2));
            }

            function getCustomerActiveTotal() {
                var type = $('#customer-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    return parseFloat($('#customer-cash-amount-input').val()) || 0;
                } else if (type === 'bank') {
                    return parseFloat($('#customer-bank-amount-input').val()) || 0;
                } else if (type === 'both') {
                    var c = parseFloat($('#customer-both-cash-amount-input').val()) || 0;
                    var b = parseFloat($('#customer-both-bank-amount-input').val()) || 0;
                    return c + b;
                }
                return 0;
            }

            function syncCustomerPaymentFromInputs() {
                var total = getCustomerActiveTotal();
                recalculateCustomerAllocations(total);
            }

            // Payment type switch (cash / bank / both)
            $(document).on('change', '#customer-payment-type-select', function () {
                var type = $(this).val();
                $('.payment-mode-pane').hide();
                $('#customer-mode-' + type).show();
                syncCustomerPaymentFromInputs();
            });

            // Payment amount input events
            $(document).on('input', '#customer-cash-amount-input, #customer-bank-amount-input, #customer-both-cash-amount-input, #customer-both-bank-amount-input', function () {
                syncCustomerPaymentFromInputs();
            });

            // Individual row allocation input change -> recalculates total amount
            $(document).on('input', '#customer-allocation-tbody .allocation-input', function () {
                var totalAllocated = 0;
                var totalCustomerDue = 0;
                $('#customer-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalCustomerDue += rowDue;
                    var val = parseFloat($(this).find('.allocation-input').val()) || 0;
                    if (val > rowDue) {
                        val = rowDue;
                        $(this).find('.allocation-input').val(rowDue.toFixed(2));
                    }
                    totalAllocated += val;
                    var rowRemain = Math.max(rowDue - val, 0);
                    $(this).find('.cell-remaining').text('৳' + rowRemain.toFixed(2));
                    var $status = $(this).find('.cell-status');
                    if (rowRemain <= 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--green-100); color:var(--green-ink);">পরিশোধিত</span>');
                    } else if (val > 0) {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--gold-100); color:var(--gold-ink);">আংশিক</span>');
                    } else {
                        $status.html('<span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600);">বাকি</span>');
                    }
                });

                var type = $('#customer-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#customer-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'bank') {
                    $('#customer-bank-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#customer-both-cash-amount-input').val()) || 0;
                    if (totalAllocated <= currentCash) {
                        $('#customer-both-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                        $('#customer-both-bank-amount-input').val('');
                    } else {
                        $('#customer-both-bank-amount-input').val((totalAllocated - currentCash).toFixed(2));
                    }
                }

                var afterDue = Math.max(totalCustomerDue - totalAllocated, 0);
                $('#customer-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-customer-pay-total').text('৳' + totalAllocated.toFixed(2));
            });

            // Quick Pay Full button
            $(document).on('click', '#btn-customer-pay-full', function () {
                var total = parseFloat($(this).data('total')) || 0;
                var type = $('#customer-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#customer-cash-amount-input').val(total.toFixed(2));
                } else if (type === 'bank') {
                    $('#customer-bank-amount-input').val(total.toFixed(2));
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#customer-both-cash-amount-input').val()) || 0;
                    if (currentCash > 0 && currentCash < total) {
                        $('#customer-both-bank-amount-input').val((total - currentCash).toFixed(2));
                    } else {
                        $('#customer-both-cash-amount-input').val(total.toFixed(2));
                        $('#customer-both-bank-amount-input').val('');
                    }
                }
                syncCustomerPaymentFromInputs();
            });

            // Reset Pay button
            $(document).on('click', '#btn-customer-pay-reset', function () {
                $('#customer-cash-amount-input').val('');
                $('#customer-bank-amount-input').val('');
                $('#customer-both-cash-amount-input').val('');
                $('#customer-both-bank-amount-input').val('');
                syncCustomerPaymentFromInputs();
            });

            // Submit customer payment via AJAX
            $(document).on('submit', '#form-customer-due-payment', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-submit-customer-payment');
                $btn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function (res) {
                        $('#customerPaymentModal').removeClass('open');
                        $('#customerDetailDrawer').removeClass('open');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'বাকি আদায় সফল হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        if (window.LaravelDataTables && window.LaravelDataTables['sales-due-data-table']) {
                            window.LaravelDataTables['sales-due-data-table'].ajax.reload(null, false);
                        } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#sales-due-data-table')) {
                            $('#sales-due-data-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = 'পেমেন্ট সংরক্ষণে সমস্যা হয়েছে';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি',
                                text: msg
                            });
                        } else {
                            alert(msg);
                        }
                    }
                });
            });
        });
        </script>
    @endpush
</x-core::layout>
