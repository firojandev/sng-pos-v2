@php
    $warehouse = $warehouse ?? new \Modules\Shop\Models\Warehouse;
    if (!isset($branchOptions)) {
        $branches = \Modules\Shop\Models\Branch::where('status', 'active')->orderBy('name')->get();
        $branchOptions = ['' => '-- শাখা নির্বাচন করুন (Select Branch) --'];
        foreach ($branches as $b) {
            $branchOptions[$b->id] = $b->name;
        }
    }
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::select
        name="branch_id"
        label="শাখা"
        label-en="Branch"
        :options="$branchOptions"
        :value="old('branch_id', $warehouse->branch_id)"
        :required="true"
    />

    <x-core::input
        name="name"
        label="গুদামের নাম"
        label-en="Warehouse Name"
        :value="old('name', $warehouse->name)"
        placeholder="যেমন: প্রধান গুদাম"
        placeholder-en="e.g. Main Warehouse"
        :required="true"
    />

    <x-core::textarea
        name="address"
        label="ঠিকানা"
        label-en="Address"
        :value="old('address', $warehouse->address)"
        placeholder="গুদামের সম্পূর্ণ ঠিকানা"
        placeholder-en="Full warehouse address"
        rows="2"
    />

    <x-core::select
        name="status"
        label="অবস্থা"
        label-en="Status"
        :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
        :value="old('status', $warehouse->status ?? 'active')"
        :required="true"
    />

    <div style="padding:12px 14px; background:var(--paper); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <div style="font-weight:600; font-size:13.5px; color:var(--ink-900);">
                <span class="bn">ডিফল্ট গুদাম হিসেবে নির্ধারণ করুন</span>
                <span class="en" style="display:none;">Set as Default Warehouse</span>
            </div>
            <div style="font-size:12px; color:var(--ink-500); margin-top:2px;">
                <span class="bn">বিক্রয় এবং ক্রয় তৈরির সময় এই গুদামটি স্বয়ংক্রিয়ভাবে নির্বাচিত থাকবে।</span>
                <span class="en" style="display:none;">This warehouse will be pre-selected by default during sales and purchases.</span>
            </div>
        </div>
        <x-core::toggle
            name="is_default"
            id="warehouse_is_default"
            value="1"
            color="primary"
            :checked="(bool) old('is_default', $warehouse->is_default)"
        />
    </div>
</div>
