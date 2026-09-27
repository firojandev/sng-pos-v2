<x-core::layout title="বেতন ও পে-রোল" title-en="Salary & Payroll" :subtitle="$employee->employee_code.' — '.$employee->name" :subtitle-en="$employee->employee_code.' — '.$employee->name" active="employees">
    <x-employee::tabbar active="employees" />

    @php
        $revisionTypes = ['joining' => 'যোগদান (Joining)', 'increment' => 'ইনক্রিমেন্ট (Increment)', 'promotion' => 'পদোন্নতি (Promotion)', 'adjustment' => 'সমন্বয় (Adjustment)', 'decrement' => 'হ্রাস (Decrement)'];
        $canEdit = auth()->user()->can('payroll.edit');
    @endphp

    <div style="display:flex; gap:8px; margin-top:16px;">
        <x-core::button as="a" href="{{ route('employees.profile.edit', $employee) }}" size="sm" variant="secondary" icon="arrow-left"><span class="bn">প্রোফাইল</span><span class="en" style="display:none;">Profile</span></x-core::button>
    </div>

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-top:12px;">{{ $errors->first() }}</div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">বেতন কাঠামো (মাসিক)</span><span class="en" style="display:none;">Salary Structure (Monthly)</span></div></div>
            <form method="POST" action="{{ route('payroll.salary.structure.update', $employee) }}">
                @csrf
                @method('PUT')
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th><span class="bn">অংশ</span><span class="en" style="display:none;">Component</span></th>
                                <th class="table-cell-right"><span class="bn">টাকা</span><span class="en" style="display:none;">Amount</span></th>
                                <th style="width:130px;"><span class="bn">নিজস্ব অঙ্ক</span><span class="en" style="display:none;">Own Amount</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $lines = collect($breakdown['lines']); @endphp
                            @foreach ($components as $salaryComponent)
                                @php $line = $lines->firstWhere('component_id', $salaryComponent->id); @endphp
                                <tr>
                                    <td>{{ $salaryComponent->name }} <span style="font-size:11.5px; color:var(--ink-500);">{{ $salaryComponent->type === 'deduction' ? '(−)' : '' }}</span></td>
                                    <td class="table-cell-right">{{ number_format((float) ($line['amount'] ?? 0), 2) }}</td>
                                    <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" :name="'amounts['.$salaryComponent->id.']'" :value="$overrides[$salaryComponent->id] ?? ''" placeholder="স্বয়ংক্রিয়" placeholder-en="Auto" :stepper="false" /></td>
                                </tr>
                            @endforeach
                            @if ($line = $lines->firstWhere('code', 'OTHER'))
                                <tr><td>{{ $line['name'] }}</td><td class="table-cell-right">{{ number_format($line['amount'], 2) }}</td><td></td></tr>
                            @endif
                            <tr>
                                <td style="font-weight:700;"><span class="bn">মোট বেতন</span><span class="en" style="display:none;">Gross Salary</span></td>
                                <td class="table-cell-right" style="font-weight:700;">{{ number_format($gross, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="color:var(--ink-600);"><span class="bn">আনুমানিক মাসিক আয়কর</span><span class="en" style="display:none;">Estimated Monthly Income Tax</span></td>
                                <td class="table-cell-right" style="color:var(--ink-600);">{{ number_format($monthlyTax, 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @if ($canEdit)
                    <div class="panel-body"><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save</span></x-core::button></div>
                @endif
            </form>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">বেতনের ইতিহাস</span><span class="en" style="display:none;">Salary History</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">কার্যকর</span><span class="en" style="display:none;">Effective</span></th>
                            <th><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                            <th class="table-cell-right"><span class="bn">আগে → পরে</span><span class="en" style="display:none;">From → To</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($revisions as $revision)
                            <tr>
                                <td style="white-space:nowrap;">{{ $revision->effective_from->format('d M, Y') }}</td>
                                <td>{{ $revisionTypes[$revision->type] ?? $revision->type }}
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $revision->designation?->name }}{{ $revision->note ? ' · '.$revision->note : '' }}</div>
                                </td>
                                <td class="table-cell-right" style="white-space:nowrap;">{{ number_format((float) $revision->previous_salary, 0) }} → <b>{{ number_format((float) $revision->new_salary, 0) }}</b></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-core::table.empty icon="trending-up" title="কোনো পরিবর্তন নেই" title-en="No changes yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($canEdit)
                <div class="panel-body">
                    <form method="POST" action="{{ route('payroll.salary.revisions.store', $employee) }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        @csrf
                        <x-core::select size="sm" name="type" label="ধরন" label-en="Type" :options="$revisionTypes" value="increment" :required="true" />
                        <x-core::input size="sm" type="date" name="effective_from" label="কার্যকর তারিখ" label-en="Effective From" :value="now()->startOfMonth()->toDateString()" :required="true" />
                        <x-core::input size="sm" type="number" step="0.01" min="0" name="new_salary" label="নতুন মোট বেতন" label-en="New Gross Salary" :value="$gross" :stepper="false" :required="true" />
                        <x-core::select size="sm" name="designation_id" label="নতুন পদবি (ঐচ্ছিক)" label-en="New Designation (optional)" :options="$designations->all()" placeholder="--" placeholder-en="--" />
                        <div style="grid-column:1 / -1;"><x-core::input size="sm" name="note" label="নোট" label-en="Note" /></div>
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save</span></x-core::button></div>
                    </form>
                </div>
            @endif
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">অগ্রিম ও ঋণ</span><span class="en" style="display:none;">Advances & Loans</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                <td>{{ $loan->issued_on->format('d M, Y') }} <div style="font-size:11.5px; color:var(--ink-500);">{{ $loan->type === 'loan' ? 'ঋণ / Loan' : 'অগ্রিম / Advance' }} · {{ number_format((float) $loan->installment, 0) }}/<span class="bn">মাস</span><span class="en" style="display:none;">mo</span></div></td>
                                <td class="table-cell-right">{{ number_format((float) $loan->amount, 2) }}</td>
                                <td class="table-cell-right" style="font-weight:700;">{{ number_format((float) $loan->amount - (float) $loan->recoveries_sum_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-core::table.empty icon="banknote" title="কোনো অগ্রিম নেই" title-en="No advances" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">পে-স্লিপ</span><span class="en" style="display:none;">Payslips</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <tbody>
                        @forelse ($payslips as $payslip)
                            <tr>
                                <td>{{ $payslip->run?->label() }}
                                    @if ($payslip->run?->isDraft())
                                        <x-core::badge color="grey" size="xs" label="খসড়া" label-en="Draft" />
                                    @endif
                                </td>
                                <td class="table-cell-right" style="font-weight:600;">{{ number_format((float) $payslip->net_pay, 2) }}</td>
                                <td class="table-cell-right" style="color:{{ $payslip->due() > 0 ? 'var(--red-600)' : 'var(--green-ink)' }};">{{ $payslip->due() > 0 ? number_format($payslip->due(), 2) : '✓' }}</td>
                                <td class="table-cell-right"><x-core::button as="a" href="{{ route('payroll.payslips.show', $payslip) }}" target="_blank" size="sm" variant="soft" color="secondary" icon="printer" icon-only title="পে-স্লিপ / Payslip" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-core::table.empty icon="receipt" title="কোনো পে-স্লিপ নেই" title-en="No payslips" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layout>
