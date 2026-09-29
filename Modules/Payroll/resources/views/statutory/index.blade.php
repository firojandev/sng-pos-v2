<x-core::layout title="পিএফ ও আয়কর জমা" title-en="Statutory Payments" subtitle="বেতন থেকে কাটা প্রভিডেন্ট ফান্ড ও আয়কর জমা দেওয়া" subtitle-en="Paying over the provident fund and income tax withheld from salary" active="payroll">
    <x-payroll::tabbar active="statutory" />

    @php $money = fn ($amount) => number_format((float) $amount, 2); @endphp

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-top:12px;">{{ $errors->first() }}</div>
    @endif

    <div class="panel" style="margin-top:16px;">
        <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:32px;">
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">কর্মচারীর পিএফ প্রদেয়</span><span class="en" style="display:none;">Employee PF Payable</span></div><div style="font-size:18px; font-weight:700;">{{ $money($outstanding['pf_employee']) }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">মালিকের পিএফ প্রদেয়</span><span class="en" style="display:none;">Employer PF Payable</span></div><div style="font-size:18px; font-weight:700;">{{ $money($outstanding['pf_employer']) }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">আয়কর প্রদেয়</span><span class="en" style="display:none;">Income Tax Payable</span></div><div style="font-size:18px; font-weight:700;">{{ $money($outstanding['tax']) }}</div></div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:minmax(300px, 380px) 1fr; gap:16px; align-items:start; margin-top:16px;">
        <div>
            <form method="GET" class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">সময়ে কত কাটা হয়েছে</span><span class="en" style="display:none;">Withheld in a Period</span></div></div>
                <div class="panel-body" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <x-core::input size="sm" type="month" name="from" label="থেকে" label-en="From" :value="$from->format('Y-m')" />
                    <x-core::input size="sm" type="month" name="to" label="পর্যন্ত" label-en="To" :value="$to->format('Y-m')" />
                    <div style="grid-column:1 / -1; font-size:12.5px; color:var(--ink-600);">
                        <span class="bn">পিএফ: কর্মচারী</span><span class="en" style="display:none;">PF: employee</span> {{ $money($withheld['employee']) }} + <span class="bn">মালিক</span><span class="en" style="display:none;">employer</span> {{ $money($withheld['employer']) }} · <span class="bn">আয়কর</span><span class="en" style="display:none;">Tax</span> {{ $money($withheld['tax']) }}
                    </div>
                    <div><x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Show</span></x-core::button></div>
                </div>
            </form>

            @can('payroll.payment')
                @foreach (['pf' => ['প্রভিডেন্ট ফান্ড জমা', 'Provident Fund Payment', 'ফান্ডের নাম / অ্যাকাউন্ট', 'PF Fund / Account'], 'tax' => ['আয়কর জমা', 'Income Tax Payment', 'সরকারি হিসাব (কোড)', 'Government Account (Code)']] as $type => [$titleBn, $titleEn, $payeeBn, $payeeEn])
                    <div class="panel" style="margin-top:16px;">
                        <div class="panel-head"><div class="panel-title"><span class="bn">{{ $titleBn }}</span><span class="en" style="display:none;">{{ $titleEn }}</span></div></div>
                        <div class="panel-body">
                            <form method="POST" action="{{ route('payroll.statutory.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <x-core::input size="sm" type="month" name="period_from" label="সময়: থেকে" label-en="Period From" :value="$from->format('Y-m')" :required="true" />
                                <x-core::input size="sm" type="month" name="period_to" label="পর্যন্ত" label-en="To" :value="$to->format('Y-m')" :required="true" />
                                @if ($type === 'pf')
                                    <x-core::input size="sm" type="number" step="0.01" min="0" name="employee_amount" label="কর্মচারীর অংশ" label-en="Employee PF" placeholder="পে-রোল থেকে" placeholder-en="From payroll" :stepper="false" />
                                    <x-core::input size="sm" type="number" step="0.01" min="0" name="employer_amount" label="মালিকের অংশ" label-en="Employer PF" placeholder="পে-রোল থেকে" placeholder-en="From payroll" :stepper="false" />
                                @else
                                    <div style="grid-column:1 / -1;"><x-core::input size="sm" type="number" step="0.01" min="0" name="amount" label="পরিমাণ" label-en="Amount" placeholder="পে-রোল থেকে" placeholder-en="From payroll" :stepper="false" /></div>
                                @endif
                                <div style="grid-column:1 / -1;"><x-core::input size="sm" name="payee" :label="$payeeBn" :label-en="$payeeEn" :placeholder="$type === 'tax' ? '1-1141-0020-0111' : ''" /></div>
                                <div style="grid-column:1 / -1;"><x-core::input size="sm" name="note" label="নোট" label-en="Note" /></div>
                                <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">যোগ করুন</span><span class="en" style="display:none;">Add</span></x-core::button></div>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endcan
        </div>

        <div class="table-container table-teal">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">ধরন / সময়</span><span class="en" style="display:none;">Type / Period</span></th>
                            <th class="table-cell-right"><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                            <th class="table-cell-right"><span class="bn">মালিক</span><span class="en" style="display:none;">Employer</span></th>
                            <th class="table-cell-right"><span class="bn">মোট</span><span class="en" style="display:none;">Total</span></th>
                            <th><span class="bn">পরিশোধ</span><span class="en" style="display:none;">Payment</span></th>
                            <th class="table-cell-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            @php $payForm = 'statutory-pay-'.$payment->id; @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $payment->type === 'pf' ? 'পিএফ / PF' : 'আয়কর / Income Tax' }}</div>
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $payment->period_from->format('M Y') }} – {{ $payment->period_to->format('M Y') }}{{ $payment->payee ? ' · '.$payment->payee : '' }}</div>
                                </td>
                                <td class="table-cell-right">{{ $payment->type === 'pf' ? $money($payment->employee_amount) : '—' }}</td>
                                <td class="table-cell-right">{{ $payment->type === 'pf' ? $money($payment->employer_amount) : '—' }}</td>
                                <td class="table-cell-right" style="font-weight:700;">{{ $money($payment->amount) }}</td>
                                <td style="font-size:12.5px;">
                                    @if ($payment->isPaid())
                                        <x-core::badge color="green" size="xs">পরিশোধিত / Paid</x-core::badge>
                                        <div style="color:var(--ink-500);">{{ $payment->payment_date?->format('d M, Y') }} · {{ $payment->account?->name }}{{ $payment->reference ? ' · '.$payment->reference : '' }}</div>
                                    @else
                                        <x-core::badge color="gold" size="xs">অপেক্ষমাণ / Pending</x-core::badge>
                                        @can('payroll.payment')
                                            <div style="display:flex; gap:4px; margin-top:6px; align-items:center;">
                                                <div style="width:150px;"><x-core::select size="sm" :no-margin="true" name="account_id" :form="$payForm" :options="$accounts" /></div>
                                                <div style="width:130px;"><x-core::input size="sm" :no-margin="true" type="date" name="payment_date" :form="$payForm" :value="now()->toDateString()" /></div>
                                                <div style="width:130px;"><x-core::input size="sm" :no-margin="true" name="reference" :form="$payForm" placeholder="চালান / রেফারেন্স" placeholder-en="Challan / Ref" /></div>
                                                <form method="POST" action="{{ route('payroll.statutory.pay', $payment) }}" id="{{ $payForm }}">
                                                    @csrf
                                                    <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="wallet" icon-only title="পরিশোধ / Pay" />
                                                </form>
                                            </div>
                                        @endcan
                                    @endif
                                </td>
                                <td class="table-cell-right">
                                    @can('payroll.payment')
                                        <form method="POST" action="{{ route('payroll.statutory.destroy', $payment) }}" class="delete-form" data-title="মুছে ফেলবেন?" data-text="পরিশোধিত হলে টাকা অ্যাকাউন্টে ফেরত যাবে।">
                                            @csrf
                                            @method('DELETE')
                                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-core::table.empty icon="landmark" title="কোনো জমা নেই" title-en="No payments yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="margin-top:12px;">{{ $payments->links() }}</div>
</x-core::layout>
