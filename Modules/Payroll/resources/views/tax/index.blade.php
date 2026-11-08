<x-core::layout title="আয়কর" title-en="Tax Setup" subtitle="আয়কর বছর, করমুক্ত সীমা, স্ল্যাব, বিনিয়োগ রেয়াত ও কর্মচারীর ঘোষণা" subtitle-en="Tax years, thresholds, slabs, investment rebate and employee declarations" active="payroll">
    <x-payroll::tabbar active="tax" />

    @php
        $canEditRules = auth()->user()->can('payroll-setup.edit');
        $canEdit = auth()->user()->can('payroll.edit');
        $categoryOptions = collect($categories)->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all();
        $locationOptions = collect($locations)->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all();
        $money = fn ($amount) => number_format((float) $amount, 0);
    @endphp

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-top:12px;">{{ $errors->first() }}</div>
    @endif

    <form method="GET" style="display:flex; gap:8px; align-items:center; flex-wrap:nowrap; margin-top:16px;">
        <div style="width:220px; flex-shrink:0;"><x-core::select size="sm" :no-margin="true" name="year" :value="$year?->id"
            :options="$taxYears->mapWithKeys(fn ($taxYear) => [$taxYear->id => 'আয়বর্ষ '.$taxYear->name.' (AY '.$taxYear->assessment_year.')'])->all()" /></div>
        <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Show</span></x-core::button>
    </form>

    @if ($year)
        <div class="panel" style="margin-top:16px;">
            <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="panel-title"><span class="bn">কর্মচারীর আয়কর — আয়বর্ষ {{ $year->name }}</span><span class="en" style="display:none;">Employee Income Tax — Income Year {{ $year->name }}</span></div>
                @can('payroll.approve')
                    <form method="POST" action="{{ route('payroll.tax.close', $year) }}" class="delete-form" data-title="বছর শেষের হিসাব করবেন?" data-text="অনুমোদিত পে-রোলের প্রকৃত বেতনে বার্ষিক কর ও সমন্বয় হিসাব হবে।">
                        @csrf
                        <x-core::button type="submit" size="sm" variant="secondary" icon="check"><span class="bn">বছর শেষের সমন্বয়</span><span class="en" style="display:none;">Year-end Adjustment</span></x-core::button>
                    </form>
                @endcan
            </div>
            <div class="panel-body" style="font-size:12px; color:var(--ink-500); padding-bottom:0;">
                <span class="bn">করযোগ্য আয় → স্ল্যাব কর → বিনিয়োগ রেয়াত → ন্যূনতম কর → বার্ষিক কর → মাসিক কর্তন (বাকি কর ÷ বাকি মাস)। রেয়াত = করযোগ্য আয়ের {{ rtrim(rtrim(number_format($year->rebate_income_percent, 2), '0'), '.') }}%, যোগ্য বিনিয়োগের {{ rtrim(rtrim(number_format($year->rebate_investment_percent, 2), '0'), '.') }}% বা {{ $money($year->rebate_cap) }} — যেটি কম।</span>
                <span class="en" style="display:none;">Taxable income → slab tax → investment rebate → minimum tax → annual tax → monthly deduction (tax still owed ÷ months left). Rebate = the lowest of {{ rtrim(rtrim(number_format($year->rebate_income_percent, 2), '0'), '.') }}% of taxable income, {{ rtrim(rtrim(number_format($year->rebate_investment_percent, 2), '0'), '.') }}% of eligible investment and {{ $money($year->rebate_cap) }}.</span>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                            <th style="width:190px;"><span class="bn">শ্রেণি / এলাকা</span><span class="en" style="display:none;">Category / Location</span></th>
                            <th style="width:120px;"><span class="bn">ঘোষিত বিনিয়োগ</span><span class="en" style="display:none;">Declared Investment</span></th>
                            <th style="width:120px;"><span class="bn">যোগ্য বিনিয়োগ</span><span class="en" style="display:none;">Eligible Investment</span></th>
                            <th class="table-cell-right"><span class="bn">আনুমানিক আয়</span><span class="en" style="display:none;">Projected Income</span></th>
                            <th class="table-cell-right"><span class="bn">স্ল্যাব কর − রেয়াত</span><span class="en" style="display:none;">Slab Tax − Rebate</span></th>
                            <th class="table-cell-right"><span class="bn">বার্ষিক কর</span><span class="en" style="display:none;">Annual Tax</span></th>
                            <th class="table-cell-right"><span class="bn">কাটা হয়েছে</span><span class="en" style="display:none;">Deducted</span></th>
                            <th class="table-cell-right"><span class="bn">মাসিক</span><span class="en" style="display:none;">Monthly</span></th>
                            <th class="table-cell-right"><span class="bn">বছর শেষে</span><span class="en" style="display:none;">Year-end</span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $employee = $row['employee'];
                                $declaration = $row['declaration'];
                                $projection = $row['projection'];
                                $assessment = $projection['assessment'];
                                $formId = 'declaration-'.$employee->id;
                            @endphp
                            <tr>
                                <td><div style="font-weight:600;">{{ $employee->name }}</div><div style="font-size:11.5px; color:var(--ink-500);">{{ $employee->employee_code }} · TIN {{ $employee->tin ?: '—' }}</div></td>
                                <td>
                                    <x-core::select size="sm" :no-margin="true" name="tax_category" :form="$formId" :value="$employee->tax_category" :options="$categoryOptions" placeholder="স্বয়ংক্রিয়" placeholder-en="Automatic" />
                                    <div style="height:4px;"></div>
                                    <x-core::select size="sm" :no-margin="true" name="tax_location" :form="$formId" :value="$employee->tax_location" :options="$locationOptions" placeholder="ডিফল্ট" placeholder-en="Default" />
                                </td>
                                <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" name="declared_investment" :form="$formId" :value="(float) $declaration->declared_investment ?: ''" :stepper="false" /></td>
                                <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" name="eligible_investment" :form="$formId" :value="(float) $declaration->eligible_investment ?: ''" :stepper="false" /></td>
                                <td class="table-cell-right">{{ $money($projection['projected_income']) }}<div style="font-size:11px; color:var(--ink-500);"><span class="bn">করযোগ্য</span><span class="en" style="display:none;">taxable</span> {{ $money($assessment['taxable_income'] ?? 0) }}</div></td>
                                <td class="table-cell-right">{{ $money($assessment['slab_tax'] ?? 0) }}<div style="font-size:11px; color:var(--green-ink);">− {{ $money($assessment['rebate'] ?? 0) }}</div></td>
                                <td class="table-cell-right" style="font-weight:700;">{{ $money($assessment['annual_tax'] ?? 0) }}</td>
                                <td class="table-cell-right">{{ $money($projection['ytd_tax']) }}</td>
                                <td class="table-cell-right" style="font-weight:600;">{{ $money($projection['monthly']) }}</td>
                                <td class="table-cell-right">
                                    @if ($declaration->closed_at)
                                        <span style="font-weight:700; color:{{ (float) $declaration->year_end_adjustment > 0 ? 'var(--red-600)' : 'var(--green-ink)' }};">{{ number_format((float) $declaration->year_end_adjustment, 0) }}</span>
                                        <div style="font-size:11px; color:var(--ink-500);">{{ (float) $declaration->year_end_adjustment > 0 ? 'বাকি / Owed' : 'ফেরত / Refund' }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="table-cell-right">
                                    @if ($canEdit)
                                        <form method="POST" action="{{ route('payroll.tax.declarations.update', [$year, $employee]) }}" id="{{ $formId }}">
                                            @csrf
                                            @method('PUT')
                                            <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" icon-only title="সংরক্ষণ / Save" />
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11"><x-core::table.empty icon="users" title="কোনো কর্মচারী নেই" title-en="No employees" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="panel" style="margin-top:16px;">
        <div class="panel-head"><div class="panel-title"><span class="bn">আয়কর বছরের নিয়ম (জুলাই–জুন)</span><span class="en" style="display:none;">Tax Year Rules (July–June)</span></div></div>
        <div class="panel-body" style="font-size:12.5px; color:var(--ink-600); padding-bottom:0;">
            <span class="bn">প্রতি বাজেটের পর অর্থ আইন দেখে যাচাই করুন। স্ল্যাব করমুক্ত সীমার উপরের অংশে প্রযোজ্য; শেষ স্ল্যাবের পরিমাণ খালি রাখুন।</span>
            <span class="en" style="display:none;">Check against the Finance Act after each budget. Slabs apply above the tax-free threshold; leave the last slab's width empty.</span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(460px, 1fr)); gap:16px; padding:16px;">
            @foreach ($taxYears->concat([null]) as $taxYear)
                @php
                    $source = $taxYear ?? $taxYears->first();
                    $slabs = collect($source?->slabs ?? [])->concat([[null, null]]);
                @endphp
                <form method="POST" action="{{ $taxYear ? route('payroll-setup.tax-years.update', $taxYear) : route('payroll-setup.tax-years.store') }}" style="border:1px solid var(--border); border-radius:8px; padding:12px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    @csrf
                    @if ($taxYear)
                        @method('PUT')
                    @endif
                    <div style="grid-column:1 / -1; font-weight:700;">{{ $taxYear ? 'আয়বর্ষ '.$taxYear->name.' (Income Year)' : 'নতুন বছর (New Year)' }}</div>
                    <x-core::input size="sm" name="name" label="আয়বর্ষ" label-en="Income Year" :value="$taxYear?->name" placeholder="2027-28" :required="true" />
                    <x-core::input size="sm" name="assessment_year" label="করবর্ষ" label-en="Assessment Year" :value="$taxYear?->assessment_year" placeholder="2028-29" />
                    <div></div>
                    <x-core::input size="sm" type="date" name="starts_on" label="শুরু" label-en="Starts" :value="$taxYear?->starts_on?->toDateString()" :required="true" />
                    <x-core::input size="sm" type="date" name="ends_on" label="শেষ" label-en="Ends" :value="$taxYear?->ends_on?->toDateString()" :required="true" />
                    <div></div>

                    <div style="grid-column:1 / -1; font-size:12px; font-weight:700; color:var(--ink-700);"><span class="bn">করমুক্ত সীমা</span><span class="en" style="display:none;">Tax-free Thresholds</span></div>
                    @foreach ($categories as $key => $label)
                        <x-core::input size="sm" type="number" :name="'thresholds['.$key.']'" :label="$label['bn']" :label-en="$label['en']" :value="$source?->thresholds[$key] ?? ''" :stepper="false" :required="$key === 'general'" />
                    @endforeach

                    <div style="grid-column:1 / -1; font-size:12px; font-weight:700; color:var(--ink-700);"><span class="bn">করমুক্ত অংশ ও রেয়াত</span><span class="en" style="display:none;">Exemption & Rebate</span></div>
                    <x-core::input size="sm" type="number" step="0.01" name="exemption_percent" label="বেতনের করমুক্ত অংশ %" label-en="Exempt Share of Salary %" :value="$source?->exemption_percent ?? 33.33" :stepper="false" :required="true" />
                    <x-core::input size="sm" type="number" name="exemption_cap" label="করমুক্ত সর্বোচ্চ" label-en="Exemption Cap" :value="$source?->exemption_cap" :stepper="false" :required="true" />
                    <div></div>
                    <x-core::input size="sm" type="number" step="0.01" name="rebate_income_percent" label="রেয়াত: আয়ের %" label-en="Rebate: % of Income" :value="$source?->rebate_income_percent ?? 3" :stepper="false" :required="true" />
                    <x-core::input size="sm" type="number" step="0.01" name="rebate_investment_percent" label="রেয়াত: বিনিয়োগের %" label-en="Rebate: % of Investment" :value="$source?->rebate_investment_percent ?? 15" :stepper="false" :required="true" />
                    <x-core::input size="sm" type="number" name="rebate_cap" label="রেয়াত সর্বোচ্চ" label-en="Rebate Cap" :value="$source?->rebate_cap ?? 1000000" :stepper="false" :required="true" />

                    <div style="grid-column:1 / -1; font-size:12px; font-weight:700; color:var(--ink-700);"><span class="bn">ন্যূনতম কর (এলাকা অনুযায়ী)</span><span class="en" style="display:none;">Minimum Tax (by Location)</span></div>
                    @foreach ($locations as $key => $label)
                        <x-core::input size="sm" type="number" :name="'minimum_taxes['.$key.']'" :label="$label['bn']" :label-en="$label['en']" :value="$source?->minimum_taxes[$key] ?? ''" :stepper="false" />
                    @endforeach

                    <div style="grid-column:1 / -1; font-size:12px; font-weight:700; color:var(--ink-700);"><span class="bn">স্ল্যাব: পরিমাণ ও হার %</span><span class="en" style="display:none;">Slabs: Width and Rate %</span></div>
                    @foreach ($slabs as $index => [$width, $rate])
                        <div style="grid-column:span 2;"><x-core::input size="sm" :no-margin="true" type="number" :name="'slab_widths['.$index.']'" :value="$width" placeholder="বাকি সব" placeholder-en="The rest" :stepper="false" /></div>
                        <div><x-core::input size="sm" :no-margin="true" type="number" step="0.01" :name="'slab_rates['.$index.']'" :value="$rate" placeholder="%" :stepper="false" /></div>
                    @endforeach
                    @if ($canEditRules)
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" :icon="$taxYear ? 'check' : 'plus'"><span class="bn">{{ $taxYear ? 'সংরক্ষণ' : 'যোগ করুন' }}</span><span class="en" style="display:none;">{{ $taxYear ? 'Save' : 'Add' }}</span></x-core::button></div>
                    @endif
                </form>
            @endforeach
        </div>
    </div>
</x-core::layout>
