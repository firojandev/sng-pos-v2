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
                    if (typeof window.lucide !== 'undefined' && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
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
        });
        </script>
    @endpush
</x-core::layout>
