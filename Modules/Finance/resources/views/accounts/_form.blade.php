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
            id="account_type_select"
            label="অ্যাকাউন্টের ধরন"
            label-en="Account Type"
            :required="true"
            :disabled="$account->exists"
            :value="old('type', $account->type ?? 'cash')"
            :options="$typeOptions"
        />
        @if ($account->exists)
            <input type="hidden" name="type" value="{{ $account->type }}">
        @endif
    </div>

    <div class="col-md-6">
        <x-core::input
            name="name"
            id="account_name_input"
            label="অ্যাকাউন্টের নাম"
            label-en="Account Title / Name"
            :required="true"
            :value="old('name', $account->name)"
            placeholder="যেমন: প্রধান ক্যাশ, ডাচ বাংলা ব্যাংক, বিকাশ মার্চেন্ট"
            placeholder-en="e.g. Main Cash, Dutch Bangla Bank, bKash Merchant"
        />
    </div>

    {{-- Bank Specific Fields --}}
    <div class="col-md-6 type-field type-bank" style="display:none;">
        <x-core::input
            name="bank_name"
            label="ব্যাংকের নাম"
            label-en="Bank Name"
            :value="old('bank_name', $account->bank_name)"
            placeholder="যেমন: Dutch-Bangla Bank, BRAC Bank"
            placeholder-en="e.g. Dutch-Bangla Bank, BRAC Bank"
        />
    </div>

    <div class="col-md-6 type-field type-bank type-mfs" style="display:none;">
        <x-core::input
            name="account_number"
            id="account_number_input"
            label="হিসাব / মোবাইল নম্বর"
            label-en="Account / Mobile Number"
            :value="old('account_number', $account->account_number)"
            placeholder="হিসাব বা মোবাইল নম্বর"
            placeholder-en="Account or Mobile Number"
        />
    </div>

    <div class="col-md-6 type-field type-bank" style="display:none;">
        <x-core::input
            name="branch_name"
            label="শাখা / রাউটিং"
            label-en="Branch / Routing"
            :value="old('branch_name', $account->branch_name)"
            placeholder="যেমন: মিরপুর শাখা"
            placeholder-en="e.g. Mirpur Branch"
        />
    </div>

    {{-- MFS Specific Fields --}}
    <div class="col-md-6 type-field type-mfs" style="display:none;">
        @php
            $providerOptions = ['' => '-- নির্বাচন করুন --'];
            foreach ($mfsProviders as $k => $label) {
                $providerOptions[$k] = $label;
            }
        @endphp
        <x-core::select
            name="mfs_provider"
            label="সার্ভিস প্রোভাইডার"
            label-en="MFS Provider"
            :value="old('mfs_provider', $account->mfs_provider)"
            :options="$providerOptions"
        />
    </div>

    <div class="col-md-6 type-field type-mfs" style="display:none;">
        @php
            $mfsTypeOptions = ['' => '-- নির্বাচন করুন --'];
            foreach ($mfsTypeLabels as $k => $labels) {
                $mfsTypeOptions[$k] = $labels['bn'] . ' (' . $labels['en'] . ')';
            }
        @endphp
        <x-core::select
            name="mfs_type"
            label="এমএফএস অ্যাকাউন্ট ধরন"
            label-en="MFS Account Type"
            :value="old('mfs_type', $account->mfs_type)"
            :options="$mfsTypeOptions"
        />
    </div>

    {{-- Opening Balance (Only on Create) --}}
    @if (! $account->exists)
        <div class="col-md-6">
            <x-core::input
                type="number"
                step="0.01"
                min="0"
                name="opening_balance"
                label="প্রারম্ভিক ব্যালেন্স (৳)"
                label-en="Opening Balance (৳)"
                :value="old('opening_balance', 0)"
                placeholder="0.00"
                prefix="৳"
            />
        </div>
    @else
        <div class="col-md-6">
            <x-core::input
                name="current_balance_display"
                label="বর্তমান ব্যালেন্স (৳)"
                label-en="Current Balance (৳)"
                :value="'৳ ' . number_format($account->current_balance, 2)"
                :disabled="true"
            />
        </div>
    @endif

    <div class="col-md-6">
        <x-core::select
            name="status"
            label="স্ট্যাটাস"
            label-en="Status"
            :required="true"
            :value="old('status', $account->status ?? 'active')"
            :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
        />
    </div>

    <div class="col-12">
        <x-core::textarea
            name="note"
            rows="2"
            label="মন্তব্য / নোট"
            label-en="Note"
            :value="old('note', $account->note)"
            placeholder="অ্যাকাউন্ট সম্পর্কিত অতিরিক্ত তথ্য"
            placeholder-en="Additional details about the account"
        />
    </div>

    <div class="col-12">
        <div class="tx-row" style="padding:12px 16px; background:var(--paper); border:1px solid var(--border); border-radius:10px; display:flex; align-items:center; justify-content:space-between; margin-top: 10px">
            <div>
                <b style="font-size:14px;"><span class="bn">ডিফল্ট অ্যাকাউন্ট হিসেবে নির্ধারণ করুন</span><span class="en" style="display:none;">Set as Default Account</span></b>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                    <span class="bn">সেলস এবং পারচেজ পেমেন্টে এই অ্যাকাউন্টটি স্বয়ংক্রিয়ভাবে প্রথম পছন্দ হিসেবে থাকবে।</span>
                    <span class="en" style="display:none;">This account will be pre-selected by default during sales and purchases.</span>
                </div>
            </div>
            <x-core::toggle
                name="is_default"
                value="1"
                :checked="(bool) old('is_default', $account->is_default)"
                color="primary"
            />
        </div>
    </div>
</div>

<div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
    <x-core::button type="submit" color="primary">
        <span class="bn">{{ $account->exists ? 'হালনাগাদ করুন' : 'সংরক্ষণ করুন' }}</span>
        <span class="en" style="display:none;">{{ $account->exists ? 'Update Account' : 'Save Account' }}</span>
    </x-core::button>
    @if (!empty($isModal))
        <x-core::button variant="secondary" type="button" class="modal-close-btn">
            <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
        </x-core::button>
    @else
        <x-core::button variant="secondary" href="{{ route('accounts.index') }}">
            <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
        </x-core::button>
    @endif
</div>

@push('scripts')
<script>
(function () {
    function initAccountForm() {
        if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
            setTimeout(initAccountForm, 20);
            return;
        }

        var $ = window.jQuery;
        $(function () {
            function updateFieldVisibility() {
                var type = $('#account_type_select').val();
                $('.type-field').hide();

                if (type === 'bank') {
                    $('.type-bank').show();
                    $('label[for="account_number_input"] .bn').text('ব্যাংক হিসাব নম্বর');
                    $('label[for="account_number_input"] .en').text('Bank Account Number');
                    $('#account_number_input').attr('placeholder', 'যেমন: 1234567890');
                } else if (type === 'mfs') {
                    $('.type-mfs').show();
                    $('label[for="account_number_input"] .bn').text('মোবাইল নম্বর');
                    $('label[for="account_number_input"] .en').text('Mobile Number');
                    $('#account_number_input').attr('placeholder', 'যেমন: 017XXXXXXXX');
                }
            }

            $('#account_type_select').on('change', function () {
                updateFieldVisibility();
            });

            updateFieldVisibility();
        });
    }

    initAccountForm();
})();
</script>
@endpush
