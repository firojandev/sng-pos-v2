@php
    $showStatus = $showStatus ?? false;
    $monthOptions = collect(range(1, 12))
        ->mapWithKeys(fn (int $month) => [$month => \Illuminate\Support\Carbon::create(2000, $month, 1)->translatedFormat('F')])
        ->all();
@endphp

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px;">
    <x-core::input
        size="sm"
        name="name"
        label="কোম্পানির নাম"
        label-en="Company Name"
        :value="old('name', $company->name)"
        :required="true"
    />

    <x-core::input
        size="sm"
        name="legal_name"
        label="আইনি নাম"
        label-en="Legal Name"
        :value="old('legal_name', $company->legal_name)"
        placeholder="যেমন: রহিম এন্টারপ্রাইজ লিমিটেড"
        placeholder-en="e.g. Rahim Enterprise Ltd."
    />

    <x-core::input
        size="sm"
        name="trade_license_no"
        label="ট্রেড লাইসেন্স নম্বর"
        label-en="Trade Licence No."
        :value="old('trade_license_no', $company->trade_license_no)"
    />

    <x-core::input
        size="sm"
        name="tin"
        label="টিআইএন (TIN)"
        label-en="TIN"
        :value="old('tin', $company->tin)"
    />

    <x-core::input
        size="sm"
        name="bin"
        label="বিআইএন (BIN)"
        label-en="BIN"
        :value="old('bin', $company->bin)"
    />

    <x-core::input
        size="sm"
        name="phone"
        label="মোবাইল / ফোন নম্বর"
        label-en="Phone Number"
        :value="old('phone', $company->phone)"
        placeholder="+8801XXXXXXXXX"
        placeholder-en="+8801XXXXXXXXX"
    />

    <x-core::input
        size="sm"
        type="email"
        name="email"
        label="ইমেইল"
        label-en="Email"
        :value="old('email', $company->email)"
    />

    <x-core::select
        size="sm"
        name="fiscal_year_start_month"
        label="অর্থবছর শুরুর মাস"
        label-en="Fiscal Year Starts In"
        :options="$monthOptions"
        :value="old('fiscal_year_start_month', $company->fiscal_year_start_month ?? 7)"
        :required="true"
    />

    <x-core::input
        size="sm"
        name="currency"
        label="মুদ্রা (কোড)"
        label-en="Currency (Code)"
        :value="old('currency', $company->currency ?? 'BDT')"
        placeholder="BDT"
        placeholder-en="BDT"
        :required="true"
    />

    @if ($showStatus)
        <x-core::select
            size="sm"
            name="status"
            label="অবস্থা"
            label-en="Status"
            :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']"
            :value="old('status', $company->status ?? 'active')"
            :required="true"
        />
    @endif
</div>

<div style="margin-top:14px;">
    <x-core::textarea
        size="sm"
        name="address"
        label="ঠিকানা"
        label-en="Address"
        :value="old('address', $company->address)"
        rows="2"
    />
</div>
