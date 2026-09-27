<x-core::layout title="অগ্রিম ও ঋণ" title-en="Advances & Loans" subtitle="কর্মচারীকে দেওয়া অগ্রিম ও ঋণ, বেতন থেকে কিস্তিতে আদায়" subtitle-en="Advances and loans to staff, recovered from salary in installments" active="payroll">
    <x-payroll::tabbar active="loans" />

    <div style="display:grid; grid-template-columns:minmax(280px, 360px) 1fr; gap:16px; align-items:start; margin-top:16px;">
        @can('payroll.create')
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">অগ্রিম / ঋণ দিন</span><span class="en" style="display:none;">Give an Advance / Loan</span></div></div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('payroll.loans.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        @csrf
                        <div style="grid-column:1 / -1;">
                            <x-core::select size="sm" name="employee_id" label="কর্মচারী" label-en="Employee" :required="true" :value="old('employee_id')" placeholder="--" placeholder-en="--"
                                :options="$employees->mapWithKeys(fn ($employee) => [$employee->id => $employee->name.' ('.$employee->employee_code.')'])->all()" />
                        </div>
                        <x-core::select size="sm" name="type" label="ধরন" label-en="Type" :required="true" :value="old('type', 'advance')" :options="['advance' => 'বেতন অগ্রিম (Advance)', 'loan' => 'ঋণ (Loan)']" />
                        <x-core::input size="sm" type="date" name="issued_on" label="তারিখ" label-en="Date" :value="old('issued_on', now()->toDateString())" :required="true" />
                        <x-core::input size="sm" type="number" step="0.01" min="1" name="amount" label="পরিমাণ" label-en="Amount" :value="old('amount')" :stepper="false" :required="true" />
                        <x-core::input size="sm" type="number" step="0.01" min="1" name="installment" label="মাসিক কিস্তি" label-en="Monthly Installment" :value="old('installment')" :stepper="false" :required="true" />
                        <x-core::input size="sm" type="month" name="deduct_from" label="কিস্তি শুরু (মাস)" label-en="Deduct From (Month)" :value="old('deduct_from', now()->format('Y-m'))" :required="true" />
                        <x-core::select size="sm" name="account_id" label="যে অ্যাকাউন্ট থেকে" label-en="Paid From" :options="$accounts" :value="old('account_id')" :required="true" />
                        <div style="grid-column:1 / -1;"><x-core::input size="sm" name="note" label="নোট" label-en="Note" :value="old('note')" /></div>
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">দিন</span><span class="en" style="display:none;">Give</span></x-core::button></div>
                    </form>
                </div>
            </div>
        @endcan

        <div>
            <form method="GET" style="display:flex; gap:8px; align-items:center; flex-wrap:nowrap; margin-bottom:12px;">
                <div style="width:200px; flex-shrink:0;"><x-core::select size="sm" :no-margin="true" name="status" :value="request('status', 'active')"
                    :options="['active' => 'চলমান (Running)', 'closed' => 'পরিশোধিত (Repaid)', 'all' => 'সব (All)']" /></div>
                <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Filter</span></x-core::button>
            </form>
            @if ($errors->any())
                <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $errors->first() }}</div>
            @endif
            <div class="table-container table-teal">
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                                <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                                <th class="table-cell-right"><span class="bn">পরিমাণ</span><span class="en" style="display:none;">Amount</span></th>
                                <th class="table-cell-right"><span class="bn">কিস্তি</span><span class="en" style="display:none;">Installment</span></th>
                                <th class="table-cell-right"><span class="bn">আদায়</span><span class="en" style="display:none;">Recovered</span></th>
                                <th class="table-cell-right"><span class="bn">বাকি</span><span class="en" style="display:none;">Balance</span></th>
                                <th class="table-cell-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loans as $loan)
                                @php $balance = round((float) $loan->amount - (float) $loan->recoveries_sum_amount, 2); @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:600;">{{ $loan->employee?->name }}</div>
                                        <div style="font-size:11.5px; color:var(--ink-500);">{{ $loan->type === 'loan' ? 'ঋণ / Loan' : 'অগ্রিম / Advance' }} · {{ $loan->account?->name }}{{ $loan->note ? ' · '.$loan->note : '' }}</div>
                                    </td>
                                    <td style="white-space:nowrap;">{{ $loan->issued_on->format('d M, Y') }}<div style="font-size:11.5px; color:var(--ink-500);"><span class="bn">কিস্তি শুরু</span><span class="en" style="display:none;">from</span> {{ $loan->deduct_from->format('M Y') }}</div></td>
                                    <td class="table-cell-right">{{ number_format((float) $loan->amount, 2) }}</td>
                                    <td class="table-cell-right">{{ number_format((float) $loan->installment, 2) }}</td>
                                    <td class="table-cell-right">{{ number_format((float) $loan->recoveries_sum_amount, 2) }}</td>
                                    <td class="table-cell-right" style="font-weight:700; color:{{ $balance > 0 ? 'var(--red-600)' : 'var(--green-ink)' }};">{{ number_format($balance, 2) }}</td>
                                    <td class="table-cell-right">
                                        <div style="display:flex; gap:4px; justify-content:flex-end; align-items:center;">
                                            @if ($balance > 0)
                                                @can('payroll.payment')
                                                    @php $repayForm = 'repay-'.$loan->id; @endphp
                                                    <div style="width:100px;"><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0.01" name="amount" :form="$repayForm" :value="$balance" :stepper="false" /></div>
                                                    <div style="width:150px;"><x-core::select size="sm" :no-margin="true" name="account_id" :form="$repayForm" :options="$accounts" /></div>
                                                    <form method="POST" action="{{ route('payroll.loans.repay', $loan) }}" id="{{ $repayForm }}">
                                                        @csrf
                                                        <input type="hidden" name="recovered_on" value="{{ now()->toDateString() }}">
                                                        <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="download" icon-only title="নগদে ফেরত নিন / Cash repayment" />
                                                    </form>
                                                @endcan
                                            @endif
                                            @can('payroll.delete')
                                                @if ((float) $loan->recoveries_sum_amount <= 0)
                                                    <form method="POST" action="{{ route('payroll.loans.destroy', $loan) }}" class="delete-form" data-title="অগ্রিম/ঋণ মুছে ফেলবেন?" data-text="টাকা অ্যাকাউন্টে ফেরত যাবে।">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><x-core::table.empty icon="banknote" title="কোনো অগ্রিম বা ঋণ নেই" title-en="No advances or loans" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="margin-top:12px;">{{ $loans->links() }}</div>
        </div>
    </div>
</x-core::layout>
