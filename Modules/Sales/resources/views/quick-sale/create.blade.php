<x-core::layout
    title="দ্রুত বেচা"
    title-en="Quick Sale"
    subtitle="এক ক্লিকে দ্রুত নগদ বিক্রয় যোগ করুন"
    subtitle-en="Add a quick cash sale in one step"
    active="quick-sale"
>
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

                <button type="submit" class="btn" style="width:100%; justify-content:center; margin-top:18px; background:var(--ink-900); color:#fff; padding:13px 0; font-size:13.5px;">
                    <span class="bn">টাকার মূল্য পেয়েছেন</span><span class="en">Amount Received</span>
                </button>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const seg = document.getElementById('paymentMethodSeg');
        const pmInput = document.getElementById('paymentMethodInput');
        seg?.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            seg.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            pmInput.value = btn.dataset.value;
        });

        const nameInput = document.getElementById('customerNameInput');
        const phoneInput = document.getElementById('customerPhoneInput');
        const idInput = document.getElementById('customerIdInput');
        const results = document.getElementById('customerResults');
        let debounceTimer = null;

        function renderResults(list) {
            if (!list.length) {
                results.style.display = 'none';
                results.innerHTML = '';
                return;
            }
            results.innerHTML = list.map((c) => (
                '<div class="cust-opt" data-id="' + c.id + '" data-name="' + String(c.name || '').replace(/"/g, '&quot;') + '" data-phone="' + (c.phone || '') + '" ' +
                'style="padding:9px 12px; font-size:12.5px; cursor:pointer; border-bottom:1px solid var(--paper-line);">' +
                (c.name || '') + (c.phone ? ' <span style="color:var(--ink-600);">(' + c.phone + ')</span>' : '') +
                '</div>'
            )).join('');
            results.style.display = 'block';
        }

        function search(q) {
            if (!q) {
                results.style.display = 'none';
                results.innerHTML = '';
                return;
            }
            fetch("{{ route('quick-sale.customers.search') }}?q=" + encodeURIComponent(q))
                .then((r) => r.json())
                .then(renderResults);
        }

        nameInput.addEventListener('input', () => {
            idInput.value = '';
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => search(nameInput.value.trim()), 250);
        });

        results.addEventListener('click', (e) => {
            const opt = e.target.closest('.cust-opt');
            if (!opt) return;
            idInput.value = opt.dataset.id;
            nameInput.value = opt.dataset.name;
            phoneInput.value = opt.dataset.phone;
            results.style.display = 'none';
            results.innerHTML = '';
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#customerNameInput') && !e.target.closest('#customerResults')) {
                results.style.display = 'none';
            }
        });
    })();
    </script>
</x-core::layout>
