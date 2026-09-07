<x-core::layout
    title="ফান্ড ট্রান্সফার"
    title-en="Fund Transfers"
    subtitle="দোকানের এক অ্যাকাউন্ট থেকে অন্য অ্যাকাউন্টে টাকা স্থানান্তরের ইতিহাস"
    subtitle-en="History of fund transfers between accounts"
    active="account-transfers"
>
    {{-- Summary KPI Stat Cards --}}
    <div class="stat-grid" style="margin-bottom:20px;">
        <x-core::stat-card
            icon="arrow-left-right"
            color="teal"
            :value="'৳ ' . number_format($totalTransferAmount, 2)"
            label="মোট স্থানান্তরিত পরিমাণ"
            label-en="Total Transferred"
            subtext="সর্বমোট স্থানান্তরের পরিমাণ"
            subtext-en="Total transferred across accounts"
        />

        <x-core::stat-card
            icon="receipt"
            color="red"
            value-color="red"
            :value="'৳ ' . number_format($totalChargeAmount, 2)"
            label="মোট ট্রান্সফার চার্জ / ফি"
            label-en="Total Transfer Fee"
            subtext="স্থানান্তর বাবদ ব্যয়িত ফি"
            subtext-en="Charges & processing fees"
        />

        <x-core::stat-card
            icon="calendar"
            color="gold"
            value-color="gold"
            :value="'৳ ' . number_format($thisMonthTransferAmount, 2)"
            label="চলতি মাসের স্থানান্তর"
            label-en="This Month's Transfers"
            :subtext="now()->format('F Y') . ' এর মোট স্থানান্তর'"
            :subtext-en="now()->format('M Y') . ' total volume'"
        />

        <x-core::stat-card
            icon="check-circle"
            color="blue"
            value-color="blue"
            :value="number_format($totalTransferCount)"
            label="মোট লেনদেন সংখ্যা"
            label-en="Total Transfers"
            subtext="সম্পন্ন স্থানান্তরের রেকর্ড"
            subtext-en="Completed transfer entries"
        />
    </div>

    @if (session('status'))
        <div class="alert alert-success" style="margin-bottom:16px; padding:10px 14px; background:var(--green-100); color:var(--green-ink); border-radius:8px; font-size:13px; font-weight:500;">
            {{ session('status') }}
        </div>
    @endif

    @php
        $fromAccountOptions = ['' => 'সকল উৎস অ্যাকাউন্ট (All Source)'];
        $toAccountOptions = ['' => 'সকল গন্তব্য অ্যাকাউন্ট (All Destination)'];
        foreach ($accounts as $acc) {
            $fromAccountOptions[$acc->id] = $acc->display_name;
            $toAccountOptions[$acc->id] = $acc->display_name;
        }
    @endphp

    {{-- Filter Toolbar & Action Buttons --}}
    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px; overflow-x:auto; max-width:100%; padding-bottom:2px;">
            <div style="width:190px; flex-shrink:0;">
                <x-core::select
                    id="filter-from-account"
                    name="filter_from_account"
                    size="sm"
                    :no-margin="true"
                    :options="$fromAccountOptions"
                />
            </div>

            <div style="width:190px; flex-shrink:0;">
                <x-core::select
                    id="filter-to-account"
                    name="filter_to_account"
                    size="sm"
                    :no-margin="true"
                    :options="$toAccountOptions"
                />
            </div>

            <div style="width:135px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-date-from"
                    name="filter_date_from"
                    size="sm"
                    :no-margin="true"
                    placeholder="হতে / From"
                    title="তারিখ হতে / Date From"
                />
            </div>

            <div style="width:135px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-date-to"
                    name="filter_date_to"
                    size="sm"
                    :no-margin="true"
                    placeholder="পর্যন্ত / To"
                    title="তারিখ পর্যন্ত / Date To"
                />
            </div>

            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reset-transfer-filters"
                title="রিসেট / Reset"
            >
                <span class="bn">রিসেট</span>
                <span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>

        @can('account-transfers.create')
            <x-core::button color="primary" size="sm" type="button" icon="plus" id="btnOpenTransferModal">
                <span class="bn">নতুন ট্রান্সফার</span>
                <span class="en" style="display:none;">New Transfer</span>
            </x-core::button>
        @endcan
    </div>

    {{-- DataTable Container --}}
    <div class="table-container">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'account-transfers-data-table']) !!}
        </div>
    </div>

    {{-- Fund Transfer Modal --}}
    @can('account-transfers.create')
    <div class="modal-backdrop @if ($errors->any()) open @endif" id="createTransferModal">
        <div class="modal-box" style="width:640px; max-width:95vw; max-height:90vh; overflow-y:auto;">
            <div class="modal-head">
                <div class="modal-title">
                    <span class="bn">নতুন ফান্ড ট্রান্সফার</span>
                    <span class="en" style="display:none;">New Fund Transfer</span>
                </div>
                <button type="button" class="drawer-x modal-close-btn">&times;</button>
            </div>
            <form method="POST" action="{{ route('account-transfers.store') }}" id="create_transfer_modal_form">
                @csrf
                @include('finance::transfers._form', ['transfer' => $transfer, 'accounts' => $accounts, 'isModal' => true])
            </form>
        </div>
    </div>
    @endcan

    @push('scripts')
    {!! $dataTable->scripts() !!}

    <script>
    (function () {
        function initTransferIndex() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                setTimeout(initTransferIndex, 20);
                return;
            }

            var $ = window.jQuery;
            $(function () {
                function reloadTransferTable() {
                    var tableId = 'account-transfers-data-table';
                    if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                        window.LaravelDataTables[tableId].ajax.reload(null, false);
                    } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                        $('#' + tableId).DataTable().ajax.reload(null, false);
                    }
                }

                // Filter change handlers
                $(document).on('change', '#filter-from-account, #filter-to-account, #filter-date-from, #filter-date-to', function () {
                    reloadTransferTable();
                });

                // Reset filter handler
                $(document).on('click', '#btn-reset-transfer-filters', function (e) {
                    e.preventDefault();
                    $('#filter-from-account').val('');
                    $('#filter-to-account').val('');
                    $('#filter-date-from').val('');
                    $('#filter-date-to').val('');
                    reloadTransferTable();
                });

                // Modal triggers
                $('#btnOpenTransferModal').on('click', function () {
                    $('#createTransferModal').addClass('open');
                });

                $(document).on('click', '.modal-close-btn', function (e) {
                    e.preventDefault();
                    $(this).closest('.modal-backdrop').removeClass('open');
                });

                $('.modal-backdrop').on('click', function (e) {
                    if ($(e.target).hasClass('modal-backdrop')) {
                        $(this).removeClass('open');
                    }
                });
            });
        }

        initTransferIndex();
    })();
    </script>
    @endpush
</x-core::layout>
