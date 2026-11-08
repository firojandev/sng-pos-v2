<x-core::layout title="বারকোড ব্যবস্থাপনা" title-en="Barcode Management" subtitle="পণ্য নির্বাচন করে সহজেই লেবেল তৈরি ও প্রিন্ট করুন" subtitle-en="Select products, customize your labels, and get ready to print" active="barcodes">
    @php
        $sampleProduct = $products->first();
    @endphp

    <div class="barcode-workspace">
        @if ($errors->any())
            <div class="bc-alert" role="alert"><x-core::icon name="circle-alert" /><span>{{ $errors->first() }}</span></div>
        @endif

        <div class="bc-intro">
            <div class="bc-intro-icon"><x-core::icon name="scan-line" /></div>
            <div>
                <h2><span class="bn">পণ্য থেকে প্রিন্ট — এক জায়গায়</span><span class="en">From product to printed label</span></h2>
                <p><span class="bn">আগের বারকোড ব্যবহার করুন অথবা নতুন বারকোড তৈরি করুন।</span><span class="en">Reuse existing barcodes or create new ones as you go.</span></p>
            </div>
            <div class="bc-intro-tag"><x-core::icon name="shield-check" /><span class="bn">আগের বারকোড অক্ষুণ্ণ থাকে</span><span class="en">Existing barcodes kept by default</span></div>
        </div>

        <div class="bc-grid">
            <section class="bc-panel bc-products" aria-labelledby="bc-products-title">
                <div class="bc-panel-heading">
                    <div class="bc-heading-group">
                        <span class="bc-step">1</span>
                        <div><h3 id="bc-products-title"><span class="bn">পণ্য নির্বাচন</span><span class="en">Select products</span></h3><p><span class="bn">লেবেল তৈরির জন্য পণ্য বেছে নিন</span><span class="en">Choose the products you want to label</span></p></div>
                    </div>
                    <x-core::badge color="secondary" size="sm">{{ number_format($products->total()) }} <span class="bn">পণ্য</span><span class="en">products</span></x-core::badge>
                </div>

                <form method="GET" action="{{ route('barcodes.index') }}" id="bc-search-form" class="filters bc-filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px;">
                    <div style="flex:1; min-width:200px;"><x-core::input size="sm" :no-margin="true" name="search" icon="search" :value="$filters['search'] ?? ''" placeholder="নাম, SKU বা বারকোড খুঁজুন" placeholder-en="Search name, SKU or barcode" aria-label="Search products" /></div>
                    <div style="width:165px; flex-shrink:0;"><x-core::select size="sm" :no-margin="true" name="category_id" aria-label="Filter by category" :value="$filters['category_id'] ?? ''" :options="['' => ['bn' => 'সব ক্যাটাগরি', 'en' => 'All categories']] + $categories" /></div>
                    <x-core::button size="sm" type="submit" color="primary" icon="search" icon-only title="Search / খুঁজুন" />
                    <x-core::button size="sm" id="bc-reset-search" variant="secondary" icon="rotate-ccw" icon-only title="Reset search / রিসেট" />
                </form>

                <div id="bc-search-feedback" class="bc-search-feedback" role="status"></div>
                <div class="bc-picker-results" @if (empty($filters['search']) && empty($filters['category_id'])) hidden @endif data-total="{{ $products->total() }}" data-last-page="{{ $products->lastPage() }}">
                    <div class="bc-picker-heading"><span class="bn">ফলাফল থেকে পণ্য যোগ করুন</span><span class="en">Add products from search results</span>
                        <x-core::button size="sm" variant="secondary" id="bc-add-all"><span class="bn">সব ফলাফল যোগ করুন</span><span class="en">Add all results</span></x-core::button>
                    </div>
                    @forelse ($products as $product)
                        <div class="bc-picker-item" data-id="{{ $product->id }}" role="button" tabindex="0" aria-label="Add {{ $product->name }}">
                            <div><strong>{{ $product->name }}</strong><span class="bc-product-sku">{{ $product->sku ?: $product->barcode }}</span></div>
                            <span class="bc-picker-affordance" aria-hidden="true"><x-core::icon name="plus" size="sm" /></span>
                            <template class="bc-product-template">@include('product::barcodes._selected-row', ['product' => $product])</template>
                        </div>
                    @empty
                        <x-core::table.empty icon="search" title="কোনো পণ্য পাওয়া যায়নি" title-en="No products found" />
                    @endforelse
                    {{ $products->links() }}
                </div>

                <div class="bc-selection-bar">
                    <div class="bc-selection-status"><span class="bc-selection-dot"></span><span id="selection-count" aria-live="polite">0 selected</span></div>
                    <x-core::button size="sm" variant="secondary" id="bc-clear-selection" icon="x"><span class="bn">সব সরান</span><span class="en">Clear selection</span></x-core::button>
                </div>
                <div class="table-responsive bc-table-wrap">
                    <table class="app-table bc-table">
                        <thead><tr>
                            <th class="bc-check-cell"></th>
                            <th><span class="bn">নির্বাচিত পণ্য</span><span class="en">Selected product</span></th>
                            <th><span class="bn">বারকোড</span><span class="en">Barcode</span></th>
                            <th><span class="bn">অবস্থা</span><span class="en">Status</span></th>
                            <th><span class="bn">লেবেলের সংখ্যা</span><span class="en">Label qty</span></th>
                        </tr></thead>
                        <tbody id="bc-selected-products">
                            @foreach ($products as $product)
                                @if ((string) ($filters['product_id'] ?? '') === (string) $product->id)
                                    @include('product::barcodes._selected-row', ['product' => $product])
                                @endif
                            @endforeach
                            <tr id="bc-selection-empty"><td colspan="5"><x-core::table.empty icon="scan-line" title="প্রথমে পণ্য খুঁজে নির্বাচন করুন" title-en="Search and select products" description="শুধু নির্বাচিত পণ্য এখানে দেখাবে" description-en="Only products you select will appear here." /></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bc-product-note"><x-core::icon name="info" /><span class="bn">একটি পণ্যের জন্য একটি বারকোড। বিক্রির সময় ইউনিট আলাদাভাবে নির্বাচন করুন।</span><span class="en">One barcode per product. Choose the selling unit separately at checkout.</span></div>
            </section>

            <aside class="bc-sidebar">
                <form method="POST" action="{{ route('barcodes.store') }}" id="barcode-form" class="bc-panel">
                    @csrf
                    <div class="bc-panel-heading">
                        <div class="bc-heading-group"><span class="bc-step">2</span><div><h3><span class="bn">লেবেল সেটিংস</span><span class="en">Label settings</span></h3><p><span class="bn">আপনার প্রিন্টের জন্য সাজিয়ে নিন</span><span class="en">Make it fit your printer</span></p></div></div>
                        <x-core::icon name="sliders-horizontal" class="bc-muted" />
                    </div>

                    <div class="bc-settings-body">
                        <div class="bc-sample-stage" aria-hidden="true">
                            <div class="bc-sample-label">
                                <div class="bc-sample-name">{{ $sampleProduct?->name ?? 'Product name' }}</div>
                                <div class="bc-sample-sku">{{ $sampleProduct?->sku ?? 'SKU-001' }}</div>
                                <div class="bc-sample-price">{{ number_format((float) ($sampleProduct?->sale_price ?? 0), 2) }} / base unit</div>
                                <div class="bc-sample-bars"></div>
                                <div class="bc-sample-number">{{ $sampleProduct?->barcode ?? '2000000000015' }}</div>
                            </div>
                            <span id="bc-size-caption">40 × 25 mm</span>
                        </div>
                        <p class="bc-sample-caption"><span class="bn">নমুনা লেআউট · আসল বারকোড পরবর্তী ধাপে</span><span class="en">Layout sample · actual barcodes in the next step</span></p>

                        <x-core::select size="sm" name="format" label="বারকোড ফরম্যাট" label-en="Barcode format" :options="['CODE128' => 'Code 128', 'EAN13' => 'EAN-13']" value="CODE128" />
                        <x-core::select size="sm" id="label-preset" label="লেবেলের মাপ" label-en="Label size" :options="['40x25' => '40 × 25 mm (compact)', '50x30' => '50 × 30 mm', '40x30' => '40 × 30 mm', '58x40' => '58 × 40 mm', 'custom' => ['bn' => 'কাস্টম মাপ', 'en' => 'Custom size']]" />
                        <div class="bc-two-fields">
                            <x-core::select size="sm" name="paper" label="কাগজ" label-en="Paper" :options="['a4' => 'A4 sheet', 'thermal' => 'Thermal roll']" />
                            <p class="bc-quantity-help"><span class="bn">প্রতি পণ্যের পাশে লেবেলের সংখ্যা দিন।</span><span class="en">Set label quantities beside each product.</span></p>
                        </div>
                        <details class="bc-advanced" @if ($errors->hasAny(['width', 'height', 'barcode_height'])) open @endif>
                            <summary><span><span class="bn">মাপ ও উচ্চতা</span><span class="en">Dimensions & barcode height</span></span><x-core::icon name="chevron-down" /></summary>
                            <div class="bc-advanced-fields">
                                <div class="bc-two-fields">
                                    <x-core::input size="sm" name="width" label="প্রস্থ (mm)" label-en="Width (mm)" type="number" min="30" max="100" step="0.1" value="40" required />
                                    <x-core::input size="sm" name="height" label="উচ্চতা (mm)" label-en="Height (mm)" type="number" min="20" max="100" step="0.1" value="25" required />
                                </div>
                                <x-core::input size="sm" name="barcode_height" label="বারকোডের উচ্চতা (mm)" label-en="Barcode height (mm)" type="number" min="10" step="0.1" value="12" required />
                            </div>
                        </details>

                        <div class="bc-field-heading"><span class="bn">লেবেলে দেখান</span><span class="en">Include on label</span></div>
                        <div class="bc-label-options">
                            <x-core::checkbox size="sm" name="show_name" label="পণ্যের নাম" label-en="Product name" checked />
                            <x-core::checkbox size="sm" name="show_sku" label="কোড / SKU" label-en="Code / SKU" checked />
                            <x-core::checkbox size="sm" name="show_price" label="বিক্রয় মূল্য" label-en="Selling price" />
                            <x-core::checkbox size="sm" name="show_number" label="বারকোড নম্বর" label-en="Barcode number" checked />
                        </div>
                        @can('products.edit')
                            <details class="bc-regenerate">
                                <summary><x-core::icon name="refresh-cw" /><span class="bn">আগের বারকোড বদলাবেন?</span><span class="en">Need to replace existing barcodes?</span></summary>
                                <div><x-core::checkbox size="sm" name="regenerate" label="নতুন বারকোড তৈরি করুন" label-en="Regenerate existing barcodes" /><p><span class="bn">আগে প্রিন্ট করা লেবেল আর কাজ করবে না।</span><span class="en">Previously printed labels will stop matching.</span></p></div>
                            </details>
                        @endcan
                    </div>
                    <div class="bc-action-area">
                        <div class="bc-total-row"><span><span class="bn">মোট লেবেল</span><span class="en">Total labels</span></span><strong id="bc-label-total" aria-live="polite">0</strong></div>
                        <x-core::button size="sm" type="submit" color="primary" icon="scan-line" id="bc-preview-button" :full-width="true"><span class="bn">লেবেল প্রিভিউ করুন</span><span class="en">Preview labels</span></x-core::button>
                        <p id="bc-action-hint" role="status"></p>
                        <div class="bc-next-step"><x-core::icon name="printer" /><span class="bn">পরবর্তী ধাপে প্রিন্ট অথবা PDF ডাউনলোড</span><span class="en">Print or download PDF in the next step</span></div>
                    </div>
                </form>
                <p class="bc-footnote"><x-core::icon name="info" /><span class="bn">নতুন বারকোড তৈরিতে পণ্য সম্পাদনার অনুমতি প্রয়োজন। EAN-13 নম্বর অভ্যন্তরীণ ব্যবহারের জন্য।</span><span class="en">Creating barcodes requires product edit access. Generated EAN-13 numbers are for internal use.</span></p>
            </aside>
        </div>
    </div>

    @push('styles')
    <style>
        .main:has(.barcode-workspace), .content:has(.barcode-workspace) { min-width:0; }
        .barcode-workspace { width:100%; min-width:0; }
        .bc-picker-results { margin:0 20px 16px; border:1px solid var(--border); border-radius:10px; background:var(--card); color:var(--ink-900); box-shadow:var(--shadow-sm); max-height:320px; overflow:auto; }
        .bc-picker-results[hidden] { display:none; }
        .bc-picker-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; background:var(--paper); font-size:11px; }
        .bc-picker-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px; border-top:1px solid var(--border); cursor:pointer; }
        .bc-picker-item:hover,.bc-picker-item:focus-visible { background:var(--paper); }
        .bc-picker-item:focus-visible { outline:2px solid var(--blue-ink); outline-offset:-2px; }
        .bc-picker-item[aria-disabled="true"] { opacity:.5; cursor:default; }
        .bc-picker-affordance { display:grid; place-items:center; width:28px; height:28px; flex-shrink:0; border-radius:7px; background:var(--paper-line); color:var(--ink-600); }
        .bc-picker-item strong { display:block; font-size:12px; }
        .bc-picker-item .bc-product-sku { display:block; }
        .bc-search-feedback { font-size:12px; color:var(--ink-600); padding:0 20px 12px; }
        .bc-search-feedback:empty { display:none; }

        .barcode-workspace { --bc-accent:var(--blue-ink); color:var(--ink-900); font-family:'Noto Sans Bengali','SolaimanLipi','Hind Siliguri','Plus Jakarta Sans','Inter',sans-serif; }
        .barcode-workspace .bc-muted { color:var(--ink-400); }
        .bc-intro { display:flex; align-items:center; gap:14px; padding:18px 0 24px; }
        .bc-intro-icon { width:46px; height:46px; border:1px solid var(--blue-ic-bg); background:var(--blue-100); color:var(--blue-ink); border-radius:14px; display:grid; place-items:center; flex-shrink:0; }
        .bc-intro h2 { font-size:16px; font-weight:700; margin:0 0 4px; }
        .bc-intro p,.bc-panel-heading p { color:var(--ink-400); font-size:12px; margin:0; }
        .bc-intro-tag { margin-left:auto; display:flex; align-items:center; gap:6px; color:var(--ink-600); font-size:11px; }
        .bc-intro-tag svg { width:15px; height:15px; color:var(--green-ink); }
        .bc-grid { width:100%; min-width:0; display:grid; grid-template-columns:minmax(0,1fr) 330px; gap:20px; align-items:start; }
        .bc-panel { border:1px solid var(--border); border-radius:16px; background:var(--card); box-shadow:var(--shadow-sm); min-width:0; overflow:hidden; }
        .bc-panel-heading { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:20px; border-bottom:1px solid var(--border); }
        .bc-heading-group { display:flex; align-items:center; gap:11px; }
        .bc-step { display:grid; place-items:center; width:29px; height:29px; border-radius:9px; background:var(--paper-line); color:var(--ink-600); font-size:12px; font-weight:700; flex-shrink:0; }
        .bc-panel-heading h3 { font-size:14px; font-weight:700; margin:0 0 3px; }
        .bc-panel-heading > svg { width:17px; height:17px; }
        .bc-filters { padding:16px 20px; overflow-x:auto; }
        .bc-selection-bar { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:11px 20px; background:var(--paper); border-top:1px solid var(--border); border-bottom:1px solid var(--border); font-size:12px; }
        .bc-selection-status { display:flex; align-items:center; gap:7px; font-weight:600; color:var(--ink-700); }
        .bc-selection-dot { width:6px; height:6px; border-radius:50%; background:var(--ink-400); }
        .bc-has-selection .bc-selection-dot { background:var(--green-ink); }
        .bc-selection-bar .form-check { margin:0; }
        .bc-table { width:100%; }
        .bc-quantity-cell { min-width:110px; width:120px; }
        .bc-quantity-cell .form-input-group { width:95px; }
        .bc-quantity-help { font-size:10px; color:var(--ink-400); line-height:1.6; align-self:center; }
        .bc-table thead th { background:var(--card); font-size:11px; font-weight:600; color:var(--ink-400); padding:13px 16px; white-space:nowrap; }
        .bc-table tbody td { padding:15px 16px; vertical-align:middle; border-bottom:1px solid var(--border); font-size:12px; }
        .bc-table .bc-check-cell { width:42px; padding-right:0; padding-left:20px; }
        .bc-check-cell .form-check { margin:0; gap:0; }
        .bc-product-name { font-size:13px; font-weight:600; color:var(--ink-900); min-width:140px; }
        .bc-product-sku { color:var(--ink-400); font-size:11px; margin-top:4px; }
        .bc-category { color:var(--ink-600); }
        .bc-barcode-value { display:inline-flex; align-items:center; gap:6px; padding:5px 8px; border:1px solid var(--border); border-radius:6px; background:var(--paper); color:var(--ink-700); font-variant-numeric:tabular-nums; white-space:nowrap; max-width:210px; overflow-wrap:anywhere; }
        .bc-barcode-value svg { width:14px; height:14px; flex-shrink:0; color:var(--ink-400); }
        .bc-table tbody tr:hover td { background:var(--paper); }
        .bc-table tbody tr.bc-row-selected td { background:var(--blue-100); }
        .bc-table-footer { padding:14px 20px; display:flex; align-items:center; justify-content:space-between; gap:12px; font-size:11px; }
        .bc-table-footer nav { margin:0; }
        .bc-product-note { display:flex; align-items:flex-start; gap:7px; padding:14px 20px; border-top:1px solid var(--border); color:var(--ink-400); font-size:11px; line-height:1.6; }
        .bc-product-note svg,.bc-footnote svg { width:14px; height:14px; flex-shrink:0; margin-top:2px; }
        .bc-sidebar { position:sticky; top:20px; }
        .bc-settings-body { padding:18px 20px 0; }
        .bc-sample-stage { padding:18px 24px 12px; border:1px dashed var(--border); border-radius:10px; background:var(--paper); display:flex; flex-direction:column; align-items:center; gap:10px; }
        .bc-sample-label { width:186px; max-width:100%; padding:12px 14px; border-radius:5px; border:1px solid var(--border); box-shadow:var(--shadow-sm); background:var(--card); color:var(--ink-900); text-align:center; font-size:9px; transition:aspect-ratio .2s; display:flex; flex-direction:column; justify-content:center; }
        .bc-sample-name { font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-bottom:3px; }
        .bc-sample-sku,.bc-sample-price { font-size:8px; color:var(--ink-600); }
        .bc-sample-bars { height:30px; flex-shrink:0; margin:5px 0 3px; background:repeating-linear-gradient(90deg,currentColor 0 1px,transparent 1px 3px,currentColor 3px 5px,transparent 5px 6px,currentColor 6px 7px,transparent 7px 10px,currentColor 10px 13px,transparent 13px 15px); }
        .bc-sample-number { font-size:8px; letter-spacing:1px; }
        #bc-size-caption { color:var(--ink-400); font-size:10px; }
        .bc-sample-caption { font-size:10px; color:var(--ink-400); text-align:center; margin:8px 0 18px; }
        .bc-two-fields { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .bc-settings-body .form-label { font-size:11px; }
        .bc-settings-body .form-group { margin-bottom:14px; }
        .bc-advanced { border:1px solid var(--border); border-radius:8px; margin:0 0 18px; }
        .bc-advanced summary { list-style:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; padding:11px 12px; font-size:11px; color:var(--ink-600); }
        .bc-advanced summary::-webkit-details-marker,.bc-regenerate summary::-webkit-details-marker { display:none; }
        .bc-advanced summary svg { width:14px; height:14px; transition:transform .2s; }
        .bc-advanced[open] summary svg { transform:rotate(180deg); }
        .bc-advanced-fields { padding:12px 12px 0; border-top:1px solid var(--border); }
        .bc-field-heading { font-size:11px; font-weight:600; color:var(--ink-700); margin-bottom:12px; }
        .bc-label-options { display:grid; grid-template-columns:1fr 1fr; gap:12px 8px; padding-bottom:18px; }
        .bc-label-options .form-check { margin:0; font-size:11px; }
        .bc-regenerate { border-top:1px solid var(--border); padding:13px 0; }
        .bc-regenerate summary { list-style:none; display:flex; align-items:center; gap:6px; font-size:10px; color:var(--ink-400); cursor:pointer; }
        .bc-regenerate summary svg { width:12px; height:12px; }
        .bc-regenerate > div { padding:12px 0 0; color:var(--red-ink); }
        .bc-regenerate p { font-size:10px; margin-top:6px; }
        .bc-action-area { padding:18px 20px; background:var(--paper); border-top:1px solid var(--border); }
        .bc-total-row { display:flex; align-items:center; justify-content:space-between; font-size:12px; color:var(--ink-600); margin-bottom:12px; }
        .bc-total-row strong { font-size:20px; color:var(--ink-900); font-variant-numeric:tabular-nums; }
        #bc-action-hint { text-align:center; font-size:10px; line-height:1.5; color:var(--ink-400); margin-top:9px; }
        .bc-next-step { display:flex; justify-content:center; align-items:center; gap:5px; font-size:10px; color:var(--ink-400); margin-top:12px; }
        .bc-next-step svg { width:12px; height:12px; }
        .bc-footnote { display:flex; align-items:flex-start; gap:7px; padding:12px 6px; color:var(--ink-400); font-size:10px; line-height:1.7; }
        .bc-alert { display:flex; align-items:center; gap:10px; padding:14px 16px; background:var(--red-100); color:var(--red-ink); border-radius:10px; margin-bottom:16px; font-size:12px; }
        .bc-alert svg { width:18px; height:18px; flex-shrink:0; }
        .bc-sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
        .bc-advanced summary:focus-visible,.bc-regenerate summary:focus-visible { outline:2px solid var(--bc-accent); outline-offset:2px; }
        @media (max-width:1100px) { .bc-grid { grid-template-columns:minmax(0,1fr) 300px; gap:14px; } .bc-intro-tag { display:none; } .bc-panel-heading,.bc-settings-body,.bc-action-area { padding-left:16px; padding-right:16px; } }
        @media (max-width:900px) { .bc-grid { grid-template-columns:1fr; } .bc-sidebar { position:static; } .bc-settings-body { display:block; } .bc-sample-stage { max-width:320px; margin:0 auto; } }
        @media (max-width:540px) { .bc-intro { padding-top:8px; } .bc-intro h2 { font-size:14px; } .bc-intro p { font-size:11px; } .bc-selection-bar { padding:12px; gap:8px; flex-wrap:wrap; } .bc-filters { padding:12px; } .bc-table-footer { flex-wrap:wrap; padding:12px; } .bc-panel-heading { padding:16px 12px; } }
    </style>
    @endpush

    @push('scripts')
    <script>
        $(function () {
            const text = (bn, en) => $('body').hasClass('lang-en') ? en : bn;
            const selectedChecks = () => $('#bc-selected-products [name="product_ids[]"]');
            let searchRequest;
            let searchTimer;
            let bulkAdding = false;
            let searchVersion = 0;
            let resultFilters = $('#bc-search-form').serializeArray();
            function addProduct(item) {
                const id = String(item.attr('data-id'));
                if (selectedChecks().filter(function () { return $(this).val() === id; }).length) { return; }
                if (selectedChecks().length >= 500) { return; }
                $('#bc-selected-products').append(item.find('template').html());
            }
            function syncPicker() {
                $('.bc-picker-item').each(function () {
                    const id = String($(this).attr('data-id'));
                    const selected = selectedChecks().filter(function () { return $(this).val() === id; }).length > 0;
                    $(this).attr('aria-disabled', String(selected)).attr('tabindex', selected ? '-1' : '0');
                });
            }
            function searchProducts(url) {
                if (searchRequest) { searchRequest.abort(); }
                const version = ++searchVersion;
                const requestedFilters = $('#bc-search-form').serializeArray();
                $('#bc-add-all').prop('disabled', true);
                $('#bc-search-feedback').text(text('খোঁজা হচ্ছে…', 'Searching…'));
                searchRequest = $.get(url || $('#bc-search-form').attr('action'), url ? undefined : $('#bc-search-form').serialize())
                    .done(function (html) {
                        if (version !== searchVersion) { return; }
                        const results = $('<div>').append($.parseHTML(html)).find('.bc-picker-results');
                        if (!results.length) {
                            $('#bc-search-feedback').text(text('সেশন শেষ হয়েছে। পেজ রিফ্রেশ করুন।', 'Your session may have expired. Refresh the page.'));
                            return;
                        }
                        resultFilters = requestedFilters;
                        $('.bc-picker-results').replaceWith(results.prop('hidden', false));
                        $('#bc-search-feedback').text('');
                        syncPicker();
                    }).fail(function (xhr, status) {
                        if (status !== 'abort') { $('#bc-search-feedback').text(text('খুঁজতে সমস্যা হয়েছে। আবার চেষ্টা করুন।', 'Search failed. Please try again.')); }
                    });
            }
            function updateSummary() {
                const selected = selectedChecks().length;
                const sample = $('#bc-selected-products .bc-product-row').first();
                $('.bc-sample-name').text(sample.find('.bc-product-name').text() || text('পণ্যের নাম', 'Product name'));
                $('.bc-sample-sku').text(sample.find('.bc-product-sku').text() || 'SKU-001');
                $('.bc-sample-number').text(sample.find('.bc-barcode-value').text().trim() || '2000000000015');
                $('.bc-sample-price').text((sample.attr('data-price') || '0.00') + ' / base unit');
                $('#bc-selection-empty').toggle(selected === 0);
                $('#bc-clear-selection').prop('disabled', selected === 0);
                syncPicker();
                let labels = 0;
                let invalidQuantity = false;
                $('#bc-selected-products .bc-product-quantity').each(function () {
                    const quantity = Number($(this).val());
                    invalidQuantity = invalidQuantity || !Number.isInteger(quantity) || quantity < 1 || quantity > 100;
                    labels += Number.isFinite(quantity) ? quantity : 0;
                });
                const invalid = bulkAdding || selected === 0 || selected > 500 || labels > 1000 || invalidQuantity;
                $('#selection-count').text(text(selected + 'টি নির্বাচিত', selected + ' selected'));
                $('#bc-label-total').text(labels.toLocaleString());
                $('#bc-preview-button').prop('disabled', invalid);
                $('.bc-selection-bar').toggleClass('bc-has-selection', selected > 0);
                let hint = text('শুরু করতে বাম থেকে পণ্য নির্বাচন করুন', 'Select products on the left to get started');
                if (selected > 500 || labels > 1000) {
                    hint = text('সর্বোচ্চ ৫০০ পণ্য ও ১,০০০ লেবেল নির্বাচন করুন', 'Limit your selection to 500 products and 1,000 labels');
                } else if (invalidQuantity) {
                    hint = text('প্রতি পণ্যে ১ থেকে ১০০টি লেবেল দিন', 'Enter 1–100 labels for each product');
                } else if (selected > 0) {
                    hint = text('প্রিন্ট করার আগে পরবর্তী ধাপে লেবেল দেখুন', 'Review your labels before printing');
                }
                $('#bc-action-hint').text(hint);
                const width = Number($('[name="width"]').val()) || 40;
                const height = Number($('[name="height"]').val()) || 25;
                $('#bc-size-caption').text(width + ' × ' + height + ' mm');
                $('.bc-sample-label').css('aspect-ratio', width + ' / ' + height);
                $('.bc-sample-bars').css('height', Math.min(60, Math.max(12, Number($('[name="barcode_height"]').val()) * 2.5)) + 'px');
                ['name', 'sku', 'price', 'number'].forEach(field => $('.bc-sample-' + field).toggle($('[name="show_' + field + '"]').prop('checked')));
            }
            $(document).on('submit', '#bc-search-form', function (event) { event.preventDefault(); if (!bulkAdding) { searchProducts(); } });
            $(document).on('input', '#bc-search-form [name="search"]', function () {
                clearTimeout(searchTimer);
                $('#bc-add-all').prop('disabled', true);
                searchTimer = setTimeout(function () { if (!bulkAdding) { searchProducts(); } }, 250);
            });
            $(document).on('change', '#bc-search-form [name="category_id"]', function () { if (!bulkAdding) { searchProducts(); } });
            $(document).on('click', '.bc-picker-results nav a', function (event) { event.preventDefault(); if (!bulkAdding) { searchProducts($(this).attr('href')); } });
            $(document).on('click', '#bc-reset-search', function () {
                if (bulkAdding) { return; }
                clearTimeout(searchTimer);
                if (searchRequest) { searchRequest.abort(); }
                searchVersion++;
                $('#bc-search-form [name="search"]').val('');
                $('#bc-search-form [name="category_id"]').val('');
                $('.bc-picker-results').prop('hidden', true);
                $('#bc-search-feedback').text('');
            });
            $(document).on('keydown', '.bc-picker-item', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $(this).trigger('click');
                }
            });
            $(document).on('click', '.bc-picker-item', function () {
                if (bulkAdding || $(this).attr('aria-disabled') === 'true') { return; }
                if (selectedChecks().length >= 500) {
                    $('#bc-search-feedback').text(text('সর্বোচ্চ ৫০০টি পণ্য নির্বাচন করুন।', 'Select at most 500 products.'));
                    return;
                }
                addProduct($(this).closest('.bc-picker-item'));
                $('.bc-picker-results').prop('hidden', true);
                $('#bc-search-form [name="search"]').val('').trigger('focus');
                updateSummary();
            });
            $(document).on('change', '#bc-selected-products [name="product_ids[]"]', function () { $(this).closest('tr').remove(); updateSummary(); });
            $(document).on('click', '#bc-clear-selection', function () { $('#bc-selected-products .bc-product-row').remove(); updateSummary(); });
            $(document).on('click', '#bc-add-all', async function () {
                const results = $('.bc-picker-results');
                if (Number(results.attr('data-total')) > 500) {
                    $('#bc-search-feedback').text(text('৫০০টির বেশি পণ্য আছে। অনুসন্ধান সীমিত করুন।', 'More than 500 results. Narrow your search first.'));
                    return;
                }
                bulkAdding = true;
                const version = searchVersion;
                const filters = resultFilters;
                $('#bc-search-form :input, #bc-add-all, #bc-preview-button').prop('disabled', true);
                try {
                    const items = [];
                    for (let page = 1; page <= Number(results.attr('data-last-page')); page++) {
                        const html = await $.get($('#bc-search-form').attr('action'), $.param(filters.concat([{name:'page', value:page}])));
                        const resultPage = $('<div>').append($.parseHTML(html)).find('.bc-picker-results');
                        if (!resultPage.length) { throw new Error(text('পণ্য লোড করা যায়নি। পেজ রিফ্রেশ করুন।', 'Could not load products. Refresh the page.')); }
                        const found = resultPage.find('.bc-picker-item');
                        found.each(function () { items.push($(this)); });
                    }
                    const uniqueIds = new Set(selectedChecks().map(function () { return $(this).val(); }).get());
                    items.forEach(item => uniqueIds.add(String(item.attr('data-id'))));
                    if (uniqueIds.size > 500) { throw new Error(text('সর্বোচ্চ ৫০০টি পণ্য নির্বাচন করুন।', 'Select at most 500 products.')); }
                    if (version === searchVersion) {
                        items.forEach(addProduct);
                        $('.bc-picker-results').prop('hidden', true);
                        $('#bc-search-form [name="search"]').val('');
                    }
                    $('#bc-search-feedback').text('');
                } catch (error) { $('#bc-search-feedback').text(error.message || text('সব পণ্য যোগ করা যায়নি। আবার চেষ্টা করুন।', 'Could not add all results. Please try again.')); }
                finally { bulkAdding = false; $('#bc-search-form :input, #bc-add-all').prop('disabled', false); updateSummary(); }
            });
            $(document).on('change input', '#barcode-form input, #barcode-form select, #bc-selected-products .bc-product-quantity', updateSummary);
            $(document).on('change', '#label-preset', function () {
                if ($(this).val() === 'custom') {
                    $('.bc-advanced').prop('open', true);
                } else {
                    const dimensions = $(this).val().split('x');
                    $('[name="width"]').val(dimensions[0]);
                    $('[name="height"]').val(dimensions[1]);
                }
                updateSummary();
            });
            $(document).on('input', '[name="width"], [name="height"]', function () { $('#label-preset').val('custom'); });
            $('#barcode-form input').on('invalid', function () { $(this).closest('details').prop('open', true); });
            $(document).on('submit', '#barcode-form', function (event) {
                if ($('[name="regenerate"]').prop('checked') && !$(this).data('confirmed')) {
                    event.preventDefault();
                    Swal.fire({title:text('আগের বারকোড বদলাবেন?', 'Replace existing barcodes?'), text:text('আগে প্রিন্ট করা লেবেল আর কাজ করবে না।', 'Previously printed labels will stop matching these products.'), icon:'warning', showCancelButton:true}).then(result => {
                        if (result.isConfirmed) { $(this).data('confirmed', true).trigger('submit'); }
                    });
                }
            });
            $(document).on('change', '#lang-toggle', function () { setTimeout(updateSummary, 0); });
            $(document).on('click', '[data-action="set-lang-bn"], [data-action="set-lang-en"], .switch-opt-bn, .switch-opt-en', function () { setTimeout(updateSummary, 0); });
            updateSummary();
        });
    </script>
    @endpush
</x-core::layout>
