<x-core::layout title="মাসিক হাজিরা" title-en="Monthly Attendance" subtitle="{{ $month->format('F Y') }}" subtitle-en="{{ $month->format('F Y') }}" active="attendance">
    <x-employee::tabbar active="monthly" />

    <form method="GET" class="section-row" style="margin:16px 0; display:flex; align-items:flex-end; gap:8px;">
        <div style="width:170px;"><x-core::input size="sm" type="month" name="month" label="মাস" label-en="Month" :value="$month->format('Y-m')" /></div>
        <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Show</span></x-core::button>
    </form>

    @php
        $columns = ['present' => 'উপস্থিত / Present', 'late' => 'দেরি / Late', 'half_day' => 'অর্ধদিবস / Half', 'absent' => 'অনুপস্থিত / Absent', 'leave' => 'ছুটি / Leave', 'holiday' => 'সরকারি / Holiday', 'weekend' => 'সাপ্তাহিক / Weekend'];
    @endphp

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                        @foreach ($columns as $label)
                            <th class="table-cell-center" style="font-size:11.5px;">{{ $label }}</th>
                        @endforeach
                        <th class="table-cell-right"><span class="bn">দেরি</span><span class="en" style="display:none;">Late</span></th>
                        <th class="table-cell-right"><span class="bn">ওভারটাইম</span><span class="en" style="display:none;">Overtime</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        @php $row = $summary[$employee->id] ?? null; @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $employee->name }}</div>
                                <div style="font-size:11.5px; color:var(--ink-500);">{{ $employee->employee_code }}</div>
                            </td>
                            @foreach (array_keys($columns) as $status)
                                <td class="table-cell-center">{{ $row['counts'][$status] ?? 0 }}</td>
                            @endforeach
                            <td class="table-cell-right" style="font-size:12px;">{{ intdiv($row['late_minutes'] ?? 0, 60) }}h {{ ($row['late_minutes'] ?? 0) % 60 }}m</td>
                            <td class="table-cell-right" style="font-size:12px;">{{ intdiv($row['overtime_minutes'] ?? 0, 60) }}h {{ ($row['overtime_minutes'] ?? 0) % 60 }}m</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) + 3 }}"><x-core::table.empty icon="users" title="কোনো সক্রিয় কর্মচারী নেই" title-en="No active employees" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
