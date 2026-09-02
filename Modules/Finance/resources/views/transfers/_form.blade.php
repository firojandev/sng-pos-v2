<div class="row g-3">
    <div class="col-md-6">
        <x-core::form-group
            name="from_account_id"
            id="from_account_id"
            label="উৎস অ্যাকাউন্ট (টাকা প্রদান করবে)"
            label-en="From Account (Source)"
            :required="true"
        >
            <div class="form-input-group">
                <select name="from_account_id" id="from_account_id" class="form-control form-select form-control-outline" required>
                    <option value=""><span class="bn">-- অ্যাকাউন্ট নির্বাচন করুন --</span></option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}" data-balance="{{ (float) $acc->current_balance }}" {{ old('from_account_id', $transfer->from_account_id) == $acc->id ? 'selected' : '' }}>
                            {{ $acc->display_name }} [ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}]
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="from_balance_hint" style="font-size:12px; color:#16a34a; margin-top:4px; display:none;">
                <span class="bn">বর্তমান উপলব্ধ ব্যালেন্স: </span><b><span id="from_balance_val">0.00</span></b>
            </div>
        </x-core::form-group>
    </div>

    <div class="col-md-6">
        <x-core::form-group
            name="to_account_id"
            id="to_account_id"
            label="গন্তব্য অ্যাকাউন্ট (টাকা গ্রহণ করবে)"
            label-en="To Account (Destination)"
            :required="true"
        >
            <div class="form-input-group">
                <select name="to_account_id" id="to_account_id" class="form-control form-select form-control-outline" required>
                    <option value=""><span class="bn">-- অ্যাকাউন্ট নির্বাচন করুন --</span></option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ old('to_account_id', $transfer->to_account_id) == $acc->id ? 'selected' : '' }}>
                            {{ $acc->display_name }} [ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}]
                        </option>
                    @endforeach
                </select>
            </div>
        </x-core::form-group>
    </div>

    <div class="col-md-6">
        <x-core::input
            type="number"
            step="0.01"
            min="0.01"
            name="amount"
            id="transfer_amount"
            label="ট্রান্সফার পরিমাণ (৳)"
            label-en="Transfer Amount (৳)"
            :required="true"
            :value="old('amount', $transfer->amount)"
            placeholder="0.00"
            prefix="৳"
        />
    </div>

    <div class="col-md-6">
        <x-core::input
            type="number"
            step="0.01"
            min="0"
            name="charge"
            id="transfer_charge"
            label="ট্রান্সফার ফি / চার্জ (৳)"
            label-en="Transfer Fee / Charge (৳)"
            :value="old('charge', $transfer->charge ?? 0)"
            placeholder="0.00"
            prefix="৳"
            helper="ক্যাশআউট চার্জ বা ব্যাংক ফি থাকলে লিখুন (উৎস অ্যাকাউন্ট থেকে কাটা হবে)"
        />
    </div>

    <div class="col-md-6">
        <x-core::input
            type="date"
            name="transfer_date"
            label="ট্রান্সফারের তারিখ"
            label-en="Transfer Date"
            :required="true"
            :value="old('transfer_date', optional($transfer->transfer_date)->format('Y-m-d') ?? now()->format('Y-m-d'))"
        />
    </div>

    <div class="col-md-6">
        <x-core::input
            name="note"
            label="মন্তব্য / রেফারেন্স"
            label-en="Note / Reference"
            :value="old('note', $transfer->note)"
            placeholder="ট্রান্সফারের কারণ বা রেফারেন্স"
            placeholder-en="Transfer reason or reference"
        />
    </div>
</div>

<div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
    <x-core::button type="submit" color="primary" id="submit_transfer_btn">
        <span class="bn">ট্রান্সফার সম্পন্ন করুন</span>
        <span class="en" style="display:none;">Confirm Transfer</span>
    </x-core::button>
    @if (!empty($isModal))
        <x-core::button variant="secondary" type="button" class="modal-close-btn">
            <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
        </x-core::button>
    @else
        <x-core::button variant="secondary" href="{{ route('account-transfers.index') }}">
            <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
        </x-core::button>
    @endif
</div>

@push('scripts')
<script>
(function () {
    function initTransferForm() {
        if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
            setTimeout(initTransferForm, 20);
            return;
        }

        var $ = window.jQuery;
        $(function () {
            function updateFromBalance() {
                var selected = $('#from_account_id option:selected');
                var balance = selected.data('balance');
                if (balance !== undefined) {
                    $('#from_balance_val').text('৳ ' + parseFloat(balance).toFixed(2));
                    $('#from_balance_hint').show();
                } else {
                    $('#from_balance_hint').hide();
                }
            }

            $('#from_account_id').on('change', function () {
                updateFromBalance();
                var fromId = $(this).val();
                $('#to_account_id option').prop('disabled', false);
                if (fromId) {
                    $('#to_account_id option[value="' + fromId + '"]').prop('disabled', true);
                    if ($('#to_account_id').val() === fromId) {
                        $('#to_account_id').val('');
                    }
                }
            });

            $('#to_account_id').on('change', function () {
                var toId = $(this).val();
                $('#from_account_id option').prop('disabled', false);
                if (toId) {
                    $('#from_account_id option[value="' + toId + '"]').prop('disabled', true);
                }
            });

            updateFromBalance();
        });
    }

    initTransferForm();
})();
</script>
@endpush
