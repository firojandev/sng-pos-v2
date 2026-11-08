<x-core::layout title="পে-রোল সেটআপ" title-en="Payroll Setup" subtitle="বেতন কাঠামো, ওভারটাইম, পিএফ, আয়কর ও উৎসব ভাতার নিয়ম" subtitle-en="Salary structure, overtime, PF, income tax and bonus rules" active="payroll-setup">
    <x-payroll::tabbar active="setup" />

    @php
        $yesNo = ['1' => 'হ্যাঁ (Yes)', '0' => 'না (No)'];
        $calculations = ['percent_of_gross' => '% মোট বেতনের (of Gross)', 'percent_of_basic' => '% মূল বেতনের (of Basic)', 'fixed' => 'নির্দিষ্ট টাকা (Fixed)'];
        $canEdit = auth()->user()->can('payroll-setup.edit');
    @endphp

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-top:12px;">{{ $errors->first() }}</div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(380px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">নিয়ম</span><span class="en" style="display:none;">Rules</span></div></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('payroll-setup.settings.update') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    @csrf
                    @method('PUT')
                    <div style="grid-column:1 / -1; font-weight:700; font-size:13px;"><span class="bn">ওভারটাইম (ধারা ১০৮: মূল বেতন ÷ ২০৮ × ২)</span><span class="en" style="display:none;">Overtime (s.108: basic ÷ 208 × 2)</span></div>
                    <x-core::select size="sm" name="overtime_enabled" label="ওভারটাইম দেওয়া হবে" label-en="Pay Overtime" :options="$yesNo" :value="$settings->overtime_enabled ? '1' : '0'" />
                    <x-core::input size="sm" type="number" step="0.01" name="overtime_multiplier" label="হারের গুণিতক" label-en="Rate Multiplier" :value="$settings->overtime_multiplier" :stepper="false" />
                    <x-core::input size="sm" type="number" name="overtime_hours_base" label="মাসিক ঘণ্টা (ভাজক)" label-en="Monthly Hours (Divisor)" :value="$settings->overtime_hours_base" :stepper="false" />
                    <div></div>

                    <div style="grid-column:1 / -1; font-weight:700; font-size:13px; margin-top:6px;"><span class="bn">অনুপস্থিতি ও বিলম্ব</span><span class="en" style="display:none;">Absence & Late</span></div>
                    <x-core::select size="sm" name="attendance_deductions" label="হাজিরা থেকে কর্তন" label-en="Deduct from Attendance" :options="$yesNo" :value="$settings->attendance_deductions ? '1' : '0'" />
                    <x-core::select size="sm" name="absence_deduction_basis" label="প্রতিদিনের কর্তন" label-en="Per-day Deduction" :value="$settings->absence_deduction_basis"
                        :options="['basic' => 'মূল বেতন ÷ ৩০ (Basic ÷ 30)', 'gross' => 'মোট বেতন ÷ ৩০ (Gross ÷ 30)']" />
                    <x-core::input size="sm" type="number" name="late_days_per_deduction" label="কত দিন বিলম্বে ১ দিনের কর্তন" label-en="Late Days per Day Deducted" :value="$settings->late_days_per_deduction" placeholder="বন্ধ" placeholder-en="Off" :stepper="false" />
                    <div></div>
                    <p style="grid-column:1 / -1; font-size:12px; color:var(--ink-500); margin:0;">
                        <span class="bn">চালু থাকলে প্রতিটি কর্মদিবস গণনা হয়: সাপ্তাহিক/সরকারি ছুটি বাদ, অনুমোদিত ছুটি = ছুটি, হাজিরা থাকলে উপস্থিত (অর্ধদিবস = ০.৫), হাজিরা না থাকলে অনুপস্থিত। যারা হাজিরা রাখেন না তাদের জন্য বন্ধ রাখুন।</span>
                        <span class="en" style="display:none;">When on, every working day counts: weekly offs and holidays are skipped, approved leave is leave, a recorded day is present (a half day 0.5), and a day with no attendance is absent. Keep it off if you don't record attendance.</span>
                    </p>

                    <div style="grid-column:1 / -1; font-weight:700; font-size:13px; margin-top:6px;"><span class="bn">মাসের মাঝে বেতন পরিবর্তন</span><span class="en" style="display:none;">Salary Change Within a Month</span></div>
                    <div style="grid-column:1 / -1;">
                        <x-core::select size="sm" name="salary_change_policy" label="কীভাবে প্রযোজ্য হবে" label-en="How It Applies" :value="$settings->salary_change_policy"
                            :options="['prorated' => 'কার্যকর দিন থেকে, দিন অনুপাতে (Immediately, prorated by days)', 'next_month' => 'পরের মাস থেকে (From the next payroll month)', 'full_month' => 'কার্যকর মাসের পুরো মাস (Whole month it takes effect)']" />
                    </div>

                    <div style="grid-column:1 / -1; font-weight:700; font-size:13px; margin-top:6px;"><span class="bn">প্রভিডেন্ট ফান্ড (মূল বেতনের %)</span><span class="en" style="display:none;">Provident Fund (% of basic)</span></div>
                    <x-core::select size="sm" name="pf_enabled" label="পিএফ চালু" label-en="PF Enabled" :options="$yesNo" :value="$settings->pf_enabled ? '1' : '0'" />
                    <x-core::input size="sm" type="number" name="pf_eligible_after_months" label="কত মাস পর থেকে" label-en="Eligible After (Months)" :value="$settings->pf_eligible_after_months" :stepper="false" />
                    <x-core::input size="sm" type="number" step="0.01" name="pf_employee_percent" label="কর্মচারীর অংশ %" label-en="Employee %" :value="$settings->pf_employee_percent" :stepper="false" />
                    <x-core::input size="sm" type="number" step="0.01" name="pf_employer_percent" label="মালিকের অংশ %" label-en="Employer %" :value="$settings->pf_employer_percent" :stepper="false" />
                    <x-core::input size="sm" type="number" name="pf_employer_vesting_years" label="মালিকের অংশ পেতে কত বছর" label-en="Employer Share Vests After (Years)" :value="$settings->pf_employer_vesting_years" :stepper="false" />
                    <div></div>

                    <div style="grid-column:1 / -1; font-weight:700; font-size:13px; margin-top:6px;"><span class="bn">আয়কর ও উৎসব ভাতা</span><span class="en" style="display:none;">Income Tax & Festival Bonus</span></div>
                    <x-core::select size="sm" name="tax_enabled" label="উৎসে আয়কর কাটা" label-en="Deduct Income Tax" :options="$yesNo" :value="$settings->tax_enabled ? '1' : '0'" />
                    <x-core::select size="sm" name="default_tax_location" label="ন্যূনতম করের এলাকা (ডিফল্ট)" label-en="Minimum Tax Location (Default)" :value="$settings->default_tax_location"
                        :options="collect(config('payroll.tax_locations'))->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all()" />
                    <x-core::input size="sm" type="number" step="0.01" name="festival_bonus_percent" label="প্রতি উৎসব ভাতা (মূল বেতনের %)" label-en="Bonus per Festival (% of Basic)" :value="$settings->festival_bonus_percent" :stepper="false" />
                    <x-core::input size="sm" type="number" name="festival_bonus_min_months" label="ন্যূনতম চাকরিকাল (মাস)" label-en="Minimum Service (Months)" :value="$settings->festival_bonus_min_months" :stepper="false" />
                    <x-core::input size="sm" type="number" name="festival_bonuses_per_year" label="বছরে উৎসব ভাতা (সংখ্যা)" label-en="Festival Bonuses per Year" :value="$settings->festival_bonuses_per_year" :stepper="false" />
                    <div></div>

                    @if ($canEdit)
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save</span></x-core::button></div>
                    @endif
                </form>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">বেতন কাঠামো</span><span class="en" style="display:none;">Salary Structure</span></div></div>
            <div class="panel-body" style="padding-bottom:0; font-size:12.5px; color:{{ abs($grossSplit - 100) < 0.01 ? 'var(--ink-600)' : 'var(--red-600)' }};">
                <span class="bn">মোট বেতন ভাগ: {{ rtrim(rtrim(number_format($grossSplit, 2), '0'), '.') }}%। বাকি অংশ "অন্যান্য ভাতা" হিসেবে যাবে।</span>
                <span class="en" style="display:none;">The gross is split {{ rtrim(rtrim(number_format($grossSplit, 2), '0'), '.') }}%; any rest is paid as "Other Allowance".</span>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">নাম / কোড</span><span class="en" style="display:none;">Name / Code</span></th>
                            <th><span class="bn">হিসাব</span><span class="en" style="display:none;">Calculation</span></th>
                            <th style="width:90px;"><span class="bn">মান</span><span class="en" style="display:none;">Value</span></th>
                            <th style="width:90px;"><span class="bn">সক্রিয়</span><span class="en" style="display:none;">Active</span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($components as $salaryComponent)
                            @php $formId = 'component-'.$salaryComponent->id; @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $salaryComponent->name }}
                                        @if ($salaryComponent->is_basic)
                                            <x-core::badge color="green" size="xs" label="মূল" label-en="Basic" />
                                        @endif
                                    </div>
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $salaryComponent->code }} · {{ $salaryComponent->type === 'earning' ? 'প্রাপ্য / Earning' : 'কর্তন / Deduction' }}</div>
                                </td>
                                <td><x-core::select size="sm" :no-margin="true" name="calculation" :form="$formId" :options="$calculations" :value="$salaryComponent->calculation" /></td>
                                <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" name="value" :form="$formId" :value="$salaryComponent->value" :stepper="false" /></td>
                                <td><x-core::select size="sm" :no-margin="true" name="is_active" :form="$formId" :options="$yesNo" :value="$salaryComponent->is_active ? '1' : '0'" /></td>
                                <td class="table-cell-right">
                                    @if ($canEdit)
                                        <div style="display:flex; gap:4px; justify-content:flex-end;">
                                            <form method="POST" action="{{ route('payroll-setup.components.update', $salaryComponent) }}" id="{{ $formId }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="name" value="{{ $salaryComponent->name }}">
                                                <input type="hidden" name="code" value="{{ $salaryComponent->code }}">
                                                <input type="hidden" name="type" value="{{ $salaryComponent->type }}">
                                                <input type="hidden" name="is_taxable" value="{{ $salaryComponent->is_taxable ? 1 : 0 }}">
                                                <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" icon-only title="সংরক্ষণ / Save" />
                                            </form>
                                            @unless ($salaryComponent->is_basic)
                                                <form method="POST" action="{{ route('payroll-setup.components.destroy', $salaryComponent) }}" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                                </form>
                                            @endunless
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($canEdit)
                <div class="panel-body">
                    <form method="POST" action="{{ route('payroll-setup.components.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        @csrf
                        <x-core::input size="sm" name="name" label="নাম" label-en="Name" placeholder="যেমন: মোবাইল ভাতা" placeholder-en="e.g. Mobile Allowance" :required="true" />
                        <x-core::input size="sm" name="code" label="কোড" label-en="Code" placeholder="MOBILE" :required="true" />
                        <x-core::select size="sm" name="type" label="ধরন" label-en="Type" :options="['earning' => 'প্রাপ্য (Earning)', 'deduction' => 'কর্তন (Deduction)']" value="earning" />
                        <x-core::select size="sm" name="calculation" label="হিসাব" label-en="Calculation" :options="$calculations" value="fixed" />
                        <x-core::input size="sm" type="number" step="0.01" min="0" name="value" label="মান" label-en="Value" :stepper="false" :required="true" />
                        <x-core::select size="sm" name="is_taxable" label="করযোগ্য" label-en="Taxable" :options="$yesNo" value="1" />
                        <input type="hidden" name="is_active" value="1">
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">যোগ করুন</span><span class="en" style="display:none;">Add</span></x-core::button></div>
                    </form>
                </div>
            @endif
        </div>
    </div>

</x-core::layout>
