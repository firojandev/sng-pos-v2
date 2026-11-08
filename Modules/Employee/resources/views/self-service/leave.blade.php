<x-core::layout title="আমার ছুটি" title-en="My Leave" subtitle="ছুটির ব্যালেন্স, আবেদন ও তার অবস্থা" subtitle-en="Leave balance, applications and their status" active="my-leave">
    @php
        $employee = $selfService['employee'];
        $leaveStatus = [
            'pending' => ['gold', 'অপেক্ষমাণ', 'Pending'],
            'approved' => ['green', 'অনুমোদিত', 'Approved'],
            'rejected' => ['red', 'প্রত্যাখ্যাত', 'Rejected'],
            'cancelled' => ['grey', 'বাতিল', 'Cancelled'],
        ];
    @endphp

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-bottom:12px;">{{ $errors->first() }}</div>
    @endif

    <div class="panel" style="margin-top:0;">
        <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
            <div class="panel-title"><span class="bn">ছুটির ব্যালেন্স ({{ now()->year }})</span><span class="en" style="display:none;">Leave Balance ({{ now()->year }})</span></div>
            <div style="font-size:12px; color:var(--ink-500);">{{ $employee->name }} · {{ $employee->employee_code }}</div>
        </div>
        <div class="panel-body">
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:10px;">
                @forelse ($selfService['balances'] as $balance)
                    <div style="border:1px solid var(--border); border-radius:10px; padding:12px; background:var(--paper);">
                        <div style="font-size:12px; font-weight:600; color:var(--ink-700);">{{ $balance['type']->name }}</div>
                        <div style="font-size:26px; font-weight:800; margin:4px 0;">{{ $balance['available'] }} <span style="font-size:12px; font-weight:500; color:var(--ink-500);"><span class="bn">দিন বাকি</span><span class="en" style="display:none;">days left</span></span></div>
                        <div style="font-size:11.5px; color:var(--ink-500);">
                            <span class="bn">প্রাপ্য</span><span class="en" style="display:none;">Entitled</span> {{ $balance['entitled'] }}
                            · <span class="bn">নেওয়া</span><span class="en" style="display:none;">Taken</span> {{ $balance['used'] }}
                            @if ($balance['carried'] > 0)
                                · <span class="bn">আগের বছরের</span><span class="en" style="display:none;">Carried</span> {{ $balance['carried'] }}
                            @endif
                        </div>
                    </div>
                @empty
                    <x-core::table.empty icon="calendar" title="কোনো ছুটির ধরন নেই" title-en="No leave types" />
                @endforelse
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:minmax(300px, 380px) 1fr; gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">নতুন আবেদন</span><span class="en" style="display:none;">New Application</span></div></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('my.leave.store') }}" enctype="multipart/form-data" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    @csrf
                    <div style="grid-column:1 / -1;">
                        <x-core::select size="sm" name="leave_type_id" label="ছুটির ধরন" label-en="Leave Type" :required="true" :value="old('leave_type_id')" placeholder="--" placeholder-en="--"
                            :options="$selfService['leaveTypes']->pluck('name', 'id')->all()" />
                    </div>
                    <x-core::input size="sm" type="date" name="from_date" label="থেকে" label-en="From" :value="old('from_date')" :required="true" />
                    <x-core::input size="sm" type="date" name="to_date" label="পর্যন্ত" label-en="To" :value="old('to_date')" :required="true" />
                    <div style="grid-column:1 / -1;"><x-core::input size="sm" name="reason" label="কারণ" label-en="Reason" :value="old('reason')" /></div>
                    <div style="grid-column:1 / -1;"><x-core::input size="sm" type="file" name="attachment" label="সংযুক্তি (ঐচ্ছিক, ৫ MB)" label-en="Attachment (optional, 5 MB)" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" /></div>
                    <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="send"><span class="bn">আবেদন করুন</span><span class="en" style="display:none;">Apply</span></x-core::button></div>
                </form>
            </div>
        </div>

        <div class="table-container table-teal">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                            <th><span class="bn">সময়কাল</span><span class="en" style="display:none;">Period</span></th>
                            <th class="table-cell-center"><span class="bn">দিন</span><span class="en" style="display:none;">Days</span></th>
                            <th><span class="bn">কারণ</span><span class="en" style="display:none;">Reason</span></th>
                            <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                            <th class="table-cell-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($selfService['leaveRequests'] as $leave)
                            @php [$color, $bn, $en] = $leaveStatus[$leave->status] ?? ['grey', $leave->status, $leave->status]; @endphp
                            <tr>
                                <td>{{ $leave->leaveType?->name }}</td>
                                <td style="white-space:nowrap; font-size:12.5px;">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                                <td class="table-cell-center">{{ $leave->days }}</td>
                                <td style="font-size:12.5px;">
                                    {{ $leave->reason ?: '—' }}
                                    @if ($leave->attachment_path)
                                        <div><a href="{{ route('leave-requests.attachment', $leave) }}"><x-core::icon name="file" size="xs" /> {{ $leave->attachment_name }}</a></div>
                                    @endif
                                </td>
                                <td class="table-cell-center">
                                    <x-core::badge :color="$color" size="xs"><span class="bn">{{ $bn }}</span><span class="en" style="display:none;">{{ $en }}</span></x-core::badge>
                                    @if ($leave->status !== 'pending' && ($leave->decider || $leave->decision_note))
                                        <div style="font-size:11px; color:var(--ink-500); margin-top:3px;">{{ $leave->decider?->name }}{{ $leave->decision_note ? ' · '.$leave->decision_note : '' }}</div>
                                    @endif
                                </td>
                                <td class="table-cell-right">
                                    @if ($leave->status === 'pending')
                                        <form method="POST" action="{{ route('my.leave.cancel', $leave) }}" class="delete-form" data-title="আবেদন প্রত্যাহার করবেন?">
                                            @csrf
                                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="x" icon-only title="প্রত্যাহার / Withdraw" />
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-core::table.empty icon="calendar" title="কোনো ছুটির আবেদন নেই" title-en="No leave applications" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layout>
