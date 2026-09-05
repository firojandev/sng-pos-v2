<x-core::layout
    title="বেচার খাতা"
    title-en="Sales Ledger"
    subtitle="বিক্রয়ের লেনদেনের ইতিহাস দেখুন"
    subtitle-en="Browse your shop's sales transaction history"
    active="sales-ledger"
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
                :href="route('sales.create')"
            >
                <span class="bn">নতুন বিক্রয়</span><span class="en" style="display:none;">New Sale</span>
            </x-core::button>
        </div>
    </div>

    {{-- Executive Summary Stat Grid --}}
    <div class="stat-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:18px; margin-top:14px;">
        <x-core::stat-card
            icon="shopping-bag"
            color="teal"
            :value="'৳' . number_format($totalAmount, 2)"
            value-id="total-sale-amount"
            label="মোট বিক্রয়"
            label-en="Total Sales"
        />
        <x-core::stat-card
            icon="check-circle"
            color="green"
            :value="'৳' . number_format($totalPaid, 2)"
            value-id="total-paid-amount"
            value-color="green"
            label="মোট পরিশোধিত"
            label-en="Total Paid"
        />
        <x-core::stat-card
            icon="alert-circle"
            color="red"
            :value="'৳' . number_format($totalDue, 2)"
            value-id="total-due-amount"
            value-color="red"
            label="মোট বাকি"
            label-en="Total Due"
        />
        <x-core::stat-card
            icon="file-text"
            color="blue"
            :value="$totalCount ?? 0"
            value-id="total-invoice-count"
            label="মোট চালান"
            label-en="Total Invoices"
        />
    </div>

    {{-- Filters Toolbar --}}
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

    {{-- DataTable Container --}}
    <div class="table-container table-teal" id="sales-list-print">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'sales-data-table']) !!}
        </div>
    </div>

    {{-- Dynamic Sale Detail Drawer --}}
    <div class="drawer-backdrop" id="saleDetailDrawer">
        <div class="drawer" id="saleDetailDrawerContent">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    {{-- Dynamic Sales Invoice Modal Container --}}
    <div id="saleInvoiceModalContainer">
        @if(isset($invoiceSale) && $invoiceSale)
            @include('sales::sales._invoice_modal', ['sale' => $invoiceSale])
        @endif
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            function reloadSalesTable() {
                var tableId = 'sales-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Sync totals from AJAX response
            $('#sales-data-table').on('xhr.dt', function (e, settings, json) {
                if (json) {
                    if (json.totalAmount !== undefined) {
                        $('#total-sale-amount').text('৳' + json.totalAmount);
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
                reloadSalesTable();
            });

            // Reset Filters
            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-from').val('');
                $('#filter-to').val('');
                $('#filter-status').val('all');

                var tableId = 'sales-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].search('').draw();
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().search('').draw();
                } else {
                    reloadSalesTable();
                }
            });

            // Print / PDF Export Handler
            $(document).on('click', '#btn-print-ledger', function (e) {
                e.preventDefault();
                var tableId = 'sales-data-table';
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

                var printUrl = '{{ route('sales.ledger.print') }}?' + $.param(params);
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
            $(document).on('click', '.clickable-sale-row td:not(:last-child), .btn-view-sale', function (e) {
                e.stopPropagation();
                var $btn = $(this).closest('.btn-view-sale');
                var url = $btn.length ? $btn.data('url') : $(this).closest('tr').data('url');
                if (!url) return;

                var $content = $('#saleDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:60px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#saleDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                    refreshLucideIcons();
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load sale details</div></div>');
                });
            });

            // Close Drawer
            $(document).on('click', '#saleDetailDrawer .drawer-x', function () {
                $('#saleDetailDrawer').removeClass('open');
            });

            $('#saleDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#saleDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Dynamic Sales Invoice Modal
            window.showSaleInvoice = function(url, closeDrawerId) {
                if (closeDrawerId) {
                    $('#' + closeDrawerId).removeClass('open');
                }
                if (!url) return;

                $.get(url, function (html) {
                    $('#saleInvoiceModalContainer').html(html);
                    refreshLucideIcons();
                    openModal('saleInvoiceModal');
                }).fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ত্রুটি!',
                        text: 'ইনভয়েস স্লিপ লোড করতে সমস্যা হয়েছে।'
                    });
                });
            };

            @if(isset($invoiceSale) && $invoiceSale)
                openModal('saleInvoiceModal');
            @endif

            $(document).on('click', '.btn-show-sale-invoice', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url') || $(this).attr('data-url');
                if (url) {
                    showSaleInvoice(url);
                }
            });
        });
        </script>
    @endpush
</x-core::layout>
