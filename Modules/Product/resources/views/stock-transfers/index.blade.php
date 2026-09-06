<x-core::layout
    title="স্টক ট্রান্সফার"
    title-en="Stock Transfers"
    subtitle="গুদামের মধ্যে পণ্য স্থানান্তর পরিচালনা করুন"
    subtitle-en="Manage and track stock transfers between warehouses"
    active="stock-transfers"
>
    {{-- Top Tab Navigation --}}
    <div class="tabbar" style="margin-bottom:16px;">
        <a href="{{ route('stock-transfers.index') }}" class="tabbtn active">
            <span class="bn">স্টক ট্রান্সফার</span><span class="en" style="display:none;">Stock Transfers</span>
        </a>
        <a href="{{ route('stock.history') }}" class="tabbtn">
            <span class="bn">স্টকের ইতিহাস</span><span class="en" style="display:none;">Stock History</span>
        </a>
    </div>

    {{-- Summary Stat Cards --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));">
            <x-core::stat-card
                icon="arrow-left-right"
                color="teal"
                :value="number_format($metrics['total'])"
                label="মোট ট্রান্সফার"
                label-en="Total Transfers"
                subtext="সকল চালান"
                subtext-en="All transfer requests"
            />

            <x-core::stat-card
                icon="clock"
                color="gold"
                :value="number_format($metrics['pending'])"
                value-color="gold"
                label="অপেক্ষমাণ অনুমোদন"
                label-en="Pending Approval"
                subtext="অনুমোদনের অপেক্ষায়"
                subtext-en="Awaiting sign-off"
            />

            <x-core::stat-card
                icon="check"
                color="blue"
                :value="number_format($metrics['approved'])"
                value-color="blue"
                label="অনুমোদিত"
                label-en="Approved"
                subtext="প্রেরণের অপেক্ষায়"
                subtext-en="Ready to dispatch"
            />

            <x-core::stat-card
                icon="arrow-right"
                color="blue"
                :value="number_format($metrics['dispatched'])"
                label="প্রেরিত (চলমান)"
                label-en="Dispatched"
                subtext="পথিমধ্যে রয়েছে"
                subtext-en="In transit"
            />

            <x-core::stat-card
                icon="check-circle"
                color="green"
                :value="number_format($metrics['received'])"
                value-color="green"
                label="সফলভাবে গৃহীত"
                label-en="Completed"
                subtext="গন্তব্যে পৌঁছেছে"
                subtext-en="Stock added to warehouse"
            />
        </div>
    @endif

    {{-- Filter Toolbar & Header Action --}}
    <div class="section-row" style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_status"
                    id="filter-status"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'সকল অবস্থা (All Status)'] + collect(\Modules\Product\Models\StockTransfer::statusLabels())->mapWithKeys(fn($item, $key) => [$key => $item['bn'] . ' (' . $item['en'] . ')'])->toArray()"
                />
            </div>
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_from_warehouse"
                    id="filter-from-warehouse"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'উৎস গুদাম (From)'] + $warehouses->pluck('name', 'id')->toArray()"
                />
            </div>
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    name="filter_to_warehouse"
                    id="filter-to-warehouse"
                    size="sm"
                    :no-margin="true"
                    :options="['' => 'গন্তব্য গুদাম (To)'] + $warehouses->pluck('name', 'id')->toArray()"
                />
            </div>
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reset-filters"
                title="রিসেট / Reset"
            >
                <span class="bn">রিসেট</span>
                <span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>

        @can('stock.transfer')
        <x-core::button
            :href="route('stock-transfers.create')"
            size="sm"
            color="primary"
            icon="plus"
        >
            <span class="bn">নতুন ট্রান্সফার</span>
            <span class="en" style="display:none;">New Transfer</span>
        </x-core::button>
        @endcan
    </div>

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'stock-transfers-data-table']) !!}
        </div>
    </div>

    {{-- Transfer Detail Drawer --}}
    <div class="drawer-backdrop" id="transferDetailDrawer">
        <div class="drawer" id="transferDetailDrawerContent" style="width:580px; max-width:95vw; background:var(--card);">
            {{-- Loaded dynamically via AJAX --}}
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            function reloadTransferTable() {
                var tableId = 'stock-transfers-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filters change
            $(document).on('change', '#filter-status, #filter-from-warehouse, #filter-to-warehouse', function () {
                reloadTransferTable();
            });

            // Reset filters
            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-status').val('');
                $('#filter-from-warehouse').val('');
                $('#filter-to-warehouse').val('');
                reloadTransferTable();
            });

            // Open Transfer Detail Drawer via AJAX
            $(document).on('click', '.btn-view-transfer-detail', function (e) {
                e.preventDefault();
                var url = $(this).data('url') || $(this).attr('href');
                if (!url) return;

                var $content = $('#transferDetailDrawerContent');
                $content.html('<div style="display:flex; align-items:center; justify-content:center; padding:80px 20px; color:var(--ink-500);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg></div>');
                $('#transferDetailDrawer').addClass('open');

                $.get(url, function (html) {
                    $content.html(html);
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                }).fail(function () {
                    $content.html('<div style="padding:24px; color:var(--red-600); text-align:center;"><div style="font-weight:600; margin-bottom:8px;">তথ্য লোড করতে সমস্যা হয়েছে</div><div style="font-size:12px; color:var(--ink-500);">Failed to load transfer details</div></div>');
                });
            });

            // Close Drawer Handlers
            $(document).on('click', '#transferDetailDrawer .drawer-x', function () {
                $('#transferDetailDrawer').removeClass('open');
            });

            $('#transferDetailDrawer').on('click', function (e) {
                if ($(e.target).is('#transferDetailDrawer')) {
                    $(this).removeClass('open');
                }
            });

            // Handle AJAX form actions inside Drawer or Table
            $(document).on('submit', '.inline-transfer-action-form, .inline-drawer-action-form', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                var url = $form.attr('action');

                $btn.prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (res) {
                        $btn.prop('disabled', false);
                        $('#transferDetailDrawer').removeClass('open');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message || 'কার্যক্রম সফল হয়েছে',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else if (typeof window.toast === 'function') {
                            window.toast(res.message || 'কার্যক্রম সফল হয়েছে', res.message_en || 'Action completed successfully');
                        }
                        reloadTransferTable();
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'কার্যক্রম সম্পন্ন করতে সমস্যা হয়েছে';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি!',
                                text: msg
                            });
                        }
                    }
                });
            });
        });
        </script>
    @endpush
</x-core::layout>
