@php
    $brandOptions = ['' => '-- নির্বাচন করুন --'];
    foreach ($brands as $brand) {
        $brandOptions[$brand->id] = $brand->name;
    }
    $selectedBrandId = old('brand_id', $model->brand_id ?? '');
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::select
        name="brand_id"
        id="model_brand_id"
        label="ব্র্যান্ড"
        label-en="Brand"
        :options="$brandOptions"
        :value="$selectedBrandId"
        size="sm"
        :required="true"
    />

    <x-core::input
        name="name"
        id="model_name"
        label="মডেলের নাম"
        label-en="Model Name"
        placeholder="যেমন: গ্যালাক্সি এস২৪"
        placeholder-en="e.g. Galaxy S24"
        :value="old('name', $model->name ?? '')"
        size="sm"
        :required="true"
    />
</div>
