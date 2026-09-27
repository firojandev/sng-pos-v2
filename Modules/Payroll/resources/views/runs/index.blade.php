<x-core::layout title="বেতন (পে-রোল)" title-en="Payroll" subtitle="মাসিক বেতন ও উৎসব ভাতা" subtitle-en="Monthly salary and festival bonus" active="payroll">
    <x-payroll::tabbar active="runs" />

    @php
        $statusColors = ['draft' => 'grey', 'unpaid' => 'gold', 'partial' => 'blue', 'paid' => 'green'];
        $statusLabels = ['draft' => 'খসড়া / Draft', 'unpaid' => 'অপরিশোধিত / Unpaid', 'partial' => 'আংশিক / Partial', 'paid' => 'পরিশোধিত / Paid'];
    @endphp

    <div style="display:grid; grid-template-columns:minmax(280px, 340px) 1fr; gap:16px; align-items:start; margin-top:16px;">
        @can('payroll.create')
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">নতুন পে-রোল</span><span class="en" style="display:none;">New Payroll</span></div></div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('payroll.runs.store') }}" style="display:flex; flex-direction:column; gap:12px;">
                        @csrf
                        <x-core::select size="sm" name="type" label="ধরন" label-en="Type" :required="true" :value="old('type', 'salary')"
                            :options="['salary' => 'মাসিক বেতন (Monthly Salary)', 'bonus' => 'উৎসব ভাতা (Festival Bonus)']" />
                        <x-core::input size="sm" type="month" name="month" label="মাস" label-en="Month" :value="old('month', $nextMonth)" :required="true" />
                        @if ($shops->isNotEmpty())
                            <x-core::select size="sm" name="shop_id" label="দোকান" label-en="Shop" :options="$shops->all()" :value="old('shop_id')" :required="true" placeholder="--" placeholder-en="--" />
                        @endif
                        <x-core::input size="sm" name="title" label="উৎসবের নাম (বোনাসের জন্য)" label-en="Festival (for a bonus)" placeholder="যেমন: ঈদুল ফিতর" placeholder-en="e.g. Eid-ul-Fitr" :value="old('title')" />
                        <x-core::input size="sm" name="note" label="নোট" label-en="Note" :value="old('note')" />
                        <p style="font-size:12px; color:var(--ink-500); margin:0;">
                            <span class="bn">এই দোকানের কর্মচারীদের হাজিরা, ওভারটাইম, ছুটি, পিএফ, আয়কর ও অগ্রিমের কিস্তি হিসাব করে পে-স্লিপ তৈরি হবে।</span>
                            <span class="en" style="display:none;">Payslips are worked out for this shop's staff from attendance, overtime, leave, PF, income tax and advance installments.</span>
                        </p>
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">তৈরি করুন</span><span class="en" style="display:none;">Create</span></x-core::button></div>
                    </form>
                </div>
            </div>
        @endcan

        <div class="table-container table-teal">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">পে-রোল</span><span class="en" style="display:none;">Payroll</span></th>
                            <th class="table-cell-center"><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Staff</span></th>
                            <th class="table-cell-right"><span class="bn">মোট প্রাপ্য</span><span class="en" style="display:none;">Earnings</span></th>
                            <th class="table-cell-right"><span class="bn">কর্তন</span><span class="en" style="display:none;">Deductions</span></th>
                            <th class="table-cell-right"><span class="bn">নিট বেতন</span><span class="en" style="display:none;">Net Pay</span></th>
                            <th class="table-cell-right"><span class="bn">পরিশোধিত</span><span class="en" style="display:none;">Paid</span></th>
                            <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                            <th class="table-cell-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($runs as $run)
                            @php $status = $run->paymentStatus(); @endphp
                            <tr>
                                <td style="font-weight:600;">
                                    {{ $run->label() }}
                                    <div style="font-size:11.5px; color:var(--ink-500); font-weight:400;">{{ $run->isBonus() ? 'উৎসব ভাতা / Bonus' : 'মাসিক বেতন / Salary' }}{{ $shops->isNotEmpty() ? ' · '.$run->shop?->name : '' }}</div>
                                </td>
                                <td class="table-cell-center">{{ $run->employees_count }}</td>
                                <td class="table-cell-right">{{ number_format((float) $run->earnings_total, 2) }}</td>
                                <td class="table-cell-right">{{ number_format((float) $run->deductions_total, 2) }}</td>
                                <td class="table-cell-right" style="font-weight:700;">{{ number_format((float) $run->net_total, 2) }}</td>
                                <td class="table-cell-right">{{ number_format((float) $run->paid_total, 2) }}</td>
                                <td class="table-cell-center"><x-core::badge :color="$statusColors[$status]" size="xs">{{ $statusLabels[$status] }}</x-core::badge></td>
                                <td class="table-cell-right">
                                    <x-core::button as="a" href="{{ route('payroll.runs.show', $run) }}" size="sm" variant="soft" color="primary" icon="eye" icon-only title="দেখুন / Open" />
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><x-core::table.empty icon="wallet" title="কোনো পে-রোল নেই" title-en="No payroll yet" description="বাম দিক থেকে মাস বেছে পে-রোল তৈরি করুন।" description-en="Pick a month on the left to create one." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="margin-top:12px;">{{ $runs->links() }}</div>
</x-core::layout>
