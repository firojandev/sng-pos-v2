<x-core::layout
    title="দ্রুত বেচা"
    title-en="Quick Sale"
    subtitle="এক ক্লিকে দ্রুত নগদ বিক্রয় যোগ করুন"
    subtitle-en="Add a quick cash sale in one step"
    active="quick-sale"
>
    @php
        $defaultAccount = $accounts->firstWhere('is_default', true) ?? $accounts->first();
        $initialAccountId = old('account_id', $defaultAccount?->id);
    @endphp

    <div class="modal-backdrop open" id="quickSaleModal">
        <div class="modal-box" style="width:460px;">
            <div class="modal-head">
                <div class="modal-title bn">দ্রুত বেচা</div>
                <div class="modal-title en" style="display:none;">Quick Sell</div>
                <a href="{{ route('sales.index') }}" class="drawer-x" title="Close">&times;</a>
            </div>

            <form method="POST" action="{{ route('quick-sale.store') }}">
                @csrf

                <div class="field" style="margin-top:0;">
                    <label class="bn">বিক্রির তারিখ</label><label class="en" style="display:none;">Sale Date</label>
                    <input type="date" name="sale_date" value="{{ old('sale_date', now()->format('Y-m-d')) }}">
                    @error('sale_date') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="bn">পেমেন্ট অ্যাকাউন্ট</label><label class="en" style="display:none;">Payment Account</label>
                    <select name="account_id" id="accountSelect" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-family:'Manrope',sans-serif;">
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" data-type="{{ $acc->type }}" {{ (int) $initialAccountId === $acc->id ? 'selected' : '' }}>
                                {{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }}) @if($acc->is_default) [ডিফল্ট] @endif
                            </option>
                        @endforeach
                    </select>
                    @error('account_id') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="bn">মূল্য পরিশোধ পদ্ধতি</label><label class="en" style="display:none;">Payment Method</label>
                    <div class="seg" id="paymentMethodSeg">
                        @foreach (['নগদ টাকা' => 'Cash', 'মোবাইল ব্যাংকিং' => 'Mobile Banking', 'ব্যাংক' => 'Bank'] as $bnLabel => $enLabel)
                            <button type="button" data-value="{{ $bnLabel }}" class="{{ old('payment_method', 'নগদ টাকা') === $bnLabel ? 'active' : '' }}">
                                <span class="bn">{{ $bnLabel }}</span><span class="en" style="display:none;">{{ $enLabel }}</span>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="{{ old('payment_method', 'নগদ টাকা') }}">
                </div>

                <div class="field">
                    <label class="bn">টাকার পরিমান <span style="color:var(--red-600);">*</span></label><label class="en" style="display:none;">Amount *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" placeholder="টাকার পরিমান" required>
                    @error('amount') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="bn">লাভ</label><label class="en" style="display:none;">Profit</label>
                    <input type="number" step="0.01" name="profit" value="{{ old('profit') }}" placeholder="লাভ">
                    @error('profit') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field" style="position:relative;">
                    <label class="bn">কাস্টমার নাম</label><label class="en" style="display:none;">Customer Name</label>
                    <input type="text" id="customerNameInput" name="customer_name" value="{{ old('customer_name') }}" placeholder="কাস্টমার নাম" autocomplete="off">
                    <div id="customerResults" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--card); border:1px solid var(--border); border-radius:10px; margin-top:4px; max-height:180px; overflow-y:auto; z-index:20; box-shadow:0 8px 20px rgba(0,0,0,.12);"></div>
                </div>

                <div class="field">
                    <label class="bn">কাস্টমার মোবাইল নম্বর</label><label class="en" style="display:none;">Customer Mobile Number</label>
                    <input type="text" id="customerPhoneInput" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+৮৮ ০১XXXXXXXXX" autocomplete="off">
                </div>
                <input type="hidden" name="customer_id" id="customerIdInput" value="{{ old('customer_id') }}">

                <div class="field">
                    <label class="bn">মন্তব্য</label><label class="en" style="display:none;">Comment</label>
                    <textarea name="note" placeholder="মন্তব্য লিখুন">{{ old('note') }}</textarea>
                    @error('note') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center; margin-top:18px; padding:13px 0; font-size:13.5px;">
                    <span class="bn">টাকার মূল্য পেয়েছেন</span><span class="en" style="display:none;">Amount Received</span>
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        function initQuickSale() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                setTimeout(initQuickSale, 20);
                return;
            }

            var $ = window.jQuery;
            $(function () {
                $('#paymentMethodSeg').on('click', 'button', function () {
                    $('#paymentMethodSeg button').removeClass('active');
                    $(this).addClass('active');
                    $('#paymentMethodInput').val($(this).data('value'));
                });

                // When account changes, update active payment method chip
                $('#accountSelect').on('change', function () {
                    var selectedType = $(this).find('option:selected').data('type');
                    var targetLabel = 'নগদ টাকা';
                    if (selectedType === 'bank') targetLabel = 'ব্যাংক';
                    else if (selectedType === 'mfs') targetLabel = 'মোবাইল ব্যাংকিং';

                    $('#paymentMethodSeg button').removeClass('active');
                    $('#paymentMethodSeg button[data-value="' + targetLabel + '"]').addClass('active');
                    $('#paymentMethodInput').val(targetLabel);
                });

                var debounceTimer = null;

                function renderResults(list) {
                    var $results = $('#customerResults');
                    if (!list || !list.length) {
                        $results.hide().empty();
                        return;
                    }
                    var html = list.map(function (c) {
                        return '<div class="cust-opt" data-id="' + c.id + '" data-name="' + String(c.name || '').replace(/"/g, '&quot;') + '" data-phone="' + (c.phone || '') + '" ' +
                            'style="padding:9px 12px; font-size:12.5px; cursor:pointer; border-bottom:1px solid var(--border);">' +
                            (c.name || '') + (c.phone ? ' <span style="color:var(--text-muted);">(' + c.phone + ')</span>' : '') +
                            '</div>';
                    }).join('');
                    $results.html(html).show();
                }

                function search(q) {
                    if (!q) {
                        $('#customerResults').hide().empty();
                        return;
                    }
                    $.getJSON("{{ route('quick-sale.customers.search') }}", { q: q }, renderResults);
                }

                $('#customerNameInput').on('input', function () {
                    $('#customerIdInput').val('');
                    clearTimeout(debounceTimer);
                    var val = $(this).val().trim();
                    debounceTimer = setTimeout(function () {
                        search(val);
                    }, 250);
                });

                $(document).on('click', '.cust-opt', function () {
                    $('#customerIdInput').val($(this).data('id'));
                    $('#customerNameInput').val($(this).data('name'));
                    $('#customerPhoneInput').val($(this).data('phone'));
                    $('#customerResults').hide().empty();
                });

                $(document).on('click', function (e) {
                    if (!$(e.target).closest('#customerNameInput, #customerResults').length) {
                        $('#customerResults').hide();
                    }
                });
            });
        }

        initQuickSale();
    })();
    </script>
    @endpush
</x-core::layout>
