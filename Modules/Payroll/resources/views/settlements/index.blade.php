<x-core::layout title="চূড়ান্ত নিষ্পত্তি" title-en="Final Settlement" subtitle="চাকরি ছাড়ার পাওনা: সুবিধা, ছুটি নগদায়ন, পিএফ, অগ্রিম সমন্বয়" subtitle-en="Dues on leaving: benefits, leave encashment, PF, advances" active="payroll">
    <x-payroll::tabbar active="settlements" />

    @php $labels = \Modules\Payroll\Models\FinalSettlement::separationLabels(); @endphp

    <div style="display:grid; grid-template-columns:minmax(280px, 340px) 1fr; gap:16px; align-items:start; margin-top:16px;">
        @can('payroll.create')
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">নতুন নিষ্পত্তি</span><span class="en" style="display:none;">New Settlement</span></div></div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('payroll.settlements.store') }}" style="display:flex; flex-direction:column; gap:12px;">
                        @csrf
                        <x-core::select size="sm" name="employee_id" label="কর্মচারী" label-en="Employee" :required="true" :value="old('employee_id')" placeholder="--" placeholder-en="--"
                            :options="$employees->mapWithKeys(fn ($employee) => [$employee->id => $employee->name.' ('.$employee->employee_code.')'])->all()" />
                        <x-core::select size="sm" name="separation_type" label="কারণ" label-en="Reason" :required="true" :value="old('separation_type', 'resignation')"
                            :options="collect($labels)->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all()" />
                        <x-core::input size="sm" type="date" name="separation_date" label="শেষ কর্মদিবস" label-en="Last Working Day" :value="old('separation_date', now()->toDateString())" :required="true" />
                        <x-core::input size="sm" name="note" label="নোট" label-en="Note" :value="old('note')" />
                        <p style="font-size:12px; color:var(--ink-500); margin:0;">
                            <span class="bn">শ্রম আইন ২০০৬ অনুযায়ী প্রস্তাবিত অঙ্ক আসবে; চূড়ান্ত করার আগে সব অঙ্ক পরিবর্তন করা যাবে। শেষ মাসের বেতন পে-রোলে দিন।</span>
                            <span class="en" style="display:none;">Amounts are suggested from the Labour Act 2006 and can all be changed before finalizing. Pay the last month's salary through payroll.</span>
                        </p>
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">হিসাব করুন</span><span class="en" style="display:none;">Calculate</span></x-core::button></div>
                    </form>
                </div>
            </div>
        @endcan

        <div class="table-container table-teal">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                            <th><span class="bn">কারণ</span><span class="en" style="display:none;">Reason</span></th>
                            <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                            <th class="table-cell-right"><span class="bn">নিট প্রদেয়</span><span class="en" style="display:none;">Net Payable</span></th>
                            <th class="table-cell-right"><span class="bn">পরিশোধিত</span><span class="en" style="display:none;">Paid</span></th>
                            <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settlements as $settlement)
                            <tr>
                                <td style="font-weight:600;">{{ $settlement->employee?->name }} <div style="font-size:11.5px; color:var(--ink-500); font-weight:400;">{{ $settlement->employee?->employee_code }}</div></td>
                                <td>{{ $labels[$settlement->separation_type]['bn'] ?? $settlement->separation_type }}</td>
                                <td style="white-space:nowrap;">{{ $settlement->separation_date->format('d M, Y') }}</td>
                                <td class="table-cell-right" style="font-weight:700;">{{ number_format((float) $settlement->net_pay, 2) }}</td>
                                <td class="table-cell-right">{{ number_format((float) $settlement->paid_amount, 2) }}</td>
                                <td class="table-cell-center">
                                    @if ($settlement->isDraft())
                                        <x-core::badge color="grey" size="xs">খসড়া / Draft</x-core::badge>
                                    @elseif ($settlement->due() > 0)
                                        <x-core::badge color="gold" size="xs">চূড়ান্ত, বাকি / Final, due</x-core::badge>
                                    @else
                                        <x-core::badge color="green" size="xs">পরিশোধিত / Paid</x-core::badge>
                                    @endif
                                </td>
                                <td class="table-cell-right"><x-core::button as="a" href="{{ route('payroll.settlements.show', $settlement) }}" size="sm" variant="soft" color="primary" icon="eye" icon-only title="দেখুন / Open" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-core::table.empty icon="briefcase" title="কোনো নিষ্পত্তি নেই" title-en="No settlements" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="margin-top:12px;">{{ $settlements->links() }}</div>
</x-core::layout>
