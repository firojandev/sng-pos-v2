<x-core::layout title="দৈনিক হাজিরা" title-en="Daily Attendance" subtitle="{{ $date->format('d M, Y') }}" subtitle-en="{{ $date->format('d M, Y') }}" active="attendance">
    <x-employee::tabbar active="attendance" />

    @php
        $statusOptions = collect(\Modules\Employee\Models\Attendance::statusLabels())->mapWithKeys(fn ($label, $key) => [$key => $label['bn'].' ('.$label['en'].')'])->all();
        $minutes = fn ($value) => $value ? intdiv($value, 60).'h '.($value % 60).'m' : '—';
        $canClock = $date->isToday() && auth()->user()->can('attendance.create');
    @endphp

    <div class="section-row" style="margin:16px 0; display:flex; align-items:flex-end; gap:8px; flex-wrap:wrap;">
        <form method="GET" style="display:flex; align-items:flex-end; gap:8px;">
            <div style="width:170px;"><x-core::input size="sm" type="date" name="date" label="তারিখ" label-en="Date" :value="$date->toDateString()" max="{{ now()->toDateString() }}" /></div>
            <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Show</span></x-core::button>
        </form>
        @can('attendance.edit')
            <form method="POST" action="{{ route('attendance.process') }}" style="margin-left:auto;">
                @csrf
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="refresh" title="যাদের এন্ট্রি নেই তাদের অনুপস্থিত/ছুটি হিসেবে চিহ্নিত করুন / Mark everyone without an entry">
                    <span class="bn">বাকিদের হিসাব করুন</span><span class="en" style="display:none;">Fill Remaining</span>
                </x-core::button>
            </form>
        @endcan
    </div>

    <form method="POST" action="{{ route('attendance.save') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
        <div class="table-container table-teal">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                            <th style="width:120px;"><span class="bn">প্রবেশ</span><span class="en" style="display:none;">In</span></th>
                            <th style="width:120px;"><span class="bn">প্রস্থান</span><span class="en" style="display:none;">Out</span></th>
                            <th style="width:190px;"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                            <th><span class="bn">দেরি / ওভারটাইম / কাজ</span><span class="en" style="display:none;">Late / Overtime / Worked</span></th>
                            <th><span class="bn">নোট</span><span class="en" style="display:none;">Note</span></th>
                            @if ($canClock)
                                <th class="table-cell-right"><span class="bn">ক্লক</span><span class="en" style="display:none;">Clock</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $index => $employee)
                            @php $record = $records[$employee->id] ?? null; @endphp
                            <tr>
                                <td>
                                    <input type="hidden" name="rows[{{ $index }}][employee_id]" value="{{ $employee->id }}">
                                    <div style="font-weight:600;">{{ $employee->name }}</div>
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $employee->employee_code }} · {{ $employee->designation }}</div>
                                </td>
                                <td><x-core::input size="sm" :no-margin="true" type="time" name="rows[{{ $index }}][check_in]" :value="$record?->check_in?->format('H:i')" /></td>
                                <td><x-core::input size="sm" :no-margin="true" type="time" name="rows[{{ $index }}][check_out]" :value="$record?->check_out?->format('H:i')" /></td>
                                <td>
                                    <x-core::select size="sm" :no-margin="true" name="rows[{{ $index }}][status]" :options="$statusOptions" :value="$record && ! $record->check_in ? $record->status : null" placeholder="— সময় থেকে —" placeholder-en="— from times —" />
                                    @if ($record)
                                        <div style="font-size:11px; color:var(--ink-500); margin-top:2px;">{{ \Modules\Employee\Models\Attendance::statusLabels()[$record->status]['bn'] ?? $record->status }}</div>
                                    @endif
                                </td>
                                <td style="font-size:12px; white-space:nowrap;">
                                    @if ($record)
                                        {{ $minutes($record->late_minutes) }} / {{ $minutes($record->overtime_minutes) }} / {{ $minutes($record->worked_minutes) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><x-core::input size="sm" :no-margin="true" name="rows[{{ $index }}][note]" :value="$record?->note" /></td>
                                @if ($canClock)
                                    <td class="table-cell-right" style="white-space:nowrap;">
                                        @if (($clockStates[$employee->id] ?? 'not_started') === 'working')
                                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="log-out" formaction="{{ route('attendance.clock', [$employee, 'out']) }}" formnovalidate><span class="bn">ক্লক আউট</span><span class="en" style="display:none;">Clock Out</span></x-core::button>
                                        @else
                                            <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="log-in" formaction="{{ route('attendance.clock', [$employee, 'in']) }}" formnovalidate><span class="bn">ক্লক ইন</span><span class="en" style="display:none;">Clock In</span></x-core::button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-core::table.empty icon="users" title="এই দোকানে কোনো সক্রিয় কর্মচারী নেই" title-en="No active employees at this shop" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @can('attendance.create')
            @if ($employees->isNotEmpty())
                <div style="margin-top:12px;">
                    <x-core::button type="submit" size="sm" variant="solid" color="primary"><span class="bn">হাজিরা সংরক্ষণ করুন</span><span class="en" style="display:none;">Save Attendance</span></x-core::button>
                </div>
            @endif
        @endcan
    </form>
</x-core::layout>
