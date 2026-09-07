@php
    $initialUnits = old('units') ?? ($product->exists
        ? $product->units->map(fn ($u) => [
            'unit_id' => $u->id,
            'is_base' => (bool) $u->pivot->is_base,
            'conversion_factor' => (float) $u->pivot->conversion_factor,
            'is_smaller_unit' => (bool) $u->pivot->is_smaller_unit,
        ])->values()->all()
        : [['unit_id' => '', 'is_base' => true, 'conversion_factor' => 1, 'is_smaller_unit' => false]]);

    $subCategoriesByCategory = $categories->mapWithKeys(
        fn ($category) => [$category->id => $category->subCategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()]
    );

    $isVat = (bool) old('is_vat', $product->is_vat ?? false);
    $hasWarranty = (bool) old('has_warranty', $product->has_warranty ?? false);
    $hasExpiry = (bool) old('has_expiry', $product->has_expiry ?? false);
    $isWholesale = (bool) old('is_wholesale', $product->is_wholesale ?? false);
    $hasDiscount = (bool) old('has_discount', $product->has_discount ?? false);
    $hasBarcode = (bool) old('has_barcode', $product->has_barcode ?? false);
    $currentStatus = old('status', $product->status ?? 'active');
@endphp

<style>
.product-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 20px;
    align-items: start;
    margin-top: 16px;
}
@media (max-width: 1024px) {
    .product-form-layout {
        grid-template-columns: 1fr;
    }
}
.form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: var(--shadow-card);
    padding: 22px;
    margin-bottom: 20px;
}
.form-card:last-child {
    margin-bottom: 0;
}
.form-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 14px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--border);
}
.form-card-title {
    font-size: 15.5px;
    font-weight: 700;
    color: var(--ink-900);
    display: flex;
    align-items: center;
    gap: 10px;
}
.form-card-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--teal-100);
    color: var(--teal-800);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.image-dropzone {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 24px 16px;
    text-align: center;
    background: var(--paper);
    cursor: pointer;
    transition: border-color 0.2s, background-color 0.2s;
    position: relative;
}
.image-dropzone:hover {
    border-color: var(--teal-600);
    background: var(--teal-50);
}
.units-table-modern {
    width: 100%;
    border-collapse: collapse;
}
.units-table-modern th {
    padding: 11px 12px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--ink-700);
    background: var(--paper);
    border-bottom: 1px solid var(--border);
    text-align: left;
}
.units-table-modern td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    background: transparent;
}
.units-table-modern tr:last-child td {
    border-bottom: none;
}
.units-table-modern tr.base-unit-row td {
    background-color: rgba(13, 148, 136, 0.05);
}
.units-table-modern tr:hover td {
    background-color: var(--paper);
}
.units-table-modern tr.base-unit-row:hover td {
    background-color: rgba(13, 148, 136, 0.09);
}
.units-table-modern select,
.units-table-modern input[type="text"],
.units-table-modern input[type="number"],
.units-table-modern .form-control {
    display: block;
    width: 100%;
    height: 38px;
    border: 1px solid var(--border);
    background-color: var(--card);
    border-radius: 9px;
    padding: 6px 12px;
    font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Hind Siliguri', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 13px;
    color: var(--ink-900);
    outline: none;
    box-sizing: border-box;
    transition: all .15s ease;
}
.units-table-modern select,
.units-table-modern select.form-select {
    padding-right: 32px !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='m6 9 6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 14px 14px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    cursor: pointer;
}
.units-table-modern select:hover:not(:disabled),
.units-table-modern input:hover:not(:disabled) {
    border-color: var(--ink-400);
}
.units-table-modern select:focus,
.units-table-modern input:focus {
    border-color: var(--teal-800) !important;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12) !important;
    background-color: var(--card) !important;
}
.btn-make-base {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    border: 1px solid var(--border);
    background: var(--paper);
    color: var(--ink-600);
    transition: all 0.15s ease;
    white-space: nowrap;
}
.btn-make-base:hover {
    border-color: var(--teal-600);
    color: var(--teal-800);
    background: var(--teal-50);
}
.btn-make-base.active {
    background: var(--teal-800);
    color: #fff;
    border-color: var(--teal-800);
    box-shadow: 0 1px 3px rgba(13, 148, 136, 0.25);
}
.btn-remove-unit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid transparent;
    cursor: pointer;
    color: var(--red-600);
    transition: all 0.15s ease;
}
.btn-remove-unit:hover {
    background: var(--red-50);
    border-color: var(--red-100);
    color: var(--red-700);
}
</style>

