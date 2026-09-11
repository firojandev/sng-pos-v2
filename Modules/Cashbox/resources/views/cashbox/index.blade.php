<x-core::layout
    title="ক্যাশবক্স"
    title-en="Cashbox"
    subtitle="দোকানের নগদ লেনদেন পরিচালনা ও ক্যাশ ইন/আউট করুন"
    subtitle-en="Manage shop cash transactions and cash in/out"
    active="cashbox"
>
    {{-- Summary Stat Cards --}}
    <div class="stat-grid" style="margin-bottom:16px;">
        <x-core::stat-card
            icon="wallet"
            color="teal"
            :value="'৳' . number_format($balance, 2)"
            value-id="stat-cashbox-balance"
            label="ক্যাশবক্স ব্যালেন্স"
            label-en="Cashbox Balance"
            subtext="বর্তমানে ক্যাশবক্সে থাকা মোট ক্যাশ"
            subtext-en="Current net cash in cashbox"
        />

        <x-core::stat-card
            icon="arrow-down-left"
            color="green"
            value-color="green"
            :value="'৳' . number_format($summary->cash_in ?? 0, 2)"
            value-id="stat-cashbox-in"
            label="মোট ক্যাশ ইন (জমা)"
            label-en="Total Cash In"
            subtext="ফিল্টার অনুযায়ী মোট ক্যাশ আগমন"
            subtext-en="Filtered incoming cash flow"
        />

        <x-core::stat-card
            icon="arrow-up-right"
            color="red"
            value-color="red"
            :value="'৳' . number_format($summary->cash_out ?? 0, 2)"
            value-id="stat-cashbox-out"
            label="মোট ক্যাশ আউট (খরচ)"
            label-en="Total Cash Out"
            subtext="ফিল্টার অনুযায়ী মোট ক্যাশ নির্গমন"
            subtext-en="Filtered outgoing cash flow"
        />

        <x-core::stat-card
            icon="repeat"
            color="blue"
            :value="number_format($summary->total_count ?? 0)"
            value-id="stat-cashbox-count"
            label="মোট লেনদেন সংখ্যা"
            label-en="Total Transactions"
            subtext="ক্যাশবক্সে মোট নিবন্ধিত ভাউচার"
            subtext-en="Total registered transactions"
        />
    </div>

    {{-- Filter Toolbar & Action Buttons --}}
    @php
        $typeOptions = [
            'all' => 'সব লেনদেন (All Transactions)',
            'cash_in' => 'ক্যাশ ইন (Cash In)',
            'cash_out' => 'ক্যাশ আউট (Cash Out)',
            'sale' => 'বেচা (Sales)',
            'purchase' => 'কেনা (Purchases)',
            'income' => 'আয় (Income)',
            'expense' => 'ব্যয় (Expense)',
            'sale_return' => 'বিক্রয় ফেরত (Sale Return)',
            'purchase_return' => 'ক্রয় ফেরত (Purchase Return)',
        ];

        $creatorOptions = ['' => 'সব ইউজার (' . $creators->count() . ')'];
        foreach ($creators as $u) {
            $creatorOptions[$u->id] = $u->name;
        }
    @endphp

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px; overflow-x:auto; max-width:100%; padding-bottom:2px;">
            <div style="width:190px; flex-shrink:0;">
                <x-core::select
                    id="filter-cashbox-type"
                    name="type"
                    size="sm"
                    :no-margin="true"
                    :options="$typeOptions"
                    :value="$type"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-cashbox-from"
                    name="from"
                    size="sm"
                    :no-margin="true"
                    :value="$from"
                    placeholder="হতে / From"
                    title="তারিখ হতে / Date From"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-cashbox-to"
                    name="to"
                    size="sm"
                    :no-margin="true"
                    :value="$to"
                    placeholder="পর্যন্ত / To"
                    title="তারিখ পর্যন্ত / Date To"
                />
            </div>

            @if ($creators->count())
                <div style="width:170px; flex-shrink:0;">
                    <x-core::select
                        id="filter-cashbox-creator"
                        name="creator"
                        size="sm"
                        :no-margin="true"
                        :options="$creatorOptions"
                        :value="$creator"
                    />
                </div>
            @endif

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

        <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
            @can('cashbox.cash-in')
                <x-core::button
                    type="button"
                    color="green"
                    size="sm"
                    icon="plus-circle"
                    id="btn-open-cash-in-modal"
                >
                    <span class="bn">ক্যাশ ইন</span>
                    <span class="en" style="display:none;">Cash In</span>
                </x-core::button>
            @endcan

            @can('cashbox.cash-out')
                <x-core::button
                    type="button"
                    color="danger"
                    size="sm"
                    icon="minus-circle"
                    id="btn-open-cash-out-modal"
                >
                    <span class="bn">ক্যাশ আউট</span>
                    <span class="en" style="display:none;">Cash Out</span>
                </x-core::button>
            @endcan
        </div>
    </div>

    {{-- Yajra DataTable Component Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'cashbox-data-table']) !!}
        </div>
    </div>

    {{-- Cash In Modal --}}
    @can('cashbox.cash-in')
    <div class="modal-backdrop @if ($errors->any() && old('cash_form') === 'in') open @endif" id="cashInModal" style="z-index:999;">
        <div class="modal-box" style="width:500px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--green-100); color:var(--green-ink); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="plus-circle" size="20" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">ক্যাশ ইন করুন</span>
                            <span class="en" style="display:none;">Cash In</span>
                        </div>
                        <div style="font-size:11.5px; color:var(--ink-500); margin-top:1px;">
                            <span class="bn">ক্যাশবক্সে সরাসরি নগদ টাকা জমা যোগ করুন</span>
                            <span class="en" style="display:none;">Add manual cash receipt into cashbox</span>
                        </div>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>

            <form method="POST" action="{{ route('cashbox.cash-in') }}" id="form-cash-in">
                @csrf
                <input type="hidden" name="cash_form" value="in">

                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="amount"
                        id="cash_in_amount"
                        label="জমার পরিমাণ"
                        label-en="Amount"
                        placeholder="0.00"
                        size="sm"
                        :required="true"
                        prefix="৳"
                        value="{{ old('cash_form') === 'in' ? old('amount') : '' }}"
                    />

                    <x-core::input
                        type="datetime-local"
                        name="occurred_at"
                        id="cash_in_occurred_at"
                        label="তারিখ ও সময়"
                        label-en="Date & Time"
                        size="sm"
                        value="{{ old('cash_form') === 'in' ? old('occurred_at') : now()->format('Y-m-d\TH:i') }}"
                    />

                    <x-core::textarea
                        name="note"
                        id="cash_in_note"
                        label="মন্তব্য বা বিবরণ"
                        label-en="Note"
                        placeholder="টাকা জমার কারণ বা বিবরণ লিখুন..."
                        size="sm"
                        :rows="2"
                        value="{{ old('cash_form') === 'in' ? old('note') : '' }}"
                    />

                    @if ($errors->any() && old('cash_form') === 'in')
                        <div style="color:var(--red-600); font-size:12px; font-weight:600; padding:6px 10px; background:var(--red-100); border-radius:6px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div style="margin-top:8px; display:flex; justify-content:flex-end; gap:8px;">
                        <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                            <span class="bn">বাতিল</span>
                            <span class="en" style="display:none;">Cancel</span>
                        </x-core::button>
                        <x-core::button type="submit" color="green" size="sm" icon="check">
                            <span class="bn">ক্যাশ ইন নিশ্চিত করুন</span>
                            <span class="en" style="display:none;">Confirm Cash In</span>
                        </x-core::button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- Cash Out Modal --}}
    @can('cashbox.cash-out')
    <div class="modal-backdrop @if ($errors->any() && old('cash_form') === 'out') open @endif" id="cashOutModal" style="z-index:999;">
        <div class="modal-box" style="width:500px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--red-100); color:var(--red-600); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="minus-circle" size="20" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">ক্যাশ আউট করুন</span>
                            <span class="en" style="display:none;">Cash Out</span>
                        </div>
                        <div style="font-size:11.5px; color:var(--ink-500); margin-top:1px;">
                            <span class="bn">ক্যাশবক্স হতে সরাসরি নগদ টাকা উত্তোলন বা খরচ</span>
                            <span class="en" style="display:none;">Deduct manual cash payout from cashbox</span>
                        </div>
                    </div>
                </div>
                <x-core::button type="button" variant="ghost" size="xs" icon="x" class="modal-close-btn" aria-label="Close" />
            </div>

            <form method="POST" action="{{ route('cashbox.cash-out') }}" id="form-cash-out">
                @csrf
                <input type="hidden" name="cash_form" value="out">

                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="amount"
                        id="cash_out_amount"
                        label="উত্তোলনের পরিমাণ"
                        label-en="Amount"
                        placeholder="0.00"
                        size="sm"
                        :required="true"
                        prefix="৳"
                        value="{{ old('cash_form') === 'out' ? old('amount') : '' }}"
                    />

                    <x-core::input
                        type="datetime-local"
                        name="occurred_at"
                        id="cash_out_occurred_at"
                        label="তারিখ ও সময়"
                        label-en="Date & Time"
                        size="sm"
                        value="{{ old('cash_form') === 'out' ? old('occurred_at') : now()->format('Y-m-d\TH:i') }}"
                    />

                    <x-core::textarea
                        name="note"
                        id="cash_out_note"
                        label="মন্তব্য বা বিবরণ"
                        label-en="Note"
                        placeholder="টাকা উত্তোলনের কারণ বা বিবরণ লিখুন..."
                        size="sm"
                        :rows="2"
                        value="{{ old('cash_form') === 'out' ? old('note') : '' }}"
                    />

                    @if ($errors->any() && old('cash_form') === 'out')
                        <div style="color:var(--red-600); font-size:12px; font-weight:600; padding:6px 10px; background:var(--red-100); border-radius:6px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div style="margin-top:8px; display:flex; justify-content:flex-end; gap:8px;">
                        <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                            <span class="bn">বাতিল</span>
                            <span class="en" style="display:none;">Cancel</span>
                        </x-core::button>
                        <x-core::button type="submit" color="danger" size="sm" icon="check">
                            <span class="bn">ক্যাশ আউট নিশ্চিত করুন</span>
                            <span class="en" style="display:none;">Confirm Cash Out</span>
                        </x-core::button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan

    @push('styles')
        <style>
            #cashbox-data-table {
                width: 100% !important;
            }
        </style>
    @endpush

    @push('scripts')
        {!! $dataTable->scripts() !!}
        <script>
        $(function () {
            function reloadCashboxTable() {
                var tableId = 'cashbox-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Real-time table filter triggers
            $(document).on('change', '#filter-cashbox-type, #filter-cashbox-from, #filter-cashbox-to, #filter-cashbox-creator', function () {
                reloadCashboxTable();
            });

            // Reset filters
            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-cashbox-type').val('all');
                $('#filter-cashbox-from').val('');
                $('#filter-cashbox-to').val('');
                $('#filter-cashbox-creator').val('');
                reloadCashboxTable();
            });

            // Open Modal Handlers
            $('#btn-open-cash-in-modal').on('click', function () {
                $('#cashInModal').addClass('open');
                setTimeout(function () {
                    $('#cash_in_amount').focus();
                }, 100);
            });

            $('#btn-open-cash-out-modal').on('click', function () {
                $('#cashOutModal').addClass('open');
                setTimeout(function () {
                    $('#cash_out_amount').focus();
                }, 100);
            });

            // Close Modal Handlers
            $(document).on('click', '.modal-close-btn', function () {
                $(this).closest('.modal-backdrop').removeClass('open');
            });

            $('.modal-backdrop').on('click', function (e) {
                if ($(e.target).hasClass('modal-backdrop')) {
                    $(this).removeClass('open');
                }
            });

            // Update KPI stats dynamically after Ajax reload if summary data returned
            $('#cashbox-data-table').on('xhr.dt', function (e, settings, json) {
                if (json && json.summary) {
                    if (json.summary.balance !== undefined) {
                        $('#stat-cashbox-balance').text('৳' + parseFloat(json.summary.balance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    }
                    if (json.summary.cash_in !== undefined) {
                        $('#stat-cashbox-in').text('৳' + parseFloat(json.summary.cash_in).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    }
                    if (json.summary.cash_out !== undefined) {
                        $('#stat-cashbox-out').text('৳' + parseFloat(json.summary.cash_out).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    }
                    if (json.summary.total_count !== undefined) {
                        $('#stat-cashbox-count').text(parseInt(json.summary.total_count).toLocaleString('en-US'));
                    }
                }
            });
        });
        </script>
    @endpush
</x-core::layout>

