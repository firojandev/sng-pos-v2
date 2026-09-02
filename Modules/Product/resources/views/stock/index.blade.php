<x-core::layout
    title="স্টক খাতা"
    title-en="Stock Ledger"
    subtitle="মজুদ ও পণ্যের অবস্থা দেখুন"
    subtitle-en="View inventory and stock status"
    active="stock"
>
    <div class="cash-page-head">
        <a href="{{ route('dashboard') }}" class="back" title="Back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 18l-6-6 6-6" stroke="#1C2B27" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <div class="ttl bn">স্টক খাতা</div>
        <div class="ttl en" style="display:none;">Stock Ledger</div>

        <div class="actions">
            <a href="{{ route('stock.history') }}" class="btn btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 1 0 2.3-5.7" stroke="#1C2B27" stroke-width="1.7" stroke-linecap="round"/><path d="M4 4v4h4M12 8v4l3 2" stroke="#1C2B27" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="bn">স্টকের ইতিহাস</span><span class="en">Stock History</span>
            </a>
            <button type="button" class="btn btn-outline" onclick="openAdjustModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#1C2B27" stroke-width="1.5" stroke-linejoin="round"/></svg>
                <span class="bn">স্টক এডিট</span><span class="en">Adjust Stock</span>
            </button>
            <a href="{{ route('purchase.create') }}" class="btn btn-gold">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                <span class="bn">নতুন ক্রয়</span><span class="en">New Purchase</span>
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-teal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                <span class="bn">প্রোডাক্ট যুক্ত করুন</span><span class="en">Add Product</span>
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('stock.index') }}" class="section-row">
        <div class="filters">
            <div class="search-inline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#8B978F" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="#8B978F" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="পণ্য খুঁজে করুন">
            </div>
            <select name="sort" onchange="this.form.submit()">
                <option value="newest" @selected($sort === 'newest')>নতুন থেকে পুরাতন</option>
                <option value="oldest" @selected($sort === 'oldest')>পুরাতন থেকে নতুন</option>
                <option value="qty_desc" @selected($sort === 'qty_desc')>মজুদ (বেশি-কম)</option>
                <option value="qty_asc" @selected($sort === 'qty_asc')>মজুদ (কম-বেশি)</option>
            </select>
            <select name="filter" onchange="this.form.submit()">
                <option value="all" @selected($filter === 'all')>All ({{ $allCount }})</option>
                <option value="low" @selected($filter === 'low')>নিম্ন মজুদ ({{ $lowCount }})</option>
                <option value="out" @selected($filter === 'out')>স্টক আউট ({{ $outCount }})</option>
            </select>
        </div>
        <div class="total-pill">
            <span class="bn">মোট মজুদ: </span><span class="en" style="display:none;">Total Stock: </span>
            <b>{{ rtrim(rtrim(number_format($totalQty, 2), '0'), '.') }}</b>
        </div>
        <div class="total-pill" style="background:var(--green-100); color:var(--green-600);">
            <span class="bn">মজুদ মূল্য: </span><span class="en" style="display:none;">Stock Value: </span>
            <b>৳{{ number_format($totalValue, 2) }}</b>
        </div>
        <button type="submit" class="btn btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 0 1 13.7-5.7L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 0 1-13.7 5.7L4 15.5M4 20v-4.5h4.5" stroke="#1C2B27" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="bn">রিফ্রেশ</span><span class="en">Refresh</span>
        </button>
    </form>

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">পণ্যের নাম</th><th class="en" style="display:none;">Product</th>
                            <th class="bn">বর্তমান মজুদ</th><th class="en" style="display:none;">Current Stock</th>
                            <th class="bn">দর</th><th class="en" style="display:none;">Rate</th>
                            <th class="bn">মোট মজুদ মূল্য</th><th class="en" style="display:none;">Total Value</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="row-avatar">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="" style="width:30px; height:30px; border-radius:8px; object-fit:cover; flex:0 0 auto;">
                                        @else
                                            <div class="av" style="background:var(--teal-800);">{{ mb_substr($product->name, 0, 1) }}</div>
                                        @endif
                                        <div>
                                            <div class="cell-main">{{ $product->name }}</div>
                                            @if ($product->stock_qty <= 0)
                                                <span class="badge b-red bn">স্টক আউট</span><span class="badge b-red en" style="display:none;">Out of Stock</span>
                                            @elseif ($product->alert_qty > 0 && $product->stock_qty <= $product->alert_qty)
                                                <span class="badge b-gold bn">কম মজুদ</span><span class="badge b-gold en" style="display:none;">Low Stock</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ rtrim(rtrim(number_format($product->stock_qty, 2), '0'), '.') }}</td>
                                <td>৳{{ number_format($product->purchase_price, 2) }}</td>
                                <td>৳{{ number_format($product->stock_value, 2) }}</td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="act" title="Adjust Stock" onclick="openAdjustModal({{ $product->id }})">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-core::table.empty
                                        icon="database"
                                        title="কোনো পণ্য নেই"
                                        title-en="No stock records found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <div class="modal-backdrop @if ($errors->any()) open @endif" id="stockAdjustModal">
        <div class="modal-box" style="width:440px;">
            <div class="modal-head">
                <div class="modal-title bn">স্টক সমন্বয়</div>
                <div class="modal-title en" style="display:none;">Stock Adjustment</div>
                <button type="button" class="drawer-x" onclick="closeModal('stockAdjustModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('stock.adjust') }}">
                @csrf
                <div class="field">
                    <label class="bn">পণ্য</label><label class="en" style="display:none;">Product</label>
                    <select name="product_id" id="adjust-product" required>
                        <option value="">-- নির্বাচন করুন --</option>
                        @foreach ($allProducts as $p)
                            <option value="{{ $p->id }}" {{ (string) old('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="bn">ব্যাচ</label><label class="en" style="display:none;">Batch</label>
                    <select name="batch_id" id="adjust-batch" required>
                        <option value="">-- আগে পণ্য নির্বাচন করুন --</option>
                    </select>
                </div>
                <div class="field">
                    <label class="bn">ধরন</label><label class="en" style="display:none;">Type</label>
                    <div class="seg" id="adjust-type-seg">
                        <button type="button" class="active" onclick="setAdjustType(this,'increase')">
                            <span class="bn">বৃদ্ধি (+)</span><span class="en">Increase (+)</span>
                        </button>
                        <button type="button" onclick="setAdjustType(this,'decrease')">
                            <span class="bn">হ্রাস (-)</span><span class="en">Decrease (-)</span>
                        </button>
                    </div>
                    <input type="hidden" name="type" id="adjust-type" value="increase">
                </div>
                <div class="field">
                    <label class="bn">পরিমাণ</label><label class="en" style="display:none;">Quantity</label>
                    <input type="number" step="0.01" min="0.01" name="quantity" value="{{ old('quantity') }}" required>
                </div>
                <div class="field">
                    <label class="bn">কারণ</label><label class="en" style="display:none;">Reason</label>
                    <textarea name="reason" placeholder="যেমনঃ নষ্ট, গণনা সংশোধন ইত্যাদি">{{ old('reason') }}</textarea>
                </div>
                @error('quantity') <div class="field-error">{{ $message }}</div> @enderror
                @error('batch_id') <div class="field-error">ব্যাচ নির্বাচন করুন</div> @enderror
                <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center; margin-top:16px;">
                    <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                </button>
            </form>
        </div>
    </div>

    <script id="stock-batches-data" type="application/json">{!! json_encode($batchesByProduct) !!}</script>
    <script>
    (function () {
        const batchesByProduct = JSON.parse(document.getElementById('stock-batches-data').textContent);
        const productSelect = document.getElementById('adjust-product');
        const batchSelect = document.getElementById('adjust-batch');

        function populateBatches(productId) {
            const batches = batchesByProduct[productId] || [];
            if (!batches.length) {
                batchSelect.innerHTML = '<option value="">কোনো ব্যাচ নেই</option>';
                return;
            }
            batchSelect.innerHTML = '<option value="">-- নির্বাচন করুন --</option>' +
                batches.map((b) => '<option value="'+b.id+'">'+b.label+'</option>').join('');
        }

        productSelect.addEventListener('change', () => populateBatches(productSelect.value));

        window.openAdjustModal = function (productId) {
            if (productId) {
                productSelect.value = productId;
                populateBatches(productId);
            }
            openModal('stockAdjustModal');
        };

        window.setAdjustType = function (btn, type) {
            document.getElementById('adjust-type').value = type;
            btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
        };

        @if (old('product_id'))
            populateBatches('{{ old('product_id') }}');
            batchSelect.value = '{{ old('batch_id') }}';
        @endif
    })();
    </script>
</x-core::layout>
