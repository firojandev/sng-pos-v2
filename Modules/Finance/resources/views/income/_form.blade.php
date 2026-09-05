<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::input
        name="source"
        label="উৎস"
        label-en="Source"
        value="{{ old('source', $income->source) }}"
        placeholder="যেমন: সার্ভিস চার্জ / বিবিধ আয়"
        size="sm"
        :required="true"
    />

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <x-core::input
            name="amount"
            type="number"
            step="0.01"
            min="0"
            prefix="৳"
            label="পরিমাণ (৳)"
            label-en="Amount (৳)"
            value="{{ old('amount', $income->amount) }}"
            placeholder="0.00"
            size="sm"
            :required="true"
            :stepper="false"
        />

        <x-core::input
            name="income_date"
            type="date"
            label="তারিখ"
            label-en="Date"
            value="{{ old('income_date', optional($income->income_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            size="sm"
            :required="true"
        />
    </div>

    @php
        $defaultCashAcc = $accounts->firstWhere('type', 'cash');
        $defaultCashId = $defaultCashAcc ? $defaultCashAcc->id : '';
        $selectedMethod = old('payment_method', $income->payment_method ?? 'cash');
        if (!in_array($selectedMethod, ['cash', 'bank', 'mfs'])) {
            $selectedMethod = 'cash';
        }
    @endphp
    <input type="hidden" id="form-default-cash-account-id" value="{{ $defaultCashId }}">

    <div class="payment-account-grid" id="form_payment_account_grid" style="display:grid; grid-template-columns:{{ $selectedMethod === 'cash' ? '1fr' : '1fr 1fr' }}; gap:12px;">
        <div class="payment-method-wrapper">
            <x-core::select
                name="payment_method"
                id="form_income_payment_method"
                label="পেমেন্ট মেথড"
                label-en="Payment Method"
                size="sm"
                :required="true"
            >
                <option value="cash" {{ $selectedMethod === 'cash' ? 'selected' : '' }}>নগদ (Cash)</option>
                <option value="bank" {{ $selectedMethod === 'bank' ? 'selected' : '' }}>ব্যাংক (Bank)</option>
                <option value="mfs" {{ $selectedMethod === 'mfs' ? 'selected' : '' }}>মোবাইল ব্যাংকিং (MFS)</option>
            </x-core::select>
        </div>

        <div class="account-select-wrapper" id="form_account_wrapper" style="display:{{ $selectedMethod === 'cash' ? 'none' : 'block' }};">
            <x-core::select
                name="account_id"
                id="form_income_account_id"
                label="জমা অ্যাকাউন্ট"
                label-en="Deposit Account"
                size="sm"
            >
                <option value="">-- অ্যাকাউন্ট নির্বাচন করুন --</option>
                @foreach ($accounts as $acc)
                    <option
                        value="{{ $acc->id }}"
                        data-type="{{ $acc->type }}"
                        {{ (int) old('account_id', $income->account_id ?? 0) === $acc->id ? 'selected' : '' }}
                    >
                        {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) - ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }}
                    </option>
                @endforeach
            </x-core::select>
        </div>
    </div>

    <x-core::textarea
        name="note"
        label="নোট"
        label-en="Note"
        placeholder="ঐচ্ছিক নোট লিখুন..."
        rows="2"
        size="sm"
        value="{{ old('note', $income->note) }}"
    />
</div>

@push('scripts')
<script>
$(function () {
    function applyFormPaymentRules(method, selectedAccountId) {
        var $grid = $('#form_payment_account_grid');
        var $wrapper = $('#form_account_wrapper');
        var $select = $('#form_income_account_id');
        var defaultCashId = $('#form-default-cash-account-id').val();

        if (!method || method === 'cash') {
            $wrapper.hide();
            $grid.css('grid-template-columns', '1fr');
            if (defaultCashId) {
                $select.val(defaultCashId);
            }
        } else if (method === 'bank' || method === 'mfs') {
            $wrapper.show();
            $grid.css('grid-template-columns', '1fr 1fr');

            var matched = false;
            $select.find('option').each(function () {
                var $opt = $(this);
                var optType = $opt.data('type');
                var optVal = $opt.val();
                if (!optVal) return;

                if (optType === method) {
                    $opt.show().prop('disabled', false);
                    if (selectedAccountId && String(optVal) === String(selectedAccountId)) {
                        $opt.prop('selected', true);
                        matched = true;
                    }
                } else {
                    $opt.hide().prop('disabled', true);
                    if ($opt.is(':selected')) {
                        $opt.prop('selected', false);
                    }
                }
            });

            if (!matched) {
                var $firstValid = $select.find('option:enabled[value!=""]:first');
                if ($firstValid.length) {
                    $firstValid.prop('selected', true);
                } else {
                    $select.val('');
                }
            }
        }
    }

    $(document).on('change', '#form_income_payment_method', function () {
        applyFormPaymentRules($(this).val());
    });

    applyFormPaymentRules($('#form_income_payment_method').val(), '{{ old('account_id', $income->account_id) }}');
});
</script>
@endpush
