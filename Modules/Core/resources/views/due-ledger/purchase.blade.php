<x-core::layout
    title="বাকির খাতা"
    title-en="Purchase Due Ledger"
    subtitle="সরবরাহকারীদের বাকি ব্যালেন্স ও লেনদেন দেখুন"
    subtitle-en="View outstanding balances for suppliers"
    active="purchase-due-ledger"
>
    <div class="cash-page-head">
        <div>
            <div class="ttl bn">বাকির খাতা</div>
            <div class="ttl en" style="display:none;">Purchase Due Ledger</div>
        </div>

        <div class="actions">
            <div class="total-pill" style="background:var(--red-100); color:var(--red-600);">
                <span class="bn">মোট সরবরাহকারী বাকি: </span><span class="en" style="display:none;">Total Supplier Due: </span>
                <b id="total-supplier-due-amount">৳{{ number_format($supplierTotalDue, 2) }}</b>
            </div>
            <x-core::button
                as="a"
                href="{{ route('purchase.create') }}"
                variant="solid"
                color="primary"
                size="sm"
                icon="plus"
            >
                <span class="bn">নতুন ক্রয়</span>
                <span class="en" style="display:none;">New Purchase</span>
            </x-core::button>
        </div>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'purchase-due-data-table']) !!}
        </div>
    </div>

    {{-- Supplier Due Detail Drawer --}}
    <div class="drawer-backdrop" id="supplierDetailDrawer">
        <div class="drawer" id="supplierDetailDrawerContent">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    {{-- Supplier Due Payment Modal --}}
    <div class="modal-backdrop" id="supplierPaymentModal">
        <div class="modal-box" id="supplierPaymentModalContent" style="width:760px; max-width:96vw; max-height:92vh; display:flex; flex-direction:column; padding:0; overflow:hidden; background:var(--card);">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            // Live update totals from AJAX response
            $('#purchase-due-data-table').on('xhr.dt', function (e, settings, json) {
                if (json && json.totalDue !== undefined) {
                    $('#total-supplier-due-amount').text('৳' + json.totalDue);
                }
            });

            // Open drawer via row click or details button
            $(document).on('click', '.clickable-supplier-row td:not(:last-child), .btn-view-supplier-due', function (e) {
                e.stopPropagation();
                var $btn = $(this).closest('.btn-view-supplier-due');
                var url = $btn.length ? $btn.data('url') : $(this).closest('tr').data('url');
                if (!url) return;

                var $content = $('#supplierDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:60px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#supplierDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load due details</div></div>');
                });
            });

            // Close Drawer
            $(document).on('click', '#supplierDetailDrawer .drawer-x', function () {
                $('#supplierDetailDrawer').removeClass('open');
            });

            $('#supplierDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#supplierDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Open Payment Modal
            $(document).on('click', '.btn-open-supplier-payment', function (e) {
                e.stopPropagation();
                var url = $(this).data('url');
                if (!url) return;

                var $content = $('#supplierPaymentModalContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:80px 20px; color:var(--ink-500);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#supplierPaymentModal').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                }).fail(function () {
                    $content.html('<div style="padding:30px; color:var(--red-600); text-align:center;">পেমেন্ট ফর্ম লোড করতে সমস্যা হয়েছে</div>');
                });
            });

            // Close Payment Modal on backdrop click
            $('#supplierPaymentModal').on('click', function (e) {
                if ($(e.target).is('#supplierPaymentModal')) {
                    $(this).removeClass('open');
                }
            });

            // FIFO Auto-Allocation function for Supplier
            function recalculateSupplierAllocations(totalVal) {
                var remaining = Math.max(parseFloat(totalVal) || 0, 0);
                var totalSupplierDue = 0;

                $('#supplier-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalSupplierDue += rowDue;
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
                var afterDue = Math.max(totalSupplierDue - enteredTotal, 0);
                $('#supplier-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-supplier-pay-total').text('৳' + enteredTotal.toFixed(2));
            }

            function getSupplierActiveTotal() {
                var type = $('#supplier-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    return parseFloat($('#supplier-cash-amount-input').val()) || 0;
                } else if (type === 'bank') {
                    return parseFloat($('#supplier-bank-amount-input').val()) || 0;
                } else if (type === 'both') {
                    var c = parseFloat($('#supplier-both-cash-amount-input').val()) || 0;
                    var b = parseFloat($('#supplier-both-bank-amount-input').val()) || 0;
                    return c + b;
                }
                return 0;
            }

            function syncSupplierPaymentFromInputs() {
                var total = getSupplierActiveTotal();
                recalculateSupplierAllocations(total);
            }

            // Payment type switch (cash / bank / both)
            $(document).on('change', '#supplier-payment-type-select', function () {
                var type = $(this).val();
                $('.payment-mode-pane').hide();
                $('#supplier-mode-' + type).show();
                syncSupplierPaymentFromInputs();
            });

            // Payment amount input events
            $(document).on('input', '#supplier-cash-amount-input, #supplier-bank-amount-input, #supplier-both-cash-amount-input, #supplier-both-bank-amount-input', function () {
                syncSupplierPaymentFromInputs();
            });

            // Individual row allocation input change -> recalculates total amount
            $(document).on('input', '#supplier-allocation-tbody .allocation-input', function () {
                var totalAllocated = 0;
                var totalSupplierDue = 0;
                $('#supplier-allocation-tbody .allocation-row').each(function () {
                    var rowDue = parseFloat($(this).data('due')) || 0;
                    totalSupplierDue += rowDue;
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

                var type = $('#supplier-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#supplier-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'bank') {
                    $('#supplier-bank-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#supplier-both-cash-amount-input').val()) || 0;
                    if (totalAllocated <= currentCash) {
                        $('#supplier-both-cash-amount-input').val(totalAllocated > 0 ? totalAllocated.toFixed(2) : '');
                        $('#supplier-both-bank-amount-input').val('');
                    } else {
                        $('#supplier-both-bank-amount-input').val((totalAllocated - currentCash).toFixed(2));
                    }
                }

                var afterDue = Math.max(totalSupplierDue - totalAllocated, 0);
                $('#supplier-balance-after').text('৳' + afterDue.toFixed(2));
                $('#lbl-supplier-pay-total').text('৳' + totalAllocated.toFixed(2));
            });

            // Quick Pay Full button
            $(document).on('click', '#btn-supplier-pay-full', function () {
                var total = parseFloat($(this).data('total')) || 0;
                var type = $('#supplier-payment-type-select').val() || 'cash';
                if (type === 'cash') {
                    $('#supplier-cash-amount-input').val(total.toFixed(2));
                } else if (type === 'bank') {
                    $('#supplier-bank-amount-input').val(total.toFixed(2));
                } else if (type === 'both') {
                    var currentCash = parseFloat($('#supplier-both-cash-amount-input').val()) || 0;
                    if (currentCash > 0 && currentCash < total) {
                        $('#supplier-both-bank-amount-input').val((total - currentCash).toFixed(2));
                    } else {
                        $('#supplier-both-cash-amount-input').val(total.toFixed(2));
                        $('#supplier-both-bank-amount-input').val('');
                    }
                }
                syncSupplierPaymentFromInputs();
            });

            // Reset Pay button
            $(document).on('click', '#btn-supplier-pay-reset', function () {
                $('#supplier-cash-amount-input').val('');
                $('#supplier-bank-amount-input').val('');
                $('#supplier-both-cash-amount-input').val('');
                $('#supplier-both-bank-amount-input').val('');
                syncSupplierPaymentFromInputs();
            });

            // Submit supplier payment via AJAX
            $(document).on('submit', '#form-supplier-due-payment', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-submit-supplier-payment');
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
                        $('#supplierPaymentModal').removeClass('open');
                        $('#supplierDetailDrawer').removeClass('open');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'বাকি পরিশোধ সফল হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        if (window.LaravelDataTables && window.LaravelDataTables['purchase-due-data-table']) {
                            window.LaravelDataTables['purchase-due-data-table'].ajax.reload(null, false);
                        } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#purchase-due-data-table')) {
                            $('#purchase-due-data-table').DataTable().ajax.reload(null, false);
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
