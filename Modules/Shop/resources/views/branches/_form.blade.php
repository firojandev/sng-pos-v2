@php
    $branch = $branch ?? new \Modules\Shop\Models\Branch;
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::input
        name="name"
        label="শাখার নাম"
        label-en="Branch Name"
        :value="old('name', $branch->name)"
        placeholder="যেমন: রাজশাহী শাখা"
        placeholder-en="e.g. Rajshahi Branch"
        :required="true"
    />

    <x-core::input
        name="phone"
        label="মোবাইল / ফোন নম্বর"
        label-en="Phone Number"
        :value="old('phone', $branch->phone)"
        placeholder="+8801XXXXXXXXX"
        placeholder-en="+8801XXXXXXXXX"
    />

    <x-core::textarea
        name="address"
        label="ঠিকানা"
        label-en="Address"
        :value="old('address', $branch->address)"
        placeholder="শাখার সম্পূর্ণ ঠিকানা"
        placeholder-en="Full branch address"
        rows="2"
    />

    <x-core::select
        name="status"
        label="অবস্থা"
        label-en="Status"
        :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
        :value="old('status', $branch->status ?? 'active')"
        :required="true"
    />
</div>
