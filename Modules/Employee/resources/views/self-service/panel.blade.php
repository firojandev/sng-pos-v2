@php
    /** @var array<string, mixed> $selfService */
    $employee = $selfService['employee'];
    $clock = $selfService['clock'];
    $today = $selfService['today'];
    $shift = $employee->effectiveShift();
    $leaveStatus = [
        'pending' => ['gold', 'অপেক্ষমাণ / Pending'],
        'approved' => ['green', 'অনুমোদিত / Approved'],
        'rejected' => ['red', 'প্রত্যাখ্যাত / Rejected'],
        'cancelled' => ['grey', 'বাতিল / Cancelled'],
    ];
    $clockLabels = [
        'not_started' => ['আজ এখনো ক্লক ইন করেননি', 'Not clocked in yet'],
        'working' => ['কাজ চলছে', 'Working'],
        'done' => ['আজকের কাজ শেষ', 'Clocked out'],
    ];
@endphp

<div class="panel" style="margin-top:16px;">
    <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="panel-title"><span class="bn">আমার কর্মক্ষেত্র</span><span class="en" style="display:none;">My Workspace</span></div>
        <div style="font-size:12px; color:var(--ink-500);">{{ $employee->employee_code }} · {{ $employee->designation }}{{ $shift ? ' · '.$shift->name.' '.substr($shift->start_time, 0, 5).'–'.substr($shift->end_time, 0, 5) : '' }}</div>
    </div>
    <div class="panel-body">
        @error('clock')
            <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $message }}</div>
        @enderror
        @error('leave')
            <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $message }}</div>
        @enderror

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:16px; align-items:start;">
            @if ($selfService['canClock'])
                <div style="border:1px solid var(--border); border-radius:10px; padding:16px; background:var(--paper);">
                    <div style="font-size:12px; color:var(--ink-500);"><span class="bn">হাজিরা</span><span class="en" style="display:none;">Attendance</span> · {{ $clock['shift_date']->format('d M, Y') }}</div>
                    <div style="font-size:28px; font-weight:800; margin:6px 0; font-family:var(--font-mono, monospace);" id="self-service-clock">{{ now()->format('h:i A') }}</div>
                    <div style="font-size:13px; font-weight:600; color:{{ $clock['state'] === 'working' ? 'var(--green-ink)' : 'var(--ink-700)' }};">
                        <span class="bn">{{ $clockLabels[$clock['state']][0] }}</span><span class="en" style="display:none;">{{ $clockLabels[$clock['state']][1] }}</span>
                    </div>
                    <div style="font-size:12px; color:var(--ink-600); margin:6px 0 12px;">
                        <span class="bn">প্রবেশ</span><span class="en" style="display:none;">In</span>: {{ $clock['clocked_in_at']?->format('h:i A') ?? '—' }}
                        · <span class="bn">প্রস্থান</span><span class="en" style="display:none;">Out</span>: {{ $clock['clocked_out_at']?->format('h:i A') ?? '—' }}
                        @if ($today && $today->late_minutes > 0)
                            · <span style="color:var(--red-600);"><span class="bn">দেরি</span><span class="en" style="display:none;">Late</span> {{ $today->late_minutes }}m</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('my.clock') }}">
                        @csrf
                        @if ($clock['state'] === 'working')
                            <input type="hidden" name="direction" value="out">
                            <x-core::button type="submit" size="sm" variant="solid" color="danger" icon="log-out"><span class="bn">ক্লক আউট</span><span class="en" style="display:none;">Clock Out</span></x-core::button>
                        @else
                            <input type="hidden" name="direction" value="in">
                            <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="log-in"><span class="bn">ক্লক ইন</span><span class="en" style="display:none;">Clock In</span></x-core::button>
                        @endif
                    </form>
                    <div style="font-size:11.5px; color:var(--ink-500); margin-top:10px;"><span class="bn">এই মাসে উপস্থিত</span><span class="en" style="display:none;">Present this month</span>: <b>{{ $selfService['monthPresent'] }}</b></div>
                </div>
            @endif

            @if ($selfService['canApplyLeave'])
                <div style="border:1px solid var(--border); border-radius:10px; padding:16px;">
                    <div style="font-size:12px; font-weight:700; color:var(--ink-700); margin-bottom:10px;"><span class="bn">ছুটির ব্যালেন্স ({{ now()->year }})</span><span class="en" style="display:none;">Leave Balance ({{ now()->year }})</span></div>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:8px;">
                        @forelse ($selfService['balances'] as $balance)
                            <div style="border:1px solid var(--border); border-radius:8px; padding:8px 10px; background:var(--paper);">
                                <div style="font-size:11.5px; color:var(--ink-500);">{{ $balance['type']->code }}</div>
                                <div style="font-size:18px; font-weight:800;">{{ $balance['available'] }} <span style="font-size:11px; font-weight:500; color:var(--ink-500);">/ {{ $balance['entitled'] }}</span></div>
                                <div style="font-size:11px; color:var(--ink-500);" title="{{ $balance['type']->name }}">{{ \Illuminate\Support\Str::limit($balance['type']->name, 22) }}</div>
                            </div>
                        @empty
                            <x-core::table.empty icon="calendar" title="কোনো ছুটির ধরন নেই" title-en="No leave types" />
                        @endforelse
                    </div>
                </div>

                <div style="border:1px solid var(--border); border-radius:10px; padding:16px;">
                    <div style="font-size:12px; font-weight:700; color:var(--ink-700); margin-bottom:10px;"><span class="bn">নতুন ছুটির আবেদন</span><span class="en" style="display:none;">New Leave Application</span></div>
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
            @endif
        </div>

        @if ($selfService['canApplyLeave'])
            <div style="font-size:12px; font-weight:700; color:var(--ink-700); margin:16px 0 8px;"><span class="bn">আমার ছুটির আবেদন</span><span class="en" style="display:none;">My Leave Applications</span></div>
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
                            <tr>
                                <td>{{ $leave->leaveType?->name }}</td>
                                <td style="white-space:nowrap; font-size:12.5px;">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                                <td class="table-cell-center">{{ $leave->days }}</td>
                                <td style="font-size:12.5px;">
                                    {{ $leave->reason ?: '—' }}
                                    @if ($leave->attachment_path)
                                        <div><a href="{{ route('leave-requests.attachment', $leave) }}"><x-core::icon name="file" size="xs" /> {{ $leave->attachment_name }}</a></div>
                                    @endif
                                    @if ($leave->decision_note && $leave->status !== 'pending')
                                        <div style="color:var(--ink-500);">{{ $leave->decision_note }}</div>
                                    @endif
                                </td>
                                <td class="table-cell-center"><x-core::badge :color="$leaveStatus[$leave->status][0] ?? 'grey'" size="xs">{{ $leaveStatus[$leave->status][1] ?? $leave->status }}</x-core::badge></td>
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
        @endif
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            setInterval(function () {
                $('#self-service-clock').text(new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }));
            }, 15000);
        });
    </script>
@endpush
