<x-core::layout
    :title="$canManage ? 'দৈনিক কাজের রিপোর্ট' : 'আমার কাজের রিপোর্ট'"
    :title-en="$canManage ? 'Daily Work Report' : 'My Work Report'"
    :subtitle="$canManage ? 'কর্মচারীর হাজিরা ও টাস্ক — তারিখ অনুযায়ী' : 'আমার হাজিরা ও টাস্ক — তারিখ অনুযায়ী'"
    :subtitle-en="$canManage ? 'Employee attendance and tasks, day by day' : 'My attendance and tasks, day by day'"
    active="hr-reporting"
>
    @php
        $statuses = \Modules\Task\Models\Task::statusLabels();
        $priorities = \Modules\Task\Models\Task::priorityLabels();
        $query = ['from' => $from->toDateString(), 'to' => $to->toDateString()] + ($canManage ? ['employee_id' => $employeeId] : []);
    @endphp

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-top:12px;">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('tasks.report') }}" style="display:flex; align-items:flex-end; flex-wrap:wrap; gap:8px; margin:16px 0 12px;">
        @if ($canManage)
            <div style="width:280px;">
                <x-core::searchable-select size="sm" :no-margin="true" name="employee_id" label="কর্মচারী" label-en="Employee" placeholder="নাম বা কোড দিয়ে খুঁজুন" placeholder-en="Search by name or code"
                    :options="['' => 'সব কর্মচারী (All employees)'] + $employees->all()" :value="(string) $employeeId" />
            </div>
        @endif
        <div style="width:160px;"><x-core::input size="sm" :no-margin="true" type="date" name="from" label="তারিখ হতে" label-en="From" :value="$from->toDateString()" /></div>
        <div style="width:160px;"><x-core::input size="sm" :no-margin="true" type="date" name="to" label="তারিখ পর্যন্ত" label-en="To" :value="$to->toDateString()" /></div>
        <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Show</span></x-core::button>
        <x-core::button as="a" href="{{ route('tasks.report.export', $query) }}" size="sm" color="primary" icon="download"><span class="bn">এক্সেল এক্সপোর্ট</span><span class="en" style="display:none;">Export Excel</span></x-core::button>
    </form>

    <p style="font-size:12px; color:var(--ink-400); margin:0 0 10px;">
        <span class="bn">একটি টাস্ক সেই দিনগুলোতে দেখায় যেদিন এটি তৈরি হয়েছে, শেষ সময় ছিল, সম্পন্ন হয়েছে, অথবা চলমান ছিল। একবারে সর্বোচ্চ {{ \Modules\Task\Services\DailyWorkReport::MAX_DAYS }} দিন।</span>
        <span class="en" style="display:none;">A task is listed on the days it was created, due, finished, or in progress. At most {{ \Modules\Task\Services\DailyWorkReport::MAX_DAYS }} days at a time.</span>
    </p>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th style="width:50px;">SL</th>
                        <th><span class="bn">কর্মচারী আইডি</span><span class="en" style="display:none;">Employee Id</span></th>
                        <th><span class="bn">কর্মচারীর নাম</span><span class="en" style="display:none;">Employee Name</span></th>
                        <th><span class="bn">তারিখ</span><span class="en" style="display:none;">Date</span></th>
                        <th><span class="bn">প্রবেশ</span><span class="en" style="display:none;">Clock In</span></th>
                        <th><span class="bn">প্রস্থান</span><span class="en" style="display:none;">Clock Out</span></th>
                        <th><span class="bn">কাজের সময়</span><span class="en" style="display:none;">Worked Hours</span></th>
                        <th><span class="bn">টাস্কের বিবরণ</span><span class="en" style="display:none;">Tasks Details</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="white-space:nowrap; font-size:12.5px;">{{ $row['employee_code'] ?? '—' }}</td>
                            <td style="font-weight:600;">{{ $row['employee_name'] }}</td>
                            <td style="white-space:nowrap; font-size:12.5px;">{{ $row['date']->format('d M, Y') }}</td>
                            <td style="white-space:nowrap; font-size:12.5px;">{{ $row['check_in']?->format('h:i A') ?? '—' }}</td>
                            <td style="white-space:nowrap; font-size:12.5px;">{{ $row['check_out']?->format('h:i A') ?? '—' }}</td>
                            <td style="white-space:nowrap; font-size:12.5px;">{{ \Modules\Task\Services\DailyWorkReport::hours($row['worked_minutes']) ?: '—' }}</td>
                            <td>
                                @forelse ($row['tasks'] as $task)
                                    @php($priority = $priorities[$task->priority] ?? $priorities['medium'])
                                    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:6px; padding:2px 0; font-size:12.5px;">
                                        <span style="color:var(--ink-900); font-weight:600;">{{ $task->title }}</span>
                                        <x-core::badge :color="$priority['color']" size="xs" :label="$priority['bn']" :label-en="$priority['en']" />
                                        <x-core::badge :color="$statuses[$task->status]['color']" size="xs" :label="$statuses[$task->status]['bn']" :label-en="$statuses[$task->status]['en']" />
                                        @if ($task->deadline)
                                            <span style="color:var(--ink-400); font-size:11.5px;">{{ $task->deadline->format('d M, Y') }}</span>
                                        @endif
                                    </div>
                                    @if ($task->description)
                                        <div style="font-size:12px; color:var(--ink-600); max-width:560px; white-space:pre-line; margin-bottom:6px;">{{ $task->description }}</div>
                                    @endif
                                @empty
                                    <span style="color:var(--ink-400);">—</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-core::table.empty icon="file-text" title="এই সময়ে কোনো হাজিরা বা টাস্ক নেই" title-en="No attendance or tasks in this period" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
