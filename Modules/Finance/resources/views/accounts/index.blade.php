<x-core::layout
    title="অ্যাকাউন্ট তালিকা"
    title-en="Accounts List"
    subtitle="দোকানের সকল ব্যাংক, মোবাইল ব্যাংকিং ও ক্যাশ অ্যাকাউন্টের ব্যালেন্স ও বিবরণ"
    subtitle-en="Overview of all bank, mobile banking, and cash accounts"
    active="accounts"
>

    {{-- KPI Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px;">
        <div class="panel" style="margin:0; padding:16px; border-left:4px solid #D4AF37;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:600;">
                <span class="bn">মোট অ্যাকাউন্টের ব্যালেন্স</span><span class="en" style="display:none;">Total Balance</span>
            </div>
            <div style="font-size:22px; font-weight:700; color:var(--text-primary); margin-top:4px;">
                ৳ {{ number_format($totalBalance, 2) }}
            </div>
        </div>

        <div class="panel" style="margin:0; padding:16px; border-left:4px solid #16a34a;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:600;">
                <span class="bn">মোট ক্যাশ ব্যালেন্স</span><span class="en" style="display:none;">Total Cash</span>
            </div>
            <div style="font-size:22px; font-weight:700; color:#16a34a; margin-top:4px;">
                ৳ {{ number_format($totalCash, 2) }}
            </div>
        </div>

        <div class="panel" style="margin:0; padding:16px; border-left:4px solid #2563eb;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:600;">
                <span class="bn">মোট ব্যাংক ব্যালেন্স</span><span class="en" style="display:none;">Total Bank</span>
            </div>
            <div style="font-size:22px; font-weight:700; color:#2563eb; margin-top:4px;">
                ৳ {{ number_format($totalBank, 2) }}
            </div>
        </div>

        <div class="panel" style="margin:0; padding:16px; border-left:4px solid #ec4899;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:600;">
                <span class="bn">মোট এমএফএস (বিকাশ/নগদ/রকেট)</span><span class="en" style="display:none;">Total MFS</span>
            </div>
            <div style="font-size:22px; font-weight:700; color:#db2777; margin-top:4px;">
                ৳ {{ number_format($totalMfs, 2) }}
            </div>
        </div>
    </div>

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            @if (session('status'))
                <div class="alert alert-success" style="margin-bottom:16px; padding:10px 14px; background:#dcfce7; color:#166534; border-radius:6px; font-size:13px;">
                    {{ session('status') }}
                </div>
            @endif

            <div class="section-row" style="display:flex; flex-wrap:wrap; gap:12px; justify-content:space-between; align-items:center;">
                <form method="GET" action="{{ route('accounts.index') }}" class="filters" style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin:0;">
                    <div style="width:230px;">
                        <x-core::input name="q" value="{{ $search }}" placeholder="অ্যাকাউন্ট খুঁজুন..." placeholder-en="Search accounts..." icon="search" size="sm" />
                    </div>

                    <div style="min-width:170px;">
                        <x-core::select
                            name="type"
                            size="sm"
                            :options="['' => 'সকল ধরন (All Types)', 'cash' => 'নগদ (Cash)', 'bank' => 'ব্যাংক (Bank)', 'mfs' => 'মোবাইল ব্যাংকিং (MFS)']"
                            :value="$type"
                            onchange="this.form.submit();"
                        />
                    </div>

                    <div style="min-width:160px;">
                        <x-core::select
                            name="status"
                            size="sm"
                            :options="['' => 'সকল স্ট্যাটাস (All Status)', 'active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
                            :value="$status"
                            onchange="this.form.submit();"
                        />
                    </div>

                    @if ($search || $type || $status)
                        <x-core::button variant="secondary" size="sm" href="{{ route('accounts.index') }}">
                            <span class="bn">রিসেট</span><span class="en" style="display:none;">Reset</span>
                        </x-core::button>
                    @endif
                </form>

                <div>
                    <x-core::button color="primary" size="sm" id="btnOpenAccountModal" icon="plus">
                        <span class="bn">নতুন অ্যাকাউন্ট</span><span class="en" style="display:none;">New Account</span>
                    </x-core::button>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">অ্যাকাউন্টের নাম ও বিবরণ</th><th class="en" style="display:none;">Account Details</th>
                            <th class="bn">ধরন</th><th class="en" style="display:none;">Type</th>
                            <th class="bn" style="text-align:right;">বর্তমান ব্যালেন্স</th><th class="en" style="display:none; text-align:right;">Current Balance</th>
                            <th class="bn" style="text-align:center;">ডিফল্ট</th><th class="en" style="display:none; text-align:center;">Default</th>
                            <th class="bn" style="text-align:center;">স্ট্যাটাস</th><th class="en" style="display:none; text-align:center;">Status</th>
                            <th style="text-align:right;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $item)
                            <tr>
                                <td class="cell-main">
                                    <div style="font-weight:600; font-size:14px;">
                                        {{ $item->name }}
                                        @if ($item->is_default)
                                            <span style="display:inline-block; font-size:10px; font-weight:700; background:#fef08a; color:#854d0e; padding:2px 6px; border-radius:4px; margin-left:6px;">ডিফল্ট (Default)</span>
                                        @endif
                                    </div>
                                    <div style="font-size:12px; color:var(--text-muted); margin-top:3px;">
                                        @if ($item->type === 'bank')
                                            <span>{{ $item->bank_name ?? 'Bank' }}</span>
                                            @if ($item->account_number) • A/C: <b>{{ $item->account_number }}</b> @endif
                                            @if ($item->branch_name) • শাখা: {{ $item->branch_name }} @endif
                                        @elseif ($item->type === 'mfs')
                                            <span>{{ $item->mfs_provider ? ucfirst($item->mfs_provider) : 'MFS' }}</span>
                                            @if ($item->account_number) • মোবাইল: <b>{{ $item->account_number }}</b> @endif
                                            @if ($item->mfs_type) • {{ $item->mfsTypeLabel()['bn'] ?? $item->mfs_type }} @endif
                                        @else
                                            <span>ক্যাশ অ্যাকাউন্ট (নগদ লেনদেন)</span>
                                        @endif
                                        @if ($item->note)
                                            <span style="font-style:italic;"> — {{ Str::limit($item->note, 40) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($item->type === 'cash')
                                        <x-core::badge color="success" size="sm" rounded="rounded">নগদ (Cash)</x-core::badge>
                                    @elseif ($item->type === 'bank')
                                        <x-core::badge color="info" size="sm" rounded="rounded">ব্যাংক (Bank)</x-core::badge>
                                    @else
                                        <x-core::badge color="danger" size="sm" rounded="rounded">MFS</x-core::badge>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; font-size:14px; font-family:'Manrope',sans-serif; color:{{ $item->current_balance < 0 ? '#dc2626' : 'inherit' }};">
                                    ৳ {{ number_format($item->current_balance, 2) }}
                                </td>
                                <td style="text-align:center;">
                                    @if ($item->is_default)
                                        <span style="color:#16a34a; font-weight:700; font-size:18px;" title="Default Account">✓</span>
                                    @else
                                        <form method="POST" action="{{ route('accounts.set-default', $item) }}" style="display:inline;">
                                            @csrf
                                            <x-core::button variant="secondary" size="xs" type="submit" title="ডিফল্ট হিসেবে সেট করুন">
                                                ডিফল্ট করুন
                                            </x-core::button>
                                        </form>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if ($item->status === 'active')
                                        <x-core::badge color="success" size="xs">সক্রিয়</x-core::badge>
                                    @else
                                        <x-core::badge color="grey" size="xs">নিষ্ক্রিয়</x-core::badge>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end; gap:6px;">
                                        <x-core::button variant="ghost" color="blue" size="xs" icon-only icon="file-text" href="{{ route('accounts.ledger', $item) }}" title="লেজার / স্টেটমেন্ট দেখুন" />
                                        <x-core::button variant="ghost" color="secondary" size="xs" icon-only icon="edit" class="btn-edit-account" data-url="{{ route('accounts.edit', $item) }}" title="সম্পাদনা" />
                                        @if (! $item->is_default)
                                            <form method="POST" action="{{ route('accounts.destroy', $item) }}" class="delete-form" data-title="অ্যাকাউন্ট মুছে ফেলতে চান?" data-text="এই অ্যাকাউন্টটি স্থায়ীভাবে মুছে ফেলতে চান?" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <x-core::button variant="ghost" color="danger" size="xs" icon-only icon="trash-2" type="submit" title="মুছে ফেলুন" />
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-core::table.empty
                                        icon="wallet"
                                        title="কোনো অ্যাকাউন্ট পাওয়া যায়নি"
                                        title-en="No accounts found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $accounts->links() }}
            </div>
        </div>
    </div>

    {{-- Create Account Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('modal_type', 'account') === 'account') open @endif" id="createAccountModal">
        <div class="modal-box" style="width:680px; max-width:95vw; max-height:90vh; overflow-y:auto;">
            <div class="modal-head">
                <div class="modal-title">
                    <span class="bn">নতুন অ্যাকাউন্ট</span>
                    <span class="en" style="display:none;">New Account</span>
                </div>
                <button type="button" class="drawer-x modal-close-btn">&times;</button>
            </div>
            <form method="POST" action="{{ route('accounts.store') }}" id="create_account_modal_form">
                @csrf
                <input type="hidden" name="modal_type" value="account">
                @include('finance::accounts._form', ['account' => $account, 'isModal' => true])
            </form>
        </div>
    </div>

    {{-- Edit Account Modal --}}
    <div class="modal-backdrop" id="editAccountModal">
        <div class="modal-box" style="width:680px; max-width:95vw; max-height:90vh; overflow-y:auto;">
            <div class="modal-head">
                <div class="modal-title">
                    <span class="bn">অ্যাকাউন্ট সম্পাদনা</span>
                    <span class="en" style="display:none;">Edit Account</span>
                </div>
                <button type="button" class="drawer-x modal-close-btn">&times;</button>
            </div>
            <form method="POST" action="" id="edit_account_modal_form">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        @php
                            $typeOptions = [];
                            foreach ($typeLabels as $k => $labels) {
                                $typeOptions[$k] = $labels['bn'] . ' (' . $labels['en'] . ')';
                            }
                        @endphp
                        <x-core::select
                            name="type"
                            id="edit_account_type_select"
                            label="অ্যাকাউন্টের ধরন"
                            label-en="Account Type"
                            :required="true"
                            :disabled="true"
                            :options="$typeOptions"
                        />
                        <input type="hidden" name="type" id="edit_account_type_hidden" value="">
                    </div>

                    <div class="col-md-6">
                        <x-core::input
                            name="name"
                            id="edit_account_name_input"
                            label="অ্যাকাউন্টের নাম"
                            label-en="Account Title / Name"
                            :required="true"
                            placeholder="যেমন: প্রধান ক্যাশ, ডাচ বাংলা ব্যাংক, বিকাশ মার্চেন্ট"
                            placeholder-en="e.g. Main Cash, Dutch Bangla Bank, bKash Merchant"
                        />
                    </div>

                    {{-- Bank Specific Fields --}}
                    <div class="col-md-6 edit-type-field edit-type-bank" style="display:none;">
                        <x-core::input
                            name="bank_name"
                            id="edit_bank_name_input"
                            label="ব্যাংকের নাম"
                            label-en="Bank Name"
                            placeholder="যেমন: Dutch-Bangla Bank, BRAC Bank"
                            placeholder-en="e.g. Dutch-Bangla Bank, BRAC Bank"
                        />
                    </div>

                    <div class="col-md-6 edit-type-field edit-type-bank edit-type-mfs" style="display:none;">
                        <x-core::input
                            name="account_number"
                            id="edit_account_number_input"
                            label="হিসাব / মোবাইল নম্বর"
                            label-en="Account / Mobile Number"
                            placeholder="হিসাব বা মোবাইল নম্বর"
                            placeholder-en="Account or Mobile Number"
                        />
                    </div>

                    <div class="col-md-6 edit-type-field edit-type-bank" style="display:none;">
                        <x-core::input
                            name="branch_name"
                            id="edit_branch_name_input"
                            label="শাখা / রাউটিং"
                            label-en="Branch / Routing"
                            placeholder="যেমন: মিরপুর শাখা"
                            placeholder-en="e.g. Mirpur Branch"
                        />
                    </div>

                    {{-- MFS Specific Fields --}}
                    <div class="col-md-6 edit-type-field edit-type-mfs" style="display:none;">
                        @php
                            $providerOptions = ['' => '-- নির্বাচন করুন --'];
                            foreach ($mfsProviders as $k => $label) {
                                $providerOptions[$k] = $label;
                            }
                        @endphp
                        <x-core::select
                            name="mfs_provider"
                            id="edit_mfs_provider_select"
                            label="সার্ভিস প্রোভাইডার"
                            label-en="MFS Provider"
                            :options="$providerOptions"
                        />
                    </div>

                    <div class="col-md-6 edit-type-field edit-type-mfs" style="display:none;">
                        @php
                            $mfsTypeOptions = ['' => '-- নির্বাচন করুন --'];
                            foreach ($mfsTypeLabels as $k => $labels) {
                                $mfsTypeOptions[$k] = $labels['bn'] . ' (' . $labels['en'] . ')';
                            }
                        @endphp
                        <x-core::select
                            name="mfs_type"
                            id="edit_mfs_type_select"
                            label="এমএফএস অ্যাকাউন্ট ধরন"
                            label-en="MFS Account Type"
                            :options="$mfsTypeOptions"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-core::input
                            name="current_balance_display"
                            id="edit_current_balance_display"
                            label="বর্তমান ব্যালেন্স (৳)"
                            label-en="Current Balance (৳)"
                            :disabled="true"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-core::select
                            name="status"
                            id="edit_status_select"
                            label="স্ট্যাটাস"
                            label-en="Status"
                            :required="true"
                            :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
                        />
                    </div>

                    <div class="col-12">
                        <x-core::textarea
                            name="note"
                            id="edit_note_input"
                            rows="2"
                            label="মন্তব্য / নোট"
                            label-en="Note"
                            placeholder="অ্যাকাউন্ট সম্পর্কিত অতিরিক্ত তথ্য"
                            placeholder-en="Additional details about the account"
                        />
                    </div>

                    <div class="col-12">
                        <div class="tx-row" style="padding:12px 16px; background:var(--paper); border:1px solid var(--border); border-radius:10px; display:flex; align-items:center; justify-content:space-between;">
                            <div>
                                <b style="font-size:14px;"><span class="bn">ডিফল্ট অ্যাকাউন্ট হিসেবে নির্ধারণ করুন</span><span class="en" style="display:none;">Set as Default Account</span></b>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                    <span class="bn">সেলস এবং পারচেজ পেমেন্টে এই অ্যাকাউন্টটি স্বয়ংক্রিয়ভাবে প্রথম পছন্দ হিসেবে থাকবে।</span>
                                    <span class="en" style="display:none;">This account will be pre-selected by default during sales and purchases.</span>
                                </div>
                            </div>
                            <x-core::toggle
                                name="is_default"
                                id="edit_is_default_toggle"
                                value="1"
                                color="primary"
                            />
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
                    <x-core::button type="submit" color="primary">
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update Account</span>
                    </x-core::button>
                    <x-core::button variant="secondary" type="button" class="modal-close-btn">
                        <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        function initAccountIndex() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                setTimeout(initAccountIndex, 20);
                return;
            }

            var $ = window.jQuery;
            $(function () {
                // Open Create Account Modal
                $('#btnOpenAccountModal').on('click', function () {
                    $('#createAccountModal').addClass('open');
                });

                // Open Edit Account Modal via AJAX
                $(document).on('click', '.btn-edit-account', function (e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    if (!url) return;

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function (data) {
                            $('#edit_account_modal_form').attr('action', data.update_url);
                            $('#edit_account_type_select').val(data.type);
                            $('#edit_account_type_hidden').val(data.type);
                            $('#edit_account_name_input').val(data.name);
                            $('#edit_bank_name_input').val(data.bank_name || '');
                            $('#edit_account_number_input').val(data.account_number || '');
                            $('#edit_branch_name_input').val(data.branch_name || '');
                            $('#edit_mfs_provider_select').val(data.mfs_provider || '');
                            $('#edit_mfs_type_select').val(data.mfs_type || '');
                            $('#edit_current_balance_display').val(data.current_balance_formatted || ('৳ ' + parseFloat(data.current_balance || 0).toFixed(2)));
                            $('#edit_status_select').val(data.status);
                            $('#edit_note_input').val(data.note || '');
                            $('#edit_is_default_toggle').prop('checked', !!data.is_default);

                            // Update field visibility based on type
                            $('.edit-type-field').hide();
                            if (data.type === 'bank') {
                                $('.edit-type-bank').show();
                                $('label[for="edit_account_number_input"] .bn').text('ব্যাংক হিসাব নম্বর');
                                $('label[for="edit_account_number_input"] .en').text('Bank Account Number');
                                $('#edit_account_number_input').attr('placeholder', 'যেমন: 1234567890');
                            } else if (data.type === 'mfs') {
                                $('.edit-type-mfs').show();
                                $('label[for="edit_account_number_input"] .bn').text('মোবাইল নম্বর');
                                $('label[for="edit_account_number_input"] .en').text('Mobile Number');
                                $('#edit_account_number_input').attr('placeholder', 'যেমন: 017XXXXXXXX');
                            }

                            $('#editAccountModal').addClass('open');
                        },
                        error: function () {
                            window.location.href = url;
                        }
                    });
                });

                // Close modals
                $(document).on('click', '.modal-close-btn', function (e) {
                    e.preventDefault();
                    $(this).closest('.modal-backdrop').removeClass('open');
                });

                // Close modal when clicking on backdrop
                $('.modal-backdrop').on('click', function (e) {
                    if ($(e.target).hasClass('modal-backdrop')) {
                        $(this).removeClass('open');
                    }
                });
            });
        }

        initAccountIndex();
    })();
    </script>
    @endpush
</x-core::layout>
