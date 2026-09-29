<x-core::layout title="চূড়ান্ত নিষ্পত্তি" title-en="Final Settlement" :subtitle="$settlement->employee?->name.' — '.$settlement->employee?->employee_code" :subtitle-en="$settlement->employee?->name.' — '.$settlement->employee?->employee_code" active="payroll">
    <x-payroll::tabbar active="settlements" />

    @php
        $labels = \Modules\Payroll\Models\FinalSettlement::separationLabels();
        $years = intdiv($settlement->service_days, 365);
        $months = intdiv($settlement->service_days % 365, 30);
    @endphp

    <div class="panel" style="margin-top:16px;">
        <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:28px; align-items:center; justify-content:space-between;">
            <div style="display:flex; flex-wrap:wrap; gap:28px;">
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">কারণ</span><span class="en" style="display:none;">Reason</span></div><div style="font-weight:700;">{{ $labels[$settlement->separation_type]['bn'] }} <span style="color:var(--ink-500); font-weight:400;">({{ $labels[$settlement->separation_type]['en'] }})</span></div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">শেষ কর্মদিবস</span><span class="en" style="display:none;">Last Day</span></div><div style="font-weight:700;">{{ $settlement->separation_date->format('d M, Y') }}</div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">চাকরিকাল</span><span class="en" style="display:none;">Service</span></div><div style="font-weight:700;">{{ $years }} <span class="bn">বছর</span><span class="en" style="display:none;">yrs</span> {{ $months }} <span class="bn">মাস</span><span class="en" style="display:none;">mo</span></div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">শেষ বেতন / মূল</span><span class="en" style="display:none;">Last Gross / Basic</span></div><div style="font-weight:700;">{{ number_format((float) $settlement->last_salary, 0) }} / {{ number_format((float) $settlement->last_basic, 0) }}</div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">নিট প্রদেয়</span><span class="en" style="display:none;">Net Payable</span></div><div style="font-size:18px; font-weight:800;">{{ number_format((float) $settlement->net_pay, 2) }}</div></div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <x-core::button type="button" size="sm" variant="secondary" icon="printer" onclick="window.print()"><span class="bn">প্রিন্ট</span><span class="en" style="display:none;">Print</span></x-core::button>
                @if ($settlement->isDraft())
                    @can('payroll.approve')
                        <form method="POST" action="{{ route('payroll.settlements.finalize', $settlement) }}" class="delete-form" data-title="নিষ্পত্তি চূড়ান্ত করবেন?" data-text="কর্মচারীর অবস্থা বদলাবে, অগ্রিম সমন্বয় হবে এবং হিসাবে পোস্ট হবে।">
                            @csrf
                            <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">চূড়ান্ত করুন</span><span class="en" style="display:none;">Finalize</span></x-core::button>
                        </form>
                    @endcan
                    @can('payroll.delete')
                        <form method="POST" action="{{ route('payroll.settlements.destroy', $settlement) }}" class="delete-form" data-title="খসড়া মুছে ফেলবেন?">
                            @csrf
                            @method('DELETE')
                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2"><span class="bn">মুছুন</span><span class="en" style="display:none;">Delete</span></x-core::button>
                        </form>
                    @endcan
                @elseif ((float) $settlement->paid_amount <= 0)
                    @can('payroll.approve')
                        <form method="POST" action="{{ route('payroll.settlements.reopen', $settlement) }}" class="delete-form" data-title="আবার খসড়া করবেন?" data-text="হিসাবের এন্ট্রি বাতিল হবে।">
                            @csrf
                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="rotate-ccw"><span class="bn">আবার খুলুন</span><span class="en" style="display:none;">Reopen</span></x-core::button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-top:12px;">{{ $errors->first() }}</div>
    @endif

    @if ($unpaidPayslips->isNotEmpty())
        <div class="panel" style="margin-top:16px; background:var(--gold-100);">
            <div class="panel-body" style="color:var(--gold-ink); font-size:13px;">
                <span class="bn">এই কর্মচারীর অপরিশোধিত বেতন আছে:</span><span class="en" style="display:none;">This employee has unpaid salary:</span>
                @foreach ($unpaidPayslips as $payslip)
                    <a href="{{ route('payroll.runs.show', $payslip->run) }}" style="color:inherit; font-weight:700;">{{ $payslip->run?->label() }} ({{ number_format($payslip->due(), 2) }})</a>{{ $loop->last ? '' : ',' }}
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('payroll.settlements.update', $settlement) }}">
        @csrf
        @method('PUT')
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
            @foreach (['earning' => ['প্রাপ্য', 'Payable'], 'deduction' => ['কর্তন', 'Deductions']] as $type => [$bn, $en])
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head"><div class="panel-title"><span class="bn">{{ $bn }}</span><span class="en" style="display:none;">{{ $en }}</span></div></div>
                    <div class="table-responsive">
                        <table class="app-table">
                            <tbody>
                                @forelse ($settlement->items->where('type', $type) as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="table-cell-right" style="width:150px;">
                                            @if ($settlement->isDraft())
                                                <x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" :name="'amounts['.$item->id.']'" :value="(float) $item->amount" :stepper="false" />
                                            @else
                                                <b>{{ number_format((float) $item->amount, 2) }}</b>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2"><x-core::table.empty icon="list" title="কিছু নেই" title-en="Nothing" /></td></tr>
                                @endforelse
                                <tr>
                                    <td style="font-weight:700;"><span class="bn">মোট</span><span class="en" style="display:none;">Total</span></td>
                                    <td class="table-cell-right" style="font-weight:700;">{{ number_format((float) ($type === 'earning' ? $settlement->earnings_total : $settlement->deductions_total), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        @if ((float) $settlement->pf_forfeited > 0)
            <p style="font-size:12.5px; color:var(--ink-600); margin-top:10px;">
                <span class="bn">মালিকের পিএফ অংশ ({{ number_format((float) $settlement->pf_forfeited, 2) }}) প্রাপ্য নয় (চাকরিকাল কম বা বরখাস্ত); এটি কোম্পানিতে ফিরবে।</span>
                <span class="en" style="display:none;">The employer's PF share ({{ number_format((float) $settlement->pf_forfeited, 2) }}) isn't vested (short service or dismissal); it returns to the company.</span>
            </p>
        @endif

        @if ($settlement->isDraft())
            @can('payroll.edit')
                <div style="display:flex; gap:8px; align-items:flex-end; margin-top:12px;">
                    <div style="width:360px;"><x-core::input size="sm" name="note" label="নোট" label-en="Note" :value="$settlement->note" /></div>
                    <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save</span></x-core::button>
                </div>
            @endcan
        @endif
    </form>

    @unless ($settlement->isDraft())
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
            @if ($settlement->due() > 0)
                @can('payroll.payment')
                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head"><div class="panel-title"><span class="bn">পরিশোধ</span><span class="en" style="display:none;">Pay</span></div></div>
                        <div class="panel-body">
                            <form method="POST" action="{{ route('payroll.settlements.pay', $settlement) }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                @csrf
                                <x-core::input size="sm" type="number" step="0.01" min="0.01" name="amount" label="পরিমাণ" label-en="Amount" :value="$settlement->due()" :stepper="false" :required="true" />
                                <x-core::input size="sm" type="date" name="paid_on" label="তারিখ" label-en="Date" :value="now()->toDateString()" :required="true" />
                                <div style="grid-column:1 / -1;"><x-core::select size="sm" name="account_id" label="যে অ্যাকাউন্ট থেকে" label-en="Pay From" :options="$accounts" :required="true" /></div>
                                <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="wallet"><span class="bn">পরিশোধ করুন</span><span class="en" style="display:none;">Pay</span></x-core::button></div>
                            </form>
                        </div>
                    </div>
                @endcan
            @endif

            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">পেমেন্ট</span><span class="en" style="display:none;">Payments</span></div></div>
                <div class="table-responsive">
                    <table class="app-table">
                        <tbody>
                            @forelse ($settlement->payments as $payment)
                                <tr>
                                    <td>{{ $payment->paid_on->format('d M, Y') }}</td>
                                    <td>{{ $payment->account?->name }}</td>
                                    <td class="table-cell-right" style="font-weight:600;">{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td class="table-cell-right">
                                        @can('payroll.payment')
                                            <form method="POST" action="{{ route('payroll.payments.destroy', $payment) }}" class="delete-form" data-title="পেমেন্ট মুছে ফেলবেন?" data-text="টাকা অ্যাকাউন্টে ফেরত যাবে।">
                                                @csrf
                                                @method('DELETE')
                                                <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><x-core::table.empty icon="wallet" title="এখনো কোনো পেমেন্ট হয়নি" title-en="No payments yet" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endunless
</x-core::layout>
