<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পে-স্লিপ (Payslip) — {{ $run->label() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 10mm; }
        body { font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Hind Siliguri', 'Plus Jakarta Sans', 'Inter', sans-serif; color: #0f172a; background: #f1f5f9; padding: 20px; font-size: 12.5px; line-height: 1.45; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-print { max-width: 780px; margin: 0 auto 16px; display: flex; justify-content: flex-end; gap: 10px; }
        .btn { display: inline-flex; align-items: center; padding: 9px 18px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; border: none; font-family: inherit; text-decoration: none; }
        .btn-primary { background: #0d9488; color: #ffffff; }
        .btn-secondary { background: #ffffff; color: #334155; border: 1px solid #cbd5e1; }
        .slip { max-width: 780px; margin: 0 auto 18px; background: #ffffff; padding: 24px 28px; border-radius: 8px; border: 1px solid #e2e8f0; page-break-after: always; }
        .slip:last-child { page-break-after: auto; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0d9488; padding-bottom: 12px; margin-bottom: 14px; }
        .head h1 { font-size: 20px; font-weight: 800; }
        .head .title { text-align: right; }
        .head .title h2 { font-size: 16px; font-weight: 700; color: #0d9488; }
        .muted { color: #64748b; font-size: 11.5px; }
        .info { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; margin-bottom: 14px; }
        .info div span { color: #64748b; }
        .days { display: grid; grid-template-columns: repeat(6, 1fr); border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 14px; }
        .days div { padding: 6px 8px; text-align: center; border-right: 1px solid #e2e8f0; }
        .days div:last-child { border-right: none; }
        .days b { display: block; font-size: 14px; }
        .cols { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8fafc; font-size: 12px; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
        td.amt, th.amt { text-align: right; white-space: nowrap; }
        tr.total td { font-weight: 700; border-top: 1px solid #cbd5e1; }
        .net { margin-top: 14px; display: flex; justify-content: space-between; align-items: center; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 6px; padding: 10px 14px; font-size: 15px; font-weight: 800; }
        .sign { display: flex; justify-content: space-between; margin-top: 46px; }
        .sign div { border-top: 1px solid #94a3b8; padding-top: 4px; width: 180px; text-align: center; color: #64748b; font-size: 11.5px; }
        @media print { body { background: #ffffff; padding: 0; } .no-print { display: none; } .slip { border: none; padding: 0; margin: 0; max-width: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn btn-secondary" onclick="window.close()">বন্ধ করুন (Close)</button>
        <button type="button" class="btn btn-primary" onclick="window.print()">প্রিন্ট করুন (Print)</button>
    </div>

    @foreach ($payslips as $payslip)
        @php
            $earnings = $payslip->items->where('type', 'earning');
            $deductions = $payslip->items->where('type', 'deduction');
        @endphp
        <div class="slip">
            <div class="head">
                <div>
                    <h1>{{ $shop?->name }}</h1>
                    <div class="muted">{{ $shop?->address }}</div>
                </div>
                <div class="title">
                    <h2>{{ $run->isBonus() ? 'উৎসব ভাতা স্লিপ (Bonus Slip)' : 'পে-স্লিপ (Payslip)' }}</h2>
                    <div class="muted">{{ $run->label() }}</div>
                </div>
            </div>

            <div class="info">
                <div><span>নাম (Name):</span> <b>{{ $payslip->employee_name }}</b></div>
                <div><span>কোড (Code):</span> {{ $payslip->employee_code }}</div>
                <div><span>পদবি (Designation):</span> {{ $payslip->designation ?: '—' }}</div>
                <div><span>মোট বেতন (Gross):</span> {{ number_format((float) $payslip->salary, 2) }}</div>
                <div><span>ব্যাংক / মোবাইল (Bank / MFS):</span> {{ $payslip->employee?->bank_account_no ?: ($payslip->employee?->mfs_number ?: '—') }}</div>
                <div><span>মূল বেতন (Basic):</span> {{ number_format((float) $payslip->basic, 2) }}</div>
            </div>

            @unless ($run->isBonus())
                <div class="days">
                    <div><span class="muted">বেতনযোগ্য দিন<br>Payable Days</span><b>{{ $payslip->payable_days }}/{{ $payslip->days_in_month }}</b></div>
                    <div><span class="muted">উপস্থিত<br>Present</span><b>{{ $payslip->present_days }}</b></div>
                    <div><span class="muted">অনুপস্থিত<br>Absent</span><b>{{ $payslip->absent_days }}</b></div>
                    <div><span class="muted">ছুটি<br>Leave</span><b>{{ $payslip->paid_leave_days }}{{ $payslip->unpaid_leave_days ? ' + '.$payslip->unpaid_leave_days : '' }}</b></div>
                    <div><span class="muted">বিলম্ব<br>Late</span><b>{{ $payslip->late_days }}</b></div>
                    <div><span class="muted">ওভারটাইম<br>Overtime</span><b>{{ number_format($payslip->overtime_minutes / 60, 1) }}h</b></div>
                </div>
            @endunless

            <div class="cols">
                <table>
                    <thead><tr><th>প্রাপ্য (Earnings)</th><th class="amt">৳</th></tr></thead>
                    <tbody>
                        @foreach ($earnings as $item)
                            <tr><td>{{ $item->name }}</td><td class="amt">{{ number_format((float) $item->amount, 2) }}</td></tr>
                        @endforeach
                        <tr class="total"><td>মোট (Total)</td><td class="amt">{{ number_format((float) $payslip->earnings_total, 2) }}</td></tr>
                    </tbody>
                </table>
                <table>
                    <thead><tr><th>কর্তন (Deductions)</th><th class="amt">৳</th></tr></thead>
                    <tbody>
                        @forelse ($deductions as $item)
                            <tr><td>{{ $item->name }}</td><td class="amt">{{ number_format((float) $item->amount, 2) }}</td></tr>
                        @empty
                            <tr><td class="muted">—</td><td class="amt">0.00</td></tr>
                        @endforelse
                        <tr class="total"><td>মোট (Total)</td><td class="amt">{{ number_format((float) $payslip->deductions_total, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="net">
                <span>নিট প্রদেয় (Net Pay)</span>
                <span>৳ {{ number_format((float) $payslip->net_pay, 2) }}</span>
            </div>

            @if ($payslip->payments->isNotEmpty())
                <div class="muted" style="margin-top:8px;">
                    পরিশোধ (Paid):
                    @foreach ($payslip->payments as $payment)
                        {{ $payment->paid_on->format('d M, Y') }} — {{ number_format((float) $payment->amount, 2) }} ({{ $payment->account?->name }}){{ $loop->last ? '' : ';' }}
                    @endforeach
                </div>
            @endif
            @if ((float) $payslip->pf_employer > 0)
                <div class="muted" style="margin-top:4px;">মালিকের পিএফ অবদান (Employer PF contribution): {{ number_format((float) $payslip->pf_employer, 2) }}</div>
            @endif

            <div class="sign">
                <div>কর্মচারীর স্বাক্ষর (Employee)</div>
                <div>কর্তৃপক্ষের স্বাক্ষর (Authorised)</div>
            </div>
        </div>
    @endforeach
</body>
</html>
