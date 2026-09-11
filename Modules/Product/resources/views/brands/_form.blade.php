<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::input
        name="name"
        id="brand_name"
        label="ব্র্যান্ডের নাম"
        label-en="Brand Name"
        placeholder="যেমন: ফ্রেশ"
        placeholder-en="e.g. Fresh"
        :value="old('name', $brand->name ?? '')"
        size="sm"
        :required="true"
    />

    <x-core::textarea
        name="description"
        id="brand_description"
        label="বিবরণ"
        label-en="Description"
        placeholder="ঐচ্ছিক বিবরণ"
        placeholder-en="Optional description"
        :value="old('description', $brand->description ?? '')"
        rows="3"
        size="sm"
    />
</div>
