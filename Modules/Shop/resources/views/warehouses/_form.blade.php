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
</div>
