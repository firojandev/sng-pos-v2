<x-core::layout title="পে-রোল" title-en="Payroll" :subtitle="$run->label().' — '.$run->shop?->name" :subtitle-en="$run->label().' — '.$run->shop?->name" active="payroll">
    <x-payroll::tabbar active="runs" />

    @php
        $status = $run->paymentStatus();
        $statusColors = ['draft' => 'grey', 'unpaid' => 'gold', 'partial' => 'blue', 'paid' => 'green'];
        $statusLabels = ['draft' => 'খসড়া / Draft', 'unpaid' => 'অনুমোদিত, অপরিশোধিত / Approved, unpaid', 'partial' => 'আংশিক পরিশোধিত / Partly paid', 'paid' => 'পরিশোধিত / Paid'];
        $due = round((float) $run->net_total - (float) $run->paid_total, 2);
    @endphp

    <div class="panel" style="margin-top:16px;">
        <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:24px; align-items:center; justify-content:space-between;">
            <div style="display:flex; flex-wrap:wrap; gap:28px;">
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></div><x-core::badge :color="$statusColors[$status]" size="sm">{{ $statusLabels[$status] }}</x-core::badge></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Staff</span></div><div style="font-size:18px; font-weight:700;">{{ $run->employees_count }}</div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">মোট প্রাপ্য</span><span class="en" style="display:none;">Earnings</span></div><div style="font-size:18px; font-weight:700;">{{ number_format((float) $run->earnings_total, 2) }}</div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">কর্তন</span><span class="en" style="display:none;">Deductions</span></div><div style="font-size:18px; font-weight:700;">{{ number_format((float) $run->deductions_total, 2) }}</div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">নিট বেতন</span><span class="en" style="display:none;">Net Pay</span></div><div style="font-size:18px; font-weight:800;">{{ number_format((float) $run->net_total, 2) }}</div></div>
                <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">বাকি</span><span class="en" style="display:none;">Due</span></div><div style="font-size:18px; font-weight:700; color:{{ $due > 0 ? 'var(--red-600)' : 'var(--green-ink)' }};">{{ number_format($due, 2) }}</div></div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <x-core::button as="a" href="{{ route('payroll.runs.print', $run) }}" target="_blank" size="sm" variant="secondary" icon="printer"><span class="bn">সব পে-স্লিপ</span><span class="en" style="display:none;">All Payslips</span></x-core::button>
                @if ($run->isDraft())
                    @can('payroll.edit')
                        <form method="POST" action="{{ route('payroll.runs.recalculate', $run) }}">
                            @csrf
                            <x-core::button type="submit" size="sm" variant="secondary" icon="refresh"><span class="bn">আবার হিসাব</span><span class="en" style="display:none;">Recalculate</span></x-core::button>
                        </form>
                    @endcan
                    @can('payroll.approve')
                        <form method="POST" action="{{ route('payroll.runs.approve', $run) }}" class="delete-form" data-title="পে-রোল অনুমোদন করবেন?" data-text="অনুমোদনের পর হিসাবে পোস্ট হবে এবং অগ্রিমের কিস্তি কাটা হবে।">
                            @csrf
                            <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">অনুমোদন</span><span class="en" style="display:none;">Approve</span></x-core::button>
                        </form>
                    @endcan
                    @can('payroll.delete')
                        <form method="POST" action="{{ route('payroll.runs.destroy', $run) }}" class="delete-form" data-title="পে-রোল মুছে ফেলবেন?">
                            @csrf
                            @method('DELETE')
                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2"><span class="bn">মুছুন</span><span class="en" style="display:none;">Delete</span></x-core::button>
                        </form>
                    @endcan
                @else
                    @can('payroll.approve')
                        @if ((float) $run->paid_total <= 0)
                            <form method="POST" action="{{ route('payroll.runs.reopen', $run) }}" class="delete-form" data-title="পে-রোল আবার খুলবেন?" data-text="হিসাবের এন্ট্রি বাতিল হবে এবং খসড়ায় ফিরবে।">
                                @csrf
                                <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="rotate-ccw"><span class="bn">আবার খুলুন</span><span class="en" style="display:none;">Reopen</span></x-core::button>
                            </form>
                        @endif
                    @endcan
                @endif
            </div>
        </div>
        @if ($run->approved_at)
            <div class="panel-body" style="padding-top:0; font-size:12px; color:var(--ink-500);">
                <span class="bn">অনুমোদন করেছেন</span><span class="en" style="display:none;">Approved by</span> {{ $run->approver?->name }} · {{ $run->approved_at->format('d M, Y h:i A') }}
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-top:12px;">{{ $errors->first() }}</div>
    @endif

    <div class="table-container table-teal" style="margin-top:16px;">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                        @unless ($run->isBonus())
                            <th class="table-cell-center" title="উপস্থিত / অনুপস্থিত / ছুটি / বিলম্ব"><span class="bn">উ / অ / ছু / বি</span><span class="en" style="display:none;">P / A / L / Late</span></th>
                            <th class="table-cell-center"><span class="bn">ওভারটাইম</span><span class="en" style="display:none;">OT</span></th>
                        @endunless
                        <th class="table-cell-right"><span class="bn">প্রাপ্য</span><span class="en" style="display:none;">Earnings</span></th>
                        <th class="table-cell-right"><span class="bn">কর্তন</span><span class="en" style="display:none;">Deductions</span></th>
                        @if ($run->isDraft())
                            <th style="width:110px;"><span class="bn">অতিরিক্ত +</span><span class="en" style="display:none;">Add +</span></th>
                            <th style="width:110px;"><span class="bn">কর্তন −</span><span class="en" style="display:none;">Deduct −</span></th>
                        @endif
                        <th class="table-cell-right"><span class="bn">নিট</span><span class="en" style="display:none;">Net</span></th>
                        @unless ($run->isDraft())
                            <th class="table-cell-right"><span class="bn">বাকি</span><span class="en" style="display:none;">Due</span></th>
                        @endunless
                        <th class="table-cell-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($run->payslips as $payslip)
                        @php $formId = 'payslip-'.$payslip->id; @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $payslip->employee_name }}</div>
                                <div style="font-size:11.5px; color:var(--ink-500);">{{ $payslip->employee_code }} · {{ $payslip->designation }} · {{ number_format((float) $payslip->salary, 0) }}
                                    @if ($payslip->payable_days < $payslip->days_in_month)
                                        · {{ $payslip->payable_days }}/{{ $payslip->days_in_month }} <span class="bn">দিন</span><span class="en" style="display:none;">days</span>
                                    @endif
                                </div>
                                @if ($payslip->adjustment_note)
                                    <div style="font-size:11.5px; color:var(--ink-600);">{{ $payslip->adjustment_note }}</div>
                                @endif
                            </td>
                            @unless ($run->isBonus())
                                <td class="table-cell-center" style="white-space:nowrap; font-size:12.5px;">{{ $payslip->present_days }} / {{ $payslip->absent_days + $payslip->unpaid_leave_days }} / {{ $payslip->paid_leave_days }} / {{ $payslip->late_days }}</td>
                                <td class="table-cell-center" style="font-size:12.5px;">{{ $payslip->overtime_minutes ? number_format($payslip->overtime_minutes / 60, 1).'h' : '—' }}</td>
                            @endunless
                            <td class="table-cell-right" title="{{ $payslip->items->where('type', 'earning')->map(fn ($item) => $item->name.': '.number_format((float) $item->amount, 2))->implode("\n") }}">{{ number_format((float) $payslip->earnings_total, 2) }}</td>
                            <td class="table-cell-right" title="{{ $payslip->items->where('type', 'deduction')->map(fn ($item) => $item->name.': '.number_format((float) $item->amount, 2))->implode("\n") }}">{{ number_format((float) $payslip->deductions_total, 2) }}</td>
                            @if ($run->isDraft())
                                <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" name="other_addition" :form="$formId" :value="(float) $payslip->other_addition ?: ''" :stepper="false" /></td>
                                <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" name="other_deduction" :form="$formId" :value="(float) $payslip->other_deduction ?: ''" :stepper="false" /></td>
                            @endif
                            <td class="table-cell-right" style="font-weight:700;">{{ number_format((float) $payslip->net_pay, 2) }}</td>
                            @unless ($run->isDraft())
                                <td class="table-cell-right" style="color:{{ $payslip->due() > 0 ? 'var(--red-600)' : 'var(--green-ink)' }};">{{ number_format($payslip->due(), 2) }}</td>
                            @endunless
                            <td class="table-cell-right">
                                <div style="display:flex; gap:4px; justify-content:flex-end;">
                                    @if ($run->isDraft())
                                        @can('payroll.edit')
                                            <form method="POST" action="{{ route('payroll.payslips.adjust', $payslip) }}" id="{{ $formId }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="adjustment_note" value="{{ $payslip->adjustment_note }}">
                                                <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" icon-only title="সংরক্ষণ / Save" />
                                            </form>
                                        @endcan
                                    @endif
                                    <x-core::button as="a" href="{{ route('payroll.payslips.show', $payslip) }}" target="_blank" size="sm" variant="soft" color="secondary" icon="printer" icon-only title="পে-স্লিপ / Payslip" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><x-core::table.empty icon="users" title="কোনো পে-স্লিপ নেই" title-en="No payslips" description="এই মাসে বেতনযোগ্য কোনো কর্মচারী পাওয়া যায়নি।" description-en="No employee is due pay for this month." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (! $run->isDraft())
        @can('payroll.payment')
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
                @if ($due > 0)
                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head"><div class="panel-title"><span class="bn">সবার বাকি বেতন পরিশোধ</span><span class="en" style="display:none;">Pay Everyone's Due</span></div></div>
                        <div class="panel-body">
                            <form method="POST" action="{{ route('payroll.runs.pay-all', $run) }}" class="delete-form" data-title="{{ number_format($due, 2) }} টাকা পরিশোধ করবেন?" style="display:flex; flex-direction:column; gap:10px;">
                                @csrf
                                <x-core::select size="sm" name="account_id" label="যে অ্যাকাউন্ট থেকে" label-en="Pay From" :options="$accounts" :required="true" />
                                <x-core::input size="sm" type="date" name="paid_on" label="তারিখ" label-en="Date" :value="now()->toDateString()" :required="true" />
                                <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="wallet"><span class="bn">পরিশোধ করুন</span><span class="en" style="display:none;">Pay</span> {{ number_format($due, 2) }}</x-core::button></div>
                            </form>
                        </div>
                    </div>

                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head"><div class="panel-title"><span class="bn">একজনকে পরিশোধ</span><span class="en" style="display:none;">Pay One Employee</span></div></div>
                        <div class="panel-body">
                            <form method="POST" id="pay-one-form" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                @csrf
                                <div style="grid-column:1 / -1;">
                                    <x-core::select size="sm" name="payslip" id="pay-one-payslip" label="কর্মচারী" label-en="Employee" :required="true" placeholder="--" placeholder-en="--"
                                        :options="$run->payslips->filter(fn ($payslip) => $payslip->due() > 0)->mapWithKeys(fn ($payslip) => [route('payroll.payslips.pay', $payslip) => $payslip->employee_name.' — '.number_format($payslip->due(), 2)])->all()" />
                                </div>
                                <x-core::input size="sm" type="number" step="0.01" min="0.01" name="amount" label="পরিমাণ" label-en="Amount" :stepper="false" :required="true" />
                                <x-core::input size="sm" type="date" name="paid_on" label="তারিখ" label-en="Date" :value="now()->toDateString()" :required="true" />
                                <div style="grid-column:1 / -1;"><x-core::select size="sm" name="account_id" label="যে অ্যাকাউন্ট থেকে" label-en="Pay From" :options="$accounts" :required="true" /></div>
                                <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="wallet"><span class="bn">পরিশোধ</span><span class="en" style="display:none;">Pay</span></x-core::button></div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @endcan

        <div class="panel" style="margin-top:16px;">
            <div class="panel-head"><div class="panel-title"><span class="bn">পেমেন্ট</span><span class="en" style="display:none;">Payments</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                            <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                            <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                            <th class="table-cell-right"><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Amount</span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_on->format('d M, Y') }}</td>
                                <td>{{ $payment->employee?->name }}</td>
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
                            <tr><td colspan="5"><x-core::table.empty icon="wallet" title="এখনো কোনো পেমেন্ট হয়নি" title-en="No payments yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            $(function () {
                $('#pay-one-payslip').on('change', function () {
                    $('#pay-one-form').attr('action', $(this).val());
                    const due = ($(this).find('option:selected').text().split('— ').pop() || '').replace(/,/g, '');
                    $('#pay-one-form [name="amount"]').val(parseFloat(due) || '');
                });
            });
        </script>
    @endpush
</x-core::layout>
