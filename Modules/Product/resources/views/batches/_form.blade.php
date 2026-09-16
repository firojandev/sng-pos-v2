@php
    $productOptions = ['' => '-- নির্বাচন করুন --'];
    foreach ($products as $product) {
        $productOptions[$product->id] = $product->sku ? $product->name . ' (' . $product->sku . ')' : $product->name;
    }
    $selectedProductId = old('product_id', $batch->product_id ?? '');
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::select
        name="product_id"
        id="batch_product_id"
        label="পণ্য"
        label-en="Product"
        :options="$productOptions"
        :value="$selectedProductId"
        size="sm"
        :required="true"
    />

    <x-core::input
        name="batch_no"
        id="batch_batch_no"
        label="ব্যাচ নং"
        label-en="Batch No"
        placeholder="যেমন BT-2026-001"
        placeholder-en="e.g. BT-2026-001"
        :value="old('batch_no', $batch->batch_no ?? '')"
        size="sm"
        :required="true"
    />

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
        <x-core::input
            type="date"
            name="mfg_date"
            id="batch_mfg_date"
            label="উৎপাদন তারিখ"
            label-en="Mfg Date"
            :value="old('mfg_date', optional($batch->mfg_date)->format('Y-m-d'))"
            size="sm"
        />

        <x-core::input
            type="date"
            name="expiry_date"
            id="batch_expiry_date"
            label="মেয়াদ শেষের তারিখ"
            label-en="Expiry Date"
            :value="old('expiry_date', optional($batch->expiry_date)->format('Y-m-d'))"
            size="sm"
        />
    </div>

    <x-core::input
        type="number"
        step="0.01"
        min="0"
        name="quantity"
        id="batch_quantity"
        label="পরিমাণ"
        label-en="Quantity"
        placeholder="0"
        placeholder-en="0"
        :value="old('quantity', $batch->quantity ?? '')"
        size="sm"
        :required="true"
    />
</div>