<div class="product-form-layout">
    {{-- Main Left Column --}}
    <div class="form-main-col">
        {{-- 1. General Information Card --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="form-card-title">
                    <div class="form-card-icon">
                        <x-core::icon name="package" size="18" />
                    </div>
                    <div>
                        <span class="bn">মৌলিক তথ্য</span>
                        <span class="en" style="display:none;">General Information</span>
                    </div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:16px;">
                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">
                    <x-core::input
                        name="name"
                        id="product_name"
                        label="পণ্যের নাম"
                        label-en="Product Name"
                        placeholder="যেমন: স্যামসাং গ্যালাক্সি এস২৪"
                        placeholder-en="e.g. Samsung Galaxy S24"
                        :value="old('name', $product->name)"
                        size="sm"
                        :required="true"
                        autofocus
                    />
                    <x-core::input
                        name="sku"
                        id="product_sku"
                        label="SKU / বারকোড আইডি"
                        label-en="SKU Code"
                        placeholder="যেমন: SKU-1001"
                        placeholder-en="e.g. SKU-1001"
                        :value="old('sku', $product->sku)"
                        size="sm"
                        :required="true"
                        style="font-family:var(--font-mono, monospace);"
                    />
                </div>

                <x-core::input
                    name="size"
                    id="product_size"
                    label="সাইজ / ভলিউম / ভ্যারিয়েন্ট"
                    label-en="Size / Volume / Variant"
                    placeholder="যেমন: 1L, 500g, 2kg, XL, 128GB"
                    placeholder-en="e.g. 1L, 500g, 2kg, XL, 128GB"
                    :value="old('size', $product->size)"
                    helper="টিপ: একই পণ্যের ভিন্ন সাইজ (যেমন তেল ১L, ২L, ৫L) আলাদা পণ্য হিসেবে যোগ করুন, কারণ প্রতিটির স্টক ও দাম আলাদা।"
                    size="sm"
                />

                <x-core::textarea
                    name="short_description"
                    id="product_short_description"
                    label="সংক্ষিপ্ত বিবরণ"
                    label-en="Short Description"
                    placeholder="পণ্য সম্পর্কিত সংক্ষিপ্ত তথ্য বা স্পেসিফিকেশন"
                    placeholder-en="Brief details or specifications about the product"
                    :value="old('short_description', $product->short_description)"
                    rows="3"
                    size="sm"
                />
            </div>
        </div>

        {{-- 2. Pricing & Stock Card --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="form-card-title">
                    <div class="form-card-icon">
                        <x-core::icon name="dollar-sign" size="18" />
                    </div>
                    <div>
                        <span class="bn">মূল্য ও স্টক সতর্কতা</span>
                        <span class="en" style="display:none;">Pricing & Stock Alert</span>
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <x-core::input
                    type="number"
                    step="0.01"
                    min="0"
                    name="purchase_price"
                    id="product_purchase_price"
                    label="ক্রয় মূল্য (৳)"
                    label-en="Purchase Price (৳)"
                    placeholder="0.00"
                    placeholder-en="0.00"
                    :value="old('purchase_price', $product->purchase_price ?? 0)"
                    size="sm"
                    :required="true"
                />
                <x-core::input
                    type="number"
                    step="0.01"
                    min="0"
                    name="sale_price"
                    id="product_sale_price"
                    label="বিক্রয় মূল্য (৳)"
                    label-en="Sale Price (৳)"
                    placeholder="0.00"
                    placeholder-en="0.00"
                    :value="old('sale_price', $product->sale_price ?? 0)"
                    size="sm"
                    :required="true"
                />
                <x-core::input
                    type="number"
                    min="0"
                    name="alert_qty"
                    id="product_alert_qty"
                    label="স্টক এলার্ট লিমিট"
                    label-en="Alert Quantity"
                    placeholder="5"
                    placeholder-en="5"
                    :value="old('alert_qty', $product->alert_qty ?? 5)"
                    size="sm"
                    :required="true"
                />
            </div>

            {{-- Feature Accordions: VAT, Wholesale, Discount --}}
            <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px;">
                {{-- VAT Toggle Box --}}
                <x-core::accordion
                    id="vat-box"
                    name="is_vat"
                    :checked="$isVat"
                    title="এই পণ্যে ভ্যাট প্রযোজ্য (VAT Applicable)"
                    title-en="VAT Applicable on this product"
                >
                    <div style="max-width:260px;">
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="vat_percentage"
                            id="product_vat_percentage"
                            label="ভ্যাটের হার (%)"
                            label-en="VAT Percentage (%)"
                            placeholder="যেমন: 5, 7.5, 15"
                            placeholder-en="e.g. 5, 7.5, 15"
                            :value="old('vat_percentage', $product->vat_percentage ?? 0)"
                            size="sm"
                        />
                    </div>
                </x-core::accordion>

                {{-- Wholesale Toggle Box --}}
                <x-core::accordion
                    id="wholesale-box"
                    name="is_wholesale"
                    :checked="$isWholesale"
                    title="পাইকারি মূল্য সুবিধা (Wholesale Pricing)"
                    title-en="Enable Wholesale Pricing"
                >
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            name="wholesale_price"
                            id="product_wholesale_price"
                            label="পাইকারি মূল্য (৳)"
                            label-en="Wholesale Price (৳)"
                            placeholder="0.00"
                            placeholder-en="0.00"
                            :value="old('wholesale_price', $product->wholesale_price)"
                            size="sm"
                        />
                        <x-core::input
                            type="number"
                            min="1"
                            name="wholesale_min_qty"
                            id="product_wholesale_min_qty"
                            label="সর্বনিম্ন পাইকারি পরিমাণ"
                            label-en="Minimum Wholesale Quantity"
                            placeholder="10"
                            placeholder-en="10"
                            :value="old('wholesale_min_qty', $product->wholesale_min_qty ?? 10)"
                            size="sm"
                        />
                    </div>
                </x-core::accordion>

                {{-- Discount Toggle Box --}}
                <x-core::accordion
                    id="discount-box"
                    name="has_discount"
                    :checked="$hasDiscount"
                    title="নির্দিষ্ট ছাড় সুবিধা (Discount Offer)"
                    title-en="Special Product Discount"
                >
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                        <x-core::select
                            name="discount_type"
                            id="discount_type_select"
                            label="ছাড়ের ধরন"
                            label-en="Discount Type"
                            size="sm"
                            :value="old('discount_type', $product->discount_type ?? 'flat')"
                            :options="[
                                'flat' => ['bn' => 'ফ্ল্যাট পরিমাণ (Flat ৳)', 'en' => 'Flat Amount (Flat ৳)'],
                                'percentage' => ['bn' => 'শতাংশ (Percentage %)', 'en' => 'Percentage (%)'],
                            ]"
                        />
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            name="discount_value"
                            id="product_discount_value"
                            label="ছাড়ের মান"
                            label-en="Discount Value"
                            placeholder="0.00"
                            placeholder-en="0.00"
                            :value="old('discount_value', $product->discount_value)"
                            size="sm"
                        />
                    </div>
                </x-core::accordion>
            </div>
        </div>

        {{-- 3. Units & Multi-Unit Setup Card --}}
        <div class="form-card" id="card-units-setup">
            <div class="form-card-head">
                <div class="form-card-title">
                    <div class="form-card-icon">
                        <x-core::icon name="scale" size="18" />
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="bn">পরিমাপ ও ইউনিট কনভার্সন</span>
                            <span class="en" style="display:none;">Units & Conversion Setup</span>
                            <span class="badge b-teal" id="badge-active-base-unit" style="font-size:11px; padding:2px 8px;">বেস ইউনিট: নির্বাচন করুন</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500); font-weight:400; margin-top:2px;">
                            <span class="bn">পণ্য বিক্রয় ও স্টকের জন্য একক এবং প্যাকেজিং রূপান্তর নির্ধারণ করুন</span>
                            <span class="en" style="display:none;">Define primary unit and packaging conversion ratios</span>
                        </div>
                    </div>
                </div>
                <x-core::button type="button" variant="secondary" size="xs" icon="plus" id="btn-add-unit">
                    <span class="bn">নতুন ইউনিট যোগ করুন</span>
                    <span class="en" style="display:none;">Add Unit</span>
                </x-core::button>
            </div>

            @error('units') <div class="field-error" style="margin-bottom:12px;">{{ $message }}</div> @enderror

            <div class="table-wrap" style="border:1px solid var(--border); border-radius:12px; overflow:hidden; background:var(--card);">
                <table class="units-table-modern" id="units-table" style="width:100%;">
                    <thead>
                        <tr style="background:var(--paper);">
                            <th style="width:110px; text-align:center;"><span class="bn">বেস স্ট্যাটাস</span><span class="en">Base Unit</span></th>
                            <th style="min-width:160px;"><span class="bn">ইউনিটের নাম</span><span class="en">Unit</span></th>
                            <th style="min-width:180px;"><span class="bn">কনভার্সন সম্পর্ক</span><span class="en">Relationship</span></th>
                            <th style="min-width:130px;"><span class="bn">রূপান্তর অনুপাত</span><span class="en">Ratio</span></th>
                            <th style="min-width:150px;"><span class="bn">লাইভ সমীকরণ</span><span class="en">Formula Preview</span></th>
                            <th style="width:44px; text-align:center;"></th>
                        </tr>
                    </thead>
                    <tbody id="units-tbody"></tbody>
                </table>
            </div>

            {{-- Live Conversion Summary Footer --}}
            <div id="units-summary-bar" style="margin-top:12px; padding:10px 14px; background:var(--paper); border:1px solid var(--border); border-radius:10px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                <div style="display:flex; align-items:center; gap:8px; font-size:12.5px; color:var(--ink-700);">
                    <x-core::icon name="sparkles" size="15" style="color:var(--teal-700);" />
                    <span style="font-weight:600;"><span class="bn">রূপান্তর প্রিভিউ:</span><span class="en" style="display:none;">Conversion Summary:</span></span>
                    <span id="units-summary-chips" style="display:inline-flex; gap:6px; flex-wrap:wrap;"></span>
                </div>
                <div style="font-size:11.5px; color:var(--ink-500);">
                    <span class="bn">টিপ: ইনভেন্টরি স্টক সর্বদা বেস ইউনিটে হিসাব হবে</span>
                    <span class="en" style="display:none;">Stock is tracked in primary base unit</span>
                </div>
            </div>
        </div>

        {{-- 4. Warranty, Expiry & Barcode Card --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="form-card-title">
                    <div class="form-card-icon">
                        <x-core::icon name="shield-check" size="18" />
                    </div>
                    <div>
                        <span class="bn">ওয়ারেন্টি, মেয়াদ ও এক্সটার্নাল বারকোড</span>
                        <span class="en" style="display:none;">Warranty, Expiry & Barcode</span>
                    </div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:10px;">
                {{-- Barcode Toggle Box --}}
                <x-core::accordion
                    id="barcode-box"
                    name="has_barcode"
                    :checked="$hasBarcode"
                    title="কাস্টম / প্রস্তুতকারক বারকোড (Custom Barcode)"
                    title-en="Manufacturer / Custom Barcode"
                >
                    <x-core::input
                        name="barcode"
                        id="product_barcode"
                        label="বারকোড নম্বর"
                        label-en="Barcode Number"
                        placeholder="যেমন: 8901030895555"
                        placeholder-en="e.g. 8901030895555"
                        :value="old('barcode', $product->barcode)"
                        size="sm"
                        style="font-family:var(--font-mono, monospace);"
                    />
                </x-core::accordion>

                {{-- Warranty Toggle Box --}}
                <x-core::accordion
                    id="warranty-box"
                    name="has_warranty"
                    :checked="$hasWarranty"
                    title="ওয়ারেন্টি সুবিধা (Product Warranty)"
                    title-en="Product Warranty"
                >
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                        <x-core::input
                            type="number"
                            min="1"
                            name="warranty_duration"
                            id="product_warranty_duration"
                            label="ওয়ারেন্টির মেয়াদ"
                            label-en="Duration"
                            placeholder="যেমন: 1, 6, 12"
                            placeholder-en="e.g. 1, 6, 12"
                            :value="old('warranty_duration', $product->warranty_duration)"
                            size="sm"
                        />
                        <x-core::select
                            name="warranty_type"
                            id="product_warranty_type"
                            label="মেয়াদ একক"
                            label-en="Duration Unit"
                            size="sm"
                            :value="old('warranty_type', $product->warranty_type)"
                            :options="[
                                'day' => ['bn' => 'দিন (Days)', 'en' => 'Days'],
                                'month' => ['bn' => 'মাস (Months)', 'en' => 'Months'],
                                'year' => ['bn' => 'বছর (Years)', 'en' => 'Years'],
                            ]"
                        />
                    </div>
                </x-core::accordion>

                {{-- Expiry Toggle Box --}}
                <x-core::accordion
                    id="expiry-box"
                    name="has_expiry"
                    :checked="$hasExpiry"
                    title="মেয়াদ উত্তীর্ণের তারিখ (Product Expiry)"
                    title-en="Track Expiry Date"
                >
                    <x-core::input
                        type="date"
                        name="expiry_date"
                        id="product_expiry_date"
                        label="মেয়াদ শেষের তারিখ"
                        label-en="Expiry Date"
                        :value="old('expiry_date', optional($product->expiry_date)->format('Y-m-d'))"
                        size="sm"
                    />
                </x-core::accordion>
            </div>
        </div>
    </div>

    {{-- Sidebar Right Column --}}
    <div class="form-sidebar-col">


        {{-- 2. Product Image Card --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="form-card-title">
                    <div class="form-card-icon">
                        <x-core::icon name="image" size="18" />
                    </div>
                    <div>
                        <span class="bn">পণ্যের ছবি</span>
                        <span class="en" style="display:none;">Product Image</span>
                    </div>
                </div>
            </div>

            <input type="file" name="image" id="product_image_input" accept="image/*" style="display:none;">

            <div class="image-dropzone" id="image-dropzone-box" role="button" tabindex="0" aria-label="Upload product image">
                <div id="image-preview-container" style="{{ $product->image_url ? '' : 'display:none;' }} margin-bottom:12px;">
                    <img id="image-preview-element" src="{{ $product->image_url ?? '' }}" alt="Preview" style="max-width:100%; height:140px; border-radius:10px; object-fit:contain; border:1px solid var(--border); background:var(--card); padding:4px;">
                    <div style="font-size:11.5px; color:var(--ink-500); margin-top:6px;">
                        <span class="bn">ছবি পরিবর্তন করতে ক্লিক করুন</span>
                        <span class="en" style="display:none;">Click to change image</span>
                    </div>
                </div>

                <div id="image-placeholder-container" style="{{ $product->image_url ? 'display:none;' : '' }}">
                    <div style="width:48px; height:48px; border-radius:50%; background:var(--teal-100); color:var(--teal-800); margin:0 auto 10px; display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="upload-cloud" size="24" />
                    </div>
                    <div style="font-weight:600; color:var(--ink-800); font-size:13px;">
                        <span class="bn">ছবি আপলোড করতে ক্লিক করুন</span>
                        <span class="en" style="display:none;">Click to upload image</span>
                    </div>
                    <div style="font-size:11.5px; color:var(--ink-500); margin-top:4px;">PNG, JPG, WebP (Max 2MB)</div>
                </div>
            </div>
            @error('image') <div class="field-error" style="margin-top:6px;">{{ $message }}</div> @enderror
        </div>

        {{-- 3. Category & Brand Card --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="form-card-title">
                    <div class="form-card-icon">
                        <x-core::icon name="folder-tree" size="18" />
                    </div>
                    <div>
                        <span class="bn">ক্যাটাগরি ও ব্র্যান্ড</span>
                        <span class="en" style="display:none;">Category & Brand</span>
                    </div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:14px;">
                <x-core::select
                    name="category_id"
                    id="f-category"
                    label="মূল ক্যাটাগরি"
                    label-en="Parent Category"
                    :options="['' => ['bn' => '-- নির্বাচন করুন --', 'en' => '-- Select --']] + $categories->pluck('name', 'id')->toArray()"
                    :value="old('category_id', $product->category_id)"
                    size="sm"
                    :required="true"
                />

                <x-core::select
                    name="sub_category_id"
                    id="f-subcategory"
                    label="সাব-ক্যাটাগরি"
                    label-en="Sub-category"
                    size="sm"
                >
                    <option value="" data-text-bn="-- নির্বাচন করুন --" data-text-en="-- Select --">-- নির্বাচন করুন --</option>
                </x-core::select>

                <x-core::select
                    name="brand_id"
                    id="f-brand"
                    label="ব্র্যান্ড"
                    label-en="Brand"
                    :options="['' => ['bn' => '-- কোনো ব্র্যান্ড নেই (None) --', 'en' => '-- None --']] + $brands->pluck('name', 'id')->toArray()"
                    :value="old('brand_id', $product->brand_id)"
                    size="sm"
                />
            </div>
        </div>

        {{-- 1. Status & Actions Card --}}
        <div class="form-card" style="position:sticky; top:16px;">
            <div class="form-card-head">
                <div class="form-card-title">
                    <div class="form-card-icon">
                        <x-core::icon name="check-circle" size="18" />
                    </div>
                    <div>
                        <span class="bn">অবস্থা ও সংরক্ষণ</span>
                        <span class="en" style="display:none;">Status & Action</span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="bn" style="margin-bottom:8px; font-weight:600; color:var(--ink-800);">পণ্যের স্থিতি</label>
                <label class="en" style="display:none; margin-bottom:8px; font-weight:600; color:var(--ink-800);">Product Status</label>
                <x-core::status-toggle
                    name="status"
                    id="status-toggle"
                    :value="$currentStatus"
                    active-label="সক্রিয় (Active)"
                    active-label-en="Active"
                    inactive-label="নিষ্ক্রিয় (Inactive)"
                    inactive-label-en="Inactive"
                />
                @error('status') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex; flex-direction:column; gap:10px;">
                <x-core::button type="submit" color="primary" size="sm" icon="check" style="width:100%; justify-content:center;">
                    <span class="bn">{{ $product->exists ? 'পণ্য হালনাগাদ করুন' : 'পণ্য সংরক্ষণ করুন' }}</span>
                    <span class="en" style="display:none;">{{ $product->exists ? 'Update Product' : 'Save Product' }}</span>
                </x-core::button>
                <x-core::button as="a" variant="secondary" :href="route('products.index')" size="sm" style="width:100%; justify-content:center;">
                    <span class="bn">বাতিল</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    var ALL_UNITS = @json($units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'code' => $u->short_code]));
    var SUBCATS_BY_CATEGORY = @json($subCategoriesByCategory);
    var SELECTED_SUBCATEGORY = @json(old('sub_category_id', $product->sub_category_id));
    var INITIAL_UNITS = @json($initialUnits);
    var unitRowIndex = 0;

    // Filter Sub-categories
    function updateSubCategoryDropdown() {
        var categoryId = $('#f-category').val();
        var $subSelect = $('#f-subcategory');
        var options = SUBCATS_BY_CATEGORY[categoryId] || [];

        var html = '<option value="">-- কোনো সাব-ক্যাটাগরি নেই (None) --</option>';
        $.each(options, function (i, sub) {
            var isSelected = String(sub.id) === String(SELECTED_SUBCATEGORY) ? ' selected' : '';
            html += '<option value="' + sub.id + '"' + isSelected + '>' + sub.name + '</option>';
        });
        $subSelect.html(html);
    }

    $('#f-category').on('change', function () {
        SELECTED_SUBCATEGORY = '';
        updateSubCategoryDropdown();
    });

    updateSubCategoryDropdown();

    // Image Upload & Preview
    $('#image-dropzone-box').on('click', function (e) {
        if ($(e.target).closest('#product_image_input').length) {
            return;
        }
        var input = document.getElementById('product_image_input');
        if (input) {
            input.click();
        }
    });

    $('#image-dropzone-box').on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            var input = document.getElementById('product_image_input');
            if (input) {
                input.click();
            }
        }
    });

    $('#image-dropzone-box').on('dragover dragenter', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css({ 'border-color': 'var(--teal-600)', 'background': 'var(--teal-50)' });
    });

    $('#image-dropzone-box').on('dragleave drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css({ 'border-color': '', 'background': '' });
    });

    $('#image-dropzone-box').on('drop', function (e) {
        var dt = e.originalEvent && e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files.length) {
            var input = document.getElementById('product_image_input');
            if (input) {
                input.files = dt.files;
                $(input).trigger('change');
            }
        }
    });

    $('#product_image_input').on('change', function (e) {
        var file = this.files && this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (evt) {
                $('#image-preview-element').attr('src', evt.target.result);
                $('#image-preview-container').show();
                $('#image-placeholder-container').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // Enhanced Unit Rows Builder & Live Preview
    function getUnitName(id) {
        var found = ALL_UNITS.find(function (u) { return String(u.id) === String(id); });
        return found ? (found.name + ' (' + found.code + ')') : '';
    }

    function getUnitShortName(id) {
        var found = ALL_UNITS.find(function (u) { return String(u.id) === String(id); });
        return found ? found.name : '';
    }

    function generateUnitOptions(selectedId) {
        var html = '';
        $.each(ALL_UNITS, function (i, u) {
            var selected = String(u.id) === String(selectedId) ? ' selected' : '';
            html += '<option value="' + u.id + '"' + selected + '>' + u.name + ' (' + u.code + ')</option>';
        });
        return html;
    }

    function refreshUnitPreviews() {
        var $rows = $('#units-tbody tr');
        var baseUnitId = null;
        var baseUnitName = '';

        // Find current base unit
        $rows.each(function () {
            var isBase = $(this).find('.is-base-hidden').val() === '1';
            if (isBase) {
                baseUnitId = $(this).find('.unit-select').val();
                baseUnitName = getUnitShortName(baseUnitId) || 'বেস ইউনিট';
                $(this).addClass('base-unit-row').css('background', 'var(--teal-50)');
                $(this).find('.btn-make-base').addClass('active').html('<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:3px;"><polyline points="20 6 9 17 4 12"/></svg> বেস');
                $(this).find('.rel-wrapper').html('<span class="badge b-teal" style="font-size:11.5px; padding:4px 8px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:2px;"><circle cx="12" cy="12" r="10"/></svg> মৌলিক একক</span><input type="hidden" name="' + $(this).find('.rel-input-name').val() + '" value="0">');
                $(this).find('.factor-wrapper').html('<span style="font-weight:600; color:var(--ink-700); font-family:var(--font-mono, monospace); padding-left:6px;">1.00</span><input type="hidden" name="' + $(this).find('.factor-input-name').val() + '" value="1">');
                $(this).find('.unit-formula-tag').html('<span class="badge b-teal" style="font-size:11px;">🎯 মূল রেফারেন্স</span>');
            } else {
                $(this).removeClass('base-unit-row').css('background', '');
                $(this).find('.btn-make-base').removeClass('active').text('বেস করুন');

                var idx = $(this).data('index');
                var relVal = $(this).find('.rel-select').val() || $(this).data('temp-rel') || '0';
                var factorVal = $(this).find('.factor-input').val() || $(this).data('temp-factor') || '1';

                if (!$(this).find('.rel-select').length) {
                    $(this).find('.rel-wrapper').html(
                        '<select name="units[' + idx + '][is_smaller_unit]" class="form-control form-select rel-select" style="font-size:12.5px; height:38px;">' +
                            '<option value="0"' + (relVal === '0' ? ' selected' : '') + '>১ একক = X বেস</option>' +
                            '<option value="1"' + (relVal === '1' ? ' selected' : '') + '>X একক = ১ বেস</option>' +
                        '</select>'
                    );
                }

                if (!$(this).find('.factor-input').length) {
                    $(this).find('.factor-wrapper').html(
                        '<input type="number" step="0.0001" min="0.0001" name="units[' + idx + '][conversion_factor]" class="form-control factor-input" value="' + factorVal + '" placeholder="1.00" required style="font-size:12.5px; height:38px;">'
                    );
                }

                var currentUnitName = getUnitShortName($(this).find('.unit-select').val()) || 'ইউনিট';
                var formulaText = '';
                if (relVal === '0') {
                    formulaText = '১ ' + currentUnitName + ' = ' + factorVal + ' ' + (baseUnitName || 'বেস');
                } else {
                    formulaText = factorVal + ' ' + currentUnitName + ' = ১ ' + (baseUnitName || 'বেস');
                }
                $(this).find('.unit-formula-tag').html('<span style="font-size:12px; font-weight:600; color:var(--ink-800); background:var(--paper); padding:4px 10px; border-radius:6px; border:1px solid var(--border); display:inline-block;">👉 ' + formulaText + '</span>');
            }
        });

        // Top base badge
        if (baseUnitName) {
            $('#badge-active-base-unit').html('🎯 বেস: ' + baseUnitName).show();
        } else {
            $('#badge-active-base-unit').hide();
        }

        // Summary chips bar
        var chipsHtml = '';
        var countConversions = 0;
        $rows.each(function () {
            var isBase = $(this).find('.is-base-hidden').val() === '1';
            if (!isBase) {
                var currentUnitName = getUnitShortName($(this).find('.unit-select').val());
                if (currentUnitName && baseUnitName) {
                    var relVal = $(this).find('.rel-select').val() || '0';
                    var factorVal = $(this).find('.factor-input').val() || '1';
                    var chipText = relVal === '0'
                        ? '1 ' + currentUnitName + ' = ' + factorVal + ' ' + baseUnitName
                        : factorVal + ' ' + currentUnitName + ' = 1 ' + baseUnitName;
                    chipsHtml += '<span class="badge b-gray" style="font-size:11.5px; font-family:var(--font-mono, monospace);">' + chipText + '</span>';
                    countConversions++;
                }
            }
        });

        if (countConversions > 0) {
            $('#units-summary-chips').html(chipsHtml);
            $('#units-summary-bar').show();
        } else {
            $('#units-summary-chips').html('<span style="color:var(--ink-500); font-size:12px;">কোনো অতিরিক্ত প্যাকেজিং রূপান্তর নেই (শুধুমাত্র ১টি মূল বেস ইউনিট)</span>');
            $('#units-summary-bar').show();
        }
    }

    function addUnitRow(row) {
        row = row || { unit_id: '', is_base: false, conversion_factor: 1, is_smaller_unit: false };
        var idx = unitRowIndex++;
        var isBase = !!row.is_base;
        var isBaseVal = isBase ? '1' : '0';

        var trHtml = '<tr data-index="' + idx + '" class="unit-row" data-temp-rel="' + (row.is_smaller_unit ? '1' : '0') + '" data-temp-factor="' + row.conversion_factor + '">' +
            '<td style="text-align:center;">' +
                '<input type="hidden" name="units[' + idx + '][is_base]" class="is-base-hidden" value="' + isBaseVal + '">' +
                '<input type="hidden" class="rel-input-name" value="units[' + idx + '][is_smaller_unit]">' +
                '<input type="hidden" class="factor-input-name" value="units[' + idx + '][conversion_factor]">' +
                '<button type="button" class="btn-make-base ' + (isBase ? 'active' : '') + '">' +
                    (isBase ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"/></svg> বেস ইউনিট' : 'বেস সেট করুন') +
                '</button>' +
            '</td>' +
            '<td>' +
                '<select name="units[' + idx + '][unit_id]" class="form-control form-select unit-select" required style="font-size:13px; height:38px;">' +
                    '<option value="">-- ইউনিট নির্বাচন --</option>' +
                    generateUnitOptions(row.unit_id) +
                '</select>' +
            '</td>' +
            '<td class="rel-wrapper">' +
                '<select name="units[' + idx + '][is_smaller_unit]" class="form-control form-select rel-select" style="font-size:12.5px; height:38px;">' +
                    '<option value="0"' + (!row.is_smaller_unit ? ' selected' : '') + '>১ একক = X বেস</option>' +
                    '<option value="1"' + (row.is_smaller_unit ? ' selected' : '') + '>X একক = ১ বেস</option>' +
                '</select>' +
            '</td>' +
            '<td class="factor-wrapper">' +
                '<input type="number" step="0.0001" min="0.0001" name="units[' + idx + '][conversion_factor]" class="form-control factor-input" value="' + row.conversion_factor + '" placeholder="1.00" required style="font-size:12.5px; height:38px;">' +
            '</td>' +
            '<td class="unit-formula-tag" style="vertical-align:middle;"></td>' +
            '<td style="text-align:center;">' +
                '<button type="button" class="btn-remove-unit" title="মুছুন">' +
                    '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2M10 11v6M14 11v6"/></svg>' +
                '</button>' +
            '</td>' +
        '</tr>';

        $('#units-tbody').append(trHtml);
        refreshUnitPreviews();
    }

    $('#btn-add-unit').on('click', function () {
        addUnitRow({ unit_id: '', is_base: false, conversion_factor: 1, is_smaller_unit: false });
    });

    $(document).on('click', '.btn-remove-unit', function () {
        if ($('#units-tbody tr').length <= 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'সতর্কতা',
                    text: 'অন্তত একটি ইউনিট থাকা আবশ্যক!',
                    confirmButtonColor: 'var(--teal-700)'
                });
            } else {
                alert('অন্তত একটি ইউনিট থাকা আবশ্যক!');
            }
            return;
        }

        var $tr = $(this).closest('tr');
        var wasBase = $tr.find('.is-base-hidden').val() === '1';
        $tr.remove();

        if (wasBase) {
            var $first = $('#units-tbody tr:first');
            $first.find('.is-base-hidden').val('1');
        }

        refreshUnitPreviews();
    });

    $(document).on('click', '.btn-make-base', function () {
        var $tr = $(this).closest('tr');
        $('#units-tbody tr').find('.is-base-hidden').val('0');
        $tr.find('.is-base-hidden').val('1');
        refreshUnitPreviews();
    });

    $(document).on('change', '.unit-select, .rel-select', function () {
        refreshUnitPreviews();
    });

    $(document).on('input', '.factor-input', function () {
        refreshUnitPreviews();
    });

    // Populate Initial Units
    if (INITIAL_UNITS && INITIAL_UNITS.length > 0) {
        $.each(INITIAL_UNITS, function (i, row) {
            addUnitRow(row);
        });
    } else {
        addUnitRow({ unit_id: '', is_base: true, conversion_factor: 1, is_smaller_unit: false });
    }
});
</script>
@endpush
