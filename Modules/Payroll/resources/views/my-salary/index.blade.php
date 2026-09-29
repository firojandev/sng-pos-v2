<x-core::layout title="আমার বেতন" title-en="My Salary" subtitle="বেতনের বিবরণ, পে-স্লিপ ও পরিশোধের ইতিহাস" subtitle-en="Salary details, payslips and payment history" active="my-salary">
    @php
        $money = fn ($amount) => '৳'.number_format((float) $amount, 2);
        $paymentStatus = [
            'paid' => ['green', 'পরিশোধিত', 'Paid'],
            'partial' => ['gold', 'আংশিক', 'Partly paid'],
            'unpaid' => ['red', 'বাকি', 'Unpaid'],
        ];
        $historyTypes = [
            'joining' => ['teal', 'যোগদান', 'Joined'],
            'promotion' => ['green', 'পদোন্নতি', 'Promotion'],
            'increment' => ['blue', 'ইনক্রিমেন্ট', 'Increment'],
            'adjustment' => ['grey', 'সমন্বয়', 'Adjustment'],
            'decrement' => ['red', 'হ্রাস', 'Decrement'],
        ];
    @endphp

    <div style="display:grid; grid-template-columns:minmax(300px, 380px) 1fr; gap:16px; align-items:start;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="panel-title"><span class="bn">বর্তমান বেতন</span><span class="en" style="display:none;">Current Salary</span></div>
                <div style="font-size:12px; color:var(--ink-500);">{{ $employee->name }} · {{ $employee->employee_code }}</div>
            </div>
            <div class="panel-body">
                <div style="font-size:28px; font-weight:800; color:var(--ink-900);">{{ $money($gross) }}
                    <span style="font-size:12px; font-weight:500; color:var(--ink-500);"><span class="bn">/ মাস (মোট)</span><span class="en" style="display:none;">/ month (gross)</span></span>
                </div>
                <div style="font-size:12.5px; color:var(--ink-600); margin:4px 0 12px;">
                    {{ $employee->designation ?: '—' }}
                    @if ($employee->joining_date)
                        · <span class="bn">যোগদান</span><span class="en" style="display:none;">Joined</span> {{ $employee->joining_date->format('d M, Y') }}
                    @endif
                </div>
                <table class="app-table">
                    <tbody>
                        @forelse ($breakdown['lines'] as $line)
                            <tr>
                                <td>{{ $line['name'] }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); color:{{ $line['type'] === 'deduction' ? 'var(--red-600)' : 'var(--ink-900)' }};">
                                    {{ $line['type'] === 'deduction' ? '−' : '' }}{{ $money($line['amount']) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><x-core::table.empty icon="wallet" title="বেতনের কাঠামো নির্ধারিত হয়নি" title-en="No salary structure yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">পে-স্লিপ</span><span class="en" style="display:none;">Payslips</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">মাস</span><span class="en" style="display:none;">Month</span></th>
                            <th class="table-cell-right"><span class="bn">মোট আয়</span><span class="en" style="display:none;">Earnings</span></th>
                            <th class="table-cell-right"><span class="bn">কর্তন</span><span class="en" style="display:none;">Deductions</span></th>
                            <th class="table-cell-right"><span class="bn">নিট বেতন</span><span class="en" style="display:none;">Net Pay</span></th>
                            <th class="table-cell-right"><span class="bn">পরিশোধিত</span><span class="en" style="display:none;">Paid</span></th>
                            <th><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                            <th class="table-cell-right"><span class="bn">পে-স্লিপ</span><span class="en" style="display:none;">Payslip</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payslips as $payslip)
                            @php
                                $status = $payslip->due() <= 0.005 ? 'paid' : ((float) $payslip->paid_amount > 0 ? 'partial' : 'unpaid');
                            @endphp
                            <tr>
                                <td style="font-weight:600; color:var(--ink-900);">{{ $payslip->run?->label() }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $money($payslip->earnings_total) }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $money($payslip->deductions_total) }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ $money($payslip->net_pay) }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $money($payslip->paid_amount) }}</td>
                                <td><x-core::badge :color="$paymentStatus[$status][0]" size="xs" :label="$paymentStatus[$status][1]" :label-en="$paymentStatus[$status][2]" /></td>
                                <td class="table-cell-right">
                                    <x-core::button :href="route('my.salary.payslip', $payslip->id)" target="_blank" size="sm" variant="soft" color="primary" icon="download" title="ডাউনলোড / প্রিন্ট (Download / Print)">
                                        <span class="bn">ডাউনলোড</span><span class="en" style="display:none;">Download</span>
                                    </x-core::button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-core::table.empty icon="file-text" title="এখনো কোনো পে-স্লিপ নেই" title-en="No payslips yet" description="বেতন অনুমোদনের পর এখানে দেখা যাবে।" description-en="They appear here once payroll is approved." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">পরিশোধের ইতিহাস</span><span class="en" style="display:none;">Payment History</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                            <th><span class="bn">কোন মাসের</span><span class="en" style="display:none;">For</span></th>
                            <th><span class="bn">মাধ্যম</span><span class="en" style="display:none;">Paid From</span></th>
                            <th class="table-cell-right"><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Amount</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_on?->format('d M, Y') }}</td>
                                <td>{{ $payment->payslip->run?->label() }}</td>
                                <td>{{ $payment->account?->name ?? '—' }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:600;">{{ $money($payment->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-core::table.empty icon="wallet" title="এখনো কোনো পরিশোধ নেই" title-en="No payments yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="panel" style="margin-top:16px;">
        <div class="panel-head"><div class="panel-title"><span class="bn">চাকরির ইতিহাস</span><span class="en" style="display:none;">Employment History</span></div></div>
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                        <th><span class="bn">পদবি</span><span class="en" style="display:none;">Position / Designation</span></th>
                        <th class="table-cell-right"><span class="bn">বেতন</span><span class="en" style="display:none;">Salary</span></th>
                        <th><span class="bn">ধরন</span><span class="en" style="display:none;">Change</span></th>
                        <th><span class="bn">মন্তব্য</span><span class="en" style="display:none;">Note</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $row)
                        @php $type = $historyTypes[$row['type']] ?? ['grey', $row['type'], \Illuminate\Support\Str::headline($row['type'])]; @endphp
                        <tr @if ($row['is_current']) style="background:var(--paper);" @endif>
                            <td>{{ $row['date']->format('d/m/Y') }}</td>
                            <td style="font-weight:600; color:var(--ink-900);">
                                {{ $row['designation'] ?? '—' }}
                                @if ($row['is_current'])
                                    <x-core::badge color="green" size="xs" label="বর্তমান" label-en="Current" />
                                @elseif ($row['is_upcoming'])
                                    <x-core::badge color="gold" size="xs" label="আসন্ন" label-en="Upcoming" />
                                @endif
                            </td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:600;">{{ number_format($row['salary'], 0) }}/=</td>
                            <td><x-core::badge :color="$type[0]" size="xs" :label="$type[1]" :label-en="$type[2]" /></td>
                            <td style="color:var(--ink-600);">{{ $row['note'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
