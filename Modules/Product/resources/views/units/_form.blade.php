<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
    <x-core::input
        name="name"
        id="unit_name"
        label="ইউনিটের নাম"
        label-en="Unit Name"
        placeholder="যেমন: কিলোগ্রাম / পিস"
        placeholder-en="e.g. Kilogram / Pcs"
        :value="old('name', $unit->name ?? '')"
        size="sm"
        :required="true"
    />

    <x-core::input
        name="short_code"
        id="unit_short_code"
        label="সংক্ষিপ্ত কোড"
        label-en="Short Code"
        placeholder="যেমন: Kg / Pcs"
        placeholder-en="e.g. Kg / Pcs"
        :value="old('short_code', $unit->short_code ?? '')"
        size="sm"
        :required="true"
    />
</div>
