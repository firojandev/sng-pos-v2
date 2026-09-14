<x-core::input
    name="name"
    label="সম্পদের নাম"
    label-en="Asset Name"
    placeholder="যেমন: ফ্রিজ, দোকানের ফার্নিচার"
    size="sm"
    :value="$asset->name"
    :required="true"
/>

<div style="display:grid; grid-template-columns: 1fr 1.15fr 1.05fr; gap:10px; align-items:flex-start;">
    <x-core::input
        name="amount"
        type="number"
        step="0.01"
        min="0"
        label="পরিমাণ (৳)"
        label-en="Amount (৳)"
        placeholder="0.00"
        prefix="৳"
        size="sm"
        :value="$asset->amount"
        :required="true"
        :stepper="false"
    />

    <x-core::form-group
        id="form-field-depreciation"
        label="অবচয়"
        label-en="Depreciation"
    >
        <div class="input-group-joined" style="display:flex; align-items:stretch; width:100%;">
            <div style="flex:1; min-width:0;">
                <x-core::input
                    name="depreciation"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    size="sm"
                    :value="$asset->depreciation"
                    :stepper="false"
                    :no-margin="true"
                    :error="false"
                    style="border-top-right-radius:0 !important; border-bottom-right-radius:0 !important;"
                />
            </div>
            <div style="width:96px; flex-shrink:0; margin-left:-1px;">
                <x-core::select
                    name="depreciation_type"
                    size="sm"
                    :value="old('depreciation_type', $asset->depreciation_type ?? 'flat')"
                    :no-margin="true"
                    :error="false"
                    style="border-top-left-radius:0 !important; border-bottom-left-radius:0 !important; background-color:var(--paper); cursor:pointer; font-weight:500;"
                    :options="[
                        'flat' => ['bn' => 'ফ্ল্যাট (৳)', 'en' => 'Flat (৳)'],
                        'percentage' => ['bn' => 'শতাংশ (%)', 'en' => 'Percent (%)'],
                    ]"
                />
            </div>
        </div>
        @error('depreciation')
            <x-core::error :message="$message" />
        @enderror
        @error('depreciation_type')
            <x-core::error :message="$message" />
        @enderror
    </x-core::form-group>

    <x-core::form-group
        id="form-field-validity"
        label="মেয়াদ"
        label-en="Validity"
    >
        <div class="input-group-joined" style="display:flex; align-items:stretch; width:100%;">
            <div style="flex:1; min-width:0;">
                <x-core::input
                    name="validity"
                    type="number"
                    step="0.1"
                    min="0"
                    placeholder="যেমন: ৫"
                    size="sm"
                    :value="$asset->validity"
                    :stepper="false"
                    :no-margin="true"
                    :error="false"
                    style="border-top-right-radius:0 !important; border-bottom-right-radius:0 !important;"
                />
            </div>
            <div style="width:82px; flex-shrink:0; margin-left:-1px;">
                <x-core::select
                    name="validity_unit"
                    size="sm"
                    :value="old('validity_unit', $asset->validity_unit ?? 'year')"
                    :no-margin="true"
                    :error="false"
                    style="border-top-left-radius:0 !important; border-bottom-left-radius:0 !important; background-color:var(--paper); cursor:pointer; font-weight:500;"
                    :options="[
                        'year' => ['bn' => 'বছর', 'en' => 'Years'],
                        'month' => ['bn' => 'মাস', 'en' => 'Months'],
                        'day' => ['bn' => 'দিন', 'en' => 'Days'],
                    ]"
                />
            </div>
        </div>
        @error('validity')
            <x-core::error :message="$message" />
        @enderror
        @error('validity_unit')
            <x-core::error :message="$message" />
        @enderror
    </x-core::form-group>
</div>

<div class="asset-net-preview" style="display:none; padding:8px 12px; border-radius:8px; background:var(--paper-line); border:1px solid var(--border); font-size:12.5px; align-items:center; justify-content:space-between;">
    <span style="color:var(--ink-600); font-weight:600;">
        <span class="bn">বর্তমান নিট মূল্য:</span>
        <span class="en" style="display:none;">Current Net Value:</span>
    </span>
    <span class="asset-net-val" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--green-ink); font-size:13.5px;">৳0.00</span>
</div>

<x-core::textarea
    name="note"
    label="নোট"
    label-en="Note"
    placeholder="ঐচ্ছিক নোট লিখুন..."
    rows="3"
    size="sm"
    :value="$asset->note"
/>
