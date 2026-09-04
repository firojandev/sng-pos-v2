<x-core::layout
    title="কেনার খাতা"
    title-en="Purchase Ledger"
    subtitle="ক্রয়ের লেনদেনের ইতিহাস দেখুন"
    subtitle-en="Browse your shop's purchase transaction history"
    active="purchase-ledger"
>
    <div class="cash-page-head">
        <div class="ttl bn">লেনদেনের ইতিহাস</div>
        <div class="ttl en" style="display:none;">Transaction History</div>

        <div class="actions">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="printer"
                id="btn-print-ledger"
            >
                <span class="bn">প্রিন্ট / PDF রিপোর্ট</span><span class="en" style="display:none;">Print / PDF Report</span>
            </x-core::button>
            <x-core::button
                size="sm"
                color="primary"
                icon="plus"
                :href="route('purchase.create')"
            >
                <span class="bn">নতুন ক্রয়</span><span class="en" style="display:none;">New Purchase</span>
            </x-core::button>
        </div>
    </div>

    {{-- Executive Summary Stat Grid --}}
    <div class="stat-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:18px; margin-top:14px;">
        <div class="stat-card" style="display:flex; align-items:center; gap:14px; padding:16px 18px;">
            <div class="ic" style="margin-bottom:0; flex-shrink:0; background:var(--teal-100); color:var(--teal-800); width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <x-core::icon name="shopping-bag" size="20" />
            </div>
            <div style="min-width:0;">
                <div class="val" id="total-purchase-amount" style="font-size:20px; line-height:1.2;">৳{{ number_format($totalAmount, 2) }}</div>
                <div class="lbl bn" style="margin-top:2px;">মোট ক্রয়</div>
                <div class="lbl en" style="display:none; margin-top:2px;">Total Purchases</div>
            </div>
        </div>
        <div class="stat-card" style="display:flex; align-items:center; gap:14px; padding:16px 18px;">
            <div class="ic" style="margin-bottom:0; flex-shrink:0; background:var(--green-100); color:var(--green-ink); width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <x-core::icon name="check-circle" size="20" />
            </div>
            <div style="min-width:0;">
                <div class="val" id="total-paid-amount" style="color:var(--green-ink); font-size:20px; line-height:1.2;">৳{{ number_format($totalPaid, 2) }}</div>
                <div class="lbl bn" style="margin-top:2px;">মোট পরিশোধিত</div>
                <div class="lbl en" style="display:none; margin-top:2px;">Total Paid</div>
            </div>
        </div>
        <div class="stat-card" style="display:flex; align-items:center; gap:14px; padding:16px 18px;">
            <div class="ic" style="margin-bottom:0; flex-shrink:0; background:var(--red-100); color:var(--red-600); width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <x-core::icon name="alert-circle" size="20" />
            </div>
            <div style="min-width:0;">
                <div class="val" id="total-due-amount" style="color:var(--red-600); font-size:20px; line-height:1.2;">৳{{ number_format($totalDue, 2) }}</div>
                <div class="lbl bn" style="margin-top:2px;">মোট বাকি</div>
                <div class="lbl en" style="display:none; margin-top:2px;">Total Due</div>
            </div>
        </div>
        <div class="stat-card" style="display:flex; align-items:center; gap:14px; padding:16px 18px;">
            <div class="ic" style="margin-bottom:0; flex-shrink:0; background:var(--blue-100); color:var(--blue-ink); width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <x-core::icon name="file-text" size="20" />
            </div>
            <div style="min-width:0;">
                <div class="val" id="total-invoice-count" style="font-size:20px; line-height:1.2;">{{ $totalCount ?? 0 }}</div>
                <div class="lbl bn" style="margin-top:2px;">মোট চালান</div>
                <div class="lbl en" style="display:none; margin-top:2px;">Total Invoices</div>
            </div>
        </div>
    </div>

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
            <div style="width:160px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    name="from"
                    id="filter-from"
                    size="sm"
                    :no-margin="true"
                    title="শুরুর তারিখ / From Date"
                />
            </div>
            <div style="width:160px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    name="to"
                    id="filter-to"
                    size="sm"
                    :no-margin="true"
                    title="শেষ তারিখ / To Date"
                />
            </div>
            <div style="width:150px; flex-shrink:0;">
                <x-core::select
                    name="status"
                    id="filter-status"
                    size="sm"
                    :no-margin="true"
                >
                    <option value="all" data-text-bn="সব অবস্থা" data-text-en="All Status">সব অবস্থা</option>
                    <option value="paid" data-text-bn="পরিশোধিত" data-text-en="Paid">পরিশোধিত</option>
                    <option value="partial" data-text-bn="আংশিক" data-text-en="Partial">আংশিক</option>
                    <option value="due" data-text-bn="বাকি" data-text-en="Due">বাকি</option>
                </x-core::select>
            </div>
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reset-filters"
                title="ফিল্টার রিসেট / Reset Filters"
            >
                <span class="bn">রিসেট</span>
                <span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>

        <div class="filter-actions" style="display:flex; align-items:center; gap:8px; margin-left:auto;">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="truck"
                id="btn-quick-receive-by-do"
                title="ডিও নম্বর দিয়ে খুঁজুন / Find D.O. Number"
            >
                <span class="bn">ডিও নম্বর দিয়ে খুঁজুন</span>
                <span class="en" style="display:none;">Find D.O. Number</span>
            </x-core::button>
        </div>
    </div>

    <div class="table-container table-teal" id="purchase-list-print">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'purchases-data-table']) !!}
        </div>
    </div>

    {{-- Purchase Detail Drawer --}}
    <div class="drawer-backdrop" id="purchaseDetailDrawer">
        <div class="drawer" id="purchaseDetailDrawerContent">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    {{-- Dynamic Receive Remaining Modal Container --}}
    <div id="receiveModalContainer"></div>

    {{-- Dynamic Receipt History Modal Container --}}
    <div id="receiptHistoryModalContainer"></div>

    {{-- Quick Find Purchase by D.O. Modal --}}
    <div class="modal-backdrop" id="findPurchaseByDoModal" style="z-index:999;">
        <div class="modal-box" style="width:460px; max-width:95vw; padding:24px; border-radius:16px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
            <div class="modal-head" style="margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="truck" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:15px; font-weight:700; color:var(--ink-900);">
                        <span class="bn">ডিও দিয়ে ক্রয় খুঁজুন</span>
                        <span class="en" style="display:none;">Find Purchase by D.O.</span>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="sm" icon="x" icon-only class="modal-close-btn" onclick="closeModal('findPurchaseByDoModal')" />
            </div>

            <form id="find-purchase-by-do-form" onsubmit="return false;">
                <div style="margin-bottom:16px;">
                    <x-core::input
                        name="lookup_do_number"
                        id="lookup_do_number"
                        label="ডিও নম্বর বা ইনভয়েস নম্বর লিখুন *"
                        label-en="Enter D.O. or Invoice No *"
                        size="sm"
                        :required="true"
                        placeholder="যেমন: PD-001 বা PU-0032"
                    />
                    <div id="lookup-error-msg" style="color:var(--red-600); font-size:12px; margin-top:6px; display:none;"></div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <x-core::button type="button" variant="secondary" size="sm" onclick="closeModal('findPurchaseByDoModal')">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="search" id="btn-submit-find-do">
                        <span class="bn">খুঁজুন ও গ্রহণ করুন</span>
                        <span class="en" style="display:none;">Find & Receive</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            function reloadPurchaseTable() {
                var tableId = 'purchases-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Sync totals from AJAX response
            $('#purchases-data-table').on('xhr.dt', function (e, settings, json) {
                if (json) {
                    if (json.totalAmount !== undefined) {
                        $('#total-purchase-amount').text('৳' + json.totalAmount);
                    }
                    if (json.totalPaid !== undefined) {
                        $('#total-paid-amount').text('৳' + json.totalPaid);
                    }
                    if (json.totalDue !== undefined) {
                        $('#total-due-amount').text('৳' + json.totalDue);
                    }
                    if (json.totalCount !== undefined) {
                        $('#total-invoice-count').text(json.totalCount);
                    }
                }
            });

            // Filters change triggers reload
            $(document).on('change', '#filter-from, #filter-to, #filter-status', function () {
                reloadPurchaseTable();
            });

            // Reset Filters
            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-from').val('');
                $('#filter-to').val('');
                $('#filter-status').val('all');

                var tableId = 'purchases-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].search('').draw();
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().search('').draw();
                } else {
                    reloadPurchaseTable();
                }
            });

            // Professional Print / PDF Export Handler
            $(document).on('click', '#btn-print-ledger', function (e) {
                e.preventDefault();
                var tableId = 'purchases-data-table';
                var searchVal = '';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    searchVal = window.LaravelDataTables[tableId].search();
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    searchVal = $('#' + tableId).DataTable().search();
                }

                var params = {
                    from: $('#filter-from').val() || '',
                    to: $('#filter-to').val() || '',
                    status: $('#filter-status').val() || 'all',
                    q: searchVal
                };

                var printUrl = '{{ route('purchase.ledger.print') }}?' + $.param(params);
                window.open(printUrl, '_blank');
            });

            // Intercept DataTables default print button to trigger the executive print report
            $(document).on('click', '.buttons-print', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('#btn-print-ledger').trigger('click');
            });

            function refreshLucideIcons() {
                if (typeof window.createIcons === 'function') {
                    window.createIcons();
                } else if (typeof window.lucide !== 'undefined' && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons({ icons: window.lucide.icons || {} });
                }
            }

            // Row click / View detail drawer
            $(document).on('click', '.clickable-purchase-row td:not(:last-child), .btn-view-purchase', function (e) {
                e.stopPropagation();
                var $btn = $(this).closest('.btn-view-purchase');
                var url = $btn.length ? $btn.data('url') : $(this).closest('tr').data('url');
                if (!url) return;

                var $content = $('#purchaseDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:60px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#purchaseDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                    refreshLucideIcons();
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load purchase details</div></div>');
                });
            });

            // Close Drawer
            $(document).on('click', '#purchaseDetailDrawer .drawer-x', function () {
                $('#purchaseDetailDrawer').removeClass('open');
            });

            $('#purchaseDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#purchaseDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Receive Modal Helper Function
            function loadAndOpenReceiveModal(url) {
                $.get(url, function (html) {
                    $('#receiveModalContainer').html(html);
                    refreshLucideIcons();
                    openModal('receiveRemainingModal');
                    setTimeout(function () {
                        $('#receive_do_number').focus();
                    }, 100);
                }).fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ত্রুটি!',
                        text: 'পণ্য গ্রহণের ফর্ম লোড করতে সমস্যা হয়েছে।'
                    });
                });
            }

            // Click Receive Button (from datatable action or drawer)
            $(document).on('click', '.btn-receive-purchase', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url');
                if (url) {
                    loadAndOpenReceiveModal(url);
                }
            });

            // Click Receipt History Button (from datatable action or drawer)
            $(document).on('click', '.btn-receipt-history', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url');
                if (!url) return;

                $.get(url, function (html) {
                    $('#receiptHistoryModalContainer').html(html);
                    refreshLucideIcons();
                    openModal('receiptHistoryModal');
                }).fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ত্রুটি!',
                        text: 'পণ্য গ্রহণের ইতিহাস লোড করতে সমস্যা হয়েছে।'
                    });
                });
            });

            // Quick Receive by D.O. Button Trigger
            $(document).on('click', '#btn-quick-receive-by-do', function (e) {
                e.preventDefault();
                $('#lookup_do_number').val('');
                $('#lookup-error-msg').hide().text('');
                openModal('findPurchaseByDoModal');
                setTimeout(function () {
                    $('#lookup_do_number').focus();
                }, 100);
            });

            // Submit Find Purchase by D.O. Form
            $(document).on('submit', '#find-purchase-by-do-form', function (e) {
                e.preventDefault();
                var val = $('#lookup_do_number').val().trim();
                if (!val) {
                    $('#lookup-error-msg').text('দয়া করে ডিও নম্বর লিখুন।').show();
                    return;
                }

                var $btn = $('#btn-submit-find-do');
                $btn.prop('disabled', true);
                $('#lookup-error-msg').hide();

                $.ajax({
                    url: '{{ route("purchase.find-by-do") }}',
                    data: { do_number: val },
                    dataType: 'json',
                    success: function (res) {
                        $btn.prop('disabled', false);
                        closeModal('findPurchaseByDoModal');
                        loadAndOpenReceiveModal(res.modal_url);
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = 'এই ডিও নম্বরের কোনো ক্রয় পাওয়া যায়নি।';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $('#lookup-error-msg').html(msg).show();
                    }
                });
            });

            // Submit Receive Remaining Form (AJAX)
            $(document).on('submit', '#receive-remaining-form', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-submit-receive-remaining');
                $btn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        closeModal('receiveRemainingModal');
                        Swal.fire({
                            icon: 'success',
                            title: 'সফল!',
                            text: response.message || 'পণ্য সফলভাবে গ্রহণ করা হয়েছে!',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        reloadPurchaseTable();

                        if ($('#purchaseDetailDrawer').hasClass('open')) {
                            var match = $form.attr('action').match(/purchase\/(\d+)\/receive/);
                            if (match && match[1]) {
                                $.get('/purchase/' + match[1], function (html) {
                                    $('#purchaseDetailDrawerContent').html(html);
                                    refreshLucideIcons();
                                });
                            }
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = 'পণ্য গ্রহণ করতে সমস্যা হয়েছে।';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'ত্রুটি!',
                            html: msg
                        });
                    }
                });
            });
        });
        </script>
    @endpush
</x-core::layout>
